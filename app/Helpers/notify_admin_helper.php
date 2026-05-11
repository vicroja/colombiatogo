<?php

/**
 * Notify Admin Helper (PMS Multi-Tenant)
 * ---------------------------------------------------------------------
 * Centraliza el envío de alertas operativas al administrador del Tenant
 * usando la plantilla 'aviso_admin_general' de Meta WhatsApp.
 *
 * Casos de uso típicos:
 *  - Cancelación de reserva
 *  - Cliente solicita contacto humano (se desactiva la IA)
 *  - La IA no pudo manejar la conversación
 *  - Errores críticos en pagos, tours o disponibilidad
 *  - Reservas nuevas que requieren atención del owner
 *
 * Diseño:
 *  - Sólo necesita: tenant_id, motivo (asunto) y nombre del huésped.
 *  - Resuelve el teléfono y nombre del admin desde tenants.settings_json.
 *  - Soporta múltiples admins (admin_phones[]) → envía a todos.
 *  - Soporta override manual de teléfono/nombre si el caller los conoce.
 *  - Anti-spam opcional: throttle por (tenant_id + asunto) para evitar
 *    avisos repetidos del mismo evento en pocos minutos.
 */

if (!function_exists('notify_admin')) {

    /**
     * Envía una alerta al/los administrador/es del tenant.
     *
     * @param int    $tenantId      ID del tenant.
     * @param string $guestName     Nombre del huésped/cliente involucrado ({{2}}).
     * @param string $subject       Asunto/mensaje específico ({{3}}). Ej: "Cancelación de reserva #123", "Solicita contacto humano".
     * @param array  $options       Opcional:
     *                              - 'guest_id'        => int     (para FK related_guest_id)
     *                              - 'reservation_id'  => int     (para FK related_reservation_id)
     *                              - 'admin_phone'     => string  (override; si no, lee de settings_json)
     *                              - 'admin_name'      => string  (override; si no, lee de settings_json)
     *                              - 'send_immediately'=> bool    (default true)
     *                              - 'throttle_minutes'=> int     (default 0; si >0 evita repetir el mismo asunto en N minutos)
     * @return bool  true si al menos un mensaje fue encolado correctamente.
     */
    function notify_admin(int $tenantId, string $guestName, string $subject, array $options = []): bool
    {
        if ($tenantId <= 0 || $subject === '') {
            log_message('error', "[NotifyAdmin] Llamada inválida: tenant={$tenantId}, subject vacío o nulo.");
            return false;
        }

        // ---------------------------------------------------------------
        // 1. Cargar datos del tenant (settings_json contiene admin_phones)
        // ---------------------------------------------------------------
        $db = \Config\Database::connect();
        $tenant = $db->table('tenants')
            ->select('id, name, settings_json, is_active')
            ->where('id', $tenantId)
            ->get()
            ->getRow();

        if (!$tenant || (int)$tenant->is_active !== 1) {
            log_message('error', "[NotifyAdmin] Tenant {$tenantId} no existe o está inactivo.");
            return false;
        }

        $settings = json_decode($tenant->settings_json ?? '{}', true) ?: [];

        // ---------------------------------------------------------------
        // 2. Resolver lista de destinatarios admin
        //    Prioridad: override manual > admin_phones[] > admin_whatsapp_phone (legacy)
        // ---------------------------------------------------------------
        $recipients = [];

        if (!empty($options['admin_phone'])) {
            $recipients[] = [
                'phone' => preg_replace('/\D+/', '', $options['admin_phone']),
                'name'  => $options['admin_name'] ?? 'Administrador'
            ];
        } elseif (!empty($settings['admin_phones']) && is_array($settings['admin_phones'])) {
            foreach ($settings['admin_phones'] as $admin) {
                if (empty($admin['phone'])) continue;
                $recipients[] = [
                    'phone' => preg_replace('/\D+/', '', $admin['phone']),
                    'name'  => $admin['name'] ?? 'Administrador'
                ];
            }
        } elseif (!empty($settings['admin_whatsapp_phone'])) {
            // Fallback legacy
            $recipients[] = [
                'phone' => preg_replace('/\D+/', '', $settings['admin_whatsapp_phone']),
                'name'  => $tenant->name ?: 'Administrador'
            ];
        }

        if (empty($recipients)) {
            log_message('error', "[NotifyAdmin] Tenant {$tenantId}: no hay teléfonos de admin configurados en settings_json.");
            return false;
        }

        // ---------------------------------------------------------------
        // 3. Throttle anti-spam (opcional)
        //    Evita re-enviar la misma alerta del mismo asunto en N min.
        // ---------------------------------------------------------------
        $throttleMin = (int)($options['throttle_minutes'] ?? 0);
        if ($throttleMin > 0) {
            $since = gmdate('Y-m-d H:i:s', time() - ($throttleMin * 60));
            $exists = $db->table('whatsapp_message_queue')
                ->where('tenant_id', $tenantId)
                ->where('recipient_type', 'ADMIN')
                ->like('shortcode_data_override_json', $subject)
                ->where('created_at >=', $since)
                ->countAllResults();
            if ($exists > 0) {
                log_message('info', "[NotifyAdmin] Tenant {$tenantId}: alerta '{$subject}' suprimida por throttle ({$throttleMin}min).");
                return false;
            }
        }

        // ---------------------------------------------------------------
        // 4. Localizar la plantilla 'aviso_admin_general' del tenant
        // ---------------------------------------------------------------
        $template = $db->table('autowhatsapptemplate')
            ->where('meta_template_name', 'aviso_admin_general')
            ->where('tenant_id', $tenantId)
            ->where('status', 'Active')
            ->get()
            ->getRow();

        if (!$template) {
            log_message('error', "[NotifyAdmin] Tenant {$tenantId}: plantilla 'aviso_admin_general' no encontrada o inactiva.");
            return false;
        }

        // ---------------------------------------------------------------
        // 5. Encolar y disparar para cada admin
        // ---------------------------------------------------------------
        $queueModel = model('App\Models\WhatsappMessageQueueModel');
        $sendImmediately = $options['send_immediately'] ?? true;
        $queuedCount = 0;

        foreach ($recipients as $admin) {

            // Mapeo posicional para los placeholders {{1}}, {{2}}, {{3}}
            // de la plantilla aprobada en Meta.
            $shortcodeOverride = [
                '1' => $admin['name'],   // Nombre del admin
                '2' => $guestName,       // Nombre del huésped
                '3' => $subject          // Asunto del aviso
            ];

            $queueData = [
                'tenant_id'                    => $tenantId,
                'autowhatsapptemplate_id'      => $template->id,
                'recipient_phone'              => $admin['phone'],
                'recipient_type'               => 'ADMIN',
                'related_guest_id'             => $options['guest_id']       ?? null,
                'related_reservation_id'       => $options['reservation_id'] ?? null,
                'scheduled_send_datetime_utc'  => gmdate('Y-m-d H:i:s'),
                'shortcode_data_override_json' => json_encode($shortcodeOverride, JSON_UNESCAPED_UNICODE),
                'is_saas'                      => 0
            ];

            $queueId = $queueModel->enqueueMessage($queueData);

            if (!$queueId) {
                log_message('warning', "[NotifyAdmin] Falló encolado para admin {$admin['phone']} (tenant {$tenantId}).");
                continue;
            }

            $queuedCount++;
            log_message('info', "[NotifyAdmin] Aviso encolado (qid:{$queueId}) → {$admin['name']} ({$admin['phone']}) | tenant {$tenantId} | asunto: {$subject}");

            // Envío inmediato (recomendado para alertas)
            if ($sendImmediately) {
                $ok = $queueModel->processSingleMessage($queueId);
                if (!$ok) {
                    log_message('warning', "[NotifyAdmin] Envío inmediato falló para qid {$queueId}. Quedará PENDING para el cron.");
                }
            }
        }

        return $queuedCount > 0;
    }
}