<?php

namespace App\Controllers;

use App\Models\GeminiModel;

class Calculadora extends BaseController
{
    protected GeminiModel $gemini;

    public function __construct()
    {
        $this->gemini = new GeminiModel();
    }

    // ──────────────────────────────────────────────────────────────
    // GET /calculadora  →  muestra el formulario / vista
    // ──────────────────────────────────────────────────────────────
    public function index(): string
    {
        return view('calculadora');
    }

    // ──────────────────────────────────────────────────────────────
    // POST /calculadora/analizar  →  crawl + Gemini + JSON response
    // ──────────────────────────────────────────────────────────────
    public function analizar()
    {
        /* ── 1. Validación básica ───────────────────────────────── */
        $rules = [
            'website'              => 'required|valid_url',
            'nombre_hotel'         => 'required|min_length[2]',
            'whatsapp_business'    => 'required',
            'whatsapp_automatico'  => 'required',
            'precio_reserva'       => 'required|numeric',
            'quien_atiende'        => 'required',
            'volumen_whatsapp'     => 'required',
            'tiene_pms'            => 'required',
        ];

        if (! $this->validate($rules)) {
            return $this->jsonError('Datos incompletos. Verifica el formulario.', 422);
        }

        $data = $this->request->getPost();

        /* ── 2. Crawl del sitio web ────────────────────────────── */
        $crawlResult = $this->crawlSite($data['website']);

        if (! $crawlResult['success']) {
            // No bloqueamos el diagnóstico si el crawl falla — seguimos sin contexto web
            log_message('warning', "[Calculadora] Crawl fallido para {$data['website']}: {$crawlResult['message']}");
            $crawlResult['text'] = '';
            $crawlResult['pages_crawled'] = 0;
        }

        /* ── 3. Construir prompt y llamar a Gemini ─────────────── */
        $systemInstruction = $this->buildSystemPrompt();
        $userMessage       = $this->buildUserMessage($data, $crawlResult);

        $history = [
            ['role' => 'user', 'parts' => [['text' => $userMessage]]]
        ];

        $geminiResponse = $this->gemini->generateChatResponse($history, $systemInstruction, 'gemini-2.5-flash');

        if (isset($geminiResponse['error'])) {
            return $this->jsonError('Error al generar el diagnóstico. Intenta de nuevo.', 503);
        }

        /* ── 4. Limpiar y decodificar el JSON de Gemini ─────────── */
        $cleanJson = $this->gemini->cleanJsonResponse($geminiResponse['text']);
        $resultado = json_decode($cleanJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', "[Calculadora] JSON inválido de Gemini: " . substr($cleanJson, 0, 500));
            return $this->jsonError('Error procesando el diagnóstico. Intenta de nuevo.', 500);
        }

        /* ── 5. Enriquecer con metadata ─────────────────────────── */
        $resultado['meta'] = [
            'hotel'          => $data['nombre_hotel'],
            'website'        => $data['website'],
            'pages_crawled'  => $crawlResult['pages_crawled'] ?? 0,
            'generated_at'   => date('Y-m-d H:i:s'),
        ];

        /* ── 6. Log interno (notificación al equipo) ────────────── */
        $this->logDiagnostico($data, $resultado);

        return $this->response
            ->setStatusCode(200)
            ->setContentType('application/json')
            ->setBody(json_encode(['success' => true, 'diagnostico' => $resultado]));
    }

    // ──────────────────────────────────────────────────────────────
    // PRIVADOS
    // ──────────────────────────────────────────────────────────────

    /**
     * Crawlea homepage + links internos de 1 nivel.
     * Máx 8 páginas, timeout total ~20s.
     */
    private function crawlSite(string $url): array
    {
        $url = $this->normalizeUrl($url);

        $client  = \Config\Services::curlrequest();
        $visited = [];
        $texts   = [];
        $queue   = [$url];
        $base    = $this->getBaseUrl($url);
        $maxPages = 8;

        while (! empty($queue) && count($visited) < $maxPages) {
            $current = array_shift($queue);

            if (in_array($current, $visited)) continue;
            $visited[] = $current;

            try {
                $response = $client->get($current, [
                    'http_errors' => false,
                    'timeout'     => 8,
                    'headers'     => [
                        'User-Agent' => 'Mozilla/5.0 (compatible; TentiiBot/1.0; +https://tentii.com)',
                        'Accept'     => 'text/html,application/xhtml+xml',
                    ],
                    'allow_redirects' => ['max' => 3],
                ]);

                if ($response->getStatusCode() !== 200) continue;

                $html = $response->getBody();

                // Extraer texto limpio
                $text = $this->extractText($html);
                if (strlen($text) > 100) {
                    $texts[$current] = mb_substr($text, 0, 3000); // máx 3k chars por página
                }

                // Solo buscamos links en la homepage (primer elemento visitado)
                if (count($visited) === 1) {
                    $links = $this->extractInternalLinks($html, $base, $url);
                    foreach ($links as $link) {
                        if (! in_array($link, $visited) && ! in_array($link, $queue)) {
                            $queue[] = $link;
                        }
                    }
                }

            } catch (\Exception $e) {
                log_message('warning', "[Calculadora/Crawl] Error en {$current}: " . $e->getMessage());
            }
        }

        if (empty($texts)) {
            return ['success' => false, 'message' => 'No se pudo leer el sitio web.', 'pages_crawled' => 0, 'text' => ''];
        }

        // Ensamblar texto final con separadores por página
        $fullText = '';
        foreach ($texts as $pageUrl => $pageText) {
            $fullText .= "\n\n--- Página: {$pageUrl} ---\n" . $pageText;
        }

        return [
            'success'       => true,
            'text'          => mb_substr($fullText, 0, 18000), // límite total al prompt
            'pages_crawled' => count($texts),
        ];
    }

    private function extractText(string $html): string
    {
        // Eliminar scripts, estilos, nav, footer, svg
        $html = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $html);
        $html = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $html);
        $html = preg_replace('/<nav[^>]*>.*?<\/nav>/si', '', $html);
        $html = preg_replace('/<footer[^>]*>.*?<\/footer>/si', '', $html);
        $html = preg_replace('/<svg[^>]*>.*?<\/svg>/si', '', $html);
        $html = preg_replace('/<form[^>]*>.*?<\/form>/si', '', $html);
        $html = preg_replace('/<!--.*?-->/si', '', $html);

        // Convertir algunos tags a saltos de línea
        $html = preg_replace('/<(br|p|h[1-6]|li|div|section|article)[^>]*>/i', "\n", $html);

        $text = strip_tags($html);

        // Limpiar espacios y líneas vacías
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        return trim($text);
    }

    private function extractInternalLinks(string $html, string $base, string $currentUrl): array
    {
        preg_match_all('/<a[^>]+href=["\']([^"\'#?]+)["\'][^>]*>/i', $html, $matches);

        $links = [];
        $skip  = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'zip', 'css', 'js', 'xml', 'ico'];

        foreach ($matches[1] as $href) {
            $href = trim($href);
            if (empty($href) || $href === '/') continue;

            // Saltar archivos de media y recursos
            $ext = strtolower(pathinfo(parse_url($href, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            if (in_array($ext, $skip)) continue;

            // Construir URL absoluta
            if (str_starts_with($href, 'http')) {
                $absolute = $href;
            } elseif (str_starts_with($href, '/')) {
                $absolute = $base . $href;
            } else {
                continue; // relativas complejas — saltar
            }

            // Solo links del mismo dominio
            if (str_starts_with($absolute, $base) && $absolute !== $currentUrl) {
                $links[] = rtrim($absolute, '/');
            }
        }

        return array_unique(array_slice($links, 0, 20)); // máx 20 candidatos
    }

    private function normalizeUrl(string $url): string
    {
        $url = trim($url);
        if (! str_starts_with($url, 'http')) {
            $url = 'https://' . $url;
        }
        return rtrim($url, '/');
    }

    private function getBaseUrl(string $url): string
    {
        $parsed = parse_url($url);
        return ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');
    }

    private function buildSystemPrompt(): string
    {
        return <<<PROMPT
Eres el motor de diagnóstico de Tentii, un PMS con IA para hoteles y operadores de tours en Latinoamérica.

Tu rol es analizar la información de un hotel (respuestas de formulario + contenido de su sitio web) y generar un diagnóstico de oportunidad honesto, específico y accionable.

REGLAS ESTRICTAS:
1. Devuelve ÚNICAMENTE un objeto JSON válido. Cero texto fuera del JSON.
2. Sé específico: menciona cosas reales que encontraste en el sitio web del hotel.
3. Sé honesto: si el hotel ya está bien en algo, dilo. No infles el problema.
4. El score_global es un número entero de 0 a 100. Un score bajo = mucha oportunidad para Tentii. Un score alto = el hotel ya está avanzado.
5. Las dimensiones tienen score de 0 a 100 cada una.
6. Los hallazgos deben ser concretos, no genéricos.

ESTRUCTURA JSON REQUERIDA (sin variaciones):
{
  "score_global": <número 0-100>,
  "nivel": "<uno de: 'Crítico' | 'Básico' | 'Intermedio' | 'Avanzado'>",
  "resumen_ejecutivo": "<2-3 oraciones directas sobre el estado actual del hotel>",
  "oportunidad_mensual_estimada": <número entero en COP — reservas que podrían recuperarse>,
  "dimensiones": {
    "atencion_cliente": {
      "score": <0-100>,
      "label": "Atención al cliente",
      "hallazgo": "<qué encontraste específicamente>"
    },
    "automatizacion": {
      "score": <0-100>,
      "label": "Automatización",
      "hallazgo": "<qué encontraste específicamente>"
    },
    "revenue_management": {
      "score": <0-100>,
      "label": "Revenue Management",
      "hallazgo": "<qué encontraste específicamente>"
    },
    "presencia_digital": {
      "score": <0-100>,
      "label": "Presencia Digital",
      "hallazgo": "<qué encontraste específicamente>"
    },
    "gestion_operativa": {
      "score": <0-100>,
      "label": "Gestión Operativa",
      "hallazgo": "<qué encontraste específicamente>"
    }
  },
  "hallazgos_criticos": [
    "<hallazgo 1 concreto>",
    "<hallazgo 2 concreto>",
    "<hallazgo 3 concreto>"
  ],
  "quick_wins": [
    "<acción rápida 1 que Tentii puede resolver>",
    "<acción rápida 2>",
    "<acción rápida 3>"
  ],
  "valor_tentii": "<párrafo de 2-3 oraciones explicando específicamente qué haría Tentii por este hotel>"
}
PROMPT;
    }

    private function buildUserMessage(array $data, array $crawl): string
    {
        $crawlInfo = $crawl['pages_crawled'] > 0
            ? "Se analizaron {$crawl['pages_crawled']} páginas del sitio web.\n\nCONTENIDO DEL SITIO WEB:\n{$crawl['text']}"
            : "No fue posible leer el sitio web (puede estar protegido o no disponible). Basa el diagnóstico solo en las respuestas del formulario.";

        $pmsInfo = $data['tiene_pms'] === 'si'
            ? "Sí usa PMS. Sistema actual: " . ($data['cual_pms'] ?? 'No especificado')
            : "No usa ningún PMS";

        return <<<MSG
Analiza este hotel y genera el diagnóstico JSON:

DATOS DEL FORMULARIO:
- Hotel: {$data['nombre_hotel']}
- Sitio web: {$data['website']}
- ¿Usa WhatsApp Business?: {$data['whatsapp_business']}
- ¿Lo tiene automatizado?: {$data['whatsapp_automatico']}
- Precio promedio por reserva: \${$data['precio_reserva']} COP
- ¿Quién atiende el WhatsApp?: {$data['quien_atiende']}
- Volumen de mensajes WhatsApp/día: {$data['volumen_whatsapp']}
- PMS: {$pmsInfo}

ANÁLISIS DEL SITIO WEB:
{$crawlInfo}

Genera el diagnóstico completo en JSON.
MSG;
    }

    private function logDiagnostico(array $data, array $resultado): void
    {
        $score = $resultado['score_global'] ?? 'N/A';
        $nivel = $resultado['nivel'] ?? 'N/A';

        log_message('info', sprintf(
            '[Calculadora/Diagnóstico] Hotel: %s | URL: %s | Score: %s | Nivel: %s | IP: %s',
            $data['nombre_hotel'],
            $data['website'],
            $score,
            $nivel,
            $this->request->getIPAddress()
        ));

        // Si tienes email configurado en CI4, puedes descomentar esto:
        /*
        $email = \Config\Services::email();
        $email->setFrom('sistema@tentii.com', 'Tentii Sistema');
        $email->setTo('ventas@tentii.com');
        $email->setSubject("Nuevo diagnóstico: {$data['nombre_hotel']} — Score {$score}");
        $email->setMessage(view('emails/diagnostico_interno', ['data' => $data, 'resultado' => $resultado]));
        $email->send();
        */
    }

    private function jsonError(string $message, int $code): \CodeIgniter\HTTP\Response
    {
        return $this->response
            ->setStatusCode($code)
            ->setContentType('application/json')
            ->setBody(json_encode(['success' => false, 'error' => $message]));
    }
}