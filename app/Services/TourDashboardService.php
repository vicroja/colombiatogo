<?php
// app/Services/TourDashboardService.php

namespace App\Services;

use Config\Database;

/**
 * Servicio que centraliza las consultas del dashboard de tours.
 * Cada método devuelve un widget listo para renderizar.
 *
 * Por qué un servicio y no consultas en los modelos:
 * - Estas consultas son específicas del dashboard (joins agresivos, agregaciones)
 * - Mantiene los modelos limpios y enfocados en su tabla
 * - Facilita testear o cachear cada widget de forma independiente
 */
class TourDashboardService
{
    private $db;
    private int $tenantId;

    public function __construct(int $tenantId)
    {
        $this->db       = Database::connect();
        $this->tenantId = $tenantId;
    }

    // =========================================================================
    // ALERTAS OPERATIVAS (la franja roja arriba del dashboard)
    // =========================================================================

    /**
     * Devuelve TODAS las cosas que necesitan acción inmediata.
     * Cada alerta tiene severidad, tipo, mensaje y link de acción.
     */
    public function getOperationalAlerts(): array
    {
        $alerts = [];

        // 1. Salidas SIN GUÍA en las próximas 48h
        $sql = "
            SELECT ts.id, ts.start_datetime, t.name AS tour_name,
                   ts.current_pax, ts.max_pax
            FROM tour_schedules ts
            INNER JOIN tours t ON t.id = ts.tour_id
            WHERE t.tenant_id = ?
              AND ts.guide_id IS NULL
              AND ts.status = 'scheduled'
              AND ts.start_datetime BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 48 HOUR)
            ORDER BY ts.start_datetime ASC
        ";
        $rows = $this->db->query($sql, [$this->tenantId])->getResultArray();

        foreach ($rows as $r) {
            $hours = $this->hoursUntil($r['start_datetime']);
            $alerts[] = [
                'severity' => $hours < 12 ? 'critical' : 'warning',
                'icon'     => 'bi-person-x',
                'type'     => 'no_guide',
                'message'  => "Sin guía asignado: <strong>{$r['tour_name']}</strong>",
                'detail'   => $this->formatRelative($r['start_datetime']) . ' · ' . $r['current_pax'] . ' pax confirmados',
                'action_url'   => "/tours/schedule/{$r['id']}/edit",
                'action_label' => 'Asignar guía',
                'sort_at'      => $r['start_datetime'],
            ];
        }

        // 2. Salidas que NO ALCANZAN MÍNIMO de pax a <48h (riesgo de cancelación)
        $sql = "
            SELECT ts.id, ts.start_datetime, t.name AS tour_name,
                   ts.current_pax, t.min_pax
            FROM tour_schedules ts
            INNER JOIN tours t ON t.id = ts.tour_id
            WHERE t.tenant_id = ?
              AND ts.status = 'scheduled'
              AND ts.current_pax < t.min_pax
              AND ts.start_datetime BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 48 HOUR)
            ORDER BY ts.start_datetime ASC
        ";
        $rows = $this->db->query($sql, [$this->tenantId])->getResultArray();

        foreach ($rows as $r) {
            $missing = (int)$r['min_pax'] - (int)$r['current_pax'];
            $alerts[] = [
                'severity' => 'warning',
                'icon'     => 'bi-people',
                'type'     => 'low_pax',
                'message'  => "Falta(n) {$missing} pax para mínimo: <strong>{$r['tour_name']}</strong>",
                'detail'   => $this->formatRelative($r['start_datetime']) . " · {$r['current_pax']}/{$r['min_pax']}",
                'action_url'   => "/tours/manifest/{$r['id']}",
                'action_label' => 'Ver manifiesto',
                'sort_at'      => $r['start_datetime'],
            ];
        }

        // 3. Reservas PENDIENTES de confirmar a <24h de la salida
        $sql = "
            SELECT tr.id, ts.start_datetime, t.name AS tour_name, g.full_name
            FROM tour_reservations tr
            INNER JOIN tour_schedules ts ON ts.id = tr.schedule_id
            INNER JOIN tours t           ON t.id = ts.tour_id
            INNER JOIN guests g          ON g.id = tr.guest_id
            WHERE tr.tenant_id = ?
              AND tr.status = 'pending'
              AND ts.start_datetime BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
            ORDER BY ts.start_datetime ASC
        ";
        $rows = $this->db->query($sql, [$this->tenantId])->getResultArray();

        foreach ($rows as $r) {
            $alerts[] = [
                'severity' => 'warning',
                'icon'     => 'bi-hourglass-split',
                'type'     => 'pending_reservation',
                'message'  => "Reserva sin confirmar: <strong>" . esc($r['full_name']) . "</strong>",
                'detail'   => $r['tour_name'] . ' · ' . $this->formatRelative($r['start_datetime']),
                'action_url'   => "/tours/reservation/{$r['id']}",
                'action_label' => 'Confirmar',
                'sort_at'      => $r['start_datetime'],
            ];
        }

        // 4. Pagos a guías pendientes con más de 7 días
        $sql = "
            SELECT COUNT(*) AS qty, COALESCE(SUM(gp.amount), 0) AS total
            FROM guide_payments gp
            WHERE gp.tenant_id = ?
              AND gp.status = 'pending'
              AND gp.created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
        ";
        $row = $this->db->query($sql, [$this->tenantId])->getRowArray();
        if ($row && (int)$row['qty'] > 0) {
            $alerts[] = [
                'severity' => 'info',
                'icon'     => 'bi-cash-stack',
                'type'     => 'old_guide_payments',
                'message'  => "Tienes {$row['qty']} pagos a guías pendientes (>7 días)",
                'detail'   => 'Total: ' . $this->money($row['total']),
                'action_url'   => '/guides/payments/pending',
                'action_label' => 'Ver pagos',
                'sort_at'      => date('Y-m-d H:i:s'),
            ];
        }

        // Ordenamos: críticas primero, después por urgencia (más cercanas primero)
        usort($alerts, function ($a, $b) {
            $sevOrder = ['critical' => 0, 'warning' => 1, 'info' => 2];
            $diff = $sevOrder[$a['severity']] - $sevOrder[$b['severity']];
            if ($diff !== 0) return $diff;
            return strcmp($a['sort_at'], $b['sort_at']);
        });

        return $alerts;
    }

    // =========================================================================
    // SALIDAS DE HOY (el widget estrella)
    // =========================================================================

    public function getTodaysDepartures(): array
    {
        $sql = "
            SELECT
                ts.id, ts.start_datetime, ts.status, ts.current_pax, ts.max_pax,
                ts.notes,
                t.id   AS tour_id, t.name AS tour_name, t.meeting_point,
                t.duration_minutes, t.min_pax,
                tg.id  AS guide_id, tg.name AS guide_name, tg.phone AS guide_phone,
                (SELECT COUNT(*) FROM tour_reservations tr
                    WHERE tr.schedule_id = ts.id
                      AND tr.status = 'pending') AS pending_reservations
            FROM tour_schedules ts
            INNER JOIN tours t        ON t.id = ts.tour_id
            LEFT  JOIN tour_guides tg ON tg.id = ts.guide_id
            WHERE t.tenant_id = ?
              AND DATE(ts.start_datetime) = CURDATE()
              AND ts.status IN ('scheduled', 'in_progress', 'completed')
            ORDER BY ts.start_datetime ASC
        ";
        $departures = $this->db->query($sql, [$this->tenantId])->getResultArray();

        // Enriquecemos cada salida con metadatos visuales
        foreach ($departures as &$d) {
            $d['occupancy_pct'] = $d['max_pax'] > 0
                ? round(($d['current_pax'] / $d['max_pax']) * 100)
                : 0;

            $d['time_label']    = date('H:i', strtotime($d['start_datetime']));
            $d['time_relative'] = $this->formatRelative($d['start_datetime']);
            $d['health']        = $this->computeDepartureHealth($d);
        }

        return $departures;
    }

    /**
     * Calcula el estado de salud de una salida.
     * Verde = todo bien, amarillo = atención, rojo = problema grave.
     */
    private function computeDepartureHealth(array $d): array
    {
        $now      = time();
        $startsAt = strtotime($d['start_datetime']);
        $hours    = ($startsAt - $now) / 3600;

        // Ya completada
        if ($d['status'] === 'completed') {
            return ['color' => 'success', 'label' => 'Finalizada', 'icon' => 'bi-check-circle-fill'];
        }
        if ($d['status'] === 'in_progress') {
            return ['color' => 'primary', 'label' => 'En curso', 'icon' => 'bi-play-circle-fill'];
        }
        if ($d['status'] === 'cancelled') {
            return ['color' => 'secondary', 'label' => 'Cancelada', 'icon' => 'bi-x-circle'];
        }

        // Está programada → ¿qué tan saludable es?
        if (empty($d['guide_id'])) {
            return ['color' => 'danger', 'label' => 'Sin guía', 'icon' => 'bi-person-x-fill'];
        }
        if ($d['current_pax'] < $d['min_pax'] && $hours < 24) {
            return ['color' => 'warning', 'label' => 'Bajo mínimo', 'icon' => 'bi-exclamation-triangle-fill'];
        }
        if ($d['pending_reservations'] > 0 && $hours < 6) {
            return ['color' => 'warning', 'label' => 'Reservas sin confirmar', 'icon' => 'bi-hourglass-split'];
        }
        return ['color' => 'success', 'label' => 'Lista', 'icon' => 'bi-check-circle-fill'];
    }

    // =========================================================================
    // PRÓXIMAS SALIDAS (siguientes N días, no incluye hoy)
    // =========================================================================

    public function getUpcomingDepartures(int $days = 7): array
    {
        $sql = "
            SELECT
                ts.id, ts.start_datetime, ts.current_pax, ts.max_pax,
                t.name AS tour_name, t.min_pax,
                tg.name AS guide_name
            FROM tour_schedules ts
            INNER JOIN tours t        ON t.id = ts.tour_id
            LEFT  JOIN tour_guides tg ON tg.id = ts.guide_id
            WHERE t.tenant_id = ?
              AND ts.status = 'scheduled'
              AND DATE(ts.start_datetime) BETWEEN
                    DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND
                    DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY ts.start_datetime ASC
        ";
        $rows = $this->db->query($sql, [$this->tenantId, $days])->getResultArray();

        // Agrupamos por día para el render
        $grouped = [];
        foreach ($rows as $r) {
            $dateKey = date('Y-m-d', strtotime($r['start_datetime']));
            $r['occupancy_pct'] = $r['max_pax'] > 0
                ? round(($r['current_pax'] / $r['max_pax']) * 100)
                : 0;
            $r['time'] = date('H:i', strtotime($r['start_datetime']));
            $grouped[$dateKey][] = $r;
        }
        return $grouped;
    }

    // =========================================================================
    // KPIs (tarjetas de números arriba)
    // =========================================================================

    public function getKpis(): array
    {
        // Pax que salen hoy (suma de current_pax de salidas no canceladas)
        $sql = "
            SELECT
                COALESCE(SUM(ts.current_pax), 0) AS pax_today,
                COUNT(ts.id) AS departures_today
            FROM tour_schedules ts
            INNER JOIN tours t ON t.id = ts.tour_id
            WHERE t.tenant_id = ?
              AND DATE(ts.start_datetime) = CURDATE()
              AND ts.status != 'cancelled'
        ";
        $today = $this->db->query($sql, [$this->tenantId])->getRowArray();

        // Ingresos del mes (total_price de reservas confirmadas/completed este mes)
        $sql = "
            SELECT COALESCE(SUM(tr.total_price), 0) AS revenue_month
            FROM tour_reservations tr
            WHERE tr.tenant_id = ?
              AND tr.status IN ('confirmed', 'completed')
              AND MONTH(tr.created_at) = MONTH(CURDATE())
              AND YEAR(tr.created_at)  = YEAR(CURDATE())
        ";
        $month = $this->db->query($sql, [$this->tenantId])->getRowArray();

        // Comparación con el mes pasado (mismo día del mes hacia atrás)
        $sql = "
            SELECT COALESCE(SUM(tr.total_price), 0) AS revenue_last
            FROM tour_reservations tr
            WHERE tr.tenant_id = ?
              AND tr.status IN ('confirmed', 'completed')
              AND MONTH(tr.created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
              AND YEAR(tr.created_at)  = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
              AND DAY(tr.created_at)   <= DAY(CURDATE())
        ";
        $lastMonth = $this->db->query($sql, [$this->tenantId])->getRowArray();

        $revenue     = (float) $month['revenue_month'];
        $revenueLast = (float) $lastMonth['revenue_last'];
        $delta       = $revenueLast > 0
            ? round((($revenue - $revenueLast) / $revenueLast) * 100, 1)
            : null;

        // Reservas nuevas hoy
        $sql = "
            SELECT COUNT(*) AS qty
            FROM tour_reservations
            WHERE tenant_id = ?
              AND DATE(created_at) = CURDATE()
        ";
        $newRes = $this->db->query($sql, [$this->tenantId])->getRowArray();

        // Ocupación promedio próximos 7 días
        $sql = "
            SELECT
                COALESCE(AVG(ts.current_pax / NULLIF(ts.max_pax, 0)) * 100, 0) AS avg_occupancy
            FROM tour_schedules ts
            INNER JOIN tours t ON t.id = ts.tour_id
            WHERE t.tenant_id = ?
              AND ts.status = 'scheduled'
              AND DATE(ts.start_datetime) BETWEEN
                    CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ";
        $occ = $this->db->query($sql, [$this->tenantId])->getRowArray();

        return [
            'pax_today'        => (int) $today['pax_today'],
            'departures_today' => (int) $today['departures_today'],
            'revenue_month'    => $revenue,
            'revenue_delta'    => $delta, // % vs mes pasado a la misma altura
            'new_reservations' => (int) $newRes['qty'],
            'avg_occupancy_7d' => round((float) $occ['avg_occupancy'], 1),
        ];
    }

    // =========================================================================
    // RESERVAS RECIENTES
    // =========================================================================

    public function getRecentReservations(int $limit = 8): array
    {
        $sql = "
            SELECT
                tr.id, tr.status, tr.total_price, tr.num_adults, tr.num_children,
                tr.created_at,
                g.full_name AS guest_name,
                t.name AS tour_name,
                ts.start_datetime
            FROM tour_reservations tr
            INNER JOIN guests g          ON g.id = tr.guest_id
            INNER JOIN tour_schedules ts ON ts.id = tr.schedule_id
            INNER JOIN tours t           ON t.id = ts.tour_id
            WHERE tr.tenant_id = ?
            ORDER BY tr.created_at DESC
            LIMIT ?
        ";
        $rows = $this->db->query($sql, [$this->tenantId, $limit])->getResultArray();

        foreach ($rows as &$r) {
            $r['pax_total']    = (int)$r['num_adults'] + (int)$r['num_children'];
            $r['time_ago']     = $this->formatTimeAgo($r['created_at']);
            $r['departure_at'] = date('d/m H:i', strtotime($r['start_datetime']));
        }
        return $rows;
    }

    // =========================================================================
    // TOP TOURS DEL MES
    // =========================================================================

    public function getTopToursOfMonth(int $limit = 5): array
    {
        $sql = "
            SELECT
                t.id, t.name,
                COUNT(tr.id) AS reservation_count,
                COALESCE(SUM(tr.num_adults + tr.num_children), 0) AS pax_total,
                COALESCE(SUM(tr.total_price), 0) AS revenue
            FROM tours t
            INNER JOIN tour_schedules ts    ON ts.tour_id = t.id
            INNER JOIN tour_reservations tr ON tr.schedule_id = ts.id
            WHERE t.tenant_id = ?
              AND tr.status IN ('confirmed', 'completed')
              AND MONTH(tr.created_at) = MONTH(CURDATE())
              AND YEAR(tr.created_at)  = YEAR(CURDATE())
            GROUP BY t.id, t.name
            ORDER BY revenue DESC
            LIMIT ?
        ";
        return $this->db->query($sql, [$this->tenantId, $limit])->getResultArray();
    }

    // =========================================================================
    // RESUMEN DE GUÍAS
    // =========================================================================

    public function getGuidesSummary(): array
    {
        $sql = "
            SELECT
                (SELECT COUNT(*) FROM tour_guides
                    WHERE tenant_id = ? AND is_active = 1) AS active_guides,

                (SELECT COUNT(DISTINCT ts.guide_id)
                    FROM tour_schedules ts
                    INNER JOIN tours t ON t.id = ts.tour_id
                    WHERE t.tenant_id = ?
                      AND ts.guide_id IS NOT NULL
                      AND DATE(ts.start_datetime) BETWEEN
                            CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                ) AS guides_working_week,

                (SELECT COALESCE(SUM(amount), 0)
                    FROM guide_payments
                    WHERE tenant_id = ? AND status = 'pending'
                ) AS total_pending_payments,

                (SELECT COUNT(*)
                    FROM guide_payments
                    WHERE tenant_id = ? AND status = 'pending'
                ) AS pending_payments_count
        ";
        return $this->db->query($sql, [
            $this->tenantId, $this->tenantId, $this->tenantId, $this->tenantId
        ])->getRowArray();
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    private function hoursUntil(string $datetime): float
    {
        return (strtotime($datetime) - time()) / 3600;
    }

    /**
     * Convierte una fecha futura a algo legible: "en 3h 20min", "mañana 9:00", etc.
     */
    private function formatRelative(string $datetime): string
    {
        $ts    = strtotime($datetime);
        $diff  = $ts - time();
        $hours = $diff / 3600;

        if ($hours < 0) {
            return 'hace ' . $this->humanDuration(abs($diff));
        }
        if ($hours < 1) {
            $mins = (int) ($diff / 60);
            return "en {$mins} min";
        }
        if ($hours < 12) {
            return 'en ' . $this->humanDuration($diff);
        }
        if (date('Y-m-d', $ts) === date('Y-m-d')) {
            return 'hoy ' . date('H:i', $ts);
        }
        if (date('Y-m-d', $ts) === date('Y-m-d', strtotime('+1 day'))) {
            return 'mañana ' . date('H:i', $ts);
        }
        return date('d/m H:i', $ts);
    }

    private function humanDuration(int $seconds): string
    {
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        if ($h > 0 && $m > 0) return "{$h}h {$m}min";
        if ($h > 0)            return "{$h}h";
        return "{$m}min";
    }

    private function formatTimeAgo(string $datetime): string
    {
        $diff = time() - strtotime($datetime);
        if ($diff < 60)         return 'ahora';
        if ($diff < 3600)       return floor($diff / 60) . ' min';
        if ($diff < 86400)      return floor($diff / 3600) . ' h';
        if ($diff < 604800)     return floor($diff / 86400) . ' d';
        return date('d/m', strtotime($datetime));
    }

    private function money(float $amount): string
    {
        return '$' . number_format($amount, 0, ',', '.');
    }
}
