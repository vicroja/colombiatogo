<?php

namespace App\Services;

use App\Models\ReservationModel;
use App\Models\AccommodationUnitModel;

class ReservationStateMachineService
{
    // Mapa de transiciones permitidas: De -> Hacia
    private $allowedTransitions = [
        'pending'    => ['confirmed', 'cancelled'],
        'confirmed'  => ['checked_in', 'cancelled'],
        'checked_in' => ['checked_out'],
        'checked_out'=> [],
        'cancelled'  => []
    ];

    public function transitionState($reservationId, $newStatus): array
    {
        $resModel = new ReservationModel();
        $reservation = $resModel->find($reservationId);

        if (!$reservation) return ['success' => false, 'message' => 'Reserva no encontrada.'];

        $currentStatus = $reservation['status'];

        // 1. Validar si la transición es legal
        if (!in_array($newStatus, $this->allowedTransitions[$currentStatus])) {
            return ['success' => false, 'message' => "Transición no válida de {$currentStatus} a {$newStatus}."];
        }

        $resModel->db->transStart();

        // 2. Actualizar estado de la reserva
        $resModel->update($reservationId, ['status' => $newStatus]);

        // 3. Actualizar estado físico de la habitación si es necesario
        $unitModel = new AccommodationUnitModel();
        if ($newStatus === 'checked_in') {
            $unitModel->update($reservation['accommodation_unit_id'], ['status' => 'occupied']);
        } elseif ($newStatus === 'checked_out' || $newStatus === 'cancelled') {
            // Liberamos la habitación
            $unitModel->update($reservation['accommodation_unit_id'], ['status' => 'available']);
        }

        $resModel->db->transComplete();

        if ($resModel->db->transStatus() === false) {
            return ['success' => false, 'message' => 'Error en la base de datos al cambiar el estado.'];
        }

        return ['success' => true, 'message' => 'Estado de la reserva actualizado con éxito.'];
    }
}