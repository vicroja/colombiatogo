<?php

namespace App\Services;

use App\Models\ReservationModel;
use App\Models\PaymentModel;
use App\Models\AccommodationUnitModel;

class DashboardService
{
    public function getTodaysMetrics()
    {
        $resModel     = new ReservationModel();
        $paymentModel = new PaymentModel();
        $unitModel    = new AccommodationUnitModel();

        $today     = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        // 1. Llegadas esperadas hoy
        $expectedCheckIns = $resModel
            ->where('check_in_date', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->countAllResults();

        // 2. Salidas esperadas hoy
        $expectedCheckOuts = $resModel
            ->where('check_out_date', $today)
            ->where('status', 'checked_in')
            ->countAllResults();

        // 3. Ingresos de hoy
        $paymentsToday = $paymentModel->like('created_at', $today)->findAll();
        $incomeToday   = array_sum(array_column($paymentsToday, 'amount'));

        // 4. Ingresos de ayer (para comparativa % — antes era hardcoded en 20000)
        $paymentsYesterday = $paymentModel->like('created_at', $yesterday)->findAll();
        $incomeYesterday   = array_sum(array_column($paymentsYesterday, 'amount'));

        // 5. Ocupación actual
        $totalUnits    = $unitModel->where('status !=', 'maintenance')->countAllResults();
        $occupiedUnits = $unitModel->where('status', 'occupied')->countAllResults();
        $occupancyRate = $totalUnits > 0
            ? round(($occupiedUnits / $totalUnits) * 100, 1)
            : 0;

        // 6. Estado detallado de unidades para el mapa
        $unitsStatus = $unitModel
            ->select('id, name, status')
            ->where('status !=', 'maintenance')
            ->orderBy('name', 'ASC')
            ->findAll();

        // 7. Próximas llegadas (hoy + mañana) con nombre de huésped
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $upcomingArrivals = $resModel
            ->select('reservations.id, reservations.check_in_date, reservations.check_out_date,
                      reservations.status, reservations.num_adults, reservations.num_children,
                      guests.full_name, accommodation_units.name as unit_name')
            ->join('guests', 'guests.id = reservations.guest_id')
            ->join('accommodation_units', 'accommodation_units.id = reservations.accommodation_unit_id')
            ->whereIn('reservations.check_in_date', [$today, $tomorrow])
            ->whereIn('reservations.status', ['pending', 'confirmed'])
            ->orderBy('reservations.check_in_date', 'ASC')
            ->findAll();

        // 8. In-house: quién está hospedado ahora
        $inHouse = $resModel
            ->select('reservations.id, reservations.check_out_date,
                      guests.full_name, accommodation_units.name as unit_name')
            ->join('guests', 'guests.id = reservations.guest_id')
            ->join('accommodation_units', 'accommodation_units.id = reservations.accommodation_unit_id')
            ->where('reservations.status', 'checked_in')
            ->orderBy('reservations.check_out_date', 'ASC')
            ->findAll();

        return [
            'expected_checkins'   => $expectedCheckIns,
            'expected_checkouts'  => $expectedCheckOuts,
            'income_today'        => $incomeToday,
            'income_yesterday'    => $incomeYesterday,   // ← real, no hardcodeado
            'occupancy_rate'      => $occupancyRate,
            'occupied_units'      => $occupiedUnits,
            'total_units'         => $totalUnits,
            'units_status'        => $unitsStatus,
            'upcoming_arrivals'   => $upcomingArrivals,
            'in_house'            => $inHouse,
        ];
    }
}