<?php
// app/Services/HotelDashboardService.php

namespace App\Services;

use Config\Database;

/**
 * Servicio del dashboard para tenants con alojamiento.
 *
 * NOTA: Este servicio está alineado en estructura con TourDashboardService
 * para que la vista pueda renderizar widgets de forma uniforme. La lógica
 * interna debe adaptarse a las tablas reales de hotel (reservations,
 * accommodation_units, etc.) que ya tienes en el sistema.
 */
class HotelDashboardService
{
    private $db;
    private int $tenantId;

    public function __construct(int $tenantId)
    {
        $this->db       = Database::connect();
        $this->tenantId = $tenantId;
    }

    /**
     * KPIs principales del hotel: ocupación, ingresos, llegadas, etc.
     */
    public function getKpis(): array
    {
        // Llegadas hoy
        $sql = "
            SELECT COUNT(*) AS qty
            FROM reservations
            WHERE tenant_id = ?
              AND DATE(check_in) = CURDATE()
              AND status IN ('confirmed', 'pending')
        ";
        $arrivals = $this->db->query($sql, [$this->tenantId])->getRowArray();

        // Salidas hoy
        $sql = "
            SELECT COUNT(*) AS qty
            FROM reservations
            WHERE tenant_id = ?
              AND DATE(check_out) = CURDATE()
              AND status IN ('confirmed', 'in_house')
        ";
        $departures = $this->db->query($sql, [$this->tenantId])->getRowArray();

        // Ocupación actual (unidades ocupadas / total)
        $sql = "
            SELECT
                SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) AS occupied,
                COUNT(*) AS total
            FROM accommodation_units
            WHERE tenant_id = ?
        ";
        $occ = $this->db->query($sql, [$this->tenantId])->getRowArray();

        $occupancy = (int)$occ['total'] > 0
            ? round(((int)$occ['occupied'] / (int)$occ['total']) * 100, 1)
            : 0;

        // Ingresos del mes
        $sql = "
            SELECT COALESCE(SUM(total_price), 0) AS revenue
            FROM reservations
            WHERE tenant_id = ?
              AND status IN ('confirmed', 'checked_in', 'checked_out')
              AND MONTH(created_at) = MONTH(CURDATE())
              AND YEAR(created_at)  = YEAR(CURDATE())
        ";
        $month = $this->db->query($sql, [$this->tenantId])->getRowArray();

        return [
            'arrivals_today'   => (int) $arrivals['qty'],
            'departures_today' => (int) $departures['qty'],
            'occupancy_pct'    => $occupancy,
            'units_occupied'   => (int) $occ['occupied'],
            'units_total'      => (int) $occ['total'],
            'revenue_month'    => (float) $month['revenue'],
        ];
    }

    public function getArrivalsToday(): array
    {
        $sql = "
            SELECT r.id, r.check_in, r.check_out, r.status,
                   g.full_name, g.phone,
                   au.name AS unit_name
            FROM reservations r
            INNER JOIN guests g              ON g.id = r.guest_id
            LEFT  JOIN accommodation_units au ON au.id = r.accommodation_unit_id
            WHERE r.tenant_id = ?
              AND DATE(r.check_in) = CURDATE()
              AND r.status IN ('confirmed', 'pending')
            ORDER BY r.check_in ASC
        ";
        return $this->db->query($sql, [$this->tenantId])->getResultArray();
    }

    public function getDeparturesToday(): array
    {
        $sql = "
            SELECT r.id, r.check_in, r.check_out, r.status,
                   g.full_name,
                   au.name AS unit_name
            FROM reservations r
            INNER JOIN guests g              ON g.id = r.guest_id
            LEFT  JOIN accommodation_units au ON au.id = r.accommodation_unit_id
            WHERE r.tenant_id = ?
              AND DATE(r.check_out) = CURDATE()
              AND r.status IN ('confirmed', 'checked_in')
            ORDER BY r.check_out ASC
        ";
        return $this->db->query($sql, [$this->tenantId])->getResultArray();
    }

    public function getUnitsStatus(): array
    {
        $sql = "
            SELECT au.id, au.name, au.status,
                   at.name AS type_name
            FROM accommodation_units au
            LEFT JOIN accommodation_types at ON at.id = au.type_id
            WHERE au.tenant_id = ?
            ORDER BY au.name ASC
        ";
        return $this->db->query($sql, [$this->tenantId])->getResultArray();
    }
}
