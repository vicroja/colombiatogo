<?php

namespace App\Models;

use CodeIgniter\Model;

class WhatsappModel extends Model
{
    protected $table            = 'whatsapp_messages';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';
    protected $useTimestamps    = true; // CI4 manejará created_at y updated_at
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields    = [
        'whatsapp_message_id', 'direction', 'sender_phone', 'recipient_phone',
        'message_body', 'message_type', 'status', 'whatsapp_timestamp',
        'raw_data', 'openai_thread', 'estado', 'conversation_state',
        'appointment_id_relation', 'media_url', 'interactive_data',
        'template_data', 'error_details', 'is_saas', 'tenant_id'
    ];

    /**
     * Obtiene el último mensaje de un hilo de conversación.
     */
    public function getLastMessageByThread($thread_id)
    {
        return $this->where('openai_thread', $thread_id)
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    /**
     * Actualiza el estado de la conversación para un mensaje específico.
     */
    public function updateConversationState($message_db_id, $state)
    {
        return $this->update($message_db_id, ['conversation_state' => $state]);
    }

    /**
     * Guarda un mensaje genérico (entrante o saliente)
     * Reemplaza tu antigua función save_message
     */
    public function saveMessage($data)
    {
        // Asegurar que los campos JSON sean strings
        foreach (['raw_data', 'interactive_data', 'template_data'] as $json_field) {
            if (isset($data[$json_field]) && !is_string($data[$json_field]) && $data[$json_field] !== null) {
                $data[$json_field] = json_encode($data[$json_field]);
            }
        }

        // Si whatsapp_timestamp viene numérico (epoch), convertirlo
        if (isset($data['whatsapp_timestamp']) && is_numeric($data['whatsapp_timestamp'])) {
            $data['whatsapp_timestamp'] = date('Y-m-d H:i:s', $data['whatsapp_timestamp']);
        }

        $this->insert($data);
        return $this->getInsertID();
    }


    /**
     * -------------------------------------------------------------------
     * COMUNICACIÓN CON LA API DE META (100% MULTI-TENANT)
     * -------------------------------------------------------------------
     */
    private function callWhatsappApi($payload_array, $is_saas = false, $tenant_id_override = null)
    {
        // 1. Determinar el Tenant Context (Inquilino actual)
        // Prioridad 1: Override directo (ej. desde un Webhook o Cronjob)
        // Prioridad 2: Sesión del usuario logueado en la web
        $tenantId = $tenant_id_override ?? session()->get('tenant_id');

        if (!$tenantId) {
            log_message('critical', '[WhatsApp API] Intento de envío sin un tenant_id definido.');
            return ['success' => false, 'message' => 'Falta el contexto del Tenant.'];
        }

        // 2. Limpiar el número de destino
        if (isset($payload_array['to'])) {
            $payload_array['to'] = $this->cleanAndValidatePhoneNumber($payload_array['to']);
        }

        // 3. Obtener credenciales del Tenant desde la tabla `tenants`
        // En CI4 puedes llamar a la BD sin necesidad de cargar modelos completos si es algo rápido
        $tenant = $this->db->table('tenants')
            ->select('settings_json')
            ->where('id', $tenantId)
            ->get()
            ->getRow();

        if (!$tenant || empty($tenant->settings_json)) {
            log_message('error', "[WhatsApp API] El Tenant ID {$tenantId} no tiene configuraciones JSON.");
            return ['success' => false, 'message' => 'Tenant sin configuración.'];
        }

        $settings = json_decode($tenant->settings_json, true);

        // 4. Asignar credenciales dinámicas
        // Ojo: Esto asume que en el JSON guardas estas llaves. Si el envío es de la línea maestra SaaS, usas credenciales maestras.

        $accessToken   = $is_saas ? getenv('SAAS_WA_ACCESS_TOKEN') : ($settings['whatsapp_token'] ?? null);
        $phoneNumberId = $is_saas ? getenv('SAAS_WA_PHONE_ID') : ($settings['whatsapp_phone_number_id'] ?? null);
        $baseUrl       = getenv('WA_API_BASE_URL') ?: 'https://graph.facebook.com/v19.0';

        if (!$accessToken || !$phoneNumberId) {
            log_message('error', "[WhatsApp API] El Tenant ID {$tenantId} no tiene Tokens de WhatsApp configurados.");
            return ['success' => false, 'message' => 'Credenciales de Meta incompletas para este Tenant.'];
        }

        $url = rtrim($baseUrl, '/') . '/' . $phoneNumberId . '/messages';

        // 5. Ejecutar CURLRequest nativo de CI4
        $client = \Config\Services::curlrequest();

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept'        => 'application/json',
                ],
                'json' => $payload_array,
                'http_errors' => false,
                'timeout' => 30
            ]);

            $httpCode = $response->getStatusCode();
            $decodedResponse = json_decode($response->getBody(), true);

            if ($httpCode >= 200 && $httpCode < 300 && isset($decodedResponse['messages'][0]['id'])) {
                return $decodedResponse;
            }

            $errorMsg = $decodedResponse['error']['message'] ?? 'Error desconocido de la API de Meta';
            log_message('error', "[WhatsApp API] Tenant {$tenantId} | HTTP {$httpCode}: {$errorMsg}");

            return ['success' => false, 'error_type' => 'api', 'message' => $errorMsg, 'http_code' => $httpCode];

        } catch (\Exception $e) {
            log_message('error', "[WhatsApp API] Tenant {$tenantId} | Falló CURLRequest: " . $e->getMessage());
            return ['success' => false, 'error_type' => 'curl', 'message' => $e->getMessage()];
        }
    }

    public function sendTextApi($recipientPhone, $messageBody, $is_saas = false, $tenant_id_override = null)
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $recipientPhone,
            'type' => 'text',
            'text' => ['preview_url' => false, 'body' => $messageBody]
        ];

        return $this->callWhatsappApi($payload, $is_saas, $tenant_id_override);
    }

    public function sendTemplateApi($recipientPhone, $templateName, $languageCode, $components, $is_saas = false, $tenant_id_override = null)
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $recipientPhone,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $languageCode],
                'components' => $components
            ]
        ];
        return $this->callWhatsappApi($payload, $is_saas, $tenant_id_override);
    }

    private function cleanAndValidatePhoneNumber($phone)
    {
        if (empty($phone) || !is_string($phone)) return false;

        $cleanedPhone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($cleanedPhone) === 10) {
            $cleanedPhone = '57' . $cleanedPhone;
        }

        if (strlen($cleanedPhone) !== 12 || strpos($cleanedPhone, '57') !== 0) {
            return false;
        }

        return $cleanedPhone;
    }

    /**
     * Obtiene la lista de conversaciones agrupadas para DataTables.
     */
    public function getConversationsDatatables($tenantId, $start, $length, $search, $filters)
    {
        $builder = $this->db->table($this->table . ' m');

        // Subconsulta para obtener la última actividad por teléfono
        $builder->select('
            MAX(m.id) as last_id,
            IF(m.direction = "incoming", m.sender_phone, m.recipient_phone) as phone_key,
            MAX(m.created_at) as last_activity
        ');
        $builder->where('m.tenant_id', $tenantId);
        $builder->groupBy('phone_key');

        // Aplicar filtros de fecha si existen
        if (!empty($filters['desde'])) $builder->where('m.created_at >=', $filters['desde'] . ' 00:00:00');
        if (!empty($filters['hasta'])) $builder->where('m.created_at <=', $filters['hasta'] . ' 23:59:59');

        $subQuery = $builder->getCompiledSelect();

        // Consulta Principal uniendo con Guests
        $mainBuilder = $this->db->table("($subQuery) as sub");
        $mainBuilder->select('sub.*, m2.message_body, m2.direction, m2.conversation_state, g.full_name as guest_name, t.name as tenant_name');
        $mainBuilder->join($this->table . ' m2', 'm2.id = sub.last_id');
        $mainBuilder->join('guests g', 'g.phone = sub.phone_key AND g.tenant_id = ' . $tenantId, 'left');
        $mainBuilder->join('tenants t', 't.id = ' . $tenantId);

        if (!empty($search)) {
            $mainBuilder->groupStart()
                ->like('g.full_name', $search)
                ->orLike('sub.phone_key', $search)
                ->orLike('m2.message_body', $search)
                ->groupEnd();
        }

        if (!empty($filters['estado'])) {
            $mainBuilder->where('m2.conversation_state', $filters['estado']);
        }

        $mainBuilder->orderBy('sub.last_activity', 'DESC');

        return $mainBuilder->get($length, $start)->getResult();
    }

    public function countAllConversations($tenantId)
    {
        return $this->db->table($this->table)->where('tenant_id', $tenantId)->groupBy('sender_phone')->countAllResults();
    }
}