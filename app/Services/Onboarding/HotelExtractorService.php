<?php

namespace App\Services\Onboarding;

use App\Models\ImportStagingModel;
use App\Models\GeminiModel;
use App\Models\BedTypeModel;
use App\Models\AmenityModel;

/**
 * HotelExtractorService
 *
 * Extrae información estructurada de un hotel/operador de tours a partir
 * de URLs, PDFs, imágenes o texto crudo.
 *
 * Diferencias vs. el extractor original de Guasapp:
 *   - El schema es específico del dominio hotelero (units, rates, tours…).
 *   - Recibe los flags has_accommodation / has_tours para ajustar qué pide.
 *   - Pasa a Gemini el catálogo actual de bed_types y amenities del tenant
 *     para que matchee por nombre exacto en vez de inventar IDs.
 *
 * NO incluye tracking de AiUsage (decisión: implementar después si hace falta).
 */
class HotelExtractorService
{
    private const MAX_URLS       = 5;
    private const MAX_FILE_BYTES = 8 * 1024 * 1024; // 8 MB

    private ImportStagingModel $stagingModel;
    private GeminiModel        $gemini;
    private WebScraperService  $scraper;
    private BedTypeModel       $bedTypeModel;
    private AmenityModel       $amenityModel;

    public function __construct()
    {
        $this->stagingModel = new ImportStagingModel();
        $this->gemini       = new GeminiModel();
        $this->scraper      = new WebScraperService();
        $this->bedTypeModel = new BedTypeModel();
        $this->amenityModel = new AmenityModel();
    }

    /**
     * Extrae desde una o más URLs (con scraping real).
     */
    public function extractFromUrls(int $tenantId, array $urls, array $profile, ?int $createdBy = null): array
    {
        $urls = array_slice(array_filter($urls), 0, self::MAX_URLS);
        if (empty($urls)) {
            return ['success' => false, 'error' => 'Debes proporcionar al menos una URL.'];
        }

        $stagingId = $this->stagingModel->insert([
            'tenant_id'        => $tenantId,
            'created_by'       => $createdBy,
            'source_type'      => 'url',
            'source_reference' => implode("\n", $urls),
            'status'           => 'pending',
        ], true);

        $scraped = $this->scraper->fetchMany($urls);

        if (empty($scraped['combined_text'])) {
            $this->stagingModel->update($stagingId, [
                'status'        => 'discarded',
                'error_message' => 'No pudimos descargar contenido de las URLs proporcionadas.',
            ]);
            return [
                'success'    => false,
                'error'      => 'No pudimos descargar contenido de las URLs proporcionadas. Verifica que sean correctas y públicas.',
                'staging_id' => $stagingId,
            ];
        }

        return $this->runExtraction(
            tenantId:      $tenantId,
            stagingId:     (int)$stagingId,
            profile:       $profile,
            scrapedText:   $scraped['combined_text'],
            scrapedImages: $scraped['all_images'],
            filePath:      null,
            mimeType:      null,
            rawText:       null
        );
    }

    /**
     * Extrae desde un archivo PDF o imagen.
     */
    public function extractFromFile(int $tenantId, string $filePath, string $mimeType, array $profile, ?int $createdBy = null): array
    {
        if (!is_file($filePath)) {
            return ['success' => false, 'error' => 'Archivo no encontrado.'];
        }
        if (filesize($filePath) > self::MAX_FILE_BYTES) {
            return ['success' => false, 'error' => 'El archivo es demasiado grande (>8MB).'];
        }

        $sourceType = str_starts_with($mimeType, 'image/') ? 'image' : 'pdf';

        $stagingId = $this->stagingModel->insert([
            'tenant_id'        => $tenantId,
            'created_by'       => $createdBy,
            'source_type'      => $sourceType,
            'source_reference' => $filePath,
            'status'           => 'pending',
        ], true);

        return $this->runExtraction(
            tenantId:      $tenantId,
            stagingId:     (int)$stagingId,
            profile:       $profile,
            scrapedText:   null,
            scrapedImages: [],
            filePath:      $filePath,
            mimeType:      $mimeType,
            rawText:       null
        );
    }

    /**
     * Extrae desde texto pegado.
     */
    public function extractFromText(int $tenantId, string $text, array $profile, ?int $createdBy = null): array
    {
        if (strlen($text) < 30) {
            return ['success' => false, 'error' => 'El texto es muy corto para extraer información útil.'];
        }

        $stagingId = $this->stagingModel->insert([
            'tenant_id'        => $tenantId,
            'created_by'       => $createdBy,
            'source_type'      => 'text',
            'source_reference' => 'Texto pegado por el usuario',
            'status'           => 'pending',
        ], true);

        return $this->runExtraction(
            tenantId:      $tenantId,
            stagingId:     (int)$stagingId,
            profile:       $profile,
            scrapedText:   null,
            scrapedImages: [],
            filePath:      null,
            mimeType:      null,
            rawText:       $text
        );
    }

    // ──────────────────────────────────────────────────────────────────
    // Llamada efectiva a Gemini
    // ──────────────────────────────────────────────────────────────────

    private function runExtraction(
        int     $tenantId,
        int     $stagingId,
        array   $profile,
        ?string $scrapedText,
        array   $scrapedImages,
        ?string $filePath,
        ?string $mimeType,
        ?string $rawText
    ): array {
        // Catálogo actual del tenant — Gemini lo usa para matchear nombres
        $bedTypes  = $this->fetchBedTypeNames($tenantId);
        $amenities = $this->fetchAmenityNames($tenantId);

        $systemInstruction = $this->buildExtractionPrompt(
            $profile,
            $bedTypes,
            $amenities,
            !empty($scrapedImages)
        );
        $userPrompt = $this->buildUserPrompt($scrapedText, $rawText, $scrapedImages);

        if ($filePath && $mimeType) {
            $result = $this->callGeminiWithFile($systemInstruction, $userPrompt, $filePath, $mimeType);
        } else {
            $fullPrompt = $systemInstruction . "\n\n## ENTRADA DEL USUARIO\n" . $userPrompt;
            $raw = $this->gemini->generateText($fullPrompt, 8192, 0.3);
            $result = [
                'success' => $raw['success'] ?? false,
                'text'    => $raw['text'] ?? '',
                'error'   => $raw['message'] ?? null,
                'usage'   => $raw['usage'] ?? ['input_tokens' => 0, 'output_tokens' => 0, 'total_tokens' => 0],
            ];
        }

        if (!$result['success']) {
            $this->stagingModel->update($stagingId, [
                'status'        => 'discarded',
                'error_message' => $result['error'] ?? 'Error en extracción.',
            ]);
            return [
                'success'    => false,
                'error'      => $result['error'] ?? 'Error en extracción.',
                'staging_id' => $stagingId,
            ];
        }

        $cleaned = $this->gemini->cleanJsonResponse($result['text']);
        $parsed  = json_decode($cleaned, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($parsed)) {
            $this->stagingModel->update($stagingId, [
                'status'         => 'discarded',
                'error_message'  => 'Gemini devolvió JSON inválido.',
                'extracted_json' => json_encode(['raw' => $result['text']], JSON_UNESCAPED_UNICODE),
                'tokens_used'    => $result['usage']['total_tokens'] ?? 0,
            ]);
            return [
                'success'    => false,
                'error'      => 'No pudimos interpretar la respuesta. Intenta de nuevo.',
                'staging_id' => $stagingId,
            ];
        }

        $parsed = $this->normalizeAndFlagIncluded($parsed);

        // Guardar pool de imágenes scrapeadas para que el applier pueda descargarlas
        if (!empty($scrapedImages)) {
            $parsed['_scraped_images_pool'] = $scrapedImages;
        }

        $this->stagingModel->update($stagingId, [
            'status'            => 'extracted',
            'detected_vertical' => $parsed['detected_vertical'] ?? 'unknown',
            'extracted_json'    => json_encode($parsed, JSON_UNESCAPED_UNICODE),
            'edited_json'       => json_encode($parsed, JSON_UNESCAPED_UNICODE),
            'tokens_used'       => $result['usage']['total_tokens'] ?? 0,
        ]);

        return [
            'success'    => true,
            'staging_id' => $stagingId,
            'data'       => $parsed,
        ];
    }

    // ──────────────────────────────────────────────────────────────────
    // Llamada directa a Gemini con archivo adjunto
    // ──────────────────────────────────────────────────────────────────

    private function callGeminiWithFile(string $systemInstruction, string $userText, string $filePath, string $mimeType): array
    {
        $apiKey = env('GEMINI_API_KEY') ?: getenv('GEMINI_API_KEY');
        if (!$apiKey) {
            return [
                'success' => false,
                'error'   => 'GEMINI_API_KEY no configurada.',
                'usage'   => ['input_tokens' => 0, 'output_tokens' => 0, 'total_tokens' => 0],
            ];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        $payload = [
            'system_instruction' => ['parts' => [['text' => $systemInstruction]]],
            'contents' => [[
                'role'  => 'user',
                'parts' => [
                    ['text' => $userText],
                    ['inlineData' => [
                        'mimeType' => $mimeType,
                        'data'     => base64_encode(file_get_contents($filePath)),
                    ]],
                ],
            ]],
            'generationConfig' => [
                'temperature'      => 0.3,
                'maxOutputTokens'  => 8192,
                'responseMimeType' => 'application/json',
            ],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 120,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error'   => "HTTP {$httpCode}",
                'usage'   => ['input_tokens' => 0, 'output_tokens' => 0, 'total_tokens' => 0],
            ];
        }

        $decoded = json_decode($response, true);
        $text    = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';

        return [
            'success' => true,
            'text'    => $text,
            'usage'   => [
                'input_tokens'  => $decoded['usageMetadata']['promptTokenCount']     ?? 0,
                'output_tokens' => $decoded['usageMetadata']['candidatesTokenCount'] ?? 0,
                'total_tokens'  => $decoded['usageMetadata']['totalTokenCount']      ?? 0,
            ],
        ];
    }

    // ──────────────────────────────────────────────────────────────────
    // Catálogos del tenant para el prompt
    // ──────────────────────────────────────────────────────────────────

    private function fetchBedTypeNames(int $tenantId): array
    {
        // BaseMultiTenantModel ya filtra por session('active_tenant_id'),
        // pero por robustez forzamos el filtro explícito al tenantId pasado.
        $rows = $this->bedTypeModel
            ->where('bed_types.tenant_id', $tenantId)
            ->findAll();
        return array_values(array_filter(array_map(fn($r) => $r['name'] ?? null, $rows)));
    }

    private function fetchAmenityNames(int $tenantId): array
    {
        $rows = $this->amenityModel
            ->where('amenities.tenant_id', $tenantId)
            ->findAll();
        return array_values(array_filter(array_map(fn($r) => $r['name'] ?? null, $rows)));
    }

    // ──────────────────────────────────────────────────────────────────
    // Prompt builders
    // ──────────────────────────────────────────────────────────────────

    /**
     * Construye el system_instruction.
     */
    private function buildExtractionPrompt(array $profile, array $bedTypes, array $amenities, bool $imagesAvailable): string
    {
        $hasAccommodation = !empty($profile['has_accommodation']);
        $hasTours         = !empty($profile['has_tours']);

        // Bloque de catálogos
        $bedTypesList  = empty($bedTypes)  ? '(catálogo vacío — puedes proponer nombres nuevos)' : '- ' . implode("\n- ", $bedTypes);
        $amenitiesList = empty($amenities) ? '(catálogo vacío — puedes proponer nombres nuevos)' : '- ' . implode("\n- ", $amenities);

        // Bloque de imágenes
        $imagesHint = $imagesAvailable
            ? "Junto al texto del sitio te paso una LISTA de URLs de imágenes extraídas del HTML. " .
              "Para cada habitación/tour/producto que detectes, si una imagen de la lista lo representa, " .
              "incluye en el item el campo `image_url` con esa URL EXACTA (no inventes URLs). " .
              "Si no estás seguro, deja `image_url: null`."
            : "Si la fuente es PDF/imagen y contiene fotos asociadas a items, descríbelo en `extraction_notes`. " .
              "No inventes URLs de imágenes — deja `image_url: null`.";

        // Bloque de secciones requeridas según perfil
        $sectionsHint = [];
        if ($hasAccommodation) {
            $sectionsHint[] = "El negocio TIENE ALOJAMIENTO: extrae `accommodation_types`, `accommodation_units`, `rate_plans`, `unit_rates`.";
        } else {
            $sectionsHint[] = "El negocio NO maneja alojamiento: deja `accommodation_types`, `accommodation_units`, `rate_plans`, `unit_rates` como arrays vacíos.";
        }
        if ($hasTours) {
            $sectionsHint[] = "El negocio TIENE TOURS: extrae `tours` (y `tour_schedules` solo si hay fechas concretas).";
        } else {
            $sectionsHint[] = "El negocio NO maneja tours: deja `tours` y `tour_schedules` como arrays vacíos.";
        }
        $sectionsBlock = implode("\n", $sectionsHint);

        return <<<PROMPT
Eres un asistente experto en extraer información estructurada de hoteles, operadores turísticos y negocios de hospitalidad.

Tu tarea: dado el contenido (web scrapeada, PDF, imagen o texto pegado), devuelve un JSON ESTRICTO con la información para configurar un PMS.

## PERFIL DEL NEGOCIO
{$sectionsBlock}

## CATÁLOGOS EXISTENTES DEL TENANT
Estos son los tipos de cama y amenidades ya creados en el sistema. Si encuentras una equivalencia, usa el NOMBRE EXACTO de esta lista. Si no, propón un nombre nuevo (se creará automáticamente).

### Tipos de cama disponibles:
{$bedTypesList}

### Amenidades disponibles:
{$amenitiesList}

## REGLAS CRÍTICAS
1. NUNCA inventes datos. Si no encuentras un dato, déjalo en `null` (o array vacío `[]` para listas).
2. Para cada precio, agrega `price_confidence`:
   - "explicit" → el precio aparecía literalmente.
   - "inferred" → lo dedujiste del contexto (ej: "desde \$200.000").
   - "null"     → no había precio.
3. Para `detected_vertical` usa:
   - "accommodation" → solo hospedaje
   - "tours"         → solo tours
   - "mixed"         → ambos
   - "unknown"       → no se puede determinar
4. Para `vertical_confidence` usa "high" / "medium" / "low".
5. {$imagesHint}
6. Para cada unidad de alojamiento (`accommodation_units`), indica `mode`:
   - "simple"   → una unidad reservable sola (habitación, suite, apartamento, glamping).
   - "compound" → es PADRE de varios cuartos (cabaña/villa/casa que se renta completa).
   - "child"    → es HIJO de una compound (un cuarto dentro de una cabaña). En este caso llena `parent_name` con el nombre EXACTO de la unidad padre.
7. Para `unit_rates`, matchea `unit_name` con `accommodation_units[].name` Y `rate_plan_name` con `rate_plans[].name`. Sin esos matches, el rate se descartará.
8. En `tours[].included` y `tours[].excluded` usa arrays de strings cortos (ej: ["Guía", "Transporte"]).
9. En `rate_plans[].amenities` usa SOLO estas keys válidas: breakfast, lunch, dinner, all_inclusive, airport_transfer, late_checkout, free_cancellation, non_refundable, wifi_premium, parking.
10. En `extraction_notes` reporta brevemente qué te costó extraer y qué quedó dudoso.

## ESTRUCTURA JSON DE RESPUESTA (OBLIGATORIA)

```json
{
  "detected_vertical": "accommodation|tours|mixed|unknown",
  "vertical_confidence": "high|medium|low",

  "business_summary": {
    "name": null,
    "description": null,
    "city": null,
    "country": null,
    "address": null,
    "phone": null,
    "email": null,
    "website": null,
    "logo_url": null,
    "checkin_time": "15:00",
    "checkout_time": "12:00"
  },

  "accommodation_types": [
    {
      "name": "Cabaña",
      "description": null,
      "base_capacity": 4,
      "max_capacity": 6
    }
  ],

  "accommodation_units": [
    {
      "name": "Cabaña Río Verde",
      "type_name": "Cabaña",
      "mode": "simple|compound|child",
      "parent_name": null,
      "description": "...",
      "base_occupancy": 2,
      "max_occupancy": 4,
      "bathrooms": 1.0,
      "beds": [
        { "bed_type_name": "Cama Queen", "quantity": 1 }
      ],
      "amenities": ["WiFi de Alta Velocidad"],
      "image_url": null
    }
  ],

  "rate_plans": [
    {
      "name": "Tarifa Estándar",
      "description": null,
      "amenities": ["breakfast"],
      "cancellation_policy": "flexible|moderate|strict|non_refundable",
      "min_nights_default": 1,
      "is_default": true
    }
  ],

  "unit_rates": [
    {
      "unit_name": "Cabaña Río Verde",
      "rate_plan_name": "Tarifa Estándar",
      "price_per_night": 0,
      "price_confidence": "explicit|inferred|null",
      "extra_person_price": 0,
      "extra_child_price": 0,
      "min_nights": 1
    }
  ],

  "tours": [
    {
      "name": "...",
      "description": "...",
      "duration_minutes": 60,
      "meeting_point": null,
      "min_pax": 1,
      "price_adult": 0,
      "price_child": 0,
      "price_confidence": "explicit|inferred|null",
      "difficulty_level": "easy|moderate|hard",
      "cancellation_policy": "flexible|moderate|strict|non_refundable",
      "included": [],
      "excluded": [],
      "image_url": null
    }
  ],

  "tour_schedules": [],

  "products": [
    {
      "name": "...",
      "description": "...",
      "category": "General",
      "unit_price": 0,
      "price_confidence": "explicit|inferred|null",
      "image_url": null
    }
  ],

  "extraction_notes": null
}
```

Si una sección no aplica, devuélvela como `[]` (NUNCA omitas la clave).

Devuelve ÚNICAMENTE el JSON, sin markdown, sin texto adicional, sin backticks.
PROMPT;
    }

    /**
     * Construye el user_prompt.
     */
    private function buildUserPrompt(?string $scrapedText, ?string $rawText, array $scrapedImages = []): string
    {
        $parts = [];

        if ($scrapedText) {
            $parts[] = "Contenido descargado del sitio web del negocio:\n\n" . $scrapedText;
        }

        if ($rawText) {
            $parts[] = "Texto del negocio (pegado por el usuario):\n\n" . trim($rawText);
        }

        if (!empty($scrapedImages)) {
            $parts[] = "\n\nLISTA DE IMÁGENES disponibles (úsalas para llenar `image_url` cuando corresponda):\n" .
                implode("\n", array_map(fn($u) => "- " . $u, array_slice($scrapedImages, 0, 40)));
        }

        if (empty($parts)) {
            $parts[] = "Analiza el archivo adjunto y extrae la información del negocio.";
        }

        return implode("\n\n", $parts);
    }

    // ──────────────────────────────────────────────────────────────────
    // Normalización post-Gemini
    // ──────────────────────────────────────────────────────────────────

    /**
     * Asegura que todas las claves existan y que cada item tenga `include = true`.
     */
    private function normalizeAndFlagIncluded(array $data): array
    {
        // Claves array que deben existir aunque vengan ausentes
        $arrayKeys = [
            'accommodation_types', 'accommodation_units', 'rate_plans',
            'unit_rates', 'tours', 'tour_schedules', 'products',
        ];

        foreach ($arrayKeys as $key) {
            if (!isset($data[$key]) || !is_array($data[$key])) {
                $data[$key] = [];
                continue;
            }
            foreach ($data[$key] as &$item) {
                if (is_array($item) && !isset($item['include'])) {
                    $item['include'] = true;
                }
            }
            unset($item);
        }

        if (!isset($data['business_summary']) || !is_array($data['business_summary'])) {
            $data['business_summary'] = [];
        }

        if (!isset($data['detected_vertical'])) {
            $data['detected_vertical'] = 'unknown';
        }

        return $data;
    }
}
