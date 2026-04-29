<?php
// app/Models/TourGuideModel.php

namespace App\Models;

class TourGuideModel extends BaseMultiTenantModel
{
    protected $table         = 'tour_guides';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tenant_id', 'user_id', 'name', 'phone', 'document',
        'specialty', 'languages', 'is_active',
        'payment_model', 'rate_fixed', 'rate_per_adult',
        'rate_per_child', 'commission_pct', 'min_pax_for_bonus', 'notes',
    ];

    /**
     * Guías activos del tenant para poblar selects.
     */
    public function getActiveGuides(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Guías con resumen de tours realizados y pagos pendientes.
     * Usado en el listado principal /guides.
     */
    public function getGuidesWithStats(int $tenantId): array
    {
        return $this->db->query("
            SELECT
                tg.*,
                COUNT(DISTINCT ts.id)                              AS total_tours,
                COALESCE(SUM(CASE WHEN gp.status = 'pending'
                    THEN gp.amount END), 0)                        AS pending_payment,
                COALESCE(SUM(CASE WHEN gp.status = 'paid'
                    THEN gp.amount END), 0)                        AS total_paid
            FROM tour_guides tg
            LEFT JOIN tour_schedules ts
                ON ts.guide_id = tg.id
               AND ts.status   = 'completed'
            LEFT JOIN guide_payments gp
                ON gp.guide_id = tg.id
            WHERE tg.tenant_id = ?
            GROUP BY tg.id
            ORDER BY tg.name ASC
        ", [$tenantId])->getResultArray();
    }

    /**
     * Historial de tours conducidos por un guía con detalle de pagos.
     * Usado en /guides/{id}/history.
     */
    public function getGuideHistory(int $guideId): array
    {
        return $this->db->query("
            SELECT
                ts.id            AS schedule_id,
                ts.start_datetime,
                ts.status        AS schedule_status,
                ts.current_pax,
                ts.max_pax,
                t.name           AS tour_name,
                gp.id            AS payment_id,
                gp.amount        AS payment_amount,
                gp.status        AS payment_status,
                gp.payment_model_snapshot,
                gp.calculation_detail_json
            FROM tour_schedules ts
            INNER JOIN tours t ON t.id = ts.tour_id
            LEFT JOIN guide_payments gp ON gp.schedule_id = ts.id
            WHERE ts.guide_id = ?
            ORDER BY ts.start_datetime DESC
        ", [$guideId])->getResultArray();
    }
}