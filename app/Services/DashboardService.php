<?php

namespace App\Services;

use App\Models\ReservationModel;
use App\Models\PaymentModel;
use App\Models\AccommodationUnitModel;

class DashboardService
{
    public function getTodaysMetrics()
    {
        $resModel = new ReservationModel();
        $paymentModel = new PaymentModel();
        $unitModel = new AccommodationUnitModel();

        $today = date('Y-m-d');

        // 1. Llegadas Esperadas Hoy (Check-ins pendientes)
        $expectedCheckIns = $resModel->where('check_in_date', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->countAllResults();

        // 2. Salidas Esperadas Hoy (Check-outs de gente in-house)
        $expectedCheckOuts = $resModel->where('check_out_date', $today)
            ->where('status', 'checked_in')
            ->countAllResults();

        // 3. Ingresos del Día (Suma de todos los pagos registrados hoy)
        $paymentsToday = $paymentModel->like('created_at', $today)->findAll();
        $incomeToday = array_sum(array_column($paymentsToday, 'amount'));

        // 4. Ocupación Actual (Unidades ocupadas vs Total)
        $totalUnits = $unitModel->where('status !=', 'maintenance')->countAllResults();
        $occupiedUnits = $unitModel->where('status', 'occupied')->countAllResults();

        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0;

        return [
            'expected_checkins'  => $expectedCheckIns,
            'expected_checkouts' => $expectedCheckOuts,
            'income_today'       => $incomeToday,
            'occupancy_rate'     => $occupancyRate,
            'occupied_units'     => $occupiedUnits,
            'total_units'        => $totalUnits
        ];
    }
}