<?php
// app/Models/GuidePaymentModel.php

namespace App\Models;

class GuidePaymentModel extends BaseMultiTenantModel
{
    protected $table         = 'guide_payments';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tenant_id', 'guide_id', 'schedule_id', 'amount',
        'payment_model_snapshot', 'calculation_detail_json',
        'status', 'payment_date', 'payment_method',
        'reference', 'notes', 'created_by',
    ];

    /**
     * Pagos pendientes de un tenant, con datos del guía y del tour.
     * Usado en la vista global /guides/payments.
     */
    public function getPendingPayments(int $tenantId): array
    {
        return $this->select([
            'guide_payments.*',
            'tour_guides.name    AS guide_name',
            'tour_guides.phone   AS guide_phone',
            'tours.name          AS tour_name',
            'tour_schedules.start_datetime',
            'tour_schedules.current_pax',
        ])
            ->join('tour_guides',    'tour_guides.id    = guide_payments.guide_id')
            ->join('tour_schedules', 'tour_schedules.id = guide_payments.schedule_id',  'left')
            ->join('tours',          'tours.id          = tour_schedules.tour_id',       'left')
            ->where('guide_payments.tenant_id', $tenantId)
            ->where('guide_payments.status',    'pending')
            ->orderBy('guide_payments.created_at', 'ASC')
            ->findAll();
    }

    /**
     * Historial de pagos de un guía específico.
     * Usado en /guides/{id}/payments.
     */
    public function getByGuide(int $guideId): array
    {
        return $this->select([
            'guide_payments.*',
            'tours.name         AS tour_name',
            'tour_schedules.start_datetime',
            'tour_schedules.current_pax',
        ])
            ->join('tour_schedules', 'tour_schedules.id = guide_payments.schedule_id', 'left')
            ->join('tours',          'tours.id          = tour_schedules.tour_id',      'left')
            ->where('guide_payments.guide_id', $guideId)
            ->orderBy('guide_payments.created_at', 'DESC')
            ->findAll();
    }
}