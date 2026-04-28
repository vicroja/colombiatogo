<?php
// app/Models/TourScheduleModel.php

namespace App\Models;

use CodeIgniter\Model;
class TourScheduleModel extends Model
{
    protected $table         = 'tour_schedules';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tour_id', 'guide_id', 'start_datetime', 'max_pax',
        'current_pax', 'price_adult_override', 'price_child_override',
        'status', 'notes',
    ];

    /**
     * Verifica si hay cupos disponibles ANTES de confirmar una reserva.
     * Se llama desde TourController::storeReservation() antes de insertar.
     *
     * @param int $scheduleId  ID de la salida
     * @param int $paxRequested Número total de personas (adultos + niños)
     * @return bool
     */
    public function checkAvailability(int $scheduleId, int $paxRequested): bool
    {
        $schedule = $this->find($scheduleId);

        if (!$schedule) {
            log_message('warning', "[TourSchedule] checkAvailability: schedule {$scheduleId} no encontrado.");
            return false;
        }

        if ($schedule['status'] !== 'scheduled') {
            log_message('warning', "[TourSchedule] checkAvailability: schedule {$scheduleId} no está en estado 'scheduled'. Estado actual: {$schedule['status']}");
            return false;
        }

        $available = (int)$schedule['max_pax'] - (int)$schedule['current_pax'];

        log_message('info', "[TourSchedule] Schedule {$scheduleId}: {$available} cupos disponibles, {$paxRequested} solicitados.");

        return $available >= $paxRequested;
    }

    /**
     * Devuelve próximas salidas de un tour con datos del guía.
     * Usado en la vista de detalle del tour y en el dashboard de operadores.
     *
     * @param int $tourId
     * @param int $limit  Máximo de resultados
     * @return array
     */
    public function getUpcomingByTour(int $tourId, int $limit = 10): array
    {
        return $this->select('tour_schedules.*, tour_guides.name AS guide_name')
            ->join('tour_guides', 'tour_guides.id = tour_schedules.guide_id', 'left')
            ->where('tour_schedules.tour_id', $tourId)
            ->where('tour_schedules.status', 'scheduled')
            ->where('tour_schedules.start_datetime >=', date('Y-m-d H:i:s'))
            ->orderBy('tour_schedules.start_datetime', 'ASC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Incrementa o decrementa current_pax de forma atómica.
     * Usar SIEMPRE dentro de una transacción de BD.
     *
     * @param int $scheduleId
     * @param int $delta  Positivo para sumar, negativo para restar
     */
    public function adjustPax(int $scheduleId, int $delta): void
    {
        $this->db->query(
            "UPDATE tour_schedules SET current_pax = current_pax + ? WHERE id = ?",
            [$delta, $scheduleId]
        );

        log_message('info', "[TourSchedule] adjustPax: schedule {$scheduleId}, delta {$delta}.");
    }
}