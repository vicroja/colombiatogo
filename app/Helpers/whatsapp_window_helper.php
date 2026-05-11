<?php

/**
 * WhatsApp Messaging Window Helper
 * ---------------------------------------------------------------------
 * Meta WhatsApp Business API permite enviar texto libre (Customer Service
 * Window / "Free-form" messages) sólo dentro de las 24h siguientes al
 * último mensaje entrante del usuario. Fuera de esa ventana, sólo se
 * pueden enviar plantillas pre-aprobadas (HSM).
 *
 * Este helper centraliza la lógica de detección para todo el sistema.
 *
 * Usos típicos:
 *  - notify_admin() decide si manda texto rico o plantilla.
 *  - Journeys a guests deciden lo mismo.
 *  - Tools de respuesta de la IA podrían validar antes de enviar texto.
 */

if (!function_exists('is_whatsapp_window_open')) {

    /**
     * Determina si la ventana de 24h de WhatsApp está abierta para un número.
     *
     * Criterio: existe al menos un mensaje INCOMING desde ese teléfono
     * dentro del tenant en los últimos 24 horas (24h - 5min de margen
     * de seguridad, por si la red de Meta atrasa el cierre real).
     *
     * @param string $phone     Teléfono del contacto (E.164 sin '+', ej: '573175153178').
     * @param int    $tenantId  Tenant que está consultando (multi-tenant).
     * @param int    $marginMin Margen de seguridad en minutos (default 5). Se resta de las 24h.
     * @return bool  true si se puede enviar texto libre, false si debe usarse plantilla.
     */
    function is_whatsapp_window_open(string $phone, int $tenantId, int $marginMin = 5): bool
    {
        // Normalizar: sólo dígitos
        $phone = preg_replace('/\D+/', '', $phone);

        if (empty($phone) || $tenantId <= 0) {
            log_message('warning', "[WaWindow] Llamada inválida: phone={$phone}, tenant={$tenantId}");
            return false; // Conservador: si no sabemos, asumimos cerrada
        }

        $db = \Config\Database::connect();

        // Ventana real: 24h - margen de seguridad
        $cutoffMinutes = (24 * 60) - $marginMin;
        $cutoff = gmdate('Y-m-d H:i:s', time() - ($cutoffMinutes * 60));

        // Buscamos el último mensaje INCOMING del teléfono dentro del tenant.
        // Preferimos whatsapp_timestamp (timestamp real de Meta) y como
        // fallback created_at (cuando nuestro server lo recibió).
        $row = $db->table('whatsapp_messages')
            ->select('whatsapp_timestamp, created_at')
            ->where('direction', 'incoming')
            ->where('sender_phone', $phone)
            ->where('tenant_id', $tenantId)
            ->orderBy('COALESCE(whatsapp_timestamp, created_at)', 'DESC', false)
            ->limit(1)
            ->get()
            ->getRow();

        if (!$row) {
            // Nunca nos ha escrito → ventana cerrada (sólo plantillas)
            return false;
        }

        // Tomamos el timestamp más confiable disponible
        $lastIncomingStr = $row->whatsapp_timestamp ?: $row->created_at;
        if (empty($lastIncomingStr)) return false;

        // Comparación. Asumimos que ambos timestamps están en UTC en BD.
        $lastIncomingTs = strtotime($lastIncomingStr . ' UTC');
        if ($lastIncomingTs === false) {
            log_message('warning', "[WaWindow] Timestamp inválido: '{$lastIncomingStr}'");
            return false;
        }

        $secondsSince = time() - $lastIncomingTs;
        $isOpen = ($secondsSince < ($cutoffMinutes * 60));

        log_message('debug', sprintf(
            "[WaWindow] tenant=%d phone=%s last_incoming=%s elapsed=%dmin → %s",
            $tenantId, $phone, $lastIncomingStr,
            (int)($secondsSince / 60),
            $isOpen ? 'OPEN' : 'CLOSED'
        ));

        return $isOpen;
    }
}


if (!function_exists('get_whatsapp_window_status')) {

    /**
     * Versión extendida: devuelve detalles útiles para logging y UI de admin.
     *
     * @return array{
     *   is_open: bool,
     *   last_incoming_at: ?string,   // datetime UTC del último mensaje entrante
     *   minutes_elapsed: ?int,       // minutos desde el último entrante
     *   minutes_remaining: ?int      // minutos antes de que se cierre (si está abierta)
     * }
     */
    function get_whatsapp_window_status(string $phone, int $tenantId, int $marginMin = 5): array
    {
        $phone = preg_replace('/\D+/', '', $phone);

        $result = [
            'is_open'           => false,
            'last_incoming_at'  => null,
            'minutes_elapsed'   => null,
            'minutes_remaining' => null,
        ];

        if (empty($phone) || $tenantId <= 0) return $result;

        $db = \Config\Database::connect();

        $row = $db->table('whatsapp_messages')
            ->select('whatsapp_timestamp, created_at')
            ->where('direction', 'incoming')
            ->where('sender_phone', $phone)
            ->where('tenant_id', $tenantId)
            ->orderBy('COALESCE(whatsapp_timestamp, created_at)', 'DESC', false)
            ->limit(1)
            ->get()
            ->getRow();

        if (!$row) return $result;

        $lastIncomingStr = $row->whatsapp_timestamp ?: $row->created_at;
        if (empty($lastIncomingStr)) return $result;

        $lastIncomingTs = strtotime($lastIncomingStr . ' UTC');
        if ($lastIncomingTs === false) return $result;

        $cutoffMinutes = (24 * 60) - $marginMin;
        $secondsSince  = time() - $lastIncomingTs;
        $minutesElapsed = (int)($secondsSince / 60);

        $result['last_incoming_at']  = $lastIncomingStr;
        $result['minutes_elapsed']   = $minutesElapsed;
        $result['is_open']           = ($secondsSince < ($cutoffMinutes * 60));
        $result['minutes_remaining'] = $result['is_open']
            ? max(0, $cutoffMinutes - $minutesElapsed)
            : 0;

        return $result;
    }
}