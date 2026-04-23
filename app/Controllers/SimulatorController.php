<?php

namespace App\Controllers;

use App\Models\AiPromptModel;
use App\Models\GeminiModel;
use App\Models\TenantModel;
use App\Models\AccommodationUnitModel;
use App\Models\UnitRateModel;
use App\Models\RatePlanModel;

/**
 * SimulatorController
 *
 * Vista de edición del prompt del asistente IA + simulador bot-vs-bot.
 * El simulador usa GeminiModel para ambos roles (hotel y cliente).
 * Los tool_calls se interceptan con respuestas ficticias — nunca toca la BD real.
 */
class SimulatorController extends BaseController
{
    private AiPromptModel        $promptModel;
    private GeminiModel          $geminiModel;
    private TenantModel          $tenantModel;
    private AccommodationUnitModel $unitModel;
    private UnitRateModel        $unitRateModel;
    private RatePlanModel        $ratePlanModel;
    private int                  $tenantId;
    private array                $tenant;
    private \CodeIgniter\Database\BaseConnection $db;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface            $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->tenantId      = session('active_tenant_id');
        $this->promptModel   = new AiPromptModel();
        $this->geminiModel   = new GeminiModel();
        $this->tenantModel   = new TenantModel();
        $this->unitModel     = new AccommodationUnitModel();
        $this->unitRateModel = new UnitRateModel();
        $this->ratePlanModel = new RatePlanModel();
        $this->tenant        = $this->tenantModel->find($this->tenantId) ?? [];
        $this->db            = \Config\Database::connect();
        $this->whatsappModel = new \App\Models\WhatsappModel(); // ← FALTA ESTA LÍNEA

    }

    // =========================================================================
    // INDEX — Vista principal: editor de prompt + simulador
    // =========================================================================
    public function index(): string
    {
        $prompt = $this->promptModel
            ->where('profile_role', 'assistant')
            ->first();

        // Unidades con precios para mostrar contexto en el editor
        $units = $this->unitModel
            ->where('parent_id IS NULL')
            ->findAll();

        $defaultPlan = $this->ratePlanModel->where('is_default', 1)->first();
        foreach ($units as &$unit) {
            $rate = null;
            if ($defaultPlan) {
                $rate = $this->unitRateModel
                    ->where('unit_id', $unit['id'])
                    ->where('rate_plan_id', $defaultPlan['id'])
                    ->first();
            }
            if (!$rate) {
                $rate = $this->unitRateModel
                    ->where('unit_id', $unit['id'])
                    ->where('is_active', 1)
                    ->first();
            }
            $unit['price_per_night'] = $rate['price_per_night'] ?? null;
        }
        unset($unit);

        return view('whatsapp/simulator', [
            'tenant' => $this->tenant,
            'prompt' => $prompt,
            'units'  => $units,
        ]);
    }

    // =========================================================================
    // SAVE PROMPT — Guardar cambios al system_instruction
    // =========================================================================
    public function savePrompt(): \CodeIgniter\HTTP\RedirectResponse
    {
        $instruction = trim($this->request->getPost('system_instruction') ?? '');

        if (empty($instruction)) {
            return redirect()->back()->with('error', 'El prompt no puede estar vacío.');
        }

        $existing = $this->promptModel
            ->where('profile_role', 'assistant')
            ->first();

        if ($existing) {
            $this->promptModel->update($existing['id'], [
                'system_instruction' => $instruction,
            ]);
        } else {
            $this->promptModel->createForTenant([
                'profile_role'       => 'assistant',
                'model_version'      => 'gemini-2.5-flash',
                'system_instruction' => $instruction,
            ]);
        }

        log_message('info', "[Simulator] Prompt actualizado para tenant {$this->tenantId}");

        return redirect()->to('/whatsapp/simulator')
            ->with('success', 'Prompt del asistente actualizado correctamente.');
    }

    // =========================================================================
    // SIMULATE TURN — Un turno de la simulación (AJAX)
    // =========================================================================


    public function simulateTurn(): \CodeIgniter\HTTP\ResponseInterface
    {
        $input      = $this->request->getJSON(true);
        $clientPhone = trim($input['phone'] ?? '');
        $isFirst     = $input['is_first'] ?? false;
        $history     = $input['history']  ?? '';
        $clientRole  = $input['client_role'] ?? 'cliente curioso que quiere reservar';

        if (empty($clientPhone)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Debes ingresar un número de teléfono para simular.'
            ]);
        }

        // 1. Si no es el primer mensaje, Gemini genera el mensaje del cliente
        $mensajeTexto = trim($input['message'] ?? '');

        if (!$isFirst && empty($mensajeTexto)) {
            $instruccion = "Actúa como un {$clientRole} chateando por WhatsApp con un hotel. " .
                "Historial:\n{$history}\n\n" .
                "Escribe tu próxima respuesta corta y natural, sin formato, solo el texto del chat.";

            $result = $this->geminiModel->generateText($instruccion, 150, 0.9);

            if (!($result['success'] ?? false)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error generando mensaje del cliente simulado.'
                ]);
            }
            $mensajeTexto = trim($result['text'], '"\'');
        }

        // 2. Construir payload falso de Meta con prefijo TEST_SIM_
        $wamidSimulado = 'TEST_SIM_' . time() . '_' . rand(1000, 9999);
        $timestampInicio = time();

        $fakePayload = [
            'object' => 'whatsapp_business_account',
            'entry'  => [[
                'id'      => 'SIMULATION_ENTRY',
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata'          => [
                            'display_phone_number' => 'SIMULATOR',
                            'phone_number_id'      => $this->getPhoneIdForTenant($this->tenantId)
                        ],
                        'contacts' => [[
                            'profile' => ['name' => 'Cliente Simulado'],
                            'wa_id'   => $clientPhone
                        ]],
                        'messages' => [[
                            'from'      => $clientPhone,
                            'id'        => $wamidSimulado,
                            'timestamp' => time(),
                            'type'      => 'text',
                            'text'      => ['body' => $mensajeTexto]
                        ]]
                    ]
                ]]
            ]]
        ];

        $jsonPayload = json_encode($fakePayload);

        // 3. Inyectar directo al WebhookService real (pasa por TODO el flujo)
        $webhookService = new \App\Services\WhatsappWebhookService();
        $webhookService->processNotification($fakePayload, $jsonPayload, false, $this->tenantId);

        // 4. Polling: esperar hasta 10s la respuesta del bot en BD
        $botReply = null;
        for ($i = 0; $i < 10; $i++) {
            $botReply = $this->whatsappModel->getLatestSimReply(
                $clientPhone,
                $this->tenantId,
                $timestampInicio
            );
            if ($botReply) break;
            sleep(1);
        }

        $botText = $botReply
            ? $botReply->message_body
            : '[Sin respuesta tras 10s. Revisa los logs.]';

        return $this->response->setJSON([
            'success'            => true,
            'role'               => 'hotel',
            'simulated_user_msg' => $mensajeTexto,
            'text'               => $botText,
        ]);
    }

// Endpoint para limpiar datos de simulación
    public function clearSimulation(): \CodeIgniter\HTTP\ResponseInterface
    {
        $borrados = $this->whatsappModel->clearSimulationData($this->tenantId);
        return $this->response->setJSON([
            'success' => true,
            'deleted' => $borrados,
            'message' => "Se eliminaron {$borrados} mensajes de simulación."
        ]);
    }

    private function getPhoneIdForTenant(int $tenantId): string
    {
        $tenant = $this->tenantModel->find($tenantId);
        $settings = json_decode($tenant['settings_json'] ?? '{}', true);
        return $settings['whatsapp_phone_number_id'] ?? '';
    }


    // =========================================================================
    // Simular turno del BOT HOTEL (Alfonso)
    // =========================================================================
    private function simulateHotelTurn(array $history, string $instruction): \CodeIgniter\HTTP\ResponseInterface
    {
        // El prompt del hotel espera JSON estricto con tool_calls o final_response
        $result = $this->geminiModel->generateChatResponse($history, $instruction);

        if (isset($result['error'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $result['error']
            ]);
        }

        $rawText = $result['text'] ?? '';

        // Limpiar posibles backticks
        $clean = preg_replace('/```json|```/', '', $rawText);
        $clean = trim($clean);

        $parsed = json_decode($clean, true);

        // Si hay tool_calls → interceptar con respuesta ficticia
        if (isset($parsed['tool_calls']) && is_array($parsed['tool_calls'])) {
            $toolResponses = [];
            foreach ($parsed['tool_calls'] as $tc) {
                $toolName = $tc['name']      ?? '';
                $args     = $tc['arguments'] ?? [];
                $fakeResult = $this->fakeToolResponse($toolName, $args);
                $toolResponses[] = [
                    'tool'   => $toolName,
                    'args'   => $args,
                    'result' => $fakeResult,
                ];
            }

            // Construir nuevo historial con el resultado de la herramienta
            // y llamar de nuevo a Gemini para que genere la respuesta final
            $newHistory = $history;
            $newHistory[] = [
                'role'  => 'model',
                'parts' => [['text' => $clean]]
            ];

            // Agregar resultados de herramientas como mensaje de función
            $toolResultText = '';
            foreach ($toolResponses as $tr) {
                $toolResultText .= "Resultado de {$tr['tool']}: " . json_encode($tr['result']) . "\n";
            }
            $newHistory[] = [
                'role'  => 'user',
                'parts' => [['text' => $toolResultText]]
            ];

            // Segunda llamada para obtener la respuesta final tras las tools
            $result2 = $this->geminiModel->generateChatResponse($newHistory, $instruction);
            $rawText2 = $result2['text'] ?? '';
            $clean2   = trim(preg_replace('/```json|```/', '', $rawText2));
            $parsed2  = json_decode($clean2, true);

            $finalText = $parsed2['final_response']
                ?? $parsed2['response']
                ?? $clean2;

            log_message('info', "[Simulator/Hotel] Tool call resuelto: {$toolResultText}");

            return $this->response->setJSON([
                'success'      => true,
                'role'         => 'hotel',
                'text'         => $finalText,
                'tool_calls'   => $toolResponses,
                'raw'          => $clean2,
                'new_history'  => $newHistory,
            ]);
        }

        // Respuesta directa sin tools
        $finalText = $parsed['final_response']
            ?? $parsed['response']
            ?? $clean;

        return $this->response->setJSON([
            'success'     => true,
            'role'        => 'hotel',
            'text'        => $finalText,
            'tool_calls'  => [],
            'raw'         => $clean,
            'new_history' => $history,
        ]);
    }

    // =========================================================================
    // Simular turno del BOT CLIENTE
    // =========================================================================
    private function simulateClientTurn(
        array  $history,
        string $clientRole,
        string $hotelInstruction
    ): \CodeIgniter\HTTP\ResponseInterface {

        // Extraer contexto del hotel del prompt para que el cliente lo conozca
        $hotelName = $this->tenant['name'] ?? 'el hotel';

        $clientInstruction = "Eres un {$clientRole} que está chateando por WhatsApp con {$hotelName}. " .
            "Estás en una conversación real de WhatsApp. " .
            "Escribe SOLO el mensaje del cliente, como si fuera texto de WhatsApp: " .
            "natural, con errores ocasionales de tipeo, emojis si aplica, sin saludos formales. " .
            "Máximo 2-3 oraciones por mensaje. " .
            "Tu objetivo FINAL es reservar, pero llega ahí de forma natural según tu rol. " .
            "Si el asistente ya te dio precios y disponibilidad, avanza hacia la reserva. " .
            "Responde SOLO con el texto del mensaje, sin explicaciones ni comillas.";

        // Para el cliente usamos generateText (texto libre, sin JSON)
        $clientHistory = $this->buildClientHistory($history);

        $result = $this->geminiModel->generateText(
            $this->buildClientPrompt($clientHistory, $clientInstruction),
            150,
            0.9
        );

        if (!($result['success'] ?? false)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $result['message'] ?? 'Error generando mensaje del cliente.'
            ]);
        }

        $text = trim($result['text']);
        // Limpiar comillas si Gemini las agrega
        $text = trim($text, '"\'');

        return $this->response->setJSON([
            'success' => true,
            'role'    => 'client',
            'text'    => $text,
        ]);
    }

    // =========================================================================
    // FAKE TOOL RESPONSES — Intercepta tools sin tocar la BD real
    // =========================================================================
    private function fakeToolResponse(string $toolName, array $args): array
    {
        // Datos ficticios pero coherentes para la simulación
        switch ($toolName) {
            case 'consultar_disponibilidad':
                $checkIn  = $args['check_in_date']  ?? date('Y-m-d', strtotime('+7 days'));
                $checkOut = $args['check_out_date']  ?? date('Y-m-d', strtotime('+9 days'));
                $personas = $args['numero_personas'] ?? 2;

                // Calcular noches
                $nights = max(1, (int) ceil(
                    (strtotime($checkOut) - strtotime($checkIn)) / 86400
                ));

                // Usar precios reales de la BD para mayor realismo
                $units    = $this->unitModel->where('parent_id IS NULL')->findAll();
                $available = [];

                foreach ($units as $unit) {
                    if (($unit['max_occupancy'] ?? 2) >= $personas) {
                        $rate = $this->unitRateModel
                            ->where('unit_id', $unit['id'])
                            ->where('is_active', 1)
                            ->first();

                        $pricePerNight = $rate['price_per_night'] ?? 150000;
                        $total         = $pricePerNight * $nights;

                        $available[] = [
                            'unit_id'        => $unit['id'],
                            'nombre'         => $unit['name'],
                            'capacidad'      => $unit['max_occupancy'],
                            'precio_noche'   => $pricePerNight,
                            'precio_total'   => $total,
                            'noches'         => $nights,
                            'disponible'     => true,
                        ];
                    }
                }

                if (empty($available)) {
                    return [
                        'disponible' => false,
                        'mensaje'    => "No hay unidades disponibles para {$personas} personas en esas fechas.",
                    ];
                }

                return [
                    'disponible'       => true,
                    'unidades'         => $available,
                    'check_in'         => $checkIn,
                    'check_out'        => $checkOut,
                    'noches'           => $nights,
                    'numero_personas'  => $personas,
                ];

            case 'crear_reserva':
                // Simular creación exitosa sin tocar BD
                $fakeFolio = 'SIM-' . strtoupper(substr(md5(uniqid()), 0, 6));
                return [
                    'success'        => true,
                    'reserva_id'     => 9999,
                    'folio'          => $fakeFolio,
                    'mensaje'        => "Reserva simulada creada exitosamente. Folio: {$fakeFolio}. " .
                        "(NOTA: Esta es una simulación, no se creó ninguna reserva real)",
                    'nombre_cliente' => $args['nombre_cliente'] ?? 'Cliente Simulado',
                    'check_in'       => $args['check_in_date']  ?? '',
                    'check_out'      => $args['check_out_date'] ?? '',
                    'precio_total'   => $args['precio_total_acordado'] ?? 0,
                ];

            case 'notificar_administrador':
                return [
                    'success' => true,
                    'mensaje' => 'Administrador notificado (SIMULACIÓN). El equipo fue alertado.',
                ];

            default:
                return [
                    'error' => "Herramienta '{$toolName}' no reconocida en el simulador.",
                ];
        }
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Construye el historial para el bot cliente
     * (invierte direcciones: lo que dijo el hotel → user para el cliente)
     */
    private function buildClientHistory(array $history): array
    {
        $clientHistory = [];
        foreach ($history as $msg) {
            // El cliente ve los mensajes del hotel como "el otro"
            $clientHistory[] = $msg;
        }
        return $clientHistory;
    }

    /**
     * Construye el prompt para el bot cliente
     */
    private function buildClientPrompt(array $history, string $instruction): string
    {
        $prompt = $instruction . "\n\nHistorial de la conversación:\n";
        foreach ($history as $msg) {
            $role = $msg['role'] === 'model' ? 'Hotel' : 'Tú (cliente)';
            $text = $msg['parts'][0]['text'] ?? '';
            // Limpiar JSON del hotel para que el cliente vea texto legible
            $decoded = json_decode($text, true);
            if ($decoded && isset($decoded['final_response'])) {
                $text = $decoded['final_response'];
            }
            $prompt .= "{$role}: {$text}\n";
        }
        $prompt .= "\nTu siguiente mensaje como cliente:";
        return $prompt;
    }

    /**
     * Carga el historial real de conversación de un número de teléfono.
     * Permite que el asistente "recuerde" conversaciones previas con ese contacto.
     */

    private function loadPhoneContext(string $phone): array
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($phone)) return [];

        // Cargar el helper de contexto real (app/Helpers/whatsapp_context_helper.php)
        helper('whatsapp_context');

        // Buscar el huésped por teléfono — igual que el webhook en producción
        $guest = $this->db->table('guests')
            ->where('tenant_id', $this->tenantId)
            ->where('phone', $phone)
            ->get()->getRow();

        // Construir contexto completo con el helper de producción
        $contextString = build_guest_context_data($guest, $this->tenantId, $phone);

        if (empty(trim($contextString))) return [];

        log_message('info', "[Simulator] Contexto real cargado para {$phone} — "
            . strlen($contextString) . " chars — huésped: "
            . ($guest->full_name ?? 'nuevo/desconocido'));

        // Inyectar como primer intercambio del historial:
        // user = contexto del sistema, model = acuse de recibo en JSON
        // Esto replica exactamente cómo el webhook pasa el contexto a Gemini
        return [
            [
                'role'  => 'user',
                'parts' => [['text' => 'CONTEXTO DEL SISTEMA (no mostrar al cliente): ' . $contextString]],
            ],
            [
                'role'  => 'model',
                'parts' => [['text' => '{"final_response": "Contexto cargado. Listo para atender."}']],
            ],
        ];
    }
}