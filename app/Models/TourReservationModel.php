<?php
// app/Models/TourReservationModel.php

namespace App\Models;

class TourReservationModel extends BaseMultiTenantModel
{
    protected $table         = 'tour_reservations';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tenant_id', 'schedule_id', 'guest_id', 'parent_reservation_id',
        'agent_id', 'num_adults', 'num_children', 'total_price',
        'pickup_location', 'status', 'price_snapshot_json', 'notes',
    ];

    /**
     * Devuelve una reserva de tour con todos sus datos relacionados
     * (tour, schedule, guest, guía) en una sola consulta.
     * Usado en la vista de detalle y en el manifiesto.
     *
     * @param int $id
     * @return array|null
     */
    public function getFullReservation(int $id): ?array
    {
        return $this->select([
            'tour_reservations.*',
            'guests.full_name',
            'guests.phone AS guest_phone',
            'guests.document AS guest_document',
            'tours.name AS tour_name',
            'tours.meeting_point',
            'tours.duration_minutes',
            'tour_schedules.start_datetime',
            'tour_schedules.max_pax',
            'tour_schedules.current_pax',
            'tour_guides.name AS guide_name',
            'tour_guides.phone AS guide_phone',
        ])
            ->join('guests',         'guests.id = tour_reservations.guest_id')
            ->join('tour_schedules', 'tour_schedules.id = tour_reservations.schedule_id')
            ->join('tours',          'tours.id = tour_schedules.tour_id')
            ->join('tour_guides',    'tour_guides.id = tour_schedules.guide_id', 'left')
            ->where('tour_reservations.id', $id)
            ->first();
    }

    /**
     * Devuelve todas las reservas activas de un schedule (para el manifiesto de carga).
     *
     * @param int $scheduleId
     * @return array
     */
    public function getManifestBySchedule(int $scheduleId): array
    {
        return $this->select([
            'tour_reservations.*',
            'guests.full_name',
            'guests.document AS guest_document',
            'guests.phone AS guest_phone',
        ])
            ->join('guests', 'guests.id = tour_reservations.guest_id')
            ->where('tour_reservations.schedule_id', $scheduleId)
            ->whereNotIn('tour_reservations.status', ['cancelled', 'refunded'])
            ->orderBy('guests.full_name', 'ASC')
            ->findAll();
    }
}