<?php

namespace App\Services;

class WhatsappWebhookService
{
    protected $db;
    protected $whatsappModel;
    protected $geminiModel;

    public function __construct()
    {
        // Instancias nativas de CI4
        $this->db = \Config\Database::connect();
        $this->whatsappModel = model('App\Models\WhatsappModel');
        $this->geminiModel = model('App\Models\GeminiModel');

        // Carga de helpers (si los tienes en app/Helpers)
        helper(['whatsapp_context']);
    }


    // =========================================================================
    // HERRAMIENTAS PMS (HOTELERÍA / CABAÑAS)
    // =========================================================================

    /**
     * Herramienta para buscar qué cabañas están libres en ciertas fechas.
     */
    public function toolConsultarDisponibilidad(array $args)
    {
        $fechaIn = $args['check_in_date'] ?? null;
        $fechaOut = $args['check_out_date'] ?? null;
        $huespedes = $args['numero_personas'] ?? 1;

        if (!$fechaIn || !$fechaOut) {
            return json_encode(['error' => 'Faltan fechas de check-in o check-out para buscar disponibilidad.']);
        }

        // Buscamos unidades que NO tengan reservas superpuestas en esas fechas
        $sql = "
            SELECT id, name, max_occupancy, beds_info, description 
            FROM accommodation_units 
            WHERE tenant_id = ? 
            AND status = 'available'
            AND max_occupancy >= ?
            AND id NOT IN (
                SELECT accommodation_unit_id 
                FROM reservations 
                WHERE tenant_id = ? 
                AND status IN ('pending', 'confirmed', 'checked_in')
                AND (check_in_date < ? AND check_out_date > ?)
            )
        ";

        $unidades = $this->db->query($sql, [
            $this->currentTenantId,
            $huespedes,
            $this->currentTenantId,
            $fechaOut, // Lógica de cruce de fechas
            $fechaIn
        ])->getResult();

        if (empty($unidades)) {
            return json_encode([
                'mensaje' => 'No hay cabañas ni habitaciones disponibles para esas fechas y esa cantidad de personas.',
                'sugerencia' => 'Dile al cliente que no tienes disponibilidad exacta, pero pregúntale si tiene flexibilidad de fechas.'
            ]);
        }

        return json_encode([
            'mensaje' => 'Sí hay disponibilidad.',
            'unidades_libres' => $unidades
        ]);
    }

    /**
     * Herramienta para crear la reserva inicial (Estado Pending/Bloqueado)
     */
    public function toolCrearReserva(array $args)
    {
        $unitId = $args['accommodation_unit_id'] ?? null;
        $fechaIn = $args['check_in_date'] ?? null;
        $fechaOut = $args['check_out_date'] ?? null;
        $precioTotal = $args['precio_total_acordado'] ?? 0;

        if (!$unitId || !$fechaIn || !$fechaOut) {
            return json_encode(['error' => 'Faltan datos clave (unidad, fechas) para crear la reserva.']);
        }

        // 1. Obtener el Guest actual (o crearlo si solo teníamos el teléfono)
        // Reutilizamos la función privada que ya teníamos en este servicio
        $nombreCliente = $args['nombre_cliente'] ?? 'Cliente WhatsApp';
        $guest = $this->getOrCreateGuest($this->currentSenderPhone, $nombreCliente, $this->currentTenantId);

        // 2. Doble validación: Asegurar que no la reservaron hace un segundo (Race condition)
        $superposicion = $this->db->table('reservations')
            ->where('accommodation_unit_id', $unitId)
            ->where('status !=', 'cancelled')
            ->where('check_in_date <', $fechaOut)
            ->where('check_out_date >', $fechaIn)
            ->countAllResults();

        if ($superposicion > 0) {
            return json_encode(['error' => 'Lo siento, esa unidad acaba de ser reservada por alguien más. Por favor ofrece otra unidad libre.']);
        }

        // 3. Insertar la reserva en estado 'pending' (A la espera del abono)
        $dataReserva = [
            'tenant_id' => $this->currentTenantId,
            'guest_id' => $guest->id,
            'accommodation_unit_id' => $unitId,
            'check_in_date' => $fechaIn,
            'check_out_date' => $fechaOut,
            'status' => 'pending',
            'total_price' => $precioTotal,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('reservations')->insert($dataReserva);
        $reservaId = $this->db->insertID();

        return json_encode([
            'mensaje' => 'Reserva creada exitosamente en estado Pendiente. Dile al cliente que la cabaña está bloqueada y envíale los datos de pago para confirmar.',
            'reservation_id' => $reservaId
        ]);
    }

    /**
     * Punto de entrada principal para procesar webhooks de Meta.
     * Aquí llega el mensaje, se enruta lógicamente, se procesa con Gemini y se responde.
     */
    public function processNotification(array $payload, string $jsonPayload, bool $isSaas, int $tenantId)
    {
        log_message('info', "[WebhookService] Iniciando procesamiento para Tenant ID: {$tenantId}");

        // 1. EXTRAER DATOS DEL PAYLOAD DE META
        $entry = $payload['entry'][0]['changes'][0]['value'] ?? [];

        // Si no es un mensaje (ej. es una actualización de estado de "leído" o "entregado"), salimos
        if (empty($entry['messages'])) {
            $this->handleStatusUpdate($entry, $tenantId);
            return;
        }

        $message = $entry['messages'][0];
        $contact = $entry['contacts'][0] ?? [];

        $senderPhone = $message['from'];
        $wamid = $message['id'];
        $messageType = $message['type'];
        $whatsappTimestamp = $message['timestamp'];
        $contactName = $contact['profile']['name'] ?? 'Usuario';

        // 2. GUARDAR MENSAJE ENTRANTE EN LA BASE DE DATOS
        $messageBody = '';
        if ($messageType === 'text') {
            $messageBody = $message['text']['body'];
        } elseif ($messageType === 'interactive') {
            // Manejo básico por si tocan un botón más adelante
            $messageBody = $message['interactive']['button_reply']['title'] ??
                $message['interactive']['list_reply']['title'] ?? 'Interacción';
        }

        $this->whatsappModel->saveMessage([
            'whatsapp_message_id' => $wamid,
            'direction'         => 'incoming',
            'sender_phone'      => $senderPhone,
            'message_body'      => $messageBody,
            'message_type'      => $messageType,
            'tenant_id'         => $tenantId,
            'whatsapp_timestamp'=> $whatsappTimestamp,
            'raw_data'          => $jsonPayload,
            'is_saas'           => $isSaas ? 1 : 0
        ]);

        // Si mandan imagen/audio y aún no lo soportamos
        if ($messageType !== 'text' && $messageType !== 'interactive') {
            $this->sendDirectReply($senderPhone, "Por el momento solo puedo leer mensajes de texto. ¿En qué te puedo ayudar?", $isSaas, $tenantId);
            return;
        }

        // 3. IDENTIFICAR O CREAR AL GUEST (Multi-tenant estricto)
        $guest = $this->getOrCreateGuest($senderPhone, $contactName, $tenantId);

        // 4. CONSTRUIR CONTEXTO (Placeholder para Helpers)
        // Aquí llamas a tu helper que busca citas, historial, etc., del tenant actual
// 4. CONSTRUIR CONTEXTO PMS MULTI-TENANT
        $systemContext = build_guest_context_data($guest, $tenantId, $senderPhone);


        // 4.5. VERIFICAR SI LA IA ESTÁ PAUSADA (MODO HUMANO)
        // Buscamos el último estado de la conversación de este huésped
        $ultimoMensaje = $this->db->table('whatsapp_messages')
            ->where('tenant_id', $tenantId)
            ->groupStart()
            ->where('sender_phone', $senderPhone)
            ->orWhere('recipient_phone', $senderPhone)
            ->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getRow();

        // Si el estado está marcado como 'PAUSED' o 'HUMAN', el bot no responde.
        if ($ultimoMensaje && $ultimoMensaje->conversation_state === 'HUMAN_MODE') {
            log_message('info', "[WebhookService] Modo Humano activo para {$senderPhone}. La IA no responderá.");
            return; // Detenemos la ejecución aquí, el mensaje ya se guardó en la BD para que el humano lo lea.
        }

        // 5. OBTENER PROMPT DEL TENANT (System Instruction)
        $promptConfig = $this->getAiPrompt($tenantId, 'assistant'); // 'assistant' es el profile_role por defecto
        if (!$promptConfig) {
            log_message('error', "[WebhookService] El Tenant {$tenantId} no tiene un prompt configurado.");
            return;
        }

        // 6. OBTENER HISTORIAL DE CHAT RECIENTE
        // Extraemos los últimos 10 mensajes para que Gemini tenga memoria de la conversación
        $chatHistory = $this->getChatHistory($senderPhone, $tenantId, 10);

        // 7. LLAMAR A GEMINI
        // Le pasamos la instrucción del sistema, el historial y el mensaje actual
        $aiResponseText = $this->callGemini(
            $messageBody,
            $promptConfig, // Le pasamos el objeto completo (tiene instruction, tools y version)
            $systemContext,
            $chatHistory
        );

        // 8. ENVIAR RESPUESTA A WHATSAPP
        $this->sendDirectReply($senderPhone, $aiResponseText, $isSaas, $tenantId);
    }

    /**
     * =================================================================================
     * MÉTODOS PRIVADOS DE SOPORTE (Lógica de negocio aislada)
     * =================================================================================
     */

    private function getOrCreateGuest(string $phone, string $name, int $tenantId)
    {
        $guest = $this->db->table('guests')
            ->where('phone', $phone)
            ->where('tenant_id', $tenantId)
            ->get()
            ->getRow();

        if (!$guest) {
            $data = [
                'tenant_id'  => $tenantId,
                'full_name'  => $name,
                'phone'      => $phone,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->table('guests')->insert($data);
            $data['id'] = $this->db->insertID();
            $guest = (object) $data;
            log_message('info', "[WebhookService] Nuevo Guest creado: ID {$guest->id} para Tenant {$tenantId}");
        }

        return $guest;
    }

    private function getAiPrompt(int $tenantId, string $role)
    {
        $prompt = $this->db->table('ai_prompts')
            ->where('tenant_id', $tenantId)
            ->where('profile_role', $role)
            ->get()
            ->getRow();

        if (!$prompt) {
            $prompt = $this->db->table('ai_prompts')
                ->where('tenant_id', 99) // Tu fallback global
                ->where('profile_role', $role)
                ->get()
                ->getRow();
        }

        return $prompt;
    }

    private function buildSystemContext($guest, int $tenantId): string
    {
        // Aquí va tu Helper de Contexto.
        // Por ahora es un Placeholder que inyecta datos dinámicos al prompt de Gemini
        $fechaActual = date('Y-m-d H:i:s');
        $diaSemana = date('l'); // Retorna Monday, Tuesday... puedes traducirlo

        $contexto = "
        [CONTEXTO DEL SISTEMA INYECTADO AUTOMÁTICAMENTE]
        - Fecha y hora actual del servidor: {$fechaActual} ({$diaSemana})
        - Nombre del usuario interactuando: {$guest->full_name}
        - Teléfono del usuario: {$guest->phone}
        ";

        // Si tienes helpers cargados, podrías hacer algo como:
        // $contexto .= build_guest_context_data($guest->id, $tenantId);

        return $contexto;
    }

    private function getChatHistory(string $phone, int $tenantId, int $limit = 10): array
    {
        // Obtenemos los últimos mensajes de este número en este tenant
        $messages = $this->db->table('whatsapp_messages')
            ->where('tenant_id', $tenantId)
            ->groupStart()
            ->where('sender_phone', $phone)
            ->orWhere('recipient_phone', $phone)
            ->groupEnd()
            ->whereIn('message_type', ['text', 'interactive']) // Solo texto por ahora
            ->orderBy('created_at', 'ASC') // Importante: ASC para que la IA lea en orden cronológico
            ->limit($limit)
            ->get()
            ->getResult();

        $history = [];
        foreach ($messages as $msg) {
            // Formatear para Gemini (usualmente usa roles 'user' y 'model')
            $role = ($msg->direction === 'incoming') ? 'user' : 'model';
            $history[] = [
                'role' => $role,
                'parts' => [['text' => $msg->message_body]]
            ];
        }

        return $history;
    }


    private function callGemini(string $currentMessage, object $promptConfig, string $systemContext, array &$history)
    {
        // 1. Unimos la instrucción estática, el esquema de tools y el contexto dinámico
        $toolsSchema = $promptConfig->tools_schema_json ? "\n\nHERRAMIENTAS DISPONIBLES:\n" . $promptConfig->tools_schema_json : "";
        $finalSystemInstruction = $promptConfig->system_instruction . $toolsSchema . "\n\n" . $systemContext;

        // 2. Añadimos el mensaje del usuario al historial
        $history[] = [
            'role' => 'user',
            'parts' => [['text' => $currentMessage]]
        ];

        $maxIterations = 5; // Evitar bucles infinitos si la IA se vuelve loca
        $iteration = 0;

        // Instanciamos el ejecutor de herramientas (Tu clase refactorizada)
        $toolExecutor = new \App\Services\WhatsappToolExecutor();
        $toolExecutor->initialize($this);

        while ($iteration < $maxIterations) {
            $iteration++;
            log_message('info', "[WebhookService] Llamando a Gemini... (Iteración {$iteration})");

            // Llamamos al modelo pasándole que espere un JSON de vuelta
            $response = $this->geminiModel->generateChatResponse($history, $finalSystemInstruction, $promptConfig->model_version);

            if (isset($response['error'])) {
                return "Disculpa, tengo problemas técnicos: " . $response['error'];
            }

            // Limpiamos la respuesta por si Gemini le puso ```json ... ``` (usando la función de tu GeminiModel)
            $cleanJson = $this->geminiModel->cleanJsonResponse($response['text']);
            $iaDecision = json_decode($cleanJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', "[WebhookService] Gemini no devolvió un JSON válido. Raw: " . $response['text']);
                return "Hubo un error interpretando mi respuesta interna. Por favor intenta de nuevo.";
            }

            // 3. Evaluar la decisión de la IA (OPCIÓN A u OPCIÓN B de tu prompt)

            // OPCIÓN B: La IA decidió enviar un mensaje final al usuario
            if (isset($iaDecision['final_response'])) {
                // Guardar la respuesta de la IA en el historial (para la BD luego)
                $history[] = [
                    'role' => 'model',
                    'parts' => [['text' => $cleanJson]] // Guardamos el JSON crudo en el historial para mantener la estructura
                ];
                return $iaDecision['final_response'];
            }

            // OPCIÓN A: La IA decidió llamar a una herramienta
            if (isset($iaDecision['tool_calls']) && is_array($iaDecision['tool_calls'])) {
                // Guardamos la decisión de llamar a la herramienta en el historial
                $history[] = [
                    'role' => 'model',
                    'parts' => [['text' => $cleanJson]]
                ];

                $toolOutputs = [];

                foreach ($iaDecision['tool_calls'] as $tool) {
                    $toolName = $tool['name'];
                    $toolArgs = $tool['arguments'] ?? [];

                    // Ejecutamos la herramienta en tu Executor
                    // Le pasamos un ID ficticio (uniqid) porque tu JSON manual no trae tool_call_id nativo
                    $executionResult = $toolExecutor->execute(uniqid(), $toolName, $toolArgs);

                    $toolOutputs[] = "Resultado de {$toolName}: " . $executionResult['output'];
                }

                // Añadimos el resultado de las herramientas al historial como si fuera el usuario respondiendo a la IA
                $history[] = [
                    'role' => 'user',
                    'parts' => [['text' => "[RESULTADO DE HERRAMIENTAS]\n" . implode("\n", $toolOutputs) . "\nAnaliza estos resultados y devuelve un JSON con 'final_response' o llama a otra herramienta si es necesario."]]
                ];

                // El bucle while() continuará y volverá a llamar a Gemini con este nuevo historial
                continue;
            }

            // Si el JSON no tiene ni final_response ni tool_calls
            log_message('error', "[WebhookService] JSON de Gemini no reconoció las opciones. Estructura: " . json_encode($iaDecision));
            return "Lo siento, no pude procesar correctamente la solicitud.";
        }

        return "Lo siento, el proceso tomó demasiados pasos y se detuvo por seguridad.";
    }


    private function sendDirectReply(string $toPhone, string $text, bool $isSaas, int $tenantId)
    {
        // 1. Envía el mensaje mediante la API de Meta
        $apiResponse = $this->whatsappModel->sendTextApi($toPhone, $text, $isSaas, $tenantId);

        // 2. Registra el mensaje de salida en la BD
        if ($apiResponse['success'] ?? false) {
            $wamid = $apiResponse['messages'][0]['id'] ?? null;

            $this->whatsappModel->saveMessage([
                'whatsapp_message_id' => $wamid,
                'direction'         => 'outgoing',
                'recipient_phone'   => $toPhone,
                'message_body'      => $text,
                'message_type'      => 'text',
                'tenant_id'         => $tenantId,
                'is_saas'           => $isSaas ? 1 : 0,
                'created_at'        => date('Y-m-d H:i:s')
            ]);
        } else {
            log_message('error', "[WebhookService] Error enviando mensaje a {$toPhone}: " . json_encode($apiResponse));
        }
    }

    private function handleStatusUpdate(array $entry, int $tenantId)
    {
        // Esto maneja cuando Meta avisa que el mensaje fue "entregado" o "leído"
        if (isset($entry['statuses'][0])) {
            $statusData = $entry['statuses'][0];
            $wamid = $statusData['id'];
            $status = $statusData['status']; // 'sent', 'delivered', 'read', 'failed'

            // Actualiza el estado en la base de datos local
            $this->db->table('whatsapp_messages')
                ->where('whatsapp_message_id', $wamid)
                ->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        }
    }

    /**
     * Herramienta para alertar al personal humano de que el huésped necesita asistencia.
     */
    public function toolNotificarAdministrador(array $args)
    {
        $mensajeUsuario = $args['mensaje'] ?? 'El huésped solicitó asistencia humana sin especificar el motivo.';

        // 1. Obtener datos del Tenant para saber a qué número de administrador avisar
        $tenant = $this->db->table('tenants')->where('id', $this->currentTenantId)->get()->getRow();

        // Asumimos que guardas el número del admin en settings_json, si no, usamos el teléfono general del tenant
        $settings = json_decode($tenant->settings_json ?? '{}', true);
        $adminPhone = $settings['admin_whatsapp_phone'] ?? $tenant->phone;

        if (empty($adminPhone)) {
            log_message('error', "[WebhookService] No se pudo notificar al admin. Tenant {$this->currentTenantId} no tiene teléfono configurado.");
            return json_encode([
                'error' => 'No se pudo contactar al administrador internamente. Pídele disculpas al huésped y dile que intente llamar al número del hotel.'
            ]);
        }

        // 2. Obtener datos del huésped actual
        // currentSenderPhone lo deberías tener definido en processNotification
        $guest = $this->getOrCreateGuest($this->currentSenderPhone, 'Huésped', $this->currentTenantId);
        $nombreHuesped = $guest->full_name;
        $telefonoHuesped = $this->currentSenderPhone;

        // 3. Formatear la Alerta Interna para el Administrador
        $alerta  = "🚨 *ALERTA DE ASISTENCIA HUMANA* 🚨\n\n";
        $alerta .= "El bot requiere tu intervención para el huésped *{$nombreHuesped}* (+{$telefonoHuesped}).\n\n";
        $alerta .= "*Motivo de escalamiento:*\n\"{$mensajeUsuario}\"\n\n";
        $alerta .= "👉 *Acción requerida:* Ingresa al panel de PMS, busca el chat de este número, *Pausa al Bot* y respóndele manualmente.";

        // 4. Enviar el mensaje por WhatsApp al Administrador
        // Usamos el mismo canal/número del hotel para enviarse un mensaje a su propio dueño/staff
        $apiResponse = $this->whatsappModel->sendTextApi($adminPhone, $alerta, $this->isSaas, $this->currentTenantId);

        if ($apiResponse && isset($apiResponse['messages'][0]['id'])) {
            // Éxito: Le decimos a Gemini que ya hicimos el trabajo para que tranquilice al cliente
            return json_encode([
                'success' => true,
                'resultado' => 'El administrador fue notificado exitosamente.',
                'instruccion_para_ia' => 'Dile al cliente que ya notificaste a un asesor humano y que se pondrán en contacto con él a la brevedad posible.'
            ]);
        } else {
            // Fallo en la API de Meta
            log_message('error', "[WebhookService] Falló el envío de la alerta al admin: " . json_encode($apiResponse));
            return json_encode([
                'error' => 'Hubo un fallo técnico al intentar avisar al administrador. Dile al cliente que hubo un error y que intente más tarde.'
            ]);
        }
    }
}