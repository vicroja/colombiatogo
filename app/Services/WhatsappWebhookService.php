<?php

namespace App\Services;

class WhatsappWebhookService
{
    protected $db;
    protected $whatsappModel;
    protected $geminiModel;

    protected $currentTenantId;
    protected $currentSenderPhone;
    protected $isSaas;

    protected $currentGuest = null; // ← agregar esta línea

    protected $isAdminMode = false;
    protected $adminInfo   = null; // datos del admin desde settings_json


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
        $fechaIn    = $args['check_in_date']  ?? null;
        $fechaOut   = $args['check_out_date'] ?? null;
        $numAdults  = (int) ($args['num_adults']   ?? 1);
        $numChildren = (int) ($args['num_children'] ?? 0);

        // Capacidad total para el filtro SQL (quién cabe físicamente)
        $totalPersonas = $numAdults + $numChildren;

        if (!$fechaIn || !$fechaOut) {
            return json_encode(['error' => 'Faltan fechas de check-in o check-out.']);
        }

        // 1. Buscar unidades libres por capacidad física y fechas
        $sql = "
        SELECT au.id, au.name, au.max_occupancy, au.beds_info, au.base_occupancy
        FROM accommodation_units au
        WHERE au.tenant_id = ?
        AND au.status = 'available'
        AND au.max_occupancy >= ?
        AND au.id NOT IN (
            SELECT accommodation_unit_id
            FROM reservations
            WHERE tenant_id = ?
            AND status IN ('pending', 'confirmed', 'checked_in')
            AND (check_in_date < ? AND check_out_date > ?)
        )
    ";

        $unidades = $this->db->query($sql, [
            $this->currentTenantId,
            $totalPersonas,          // ← filtra por capacidad real
            $this->currentTenantId,
            $fechaOut,
            $fechaIn
        ])->getResult();

        if (empty($unidades)) {
            return json_encode([
                'mensaje'    => 'No hay cabañas disponibles para esas fechas y cantidad de personas.',
                'sugerencia' => 'Pregúntale si tiene flexibilidad de fechas o si pueden dividirse en dos cabañas.'
            ]);
        }

        // 2. Obtener plan tarifario por defecto
        $defaultPlan = $this->db->table('rate_plans')
            ->where('tenant_id', $this->currentTenantId)
            ->where('is_default', 1)
            ->get()->getRow();
        $ratePlanId = $defaultPlan ? $defaultPlan->id : 1;

        // 3. Calcular precio con PriceCalculatorService (él maneja extras internamente)
        $priceService = new \App\Services\PriceCalculatorService();
        $resultadosIA = [];

        foreach ($unidades as $u) {
            $calc = $priceService->calculateStay(
                $u->id,
                $ratePlanId,
                $fechaIn,
                $fechaOut,
                $numAdults,   // ← adultos separados
                $numChildren  // ← niños separados
            );

            $resultadosIA[] = [
                'id_unidad'               => $u->id,
                'nombre'                  => $u->name,
                'noches'                  => $calc['nights'],
                'adultos'                 => $numAdults,
                'niños'                   => $numChildren,
                'precio_total_definitivo' => $calc['total_price'],
                'desglose'                => "Habitación: {$calc['room_total']} | Extras: {$calc['extra_total']}",
                'camas'                   => $u->beds_info,
            ];
        }

        return json_encode([
            'mensaje'        => 'Hay disponibilidad. Precios TOTALES ya calculados para toda la estancia.',
            'unidades_libres' => $resultadosIA
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

        // FIX B1 + R2: Transacción atómica previene race condition. num_adults/children ahora se persisten.
        $numAdults   = (int)($args['num_adults']   ?? 1);
        $numChildren = (int)($args['num_children'] ?? 0);

        $this->db->transStart();

        $this->db->table('reservations')->insert([
            'tenant_id'             => $this->currentTenantId,
            'guest_id'              => $guest->id,
            'accommodation_unit_id' => $unitId,
            'check_in_date'         => $fechaIn,
            'check_out_date'        => $fechaOut,
            'status'                => 'pending',
            'total_price'           => $precioTotal,
            'num_adults'            => $numAdults,
            'num_children'          => $numChildren,
            'created_at'            => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s'),
        ]);
        $reservaId = $this->db->insertID();

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            log_message('error', "[WebhookService/toolCrearReserva] Transacción fallida para unit {$unitId}.");
            return json_encode(['error' => 'Error en la base de datos al crear la reserva. Por favor intenta de nuevo.']);
        }

        return json_encode([
            'mensaje'        => 'Reserva creada exitosamente en estado Pendiente. Dile al cliente que la unidad está bloqueada y envíale los datos de pago para confirmar.',
            'reservation_id' => $reservaId,
        ]);
    }

    /**
     * Punto de entrada principal para procesar webhooks de Meta.
     * Aquí llega el mensaje, se enruta lógicamente, se procesa con Gemini y se responde.
     */
    public function processNotification(array $payload, string $jsonPayload, bool $isSaas, int $tenantId)
    {
        log_message('info', "[WebhookService] Iniciando procesamiento para Tenant ID: {$tenantId}");
        $this->currentTenantId = $tenantId;
        $this->isSaas = $isSaas;

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
        $this->currentSenderPhone = $senderPhone;

        $wamid = $message['id'];
        $messageType = $message['type'];
        $whatsappTimestamp = $message['timestamp'];
        $contactName = $contact['profile']['name'] ?? 'Usuario';

        // 2. GUARDAR MENSAJE ENTRANTE EN LA BASE DE DATOS
        $messageBody = '';
        if ($messageType === 'text') {
            $messageBody = $message['text']['body'];
        }
        // --- INICIO CORRECCIÓN: INTERCEPTOR DE AUDIO ---
        elseif ($messageType === 'audio' || $messageType === 'voice') {
            log_message('info', "[AudioInterceptor] Detectada nota de voz de {$senderPhone}. Transcribiendo...");

            $messageBody = $this->handleAudioInterceptor($message, $tenantId, $isSaas);

            // Si la transcripción fue exitosa, actualizamos el registro en la BD
            if ($messageBody) {
                //aun no se hace nada en este caso
            } else {
                $this->sendDirectReply($senderPhone, "Recibí tu audio, pero no logré transcribirlo correctamente. ¿Podrías escribírmelo?", $isSaas, $tenantId);
                return;
            }
        }

        $savedMessageId = $this->whatsappModel->saveMessage([
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


        // 2.5. DETECTAR SI ES ADMIN O GUEST
        $this->adminInfo = $this->detectAdminSender($senderPhone, $tenantId);
        $this->isAdminMode = ($this->adminInfo !== null);

        if ($this->isAdminMode) {
            // ═══ FLUJO ADMIN ═══
            log_message('info', "[WebhookService] Modo ADMIN detectado para {$senderPhone} (Tenant {$tenantId})");

            // Solo soportamos texto e interactivos para admin por ahora
            if ($messageType !== 'text' && $messageType !== 'interactive' && $messageType !== 'audio' && $messageType !== 'voice') {
                $this->sendDirectReply($senderPhone, "Por el momento solo proceso mensajes de texto en modo gestión.", $isSaas, $tenantId);
                return;
            }

            $this->processAdminMessage($messageBody, $senderPhone, $tenantId, $isSaas, $savedMessageId);
            return;
        }

// ═══ FLUJO GUEST (sin cambios) ═══

// 3. IDENTIFICAR O CREAR AL GUEST (Multi-tenant estricto)
        $guest = $this->getOrCreateGuest($senderPhone, $contactName, $tenantId);
        $this->currentGuest = $guest;

// --- INICIO CORRECCIÓN: INTERCEPTOR DE IMÁGENES ---
        if ($messageType === 'image') {
            if($guest){
                $this->handleImageReceipt($message, $guest, $tenantId, $isSaas);
                return;
            }
            return;
        }

// Si mandan imagen/audio y aún no lo soportamos
        if ($messageType !== 'text' && $messageType !== 'interactive') {
            $this->sendDirectReply($senderPhone, "Por el momento solo puedo leer mensajes de texto. ¿En qué te puedo ayudar?", $isSaas, $tenantId);
            return;
        }

// 4. CONSTRUIR CONTEXTO PMS MULTI-TENANT
        $systemContext = build_guest_context_data($guest, $tenantId, $senderPhone);

        // 4.5. ACTUALIZAR ESTADO Y VERIFICAR HANDOFF (IA vs HUMANO)
        if ($guest) {
            // A) Si el chat estaba cerrado o inactivo, lo despertamos porque el cliente acaba de hablar
            if (isset($guest->chat_state) && $guest->chat_state !== 'ACTIVE') {
                $this->db->table('guests')->where('id', $guest->id)->update(['chat_state' => 'ACTIVE']);
                log_message('info', "[WebhookService] Chat reactivado (ACTIVE) para {$senderPhone}");
            }

            // B) Verificamos si la IA está desactivada (Handoff manual)
            if (isset($guest->ai_active) && $guest->ai_active == 0) {
                log_message('info', "[WebhookService] Modo Humano activo para {$senderPhone}. La IA ignorará el mensaje.");
                // El mensaje entrante ya se guardó en el paso 2, así que el humano lo verá en su panel.
                return;
            }
        }


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





        // 5. OBTENER PROMPT DEL TENANT (System Instruction)
        $promptConfig = $this->getAiPrompt($tenantId, 'assistant'); // 'assistant' es el profile_role por defecto
        if (!$promptConfig) {
            log_message('error', "[WebhookService] El Tenant {$tenantId} no tiene un prompt configurado.");
            return;
        }

        // 6. OBTENER HISTORIAL DE CHAT RECIENTE
        // Extraemos los últimos 10 mensajes para que Gemini tenga memoria de la conversación
        $chatHistory = $this->getChatHistory($senderPhone, $tenantId, 10, $savedMessageId);

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
            $insertId = $this->db->insertID();

            // --- INICIO CORRECCIÓN ---
            // Volvemos a consultar la base de datos para obtener el registro completo.
            // Esto garantiza que el objeto $guest tenga TODAS las columnas de la tabla
            // (document, chat_state, ai_active, etc.) evitando el "Undefined property".
            $guest = $this->db->table('guests')
                ->where('id', $insertId)
                ->get()
                ->getRow();
            // --- FIN CORRECCIÓN ---

            log_message('info', "[WebhookService] Nuevo Guest creado e hidratado: ID {$guest->id} para Tenant {$tenantId}");
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

    /**
     * @deprecated Reemplazado por build_guest_context_data() del helper whatsapp_context.
     * Conservado solo para referencia — no llamar directamente.
     * El contexto real se genera en processNotification() línea ~264.
     */
    private function buildSystemContext($guest, int $tenantId): string
    {
        log_message('warning', '[WebhookService] buildSystemContext() está deprecado — usar build_guest_context_data().');
        return build_guest_context_data($guest, $tenantId, $guest->phone ?? '');
    }


    private function getChatHistory(string $phone, int $tenantId, int $limit = 10, int $excludeId = 0): array
    {
        // 1. Subconsulta: trae los IDs de los últimos $limit mensajes (orden DESC)
        // FIX R1: Excluir mensajes de relay de herramientas que callGemini inyecta al historial.
        // Estos mensajes ([Consultando:...] y [RESULTADO DE HERRAMIENTAS]) no son mensajes reales
        // del usuario/asistente y confunden a Gemini en conversaciones largas.
        $subQuery = $this->db->table('whatsapp_messages')
            ->select('id')
            ->where('tenant_id', $tenantId)
            ->groupStart()
            ->where('sender_phone', $phone)
            ->orWhere('recipient_phone', $phone)
            ->groupEnd()
            ->whereIn('message_type', ['text', 'interactive'])
            ->groupStart()
            ->where('message_body NOT LIKE', '[Consultando:%')
            ->where('message_body NOT LIKE', '[RESULTADO DE HERRAMIENTAS]%')
            ->groupEnd();

        if ($excludeId > 0) {
            $subQuery->where('id !=', $excludeId);
        }

        $lastIds = $subQuery
            ->orderBy('created_at', 'DESC') // ← los más recientes primero
            ->limit($limit)
            ->get()
            ->getResultArray();

        if (empty($lastIds)) {
            return [];
        }

        $ids = array_column($lastIds, 'id');

        // 2. Query principal: trae esos mensajes en orden ASC (cronológico para Gemini)
        $messages = $this->db->table('whatsapp_messages')
            ->whereIn('id', $ids)
            ->orderBy('created_at', 'ASC') // ← Gemini los lee en orden natural
            ->get()
            ->getResult();

        // 3. Formatear para Gemini
        $history = [];
        foreach ($messages as $msg) {
            $role = ($msg->direction === 'incoming') ? 'user' : 'model';
            $history[] = [
                'role'  => $role,
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

// --- INICIO CORRECCIÓN: TOLERANCIA A FALLOS DE FORMATO IA ---
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('warning', "[WebhookService] Gemini no devolvió JSON válido. Forzando formato. Raw: " . $response['text']);
                // Si falla el JSON, forzamos a que sea un final_response plano.
                $iaDecision = [
                    'final_response' => trim($cleanJson)
                ];
            } elseif (is_string($iaDecision)) {
                log_message('warning', "[WebhookService] Gemini devolvió un string JSON, no un objeto. Forzando formato. String: " . $iaDecision);
                // A veces Gemini devuelve "Texto" (con comillas). json_decode lo hace string, no array.
                $iaDecision = [
                    'final_response' => trim($iaDecision)
                ];
            }

            // --- FIN CORRECCIÓN ---

            // 3. Evaluar la decisión de la IA (OPCIÓN A u OPCIÓN B de tu prompt)

            // OPCIÓN B: La IA decidió enviar un mensaje final al usuario
            if (isset($iaDecision['final_response'])) {
                $history[] = [
                    'role'  => 'model',
                    'parts' => [['text' => $iaDecision['final_response']]]
                ];

                // Procesar metadata si existe — actualiza funnel_stage y estado en BD
                // FIX R3: currentGuest puede ser null si processNotification falló antes de asignarlo.
                // Verificar que sea un objeto válido (no solo truthy) antes de processar metadata.
                if (!empty($iaDecision['metadata']) && isset($this->currentGuest) && is_object($this->currentGuest)) {
                    $this->processMetadata($iaDecision['metadata'], $this->currentGuest);
                }

                return $iaDecision['final_response'];
            }

            // OPCIÓN A: La IA decidió llamar a una herramienta
            if (isset($iaDecision['tool_calls']) && is_array($iaDecision['tool_calls'])) {
                $toolNames = array_column($iaDecision['tool_calls'], 'name');
                $history[] = [
                    'role'  => 'model',
                    'parts' => [['text' => '[Consultando: ' . implode(', ', $toolNames) . '...]']]
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
        // Meta no devuelve un campo 'success'. El éxito se confirma si viene el ID del mensaje ('wamid').
        if (isset($apiResponse['messages'][0]['id'])) {
            $wamid = $apiResponse['messages'][0]['id'];

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
            // Si realmente falla, Meta devuelve un objeto 'error'
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

    /**
     * Herramienta para escalar la conversación a un administrador humano.
     * Decide automáticamente entre TEXTO LIBRE (si la ventana 24h está abierta)
     * o PLANTILLA APROBADA (si está cerrada), maximizando la calidad del aviso.
     */
    public function toolNotificarAdministrador(array $args)
    {
        // ─── 1. Compatibilidad con schema viejo y nuevo ─────────────────────
        $asunto    = $args['asunto']    ?? $args['mensaje'] ?? 'El cliente solicitó asistencia humana.';
        $categoria = $args['categoria'] ?? 'otro';
        $asunto    = mb_substr($asunto, 0, 250);

        // ─── 2. Datos del huésped y del tenant ──────────────────────────────
        $guest = $this->getOrCreateGuest(
            $this->currentSenderPhone,
            'Huésped',
            $this->currentTenantId
        );
        $nombreHuesped   = $guest->full_name ?: 'Cliente sin nombre';
        $telefonoHuesped = $this->currentSenderPhone;

        $tenant   = $this->db->table('tenants')->where('id', $this->currentTenantId)->get()->getRow();
        $settings = json_decode($tenant->settings_json ?? '{}', true) ?: [];

        // Resolver lista de admins (mismo patrón que notify_admin)
        $admins = [];
        if (!empty($settings['admin_phones']) && is_array($settings['admin_phones'])) {
            foreach ($settings['admin_phones'] as $a) {
                if (empty($a['phone'])) continue;
                $admins[] = [
                    'phone' => preg_replace('/\D+/', '', $a['phone']),
                    'name'  => $a['name'] ?? 'Administrador'
                ];
            }
        } elseif (!empty($settings['admin_whatsapp_phone'])) {
            $admins[] = [
                'phone' => preg_replace('/\D+/', '', $settings['admin_whatsapp_phone']),
                'name'  => $tenant->name ?: 'Administrador'
            ];
        }

        if (empty($admins)) {
            log_message('error', "[WebhookService/Tool] Tenant {$this->currentTenantId} sin admins configurados.");
            // Igual desactivamos la IA — el chat queda OMITTED para revisión
            $this->db->table('guests')->where('id', $guest->id)
                ->update(['ai_active' => 0, 'chat_state' => 'OMITTED']);
            return json_encode([
                'error' => 'No hay administradores configurados. El chat quedó marcado para revisión manual.',
                'instruccion_para_ia' => 'Dile al cliente que su solicitud fue registrada y será atendida pronto. Este es tu ÚLTIMO mensaje.'
            ]);
        }

        log_message('info', "[WebhookService/Tool] Escalamiento | tenant:{$this->currentTenantId} | guest:{$nombreHuesped} | categoria:{$categoria} | admins:" . count($admins));

        // ─── 3. Cargar helpers ──────────────────────────────────────────────
        helper('whatsapp_window');
        helper('notify_admin');

        // ─── 4. Procesar cada admin individualmente ─────────────────────────
        // Cada admin puede tener una ventana de 24h diferente: uno escribió
        // hace 1h (ventana abierta), otro hace 3 días (cerrada).
        $alMenosUnoEnviado = false;

        foreach ($admins as $admin) {
            $ventanaAbierta = is_whatsapp_window_open($admin['phone'], $this->currentTenantId);

            if ($ventanaAbierta) {
                // ─── 4a. VENTANA ABIERTA → texto libre rico ─────────────────
                $alerta = $this->buildAdminAlertFreeForm(
                    $admin['name'],
                    $nombreHuesped,
                    $telefonoHuesped,
                    $asunto,
                    $categoria
                );

                $apiResponse = $this->whatsappModel->sendTextApi(
                    $admin['phone'],
                    $alerta,
                    $this->isSaas,
                    $this->currentTenantId
                );

                if ($apiResponse && isset($apiResponse['messages'][0]['id'])) {
                    log_message('info', "[WebhookService/Tool] Alerta (texto libre) enviada a {$admin['name']} ({$admin['phone']}).");
                    $alMenosUnoEnviado = true;

                    // Registrar en el historial para que aparezca en el panel
                    $this->whatsappModel->saveMessage([
                        'whatsapp_message_id' => $apiResponse['messages'][0]['id'],
                        'direction'           => 'outgoing',
                        'recipient_phone'     => $admin['phone'],
                        'message_body'        => $alerta,
                        'message_type'        => 'text',
                        'tenant_id'           => $this->currentTenantId,
                        'is_saas'             => $this->isSaas ? 1 : 0,
                        'created_at'          => date('Y-m-d H:i:s')
                    ]);
                } else {
                    log_message('warning', "[WebhookService/Tool] Falló texto libre a {$admin['phone']}, intentando plantilla como fallback.");
                    // Fallback: intentar con plantilla si el texto libre falla
                    $ok = notify_admin(
                        $this->currentTenantId, $nombreHuesped, $asunto,
                        [
                            'guest_id'         => $guest->id,
                            'admin_phone'      => $admin['phone'],
                            'admin_name'      => $admin['name'],
                            'send_immediately' => true,
                            'throttle_minutes' => 0  // ya estamos en fallback, no throttlear
                        ]
                    );
                    if ($ok) $alMenosUnoEnviado = true;
                }
            } else {
                // ─── 4b. VENTANA CERRADA → plantilla aprobada ───────────────
                log_message('info', "[WebhookService/Tool] Ventana cerrada para {$admin['phone']} → usando plantilla.");
                $ok = notify_admin(
                    $this->currentTenantId,
                    $nombreHuesped,
                    $asunto,
                    [
                        'guest_id'         => $guest->id,
                        'admin_phone'      => $admin['phone'],
                        'admin_name'       => $admin['name'],
                        'send_immediately' => true,
                        'throttle_minutes' => 10
                    ]
                );
                if ($ok) $alMenosUnoEnviado = true;
            }
        }

        // ─── 5. Desactivar IA SIEMPRE (aunque envíos hayan fallado) ─────────
        $this->db->table('guests')
            ->where('id', $guest->id)
            ->update(['ai_active' => 0, 'chat_state' => 'OMITTED']);

        log_message('info', "[WebhookService/Tool] IA desactivada para guest {$guest->id}.");

        // ─── 6. Respuesta a Gemini ──────────────────────────────────────────
        if ($alMenosUnoEnviado) {
            return json_encode([
                'success' => true,
                'resultado' => 'Administrador(es) notificado(s) correctamente. IA desactivada.',
                'instruccion_para_ia' => 'Da un mensaje de cierre cálido: dile al cliente que ya avisaste al equipo, '
                    . 'que un asesor humano se pondrá en contacto pronto, y agradece su paciencia. '
                    . 'Este es tu ÚLTIMO mensaje en esta conversación — no llames más herramientas.'
            ]);
        } else {
            return json_encode([
                'success' => false,
                'resultado' => 'Falló el envío automático al admin, pero el chat quedó marcado para revisión humana.',
                'instruccion_para_ia' => 'Dile al cliente que su solicitud fue registrada y que un asesor lo contactará pronto. '
                    . 'Este es tu ÚLTIMO mensaje — no llames más herramientas.'
            ]);
        }
    }


    /**
     * Construye el texto libre rico para enviar al admin cuando la ventana de
     * 24h está abierta. Aprovechamos para meter contexto que la plantilla
     * de Meta no permite (emojis, negritas, links al panel, etc.).
     */
    private function buildAdminAlertFreeForm(
        string $adminName,
        string $guestName,
        string $guestPhone,
        string $asunto,
        string $categoria
    ): string {
        // Emoji por categoría para que el admin reconozca de un vistazo
        $emoji = [
            'solicita_humano'     => '🙋',
            'cliente_molesto'     => '😠',
            'cotizacion_especial' => '💰',
            'problema_tecnico'    => '⚠️',
            'ia_no_resuelve'      => '🤖',
            'otro'                => '📌',
        ][$categoria] ?? '🚨';

        $txt  = "{$emoji} *Aviso para {$adminName}*\n\n";
        $txt .= "Hay un nuevo asunto para revisar.\n\n";
        $txt .= "👤 *Cliente:* {$guestName}\n";
        $txt .= "📱 *Teléfono:* +{$guestPhone}\n";
        $txt .= "🗂️ *Categoría:* " . str_replace('_', ' ', $categoria) . "\n\n";
        $txt .= "📝 *Asunto:*\n{$asunto}\n\n";
        $txt .= "👉 Ingresa al panel PMS, busca el chat de este número y respóndele. ";
        $txt .= "La IA ya quedó desactivada para este cliente.";

        return $txt;
    }


    public function toolEnviarFotosCabana(array $args): string
    {
        $entityType = $args['entity_type'] ?? 'tenant';
        $unitId     = $args['unit_id'] ?? null;

        // 1. Obtener la URL base del servidor para construir URLs públicas
        $baseUrl = rtrim(config('App')->baseURL, '/');

        // 2. Consultar fotos según el tipo
        $builder = $this->db->table('tenant_media')
            ->where('tenant_id', $this->currentTenantId)
            ->where('entity_type', $entityType)
            ->where('file_type', 'image')
            ->orderBy('is_main', 'DESC') // La foto principal primero
            ->orderBy('sort_order', 'ASC');

        if ($entityType === 'unit' && $unitId) {
            $builder->where('entity_id', $unitId);
        }

        $fotos = $builder->limit(5)->get()->getResult(); // Máx 5 fotos para no saturar

        if (empty($fotos)) {
            return json_encode([
                'error'       => 'No hay fotos disponibles para mostrar.',
                'instruccion' => 'Dile al cliente que en este momento no tienes fotos cargadas pero que puede visitar la web del hotel.'
            ]);
        }

        // 3. Enviar cada foto por WhatsApp
        $enviadas = 0;
        foreach ($fotos as $foto) {
            $imageUrl = $baseUrl . '/' . ltrim($foto->file_path, '/');
            $caption  = $foto->description ?? '';

            $payload = [
                'messaging_product' => 'whatsapp',
                'to'                => $this->currentSenderPhone,
                'type'              => 'image',
                'image'             => [
                    'link'    => $imageUrl,
                    'caption' => $caption
                ]
            ];

            // Reutilizamos el método privado callWhatsappApi del WhatsappModel
            $result = $this->whatsappModel->sendImageApi(
                $this->currentSenderPhone,
                $imageUrl,
                $caption,
                $this->isSaas,
                $this->currentTenantId
            );

            if (isset($result['messages'][0]['id'])) {
                $enviadas++;
            }

            // Pequeña pausa para no saturar la API de Meta
            if (count($fotos) > 1) {
                usleep(300000); // 0.3 segundos entre fotos
            }
        }

        return json_encode([
            'success'     => true,
            'enviadas'    => $enviadas,
            'instruccion' => "Se enviaron {$enviadas} foto(s) al cliente. Ahora pregúntale qué le pareció o si quiere reservar."
        ]);
    }

    /**
     * Procesa una imagen entrante, descarga, aplica OCR y registra el pago si es un comprobante válido.
     */
    private function handleImageReceipt(array $message, object $guest, int $tenantId, bool $isSaas)
    {
        $senderPhone = $message['from'];
        $mediaId = $message['image']['id'];

        log_message('info', "[WebhookService/Pagos] Interceptada imagen de {$senderPhone}. Evaluando comprobante...");

        // 1. Obtener Token del Tenant para descargar
        $tenant = $this->db->table('tenants')->where('id', $tenantId)->get()->getRow();
        $settings = json_decode($tenant->settings_json ?? '{}', true);

        $accessToken = $isSaas ? getenv('SAAS_WA_ACCESS_TOKEN') : ($settings['whatsapp_token'] ?? '');
        $bankAccounts = $settings['bank_accounts'] ?? []; // Array con cuentas válidas del hotel

        if (empty($accessToken)) {
            log_message('error', "[WebhookService/Pagos] Tenant {$tenantId} sin token para descargar imagen.");
            return;
        }

        // 2. Descargar la imagen
        $mediaFile = $this->whatsappModel->downloadMediaFromMeta($mediaId, $accessToken);

        if (!$mediaFile) {
            $this->sendDirectReply($senderPhone, "Recibí una imagen, pero hubo un error técnico al descargarla. Por favor intenta de nuevo en unos minutos.", $isSaas, $tenantId);
            return;
        }

        // 3. Analizar con Gemini Vision
        $base64Image = base64_encode($mediaFile['data']);
        $ocrResult = $this->geminiModel->analyzeReceiptImage($base64Image, $mediaFile['mime_type'], $bankAccounts);

        if (!$ocrResult['success'] || empty($ocrResult['data'])) {
            $this->sendDirectReply($senderPhone, "Disculpa, no pude leer correctamente la imagen.", $isSaas, $tenantId);
            return;
        }

        $ocrData = $ocrResult['data'];

        // 4. Lógica de Decisión
        if (!($ocrData['is_receipt'] ?? false)) {
            // No es un comprobante (es una foto normal)
            $this->sendDirectReply($senderPhone, "¡Qué buena foto! 📸 Recuerda que soy el asistente virtual del hotel. ¿En qué te puedo ayudar con tu estadía?", $isSaas, $tenantId);
            return;
        }

        // Es un comprobante, ¿es a una cuenta válida?
        if (!empty($bankAccounts) && !($ocrData['is_valid_account'] ?? false)) {
            $this->sendDirectReply($senderPhone, "Recibí tu comprobante, pero la cuenta de destino no coincide con las cuentas oficiales del hotel. Por favor, comunícate con un asesor humano para verificar.", $isSaas, $tenantId);
            return;
        }

        $amount = (float) ($ocrData['amount'] ?? 0);

        if ($amount <= 0) {
            $this->sendDirectReply($senderPhone, "Detecté tu comprobante, pero no logré leer el monto pagado de forma clara. En un momento un asesor lo verificará manualmente.", $isSaas, $tenantId);
            return;
        }

        // 5. Buscar Reserva Activa
        $reserva = $this->db->table('reservations')
            ->where('guest_id', $guest->id)
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->orderBy('id', 'DESC')
            ->get()->getRow();

        // --- NUEVO: GUARDADO FÍSICO DEL ARCHIVO ---
        $uploadPath = FCPATH . "uploads/tenants/{$tenantId}/payments/";
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Generamos un nombre único para evitar colisiones
        $fileName = "receipt_" . time() . "_" . uniqid() . ".jpg";
        $fullPath = $uploadPath . $fileName;
        $dbPath   = "uploads/tenants/{$tenantId}/payments/" . $fileName;

        file_put_contents($fullPath, $mediaFile['data']);
        log_message('info', "[WebhookService/Pagos] Archivo guardado físicamente en: {$dbPath}");
        // ------------------------------------------

        // 6. Registrar el Pago en la BD (Ahora con attachment_path)
        // FIX B3: Validar que existe una reserva activa antes de intentar registrar el pago.
        // Sin esta guardia, $reserva->id lanza un error fatal si el cliente no tiene reservas.
        if (!$reserva) {
            log_message('warning', "[WebhookService/Pagos] Comprobante recibido de {$senderPhone} pero no tiene reservas activas. Pago ignorado.");
            $this->sendDirectReply(
                $senderPhone,
                "Recibí tu comprobante, pero no encontré ninguna reserva activa a tu nombre. " .
                "Por favor comunícate con nosotros para verificar.",
                $isSaas,
                $tenantId
            );
            return;
        }

        $this->db->table('payments')->insert([
            'tenant_id'      => $tenantId,
            'reservation_id' => $reserva->id,
            'amount'         => $amount,
            'payment_method' => 'bank_transfer',
            'reference'      => $ocrData['reference'] ?? 'Sin referencia',
            'bank_name'      => $ocrData['bank_name'] ?? 'No detectado',
            'receipt_date'   => $ocrData['date'] ?? date('Y-m-d'),
            'ocr_raw_data'   => json_encode($ocrData),
            'attachment_path' => $dbPath, // <-- Guardamos la referencia para el administrador
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s')
        ]);

        // 7. Actualizar estado de la reserva si estaba en pending
        if ($reserva->status === 'pending') {
            $this->db->table('reservations')->where('id', $reserva->id)->update(['status' => 'confirmed']);
            $estadoAviso = "¡Excelente! Hemos registrado tu pago por $".number_format($amount, 0)." y **tu reserva ha sido confirmada** exitosamente. 🎉";
        } else {
            $estadoAviso = "¡Gracias! Hemos registrado un abono adicional por $".number_format($amount, 0)." a tu reserva actual.";
        }

        log_message('info', "[WebhookService/Pagos] Pago de {$amount} registrado con éxito para Reserva {$reserva->id} (Guest: {$senderPhone})");

        // 8. Responder al cliente
        $this->sendDirectReply($senderPhone, $estadoAviso . "\n\nReferencia procesada: " . ($ocrData['reference'] ?? 'OK') . "\n¡Te esperamos pronto!", $isSaas, $tenantId);
    }
    /**
     * Descarga y transcribe una nota de voz entrante.
     */
    private function handleAudioInterceptor(array $message, int $tenantId, bool $isSaas): ?string
    {
        $mediaId = $message['audio']['id'] ?? $message['voice']['id'];

        // 1. Obtener credenciales del Tenant
        $tenant = $this->db->table('tenants')->where('id', $tenantId)->get()->getRow();
        $settings = json_decode($tenant->settings_json ?? '{}', true);
        $accessToken = $isSaas ? getenv('SAAS_WA_ACCESS_TOKEN') : ($settings['whatsapp_token'] ?? '');

        // 2. Descargar el archivo binario (Usando el método de WhatsappModel)
        $mediaFile = $this->whatsappModel->downloadMediaFromMeta($mediaId, $accessToken);

        if (!$mediaFile) return null;

        // 3. Llamar a Gemini para la transcripción
        $result = $this->geminiModel->transcribeAudio(
            $mediaFile['data'], // Binario raw
            $mediaFile['mime_type'],
            "voice_note_{$mediaId}"
        );

        return ($result['status'] === 'success') ? $result['message'] : null;
    }


    // Agregar al final de WhatsappWebhookService, antes del cierre de la clase

    /**
     * Procesa el metadata devuelto por Gemini en cada final_response.
     * Actualiza funnel_stage y conversation_context_json en la tabla guests.
     * También persiste datos del huésped si Gemini los detectó en la conversación.
     *
     * @param array  $metadata  El objeto metadata del JSON de Gemini
     * @param object $guest     El objeto guest actual
     */
    private function processMetadata(array $metadata, object $guest): void
    {
        $updates = [];

        // 1. Actualizar etapa del funnel si cambió
        $validStages = ['cold', 'interested', 'evaluating', 'objecting', 'ready_close', 'post_booking'];
        if (!empty($metadata['funnel_stage']) && in_array($metadata['funnel_stage'], $validStages)) {
            $updates['funnel_stage'] = $metadata['funnel_stage'];
        }

        // 2. Persistir el estado de conversación actualizado
        if (!empty($metadata['actualizar_estado'])) {
            $contextoActual = json_decode($guest->conversation_context_json ?? '{}', true) ?? [];
            $contextoNuevo  = array_merge($contextoActual, $metadata['actualizar_estado']);
            $updates['conversation_context_json'] = json_encode($contextoNuevo);
        }

        // 3. Actualizar datos del huésped si Gemini detectó información nueva
        if (!empty($metadata['datos_huesped'])) {
            $datosNuevos = $metadata['datos_huesped'];

            // Solo actualizar campos que estaban vacíos — no sobreescribir datos existentes
            if (!empty($datosNuevos['nombre']) && empty($guest->full_name)) {
                $updates['full_name'] = $datosNuevos['nombre'];
            }
            if (!empty($datosNuevos['documento']) && empty($guest->document)) {
                $updates['document'] = $datosNuevos['documento'];
            }
        }

        if (!empty($updates)) {
            $this->db->table('guests')->where('id', $guest->id)->update($updates);

            log_message('info', "[WebhookService] Metadata procesado para guest {$guest->id}: " .
                json_encode($updates));
        }
    }

    /**
     * Tool: consultar tours disponibles.
     * Se agrega al WebhookService para ser llamada por WhatsappToolExecutor.
     */
    public function toolConsultarToursDisponibles(array $args): string
    {
        $fechaDesde  = $args['fecha_desde'] ?? date('Y-m-d');
        $fechaHasta  = $args['fecha_hasta'] ?? null;
        $numPersonas = (int)($args['num_personas'] ?? 1);

        $query = "
        SELECT
            ts.id AS schedule_id,
            t.id  AS tour_id,
            t.name AS tour_nombre,
            t.description,
            t.duration_minutes,
            t.meeting_point,
            t.difficulty_level,
            ts.start_datetime,
            ts.max_pax,
            ts.current_pax,
            (ts.max_pax - ts.current_pax) AS cupos_disponibles,
            COALESCE(ts.price_adult_override, t.price_adult) AS precio_adulto,
            COALESCE(ts.price_child_override, t.price_child) AS precio_nino
        FROM tour_schedules ts
        JOIN tours t ON t.id = ts.tour_id
        WHERE t.tenant_id = ?
          AND t.is_active = 1
          AND ts.status = 'scheduled'
          AND ts.start_datetime >= ?
          AND (ts.max_pax - ts.current_pax) >= ?
    ";

        $params = [$this->currentTenantId, $fechaDesde . ' 00:00:00', $numPersonas];

        if ($fechaHasta) {
            $query .= " AND ts.start_datetime <= ?";
            $params[] = $fechaHasta . ' 23:59:59';
        }

        $query .= " ORDER BY ts.start_datetime ASC LIMIT 10";

        $salidas = $this->db->query($query, $params)->getResult();

        if (empty($salidas)) {
            return json_encode([
                'mensaje'    => 'No hay tours disponibles para esas fechas y cantidad de personas.',
                'sugerencia' => 'Pregúntale si tiene flexibilidad de fechas o si quiere ver otras opciones.',
            ]);
        }

        $resultados = array_map(fn($s) => [
            'schedule_id'      => (int)$s->schedule_id,
            'tour_id'          => (int)$s->tour_id,
            'nombre'           => $s->tour_nombre,
            'descripcion'      => $s->description,
            'duracion_minutos' => (int)$s->duration_minutes,
            'punto_encuentro'  => $s->meeting_point,
            'dificultad'       => $s->difficulty_level,
            'fecha_salida'     => $s->start_datetime,
            'cupos_disponibles'=> (int)$s->cupos_disponibles,
            'precio_adulto'    => (float)$s->precio_adulto,
            'precio_nino'      => (float)$s->precio_nino,
            'precio_total_ejemplo' => "Para {$numPersonas} adultos: " .
                number_format((float)$s->precio_adulto * $numPersonas, 0, ',', '.'),
        ], $salidas);

        return json_encode([
            'mensaje'  => 'Tours disponibles encontrados.',
            'salidas'  => $resultados,
        ]);
    }

    /**
     * Tool: reservar un tour desde WhatsApp.
     */
    public function toolReservarTour(array $args): string
    {
        $scheduleId    = (int)($args['schedule_id']            ?? 0);
        $numAdults     = (int)($args['num_adults']             ?? 1);
        $numChildren   = (int)($args['num_children']           ?? 0);
        $precioTotal   = (float)($args['precio_total_acordado'] ?? 0);
        $nombreCliente = $args['nombre_cliente']               ?? 'Cliente WhatsApp';
        $pickupLocation= $args['pickup_location']              ?? null;

        if (!$scheduleId || $precioTotal <= 0) {
            return json_encode(['error' => 'Faltan datos clave para crear la reserva de tour.']);
        }

        $scheduleModel = new \App\Models\TourScheduleModel();
        $totalPax      = $numAdults + $numChildren;

        // Verificar disponibilidad en tiempo real (previene race conditions)
        if (!$scheduleModel->checkAvailability($scheduleId, $totalPax)) {
            return json_encode([
                'error' => 'Lo siento, los cupos se llenaron mientras conversábamos. Llama a consultar_tours_disponibles para ver alternativas.',
            ]);
        }

        // Obtener o crear el guest
        $guest = $this->getOrCreateGuest($this->currentSenderPhone, $nombreCliente, $this->currentTenantId);

        $db = \Config\Database::connect();
        $db->transStart();

        // Crear la reserva de tour
        $tourResModel = new \App\Models\TourReservationModel();
        $tourResId = $tourResModel->insert([
            'tenant_id'           => $this->currentTenantId,
            'schedule_id'         => $scheduleId,
            'guest_id'            => $guest->id,
            'num_adults'          => $numAdults,
            'num_children'        => $numChildren,
            'total_price'         => $precioTotal,
            'pickup_location'     => $pickupLocation,
            'status'              => 'confirmed',
            'price_snapshot_json' => json_encode([
                'origen'       => 'whatsapp',
                'precio_adulto'=> $precioTotal / max($numAdults, 1),
                'num_adults'   => $numAdults,
                'num_children' => $numChildren,
                'total'        => $precioTotal,
            ]),
        ]);

        // Actualizar cupos del schedule
        $scheduleModel->adjustPax($scheduleId, $totalPax);

        $db->transComplete();

        if ($db->transStatus() === false) {
            log_message('error', "[WebhookService/toolReservarTour] Transacción fallida para schedule {$scheduleId}.");
            return json_encode(['error' => 'Error en la base de datos. Por favor intenta de nuevo.']);
        }

        log_message('info', "[WebhookService/toolReservarTour] Tour reservado #{$tourResId} para guest {$guest->id}.");

        return json_encode([
            'success'        => true,
            'reservation_id' => $tourResId,
            'mensaje'        => 'Tour reservado exitosamente en estado confirmado.',
            'instruccion'    => 'Dile al cliente que su tour está confirmado y envíale los datos del punto de encuentro y hora de salida. Luego infórmale los medios de pago disponibles.',
        ]);
    }

    /**
     * Tool: Envía fotos y videos de un tour específico al cliente.
     * Lee media_json del tour y envía las imágenes/videos por WhatsApp.
     *
     * Llamada por WhatsappToolExecutor cuando Gemini decide usar 'enviar_fotos_tour'.
     */
    public function toolEnviarFotosTour(array $args): string
    {
        $tourId = (int)($args['tour_id'] ?? 0);

        if (!$tourId) {
            return json_encode([
                'error'       => 'No se especificó el tour_id.',
                'instruccion' => 'Pregúntale al cliente de qué tour quiere ver fotos.',
            ]);
        }

        // 1. Obtener el tour y verificar que pertenece al tenant
        $tour = $this->db->table('tours')
            ->where('id', $tourId)
            ->where('tenant_id', $this->currentTenantId)
            ->get()
            ->getRow();

        if (!$tour) {
            return json_encode([
                'error'       => 'Tour no encontrado para este establecimiento.',
                'instruccion' => 'Informa al cliente que no encontraste ese tour y ofrece consultar los tours disponibles.',
            ]);
        }

        // 2. Decodificar media_json
        $mediaItems = json_decode($tour->media_json ?? '[]', true) ?? [];

        if (empty($mediaItems)) {
            return json_encode([
                'error'       => 'Este tour no tiene fotos ni videos cargados aún.',
                'instruccion' => "Dile al cliente que en este momento no hay fotos disponibles del tour \"{$tour->name}\", pero descríbele la experiencia con entusiasmo usando la información del catálogo.",
            ]);
        }

        // 3. URL base del servidor
        $baseUrl = rtrim(config('App')->baseURL, '/');

        // 4. Enviar cada media (máx 5 para no saturar)
        $enviadas   = 0;
        $maxEnvios  = 5;
        $itemsToSend = array_slice($mediaItems, 0, $maxEnvios);

        foreach ($itemsToSend as $item) {
            $mediaUrl = $baseUrl . '/' . ltrim($item['path'] ?? '', '/');
            $caption  = $item['description'] ?? '';
            $type     = $item['type'] ?? 'image';

            if ($type === 'video') {
                // Enviar como video
                $result = $this->whatsappModel->sendVideoApi(
                    $this->currentSenderPhone,
                    $mediaUrl,
                    $caption,
                    $this->isSaas,
                    $this->currentTenantId
                );
            } else {
                // Enviar como imagen
                $result = $this->whatsappModel->sendImageApi(
                    $this->currentSenderPhone,
                    $mediaUrl,
                    $caption,
                    $this->isSaas,
                    $this->currentTenantId
                );
            }

            if (isset($result['messages'][0]['id'])) {
                $enviadas++;
            } else {
                log_message('warning', "[WebhookService/toolEnviarFotosTour] Fallo al enviar media '{$item['original']}' del tour {$tourId}: " . json_encode($result));
            }

            // Pausa entre envíos para no saturar la API de Meta
            if (count($itemsToSend) > 1) {
                usleep(400000); // 0.4 segundos
            }
        }

        $totalMedia = count($mediaItems);
        $tipoEnviado = $enviadas === 1 ? 'archivo' : 'archivos';

        log_message('info', "[WebhookService/toolEnviarFotosTour] {$enviadas} de {$totalMedia} media enviadas del tour '{$tour->name}' (ID: {$tourId}) a {$this->currentSenderPhone}.");

        return json_encode([
            'success'     => true,
            'tour_nombre' => $tour->name,
            'enviadas'    => $enviadas,
            'total_media' => $totalMedia,
            'instruccion' => "Se enviaron {$enviadas} {$tipoEnviado} del tour \"{$tour->name}\" al cliente. Pregúntale qué le parecen las fotos y si quiere reservar una salida.",
        ]);
    }
    /**
     * Tool: Envía un documento/archivo del tenant al cliente por WhatsApp.
     * Lee el registro de tenant_media por ID y envía según file_type.
     */
    public function toolEnviarDocumento(array $args): string
    {
        $documentId = (int)($args['document_id'] ?? 0);

        if (!$documentId) {
            return json_encode([
                'error'       => 'No se especificó el document_id.',
                'instruccion' => 'Pregúntale al cliente qué documento necesita y consulta el listado de documentos_disponibles del contexto.',
            ]);
        }

        // 1. Obtener el registro y verificar que pertenece al tenant
        $media = $this->db->table('tenant_media')
            ->where('id', $documentId)
            ->where('tenant_id', $this->currentTenantId)
            ->get()
            ->getRow();

        if (!$media) {
            return json_encode([
                'error'       => 'Documento no encontrado para este establecimiento.',
                'instruccion' => 'Informa al cliente que no encontraste ese documento. Revisa el listado de documentos_disponibles del contexto.',
            ]);
        }

        // 2. URL pública del archivo
        $baseUrl  = rtrim(config('App')->baseURL, '/');
        $mediaUrl = $baseUrl . '/' . ltrim($media->file_path, '/');
        $caption  = $media->description ?? '';

        // 3. Enviar según el tipo de archivo
        $result = null;
        switch ($media->file_type) {
            case 'image':
                $result = $this->whatsappModel->sendImageApi(
                    $this->currentSenderPhone, $mediaUrl, $caption,
                    $this->isSaas, $this->currentTenantId
                );
                break;
            case 'video':
                $result = $this->whatsappModel->sendVideoApi(
                    $this->currentSenderPhone, $mediaUrl, $caption,
                    $this->isSaas, $this->currentTenantId
                );
                break;
            default: // pdf, doc, xlsx, etc.
                $result = $this->whatsappModel->sendDocumentApi(
                    $this->currentSenderPhone, $mediaUrl, $caption,
                    $media->description ?? basename($media->file_path),
                    $this->isSaas, $this->currentTenantId
                );
                break;
        }

        if (isset($result['messages'][0]['id'])) {
            log_message('info', "[WebhookService/toolEnviarDocumento] Documento #{$documentId} ({$media->file_type}) enviado a {$this->currentSenderPhone}.");
            return json_encode([
                'success'     => true,
                'instruccion' => "Se envió el documento \"{$media->description}\" ({$media->file_type}) al cliente. Pregúntale si necesita algo más.",
            ]);
        }

        log_message('warning', "[WebhookService/toolEnviarDocumento] Fallo al enviar documento #{$documentId}: " . json_encode($result));
        return json_encode([
            'error'       => 'No se pudo enviar el documento. Hubo un error técnico.',
            'instruccion' => 'Dile al cliente que hubo un problema técnico al enviar el archivo y que lo intente más tarde o escala al administrador.',
        ]);
    }

    /**
     * Verifica si el teléfono remitente es un admin del tenant.
     * Busca en settings_json.admin_phones[] y fallback a admin_whatsapp_phone.
     *
     * @return array|null  Datos del admin si es admin, null si es guest normal
     */
    private function detectAdminSender(string $senderPhone, int $tenantId): ?array
    {
        $tenant = $this->db->table('tenants')->where('id', $tenantId)->get()->getRow();
        if (!$tenant) return null;

        $settings = json_decode($tenant->settings_json ?? '{}', true) ?? [];

        // 1. Buscar en el array nuevo admin_phones
        $adminPhones = $settings['admin_phones'] ?? [];
        foreach ($adminPhones as $admin) {
            $normalizedAdmin = preg_replace('/[^0-9]/', '', $admin['phone'] ?? '');
            $normalizedSender = preg_replace('/[^0-9]/', '', $senderPhone);
            if ($normalizedAdmin === $normalizedSender) {
                return [
                    'phone' => $admin['phone'],
                    'name'  => $admin['name'] ?? 'Administrador',
                    'role'  => $admin['role'] ?? 'owner',
                ];
            }
        }

        // 2. Fallback: campo legacy admin_whatsapp_phone
        $legacyPhone = preg_replace('/[^0-9]/', '', $settings['admin_whatsapp_phone'] ?? '');
        $normalizedSender = preg_replace('/[^0-9]/', '', $senderPhone);
        if (!empty($legacyPhone) && $legacyPhone === $normalizedSender) {
            return [
                'phone' => $settings['admin_whatsapp_phone'],
                'name'  => $tenant->name ?? 'Administrador',
                'role'  => 'owner',
            ];
        }

        return null;
    }

    /**
     * Procesa un mensaje del admin/propietario del tenant.
     * Usa un prompt y tools diferentes al flujo de guests.
     */
    private function processAdminMessage(string $messageBody, string $senderPhone, int $tenantId, bool $isSaas, int $savedMessageId): void
    {
        // 1. Obtener prompt de admin (profile_role = 'admin')
        $promptConfig = $this->getAiPrompt($tenantId, 'admin');
        if (!$promptConfig) {
            log_message('warning', "[WebhookService] Tenant {$tenantId} no tiene prompt admin. Usando fallback.");
            $this->sendDirectReply($senderPhone, "El módulo de gestión por WhatsApp aún no está configurado para tu cuenta. Contacta soporte.", $isSaas, $tenantId);
            return;
        }

        // 2. Construir contexto operativo del admin
        $systemContext = build_admin_context_data($tenantId, $this->adminInfo);

        // 3. Historial de chat (funciona igual, filtra por phone)
        $chatHistory = $this->getChatHistory($senderPhone, $tenantId, 10, $savedMessageId);

        // 4. Llamar a Gemini con el prompt y tools de admin
        $aiResponseText = $this->callGemini(
            $messageBody,
            $promptConfig,
            $systemContext,
            $chatHistory
        );

        // 5. Responder
        $this->sendDirectReply($senderPhone, $aiResponseText, $isSaas, $tenantId);
    }
    // =========================================================================
// HERRAMIENTAS ADMIN (GESTIÓN POR WHATSAPP)
// =========================================================================

    /**
     * Tool Admin: Lista reservas de hoy o de un rango de fechas.
     */
    public function toolAdminListarReservas(array $args): string
    {
        $fecha = $args['fecha'] ?? date('Y-m-d');
        $estado = $args['estado'] ?? null; // opcional: pending, confirmed, etc.

        $tenantId = $this->currentTenantId;
        $settings = json_decode(
            $this->db->table('tenants')->where('id', $tenantId)->get()->getRow()->settings_json ?? '{}',
            true
        );
        $hasAccommodation = (bool)($settings['has_accommodation'] ?? true);
        $hasTours         = (bool)($settings['has_tours'] ?? false);

        $resultados = [];

        // ── Reservas de alojamiento ──
        if ($hasAccommodation) {
            $builder = $this->db->table('reservations r')
                ->select('r.id, r.status, r.check_in_date, r.check_out_date, r.total_price,
                      r.num_adults, r.num_children,
                      g.full_name, g.phone,
                      u.name as unit_name')
                ->join('guests g', 'g.id = r.guest_id')
                ->join('accommodation_units u', 'u.id = r.accommodation_unit_id')
                ->where('r.tenant_id', $tenantId)
                ->where('r.check_in_date <=', $fecha)
                ->where('r.check_out_date >=', $fecha);

            if ($estado) {
                $builder->where('r.status', $estado);
            } else {
                $builder->whereIn('r.status', ['pending', 'confirmed', 'checked_in']);
            }

            $reservas = $builder->orderBy('r.check_in_date', 'ASC')->get()->getResult();

            foreach ($reservas as $r) {
                $pagado = (float)($this->db->table('payments')
                    ->selectSum('amount')
                    ->where('reservation_id', $r->id)
                    ->where('entity_type', 'reservation')
                    ->get()->getRow()->amount ?? 0);

                $resultados['reservas_alojamiento'][] = [
                    'id'         => (int)$r->id,
                    'huesped'    => $r->full_name,
                    'telefono'   => $r->phone,
                    'unidad'     => $r->unit_name,
                    'check_in'   => $r->check_in_date,
                    'check_out'  => $r->check_out_date,
                    'adultos'    => (int)$r->num_adults,
                    'ninos'      => (int)$r->num_children,
                    'estado'     => $r->status,
                    'total'      => (float)$r->total_price,
                    'pagado'     => $pagado,
                    'saldo'      => (float)$r->total_price - $pagado,
                ];
            }
        }

        // ── Reservas de tours ──
        if ($hasTours) {
            $builderTours = $this->db->table('tour_reservations tr')
                ->select('tr.id, tr.status, tr.num_adults, tr.num_children, tr.total_price,
                      g.full_name, g.phone,
                      t.name as tour_name,
                      ts.start_datetime')
                ->join('guests g', 'g.id = tr.guest_id')
                ->join('tour_schedules ts', 'ts.id = tr.schedule_id')
                ->join('tours t', 't.id = ts.tour_id')
                ->where('tr.tenant_id', $tenantId)
                ->where('DATE(ts.start_datetime)', $fecha);

            if ($estado) {
                $builderTours->where('tr.status', $estado);
            } else {
                $builderTours->whereIn('tr.status', ['pending', 'confirmed']);
            }

            $tourRes = $builderTours->orderBy('ts.start_datetime', 'ASC')->get()->getResult();

            foreach ($tourRes as $tr) {
                $pagadoTour = (float)($this->db->table('payments')
                    ->selectSum('amount')
                    ->where('reservation_id', $tr->id)
                    ->where('entity_type', 'tour_reservation')
                    ->get()->getRow()->amount ?? 0);

                $resultados['reservas_tours'][] = [
                    'id'          => (int)$tr->id,
                    'cliente'     => $tr->full_name,
                    'telefono'    => $tr->phone,
                    'tour'        => $tr->tour_name,
                    'fecha_salida'=> $tr->start_datetime,
                    'adultos'     => (int)$tr->num_adults,
                    'ninos'       => (int)$tr->num_children,
                    'estado'      => $tr->status,
                    'total'       => (float)$tr->total_price,
                    'pagado'      => $pagadoTour,
                    'saldo'       => (float)$tr->total_price - $pagadoTour,
                ];
            }
        }

        if (empty($resultados)) {
            return json_encode([
                'mensaje' => "No hay reservas activas para la fecha {$fecha}.",
            ]);
        }

        return json_encode([
            'fecha_consultada' => $fecha,
            'resultados'       => $resultados,
        ]);
    }

    /**
     * Tool Admin: Resumen del día (dashboard rápido).
     */
    public function toolAdminResumenDia(array $args): string
    {
        $fecha = $args['fecha'] ?? date('Y-m-d');
        $tenantId = $this->currentTenantId;

        $settings = json_decode(
            $this->db->table('tenants')->where('id', $tenantId)->get()->getRow()->settings_json ?? '{}',
            true
        );
        $hasAccommodation = (bool)($settings['has_accommodation'] ?? true);
        $hasTours         = (bool)($settings['has_tours'] ?? false);

        $resumen = ['fecha' => $fecha];

        if ($hasAccommodation) {
            // Check-ins del día
            $checkIns = $this->db->table('reservations')
                ->where('tenant_id', $tenantId)
                ->where('check_in_date', $fecha)
                ->whereIn('status', ['pending', 'confirmed'])
                ->countAllResults();

            // Check-outs del día
            $checkOuts = $this->db->table('reservations')
                ->where('tenant_id', $tenantId)
                ->where('check_out_date', $fecha)
                ->where('status', 'checked_in')
                ->countAllResults();

            // Hospedados actualmente
            $hospedados = $this->db->table('reservations')
                ->where('tenant_id', $tenantId)
                ->where('status', 'checked_in')
                ->countAllResults();

            // Unidades totales
            $totalUnidades = $this->db->table('accommodation_units')
                ->where('tenant_id', $tenantId)
                ->where('status', 'available')
                ->countAllResults();

            $resumen['alojamiento'] = [
                'check_ins_hoy'    => $checkIns,
                'check_outs_hoy'   => $checkOuts,
                'hospedados_ahora' => $hospedados,
                'unidades_totales' => $totalUnidades,
                'ocupacion_pct'    => $totalUnidades > 0
                    ? round(($hospedados / $totalUnidades) * 100) . '%'
                    : 'N/A',
            ];
        }

        if ($hasTours) {
            // Tours del día
            $toursHoy = $this->db->query("
            SELECT t.name, ts.start_datetime, ts.max_pax, ts.current_pax,
                   (ts.max_pax - ts.current_pax) as cupos_libres,
                   tg.name as guia
            FROM tour_schedules ts
            JOIN tours t ON t.id = ts.tour_id
            LEFT JOIN tour_guides tg ON tg.id = ts.guide_id
            WHERE t.tenant_id = ?
              AND DATE(ts.start_datetime) = ?
              AND ts.status = 'scheduled'
            ORDER BY ts.start_datetime ASC
        ", [$tenantId, $fecha])->getResult();

            $resumen['tours_del_dia'] = array_map(fn($ts) => [
                'tour'    => $ts->name,
                'hora'    => $ts->start_datetime,
                'cupos'   => "{$ts->current_pax}/{$ts->max_pax}",
                'libres'  => (int)$ts->cupos_libres,
                'guia'    => $ts->guia ?? 'Sin asignar',
            ], $toursHoy);
        }

        // Pagos recibidos hoy
        $pagosHoy = $this->db->table('payments')
            ->selectSum('amount')
            ->where('tenant_id', $tenantId)
            ->where('DATE(created_at)', $fecha)
            ->get()->getRow();

        $resumen['ingresos_hoy'] = (float)($pagosHoy->amount ?? 0);

        // Reservas pendientes de pago (cualquier fecha)
        $pendientes = $this->db->table('reservations')
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->countAllResults();

        $resumen['reservas_pendientes_pago'] = $pendientes;

        return json_encode($resumen);
    }

    /**
     * Tool Admin: Consultar pagos de una reserva o del día.
     */
    public function toolAdminConsultarPagos(array $args): string
    {
        $tenantId = $this->currentTenantId;

        // Modo A: pagos de una reserva específica
        if (!empty($args['reservation_id'])) {
            $resId = (int)$args['reservation_id'];
            $entityType = $args['tipo_reserva'] ?? 'reservation'; // 'reservation' o 'tour_reservation'

            $pagos = $this->db->table('payments')
                ->where('tenant_id', $tenantId)
                ->where('reservation_id', $resId)
                ->where('entity_type', $entityType)
                ->orderBy('created_at', 'ASC')
                ->get()->getResult();

            if (empty($pagos)) {
                return json_encode(['mensaje' => "No hay pagos registrados para la reserva #{$resId}."]);
            }

            $listaPagos = array_map(fn($p) => [
                'monto'      => (float)$p->amount,
                'metodo'     => $p->payment_method,
                'referencia' => $p->reference,
                'fecha'      => $p->created_at,
            ], $pagos);

            $totalPagado = array_sum(array_column($listaPagos, 'monto'));

            return json_encode([
                'reserva_id'   => $resId,
                'pagos'        => $listaPagos,
                'total_pagado' => $totalPagado,
            ]);
        }

        // Modo B: pagos del día
        $fecha = $args['fecha'] ?? date('Y-m-d');
        $pagos = $this->db->table('payments')
            ->where('tenant_id', $tenantId)
            ->where('DATE(created_at)', $fecha)
            ->orderBy('created_at', 'ASC')
            ->get()->getResult();

        if (empty($pagos)) {
            return json_encode(['mensaje' => "No hay pagos registrados para el {$fecha}."]);
        }

        $listaPagos = array_map(fn($p) => [
            'reserva_id' => (int)$p->reservation_id,
            'tipo'       => $p->entity_type ?? 'reservation',
            'monto'      => (float)$p->amount,
            'metodo'     => $p->payment_method,
            'referencia' => $p->reference,
            'hora'       => $p->created_at,
        ], $pagos);

        return json_encode([
            'fecha'        => $fecha,
            'pagos'        => $listaPagos,
            'total_del_dia'=> array_sum(array_column($listaPagos, 'monto')),
        ]);
    }

    /**
     * Tool Admin: Buscar reserva por nombre o teléfono del cliente.
     */
    public function toolAdminBuscarReserva(array $args): string
    {
        $tenantId = $this->currentTenantId;
        $busqueda = $args['busqueda'] ?? '';

        if (strlen($busqueda) < 3) {
            return json_encode(['error' => 'El término de búsqueda debe tener al menos 3 caracteres.']);
        }

        $settings = json_decode(
            $this->db->table('tenants')->where('id', $tenantId)->get()->getRow()->settings_json ?? '{}',
            true
        );
        $hasAccommodation = (bool)($settings['has_accommodation'] ?? true);
        $hasTours         = (bool)($settings['has_tours'] ?? false);

        $resultados = [];

        if ($hasAccommodation) {
            $reservas = $this->db->table('reservations r')
                ->select('r.id, r.status, r.check_in_date, r.check_out_date, r.total_price,
                      g.full_name, g.phone, u.name as unit_name')
                ->join('guests g', 'g.id = r.guest_id')
                ->join('accommodation_units u', 'u.id = r.accommodation_unit_id')
                ->where('r.tenant_id', $tenantId)
                ->groupStart()
                ->like('g.full_name', $busqueda)
                ->orLike('g.phone', $busqueda)
                ->groupEnd()
                ->whereIn('r.status', ['pending', 'confirmed', 'checked_in'])
                ->orderBy('r.check_in_date', 'DESC')
                ->limit(5)
                ->get()->getResult();

            foreach ($reservas as $r) {
                $resultados['alojamiento'][] = [
                    'id'        => (int)$r->id,
                    'huesped'   => $r->full_name,
                    'telefono'  => $r->phone,
                    'unidad'    => $r->unit_name,
                    'check_in'  => $r->check_in_date,
                    'check_out' => $r->check_out_date,
                    'estado'    => $r->status,
                    'total'     => (float)$r->total_price,
                ];
            }
        }

        if ($hasTours) {
            $tourRes = $this->db->table('tour_reservations tr')
                ->select('tr.id, tr.status, tr.total_price,
                      g.full_name, g.phone,
                      t.name as tour_name, ts.start_datetime')
                ->join('guests g', 'g.id = tr.guest_id')
                ->join('tour_schedules ts', 'ts.id = tr.schedule_id')
                ->join('tours t', 't.id = ts.tour_id')
                ->where('tr.tenant_id', $tenantId)
                ->groupStart()
                ->like('g.full_name', $busqueda)
                ->orLike('g.phone', $busqueda)
                ->groupEnd()
                ->whereIn('tr.status', ['pending', 'confirmed'])
                ->orderBy('ts.start_datetime', 'DESC')
                ->limit(5)
                ->get()->getResult();

            foreach ($tourRes as $tr) {
                $resultados['tours'][] = [
                    'id'          => (int)$tr->id,
                    'cliente'     => $tr->full_name,
                    'telefono'    => $tr->phone,
                    'tour'        => $tr->tour_name,
                    'fecha_salida'=> $tr->start_datetime,
                    'estado'      => $tr->status,
                    'total'       => (float)$tr->total_price,
                ];
            }
        }

        if (empty($resultados)) {
            return json_encode(['mensaje' => "No encontré reservas activas para \"{$busqueda}\"."]);
        }

        return json_encode(['resultados' => $resultados]);
    }

    /**
     * Tool Admin: Cambiar estado de una reserva.
     */
    public function toolAdminCambiarEstadoReserva(array $args): string
    {
        $tenantId      = $this->currentTenantId;
        $reservaId     = (int)($args['reservation_id'] ?? 0);
        $nuevoEstado   = $args['nuevo_estado'] ?? '';
        $tipoReserva   = $args['tipo_reserva'] ?? 'alojamiento'; // 'alojamiento' o 'tour'

        $estadosValidos = ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'];
        if (!in_array($nuevoEstado, $estadosValidos)) {
            return json_encode(['error' => "Estado \"{$nuevoEstado}\" no válido. Opciones: " . implode(', ', $estadosValidos)]);
        }

        if ($tipoReserva === 'tour') {
            $tabla = 'tour_reservations';
            $estadosValidos = ['pending', 'confirmed', 'cancelled', 'refunded'];
            if (!in_array($nuevoEstado, $estadosValidos)) {
                return json_encode(['error' => "Para tours, estados válidos: " . implode(', ', $estadosValidos)]);
            }
        } else {
            $tabla = 'reservations';
        }

        $reserva = $this->db->table($tabla)
            ->where('id', $reservaId)
            ->where('tenant_id', $tenantId)
            ->get()->getRow();

        if (!$reserva) {
            return json_encode(['error' => "Reserva #{$reservaId} no encontrada."]);
        }

        $estadoAnterior = $reserva->status;

        $this->db->table($tabla)
            ->where('id', $reservaId)
            ->update([
                'status'     => $nuevoEstado,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        log_message('info', "[AdminTool] Reserva #{$reservaId} ({$tabla}): {$estadoAnterior} → {$nuevoEstado} por admin {$this->currentSenderPhone}");

        return json_encode([
            'success'         => true,
            'reservation_id'  => $reservaId,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo'    => $nuevoEstado,
            'mensaje'         => "Reserva #{$reservaId} actualizada de {$estadoAnterior} a {$nuevoEstado}.",
        ]);
    }
}