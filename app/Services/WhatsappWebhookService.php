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

        // 3. IDENTIFICAR O CREAR AL GUEST (Multi-tenant estricto)
        $guest = $this->getOrCreateGuest($senderPhone, $contactName, $tenantId);

        // --- INICIO CORRECCIÓN: INTERCEPTOR DE IMÁGENES ---
        if ($messageType === 'image') {
            if($guest){
                $this->handleImageReceipt($message, $guest, $tenantId, $isSaas);
                return; // Detenemos el flujo conversacional normal
            }
            return; //no era un guest y mandó una imagen, todo: si un tenant manda algo implementar funcionalidad
        }


        // Si mandan imagen/audio y aún no lo soportamos
        if ($messageType !== 'text' && $messageType !== 'interactive') {
            $this->sendDirectReply($senderPhone, "Por el momento solo puedo leer mensajes de texto. ¿En qué te puedo ayudar?", $isSaas, $tenantId);
            return;
        }



        // 4. CONSTRUIR CONTEXTO (Placeholder para Helpers)
        // Aquí llamas a tu helper que busca citas, historial, etc., del tenant actual
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


    private function getChatHistory(string $phone, int $tenantId, int $limit = 10, int $excludeId = 0): array
    {
        // 1. Subconsulta: trae los IDs de los últimos $limit mensajes (orden DESC)
        $subQuery = $this->db->table('whatsapp_messages')
            ->select('id')
            ->where('tenant_id', $tenantId)
            ->groupStart()
            ->where('sender_phone', $phone)
            ->orWhere('recipient_phone', $phone)
            ->groupEnd()
            ->whereIn('message_type', ['text', 'interactive']);

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

            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', "[WebhookService] Gemini no devolvió un JSON válido. Raw: " . $response['text']);
                return "Hubo un error interpretando mi respuesta interna. Por favor intenta de nuevo.";
            }

            // 3. Evaluar la decisión de la IA (OPCIÓN A u OPCIÓN B de tu prompt)

            // OPCIÓN B: La IA decidió enviar un mensaje final al usuario
            if (isset($iaDecision['final_response'])) {
                $history[] = [
                    'role'  => 'model',
                    'parts' => [['text' => $iaDecision['final_response']]] // ← solo el texto legible
                ];
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

            // --- NUEVO: APAGAR IA AUTOMÁTICAMENTE ---
            // Ponemos el chat en manos humanas y marcamos el estado para que resalte en el panel
            $this->db->table('guests')
                ->where('id', $guest->id)
                ->update(['ai_active' => 0, 'chat_state' => 'OMITTED']);

            log_message('info', "[WebhookService/Tool] Administrador notificado. IA auto-desactivada para {$telefonoHuesped}.");

            // Éxito: Le decimos a Gemini que ya hicimos el trabajo para que tranquilice al cliente (será su último mensaje)
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
}