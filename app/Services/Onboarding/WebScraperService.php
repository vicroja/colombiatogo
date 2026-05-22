<?php

namespace App\Services\Onboarding;

/**
 * WebScraperService — scraping real de páginas web.
 *
 * Hace fetch HTTP, extrae texto limpio del HTML y recolecta URLs de
 * imágenes candidatas. Alimenta al HotelExtractorService con contenido
 * real (no solo la URL pelada, que Gemini no resuelve por sí solo).
 *
 * Reutilizado tal cual del proyecto Guasapp — es agnóstico al dominio.
 */
class WebScraperService
{
    private const TIMEOUT_SECONDS = 15;
    private const MAX_HTML_BYTES  = 2 * 1024 * 1024; // 2MB por página
    private const MAX_TEXT_CHARS  = 30000;            // texto que mandamos a Gemini
    private const MAX_IMAGES      = 40;               // imágenes candidatas por página
    private const USER_AGENT      = 'Mozilla/5.0 (compatible; PmsImportBot/1.0)';

    /**
     * Descarga una página y devuelve texto + URLs de imágenes.
     */
    public function fetch(string $url): array
    {
        $url = trim($url);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->errorResult($url, 'URL inválida.');
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => self::TIMEOUT_SECONDS,
            CURLOPT_USERAGENT      => self::USER_AGENT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: es-ES,es;q=0.9,en;q=0.8',
            ],
        ]);

        $html     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) ?: $url;
        $error    = curl_error($ch);
        curl_close($ch);

        if ($html === false || $httpCode >= 400) {
            log_message('warning', "[WebScraper] {$url} → HTTP {$httpCode} | curl: {$error}");
            return $this->errorResult($url, "No pudimos descargar la página (HTTP {$httpCode}).");
        }

        if (strlen($html) > self::MAX_HTML_BYTES) {
            $html = substr($html, 0, self::MAX_HTML_BYTES);
        }

        $title    = $this->extractTitle($html);
        $metaDesc = $this->extractMetaDescription($html);
        $images   = $this->extractImageUrls($html, $finalUrl);
        $text     = $this->htmlToCleanText($html);

        if (strlen($text) > self::MAX_TEXT_CHARS) {
            $text = substr($text, 0, self::MAX_TEXT_CHARS) . "\n\n[...texto truncado...]";
        }

        log_message('info', "[WebScraper] OK {$url} | " . strlen($text) . " chars | " . count($images) . " imgs");

        return [
            'success'          => true,
            'url'              => $finalUrl,
            'text'             => $text,
            'images'           => $images,
            'title'            => $title,
            'meta_description' => $metaDesc,
            'error'            => null,
        ];
    }

    /**
     * Descarga varias URLs y concatena resultados.
     */
    public function fetchMany(array $urls): array
    {
        $pages     = [];
        $errors    = [];
        $combined  = '';
        $allImages = [];

        foreach ($urls as $url) {
            $page = $this->fetch($url);
            $pages[] = $page;

            if (!$page['success']) {
                $errors[] = $url;
                continue;
            }

            $combined .= "\n\n========================================\n";
            $combined .= "FUENTE: {$page['url']}\n";
            if ($page['title'])            $combined .= "TÍTULO: {$page['title']}\n";
            if ($page['meta_description']) $combined .= "DESCRIPCIÓN: {$page['meta_description']}\n";
            $combined .= "========================================\n\n";
            $combined .= $page['text'];

            $allImages = array_merge($allImages, $page['images']);
        }

        $allImages = array_values(array_unique($allImages));

        return [
            'success'       => count($pages) > count($errors),
            'combined_text' => trim($combined),
            'all_images'    => $allImages,
            'pages'         => $pages,
            'errors'        => $errors,
        ];
    }

    /**
     * Descarga una imagen y la guarda en disco. Devuelve el filename o null.
     */
    public function downloadImage(string $imageUrl, string $destDir, string $prefix = 'img'): ?string
    {
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return null;
        }
        if (!is_dir($destDir) && !mkdir($destDir, 0777, true) && !is_dir($destDir)) {
            log_message('error', "[WebScraper] No se pudo crear directorio: {$destDir}");
            return null;
        }

        $ch = curl_init($imageUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_USERAGENT      => self::USER_AGENT,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $data     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $mime     = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($data === false || $httpCode >= 400 || empty($data)) {
            return null;
        }

        $ext = match (true) {
            str_contains($mime, 'jpeg'), str_contains($mime, 'jpg') => 'jpg',
            str_contains($mime, 'png')                              => 'png',
            str_contains($mime, 'webp')                             => 'webp',
            str_contains($mime, 'gif')                              => 'gif',
            default                                                 => null,
        };
        if (!$ext) return null;

        // Filtro de tamaño mínimo: descarta imágenes < 5KB (probablemente iconos)
        if (strlen($data) < 5000) return null;

        $filename = $prefix . '_' . uniqid() . '.' . $ext;
        $fullPath = rtrim($destDir, '/') . '/' . $filename;

        if (file_put_contents($fullPath, $data) === false) {
            return null;
        }

        return $filename;
    }

    // ───────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ───────────────────────────────────────────────────────────────────

    private function errorResult(string $url, string $msg): array
    {
        return [
            'success'          => false,
            'url'              => $url,
            'text'             => '',
            'images'           => [],
            'title'            => null,
            'meta_description' => null,
            'error'            => $msg,
        ];
    }

    private function extractTitle(string $html): ?string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
            return trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8'));
        }
        return null;
    }

    private function extractMetaDescription(string $html): ?string
    {
        if (preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/is', $html, $m)) {
            return trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
        }
        if (preg_match('/<meta\s+property=["\']og:description["\']\s+content=["\'](.*?)["\']/is', $html, $m)) {
            return trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
        }
        return null;
    }

    private function extractImageUrls(string $html, string $baseUrl): array
    {
        $images = [];

        if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
            foreach ($matches[1] as $src) {
                $abs = $this->resolveUrl($src, $baseUrl);
                if ($abs && $this->isLikelyContentImage($abs)) $images[] = $abs;
            }
        }

        if (preg_match_all('/<img[^>]+data-src=["\']([^"\']+)["\']/i', $html, $matches)) {
            foreach ($matches[1] as $src) {
                $abs = $this->resolveUrl($src, $baseUrl);
                if ($abs && $this->isLikelyContentImage($abs)) $images[] = $abs;
            }
        }

        if (preg_match('/<meta\s+property=["\']og:image["\']\s+content=["\'](.*?)["\']/i', $html, $m)) {
            $abs = $this->resolveUrl($m[1], $baseUrl);
            if ($abs) array_unshift($images, $abs);
        }

        return array_values(array_unique(array_slice($images, 0, self::MAX_IMAGES)));
    }

    private function isLikelyContentImage(string $url): bool
    {
        $lower = strtolower($url);
        $blacklist = ['icon', 'sprite', 'spacer', 'pixel.gif', 'blank.gif', 'whatsapp.svg', 'facebook.svg', 'instagram.svg', 'tiktok.svg', '/emoji/', 'favicon'];
        foreach ($blacklist as $bad) {
            if (str_contains($lower, $bad)) return false;
        }
        return (bool)preg_match('/\.(jpg|jpeg|png|webp|gif)(\?.*)?$/i', $lower)
            || str_contains($lower, '/uploads/')
            || str_contains($lower, '/wp-content/')
            || str_contains($lower, '/media/')
            || str_contains($lower, 'cloudinary.com')
            || str_contains($lower, 'cloudfront.net');
    }

    private function resolveUrl(string $url, string $base): ?string
    {
        if (empty($url)) return null;
        if (str_starts_with($url, 'data:')) return null;
        if (filter_var($url, FILTER_VALIDATE_URL)) return $url;
        if (str_starts_with($url, '//')) {
            $scheme = parse_url($base, PHP_URL_SCHEME) ?: 'https';
            return $scheme . ':' . $url;
        }
        $parts = parse_url($base);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) return null;
        $origin = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
        if (str_starts_with($url, '/')) return $origin . $url;
        $path = $parts['path'] ?? '/';
        $dir  = substr($path, 0, strrpos($path, '/') + 1);
        return $origin . $dir . $url;
    }

    private function htmlToCleanText(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is',     ' ', $html);
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is',       ' ', $html);
        $html = preg_replace('/<noscript\b[^>]*>.*?<\/noscript>/is', ' ', $html);
        $html = preg_replace('/<svg\b[^>]*>.*?<\/svg>/is',           ' ', $html);

        $html = preg_replace('/<h([1-6])[^>]*>(.*?)<\/h\1>/is', "\n\n### $2 ###\n\n", $html);
        $html = preg_replace('/<li[^>]*>(.*?)<\/li>/is', "\n• $1", $html);
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $html = preg_replace('/<\/(p|div|tr|h[1-6])>/i', "\n", $html);

        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $text);

        return trim($text);
    }
}
