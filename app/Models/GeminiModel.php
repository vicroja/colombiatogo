<?php

namespace App\Models;

use CodeIgniter\Model;

class GeminiModel extends Model
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
    protected $defaultModel = 'gemini-2.0-flash';

    // Configuración de Resiliencia (Heredada de tu sistema anterior)
    protected $maxRetries = 3;
    protected $baseDelay = 1; // Segundos base para espera antes de reintentar

    // Cadena de Fallback
    protected $fallbackModels = [
        'gemini-2.0-flash',
        'gemini-2.5-flash',
        'gemini-1.5-flash'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = getenv('GEMINI_API_KEY');

        if (empty($this->apiKey)) {
            log_message('critical', '[GeminiModel] GEMINI_API_KEY no está configurada en el archivo .env');
        }
    }

    /**
     * 1. GENERACIÓN DE CHAT (El núcleo conversacional del Webhook)
     */
    public function generateChatResponse(array $history, string $systemInstruction, ?string $modelVersion = null): array
    {
        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $systemInstruction]]
            ],
            'contents' => $history,
            'generationConfig' => [
                'temperature' => 0.4,
                'responseMimeType' => 'application/json', // <-- ¡EL TRUCO ESTÁ AQUÍ! Ffuerza a la API a responder solo JSON
            ]
        ];

        $modelToUse = $modelVersion ?: $this->defaultModel;

        $response = $this->executeRequest($payload, $modelToUse);

        if ($response['status'] === 'success') {
            return ['text' => $response['message']];
        }

        return ['error' => $response['message']];
    }

    /**
     * 2. TRANSCRIPCIÓN DE AUDIO (Migrado exacto de tu CLI/Gemini_model anterior)
     */
    public function transcribeAudio(string $base64Audio, string $mimeType, string $filename = ''): array
    {
        // Tu prompt estricto original
        $promptText = "Devuelve SOLO el texto transcrito, sin introducciones, ni tiempos, ni formatos markdown (como ```json). " .
            "Si hay múltiples hablantes, transcríbelo como un texto continuo salvo que sea muy confuso. " .
            "Entrega el texto con buena ortografía, gramática. " .
            "REGLA IMPORTANTE: debes devolver solo la transcripción del texto, no debes entablar una conversación, tu rol y scope es solo transcribir con buena fidelidad.";

        // Estructura oficial de Gemini para archivos base64 en línea
        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $promptText],
                        [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => base64_encode($base64Audio) // Aseguramos codificación correcta
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1, // Temperatura baja para mayor fidelidad de transcripción
            ]
        ];

        // Usar un modelo rápido por defecto para audio
        $response = $this->executeRequest($payload, 'gemini-2.5-flash');

        if ($response['status'] === 'success') {
            log_message('info', "[GeminiModel] Audio transcrito con éxito: {$filename}");
            return ['status' => 'success', 'message' => $response['message']];
        }

        return ['status' => 'error', 'message' => $response['message']];
    }

    /**
     * 3. GENERACIÓN DE IMÁGENES (Llamado desde tu Gemini.php controller)
     */
    public function generateImage(string $prompt, string $aspectRatio = '1:1'): array
    {
        // Nota: Google utiliza modelos específicos de la familia Imagen para generar fotos.
        // Si tienes acceso a la API unificada de Imagen 3, este es el endpoint.
        $payload = [
            'instances' => [
                ['prompt' => $prompt]
            ],
            'parameters' => [
                'sampleCount' => 1,
                'aspectRatio' => $aspectRatio
            ]
        ];

        // El endpoint para imágenes suele cambiar, usamos el de predicción estándar de Imagen
        $url = "[https://generativelanguage.googleapis.com/v1beta/models/imagen-3.0-generate-001:predict?key=](https://generativelanguage.googleapis.com/v1beta/models/imagen-3.0-generate-001:predict?key=){$this->apiKey}";

        $client = \Config\Services::curlrequest();

        try {
            $response = $client->post($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => $payload,
                'http_errors' => false,
                'timeout' => 45
            ]);

            $decoded = json_decode($response->getBody(), true);

            if ($response->getStatusCode() == 200 && isset($decoded['predictions'][0]['bytesBase64Encoded'])) {
                return [
                    'status' => 'success',
                    'image_base64' => $decoded['predictions'][0]['bytesBase64Encoded']
                ];
            }

            return [
                'status' => 'error',
                'message' => $decoded['error']['message'] ?? 'Error desconocido al generar la imagen'
            ];

        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * 4. LIMPIEZA DE JSON (Usado intensivamente en tu Cli.php para las predicciones)
     */

    /*public function cleanJsonResponse(string $text): string
    {
        $text = trim($text);

        // Quitar bloques ```json y ```
        if (strpos($text, '```json') === 0) {
            $text = substr($text, 7);
            if (substr($text, -3) === '```') {
                $text = substr($text, 0, -3);
            }
        } elseif (strpos($text, '```') === 0) {
            $text = substr($text, 3);
            if (substr($text, -3) === '```') {
                $text = substr($text, 0, -3);
            }
        }

        return trim($text);
    }*/


    public function cleanJsonResponse($text)
    {
        $text = preg_replace('/```\s*$/mi', '', $text);
        $text = trim($text);

        // 3. Extraer estrictamente el objeto JSON
        // Esto ignora cualquier texto introductorio o de despedida ("¡Hola! Aquí está tu JSON:")
        $start = strpos($text, '{');
        $end = strrpos($text, '}');

        if ($start !== false && $end !== false) {
            // Cortamos la cadena para quedarnos solo con el contenido desde { hasta }
            $text = substr($text, $start, $end - $start + 1);
        }

        // 4. Verificación rápida: ¿Ya es un JSON válido?
        // Si usamos responseMimeType en la API, casi siempre llegará perfecto aquí.
        json_decode($text);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $text; // Si es válido, no lo tocamos más.
        }

        // 5. El Salvavidas (Solo se ejecuta si el JSON llegó roto)
        // Busca texto entre comillas dobles y escapa los saltos de línea y tabulaciones reales
        $text = preg_replace_callback('/"(?:\\\\.|[^\\\\"])*"/s', function($matches) {
            // Usamos comillas dobles con doble barra invertida para asegurar que PHP
            // escriba literalmente los caracteres \ y n en el string final.
            return str_replace(
                ["\r\n", "\r", "\n", "\t"],
                ["\\n", "\\n", "\\n", "\\t"],
                $matches[0]
            );
        }, $text);

        return $text;
    }


    /**
     * 5. MOTOR DE EJECUCIÓN CON RESILIENCIA Y FALLBACKS
     * Esta función privada centraliza las peticiones HTTP, reintentos y rotación de modelos.
     */
    private function executeRequest(array $payload, string $primaryModel): array
    {
        $client = \Config\Services::curlrequest();
        $modelsToTry = array_unique(array_merge([$primaryModel], $this->fallbackModels));

        foreach ($modelsToTry as $currentModel) {
            $url = $this->baseUrl . $currentModel . ':generateContent?key=' . $this->apiKey;

            for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
                try {
                    $response = $client->post($url, [
                        'headers' => ['Content-Type' => 'application/json'],
                        'json' => $payload,
                        'http_errors' => false,
                        'timeout' => 45
                    ]);

                    $httpCode = $response->getStatusCode();
                    $body = $response->getBody();
                    $decoded = json_decode($body, true);

                    // Éxito
                    if ($httpCode >= 200 && $httpCode < 300) {
                        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;
                        if ($text !== null) {
                            return ['status' => 'success', 'message' => trim($text)];
                        }
                    }

                    // Errores de la API
                    $errorMsg = $decoded['error']['message'] ?? 'Error desconocido';
                    $errorCode = $decoded['error']['code'] ?? $httpCode;

                    // Si es un error 400 (Bad Request), no tiene sentido reintentar con el mismo modelo
                    if ($httpCode == 400) {
                        log_message('error', "[GeminiModel] Error 400 con {$currentModel}: {$errorMsg}. Saltando a otro modelo.");
                        break; // Sale del bucle de intentos, pasa al siguiente modelo
                    }

                    // Si es 429 (Too Many Requests) o 503 (Service Unavailable), esperamos y reintentamos
                    if ($httpCode == 429 || $httpCode == 503) {
                        log_message('warning', "[GeminiModel] {$errorCode} con {$currentModel}. Intento {$attempt}/{$this->maxRetries}. Esperando...");
                        sleep($this->baseDelay * $attempt);
                        continue;
                    }

                } catch (\Exception $e) {
                    log_message('error', "[GeminiModel] Excepción con {$currentModel} (Intento {$attempt}): " . $e->getMessage());
                    sleep($this->baseDelay * $attempt);
                }
            }

            log_message('warning', "[GeminiModel] Agotados intentos para el modelo {$currentModel}. Cambiando al siguiente de la lista fallback.");
        }

        return ['status' => 'error', 'message' => 'Todos los modelos de fallback fallaron o no se pudo establecer conexión.'];
    }

    /**
     * Generación de texto libre (sin forzar JSON)
     * Usado por el wizard de onboarding para descripciones y prompts
     */
    public function generateText(string $prompt, int $maxTokens = 1000, float $temperature = 0.7): array
    {
        $payload = [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'temperature'     => $temperature,
                'maxOutputTokens' => $maxTokens,
                // Sin responseMimeType — retorna texto libre
            ]
        ];

        $response = $this->executeRequest($payload, $this->defaultModel);

        if ($response['status'] === 'success') {
            return ['success' => true, 'text' => trim($response['message'])];
        }

        return ['success' => false, 'message' => $response['message']];
    }
}