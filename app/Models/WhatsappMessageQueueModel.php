<?php

namespace App\Models;

use CodeIgniter\Model;

class WhatsappMessageQueueModel extends Model
{
    protected $table            = 'whatsapp_message_queue';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';

    // CI4 manejará automáticamente las fechas si usas insert() y update()
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields    = [
        'autowhatsapptemplate_id', 'recipient_phone', 'recipient_type',
        'related_reservation_id', 'related_guest_id', 'tenant_id',
        'shortcode_data_override_json', 'scheduled_send_datetime_utc',
        'processing_status', 'send_attempts', 'last_attempt_datetime_utc',
        'sent_whatsapp_message_id', 'response_interaction_id', 'error_log', 'is_saas'
    ];

    /**
     * Añade un mensaje a la cola de envío.
     * Reemplaza tu antigua función enqueue_message
     */
    public function enqueueMessage(array $data)
    {
        if (empty($data['autowhatsapptemplate_id']) || empty($data['recipient_phone']) || empty($data['scheduled_send_datetime_utc']) || empty($data['tenant_id'])) {
            log_message('error', '[WhatsappQueueModel] Intento de encolar mensaje con datos insuficientes: ' . json_encode($data));
            return false;
        }

        $defaults = [
            'processing_status' => 'PENDING',
            'send_attempts'     => 0,
            'is_saas'           => 0,
            'recipient_type'    => 'GUEST' // Por defecto le enviamos al huésped
        ];

        $insertData = array_merge($defaults, $data);

        // Asegurar que el campo JSON se guarde correctamente
        if (isset($insertData['shortcode_data_override_json']) && !is_string($insertData['shortcode_data_override_json'])) {
            $insertData['shortcode_data_override_json'] = json_encode($insertData['shortcode_data_override_json']);
        }

        $this->insert($insertData);
        return $this->getInsertID();
    }

    /**
     * Procesa y envía un único mensaje de la cola al instante.
     * Retorna true si fue exitoso, false en caso contrario.
     */
    public function processSingleMessage(int $queueId): bool
    {
        $whatsappModel = model('App\Models\WhatsappModel');

        // 1. Obtener el mensaje con los datos de su plantilla (Join)
        $builder = $this->db->table($this->table . ' q');
        $builder->select('q.*, t.meta_template_name, t.meta_template_language_code, t.whatsapp_message_format, t.message_body_text, t.meta_template_components_config_json');
        $builder->join('autowhatsapptemplate t', 't.id = q.autowhatsapptemplate_id', 'left');
        $builder->where('q.id', $queueId);
        $msg = $builder->get()->getRow();

        if (!$msg) return false;

        // 2. Marcar como PROCESSING
        $this->update($queueId, [
            'processing_status' => 'PROCESSING',
            'last_attempt_datetime_utc' => gmdate('Y-m-d H:i:s')
        ]);

        try {
            $apiResponse = null;

            // 3. Lógica para enviar PLANTILLAS (Templates)
            if ($msg->whatsapp_message_format === 'META_TEMPLATE') {
                $components = [];
                $overrideData = json_decode($msg->shortcode_data_override_json ?? '{}', true);

                if (!empty($overrideData)) {
                    $parameters = [];
                    foreach ($overrideData as $key => $value) {
                        $parameters[] = ['type' => 'text', 'text' => (string) $value];
                    }
                    $components[] = ['type' => 'body', 'parameters' => $parameters];
                }

                $apiResponse = $whatsappModel->sendTemplateApi(
                    $msg->recipient_phone, $msg->meta_template_name,
                    $msg->meta_template_language_code, $components,
                    (bool)$msg->is_saas, $msg->tenant_id
                );
            }
            // 4. Lógica para TEXTO LIBRE
            elseif ($msg->whatsapp_message_format === 'TEXT') {
                helper('whatsapp');
                $textoFinal = parse_whatsapp_template_message($msg->message_body_text, $msg->shortcode_data_override_json);

                $apiResponse = $whatsappModel->sendTextApi(
                    $msg->recipient_phone, $textoFinal,
                    (bool)$msg->is_saas, $msg->tenant_id
                );
            }

            // 5. Evaluar respuesta de Meta
            if ($apiResponse && isset($apiResponse['messages'][0]['id'])) {
                $wamid = $apiResponse['messages'][0]['id'];

                $this->update($queueId, [
                    'processing_status' => 'SENT',
                    'sent_whatsapp_message_id' => $wamid,
                    'send_attempts' => $msg->send_attempts + 1
                ]);

                // Guardar en el historial del chat
                $whatsappModel->saveMessage([
                    'whatsapp_message_id' => $wamid,
                    'direction'           => 'outgoing',
                    'recipient_phone'     => $msg->recipient_phone,
                    'message_type'        => strtolower($msg->whatsapp_message_format),
                    'tenant_id'           => $msg->tenant_id,
                    'is_saas'             => $msg->is_saas,
                    'created_at'          => date('Y-m-d H:i:s')
                ]);

                return true;
            } else {
                // Falló Meta
                $this->update($queueId, [
                    'processing_status' => ($msg->send_attempts >= 2) ? 'FAILED' : 'PENDING',
                    'error_log'         => json_encode($apiResponse),
                    'send_attempts'     => $msg->send_attempts + 1
                ]);
                return false;
            }

        } catch (\Exception $e) {
            $this->update($queueId, [
                'processing_status' => 'FAILED',
                'error_log'         => 'Excepción: ' . $e->getMessage(),
                'send_attempts'     => $msg->send_attempts + 1
            ]);
            return false;
        }
    }
}