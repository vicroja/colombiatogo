<?php

/**
 * WhatsApp Journey Helper (PMS Multi-Tenant)
 * Dispara el encolamiento de mensajes automatizados (Journeys) basados en eventos del hotel.
 */

if (!function_exists('trigger_whatsapp_journey_event')) {

    /**
     * @param string $triggerEventSource Ej: 'RESERVATION_CREATED', 'CHECKIN_REMINDER'.
     * @param array $relatedIds Ej: ['tenant_id'=>1, 'reservation_id'=>5, 'guest_id'=>10, 'fallback_phone'=>'57300...']
     * @param bool $sendImmediately Si es true, programa el envío para este mismo instante (UTC).
     */
    function trigger_whatsapp_journey_event(string $triggerEventSource, array $relatedIds, bool $sendImmediately = false)
    {
        $tenantId = $relatedIds['tenant_id'] ?? null;

        if (!$tenantId) {
            log_message('error', "[JourneyHelper] Falla al disparar '{$triggerEventSource}': Faltante tenant_id.");
            return false;
        }

        // 1. Cargar Modelos de CI4
        $templateModel = model('App\Models\AutowhatsapptemplateModel');
        $queueModel    = model('App\Models\WhatsappMessageQueueModel');

        // 2. Buscar si el Inquilino (Tenant) tiene plantillas activas para este evento
        $activeTemplates = $templateModel->get_active_templates_by_trigger($triggerEventSource, $tenantId);

        if (empty($activeTemplates)) {
            log_message('info', "[JourneyHelper] Evento '{$triggerEventSource}' ignorado: No hay plantillas activas para el Tenant {$tenantId}.");
            return false;
        }

        $messagesQueued = 0;
        $guestId = $relatedIds['guest_id'] ?? null;
        $reservationId = $relatedIds['reservation_id'] ?? null;
        $recipientPhone = $relatedIds['fallback_phone'] ?? null;

        // Si no pasaron el teléfono directo, intentamos buscarlo en la DB usando el guest_id
        if (empty($recipientPhone) && $guestId) {
            $db = \Config\Database::connect();
            $guest = $db->table('guests')->where('id', $guestId)->get()->getRow();
            if ($guest && !empty($guest->phone)) {
                $recipientPhone = $guest->phone;
            }
        }

        if (empty($recipientPhone)) {
            log_message('error', "[JourneyHelper] No se pudo determinar el teléfono destino para '{$triggerEventSource}'.");
            return false;
        }

        // 3. Procesar cada plantilla configurada para este evento
        foreach ($activeTemplates as $template) {

            // Calcular fecha de envío
            $sendDatetimeUtc = gmdate('Y-m-d H:i:s'); // Inmediato por defecto (UTC)

            if (!$sendImmediately && $template->trigger_delay_value !== null && $template->trigger_delay_unit !== null) {
                // Si la plantilla tiene un retraso configurado (ej. 24 HOURS después, o -24 HOURS antes del checkin)
                // Aquí deberías cruzar la lógica con la fecha del Check-in (reservation_id)
                // Por ahora lo simplificamos a "Sumar al momento actual" para mantener la base de CI3
                $modifyString = sprintf('%+d %s', $template->trigger_delay_value, strtolower($template->trigger_delay_unit));
                $dateObj = new \DateTime('now', new \DateTimeZone('UTC'));
                $dateObj->modify($modifyString);
                $sendDatetimeUtc = $dateObj->format('Y-m-d H:i:s');
            }

            // Datos extra para el Shortcode Parser (ej. Nombre del huésped, Nombre de Cabaña)
            $shortcodeDataOverride = $relatedIds['shortcode_data_override_json'] ?? null;

            // Preparamos la inserción en la cola
            $queueData = [
                'tenant_id'                   => $tenantId,
                'autowhatsapptemplate_id'     => $template->id,
                'recipient_phone'             => $recipientPhone,
                'recipient_type'              => $template->recipient_type ?? 'GUEST',
                'related_guest_id'            => $guestId,
                'related_reservation_id'      => $reservationId,
                'scheduled_send_datetime_utc' => $sendDatetimeUtc,
                'shortcode_data_override_json'=> $shortcodeDataOverride,
                'is_saas'                     => $relatedIds['is_saas'] ?? 0
            ];

            if ($queueId = $queueModel->enqueueMessage($queueData)) {
                $messagesQueued++;
                log_message('info', "[JourneyHelper] Mensaje encolado (ID: {$queueId}) para envío el {$sendDatetimeUtc} UTC.");

                // 🔥 AQUÍ ESTÁ LA MAGIA DEL ENVÍO INMEDIATO 🔥
                if ($sendImmediately) {
                    log_message('info', "[JourneyHelper] Ejecutando envío INMEDIATO para el mensaje ID: {$queueId}");

                    // Llamamos al método que acabamos de crear en el modelo
                    $fueExitoso = $queueModel->processSingleMessage($queueId);

                    if (!$fueExitoso) {
                        log_message('warning', "[JourneyHelper] El envío inmediato falló para el ID {$queueId}. Quedará PENDING para que el Cronjob lo reintente.");
                    }
                }
            }
        }

        return $messagesQueued > 0;
    }
}