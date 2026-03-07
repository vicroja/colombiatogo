<?php

namespace App\Services;

use App\Models\UnitRateModel;
use App\Models\SeasonalRateModel;

class PriceCalculatorService
{
    /**
     * Calcula el precio total de una estancia, día por día.
     */
    public function calculateStay($unitId, $ratePlanId, $checkIn, $checkOut)
    {
        $rateModel = new UnitRateModel();
        $seasonModel = new SeasonalRateModel();

        // 1. Obtener Tarifa Base de la Matriz
        $baseRate = $rateModel->where('unit_id', $unitId)
            ->where('rate_plan_id', $ratePlanId)
            ->first();

        $basePrice = $baseRate ? $baseRate['price_per_night'] : 0;

        $totalPrice = 0;
        $dailyDetails = [];

        // 2. Iterar día por día (sin incluir el día de salida)
        $currentDate = strtotime($checkIn);
        $endDate = strtotime($checkOut);

        while ($currentDate < $endDate) {
            $dateStr = date('Y-m-d', $currentDate);
            $dailyPrice = $basePrice;
            $appliedSeason = null;

            // 3. Buscar si hay una temporada para ESTE día específico
            // Buscamos temporadas activas que cubran la fecha
            $seasons = $seasonModel->where('is_active', 1)
                ->where('start_date <=', $dateStr)
                ->where('end_date >=', $dateStr)
                ->orderBy('priority', 'DESC')
                ->findAll();

            // Filtramos las temporadas para asegurar que apliquen a esta unidad/plan (o sean globales)
            foreach ($seasons as $season) {
                $unitMatches = is_null($season['unit_id']) || $season['unit_id'] == $unitId;
                $planMatches = is_null($season['rate_plan_id']) || $season['rate_plan_id'] == $ratePlanId;

                if ($unitMatches && $planMatches) {
                    $appliedSeason = $season['name'];

                    // Aplicar la matemática según el tipo de modificador
                    if ($season['modifier_type'] == 'fixed') {
                        $dailyPrice = $season['modifier_value'];
                    } elseif ($season['modifier_type'] == 'percent_increase') {
                        $dailyPrice += ($basePrice * ($season['modifier_value'] / 100));
                    } elseif ($season['modifier_type'] == 'percent_decrease') {
                        $dailyPrice -= ($basePrice * ($season['modifier_value'] / 100));
                    }

                    break; // Tomamos solo el de mayor prioridad y salimos del loop
                }
            }

            // Sumamos al total y guardamos el detalle para auditoría
            $totalPrice += $dailyPrice;
            $dailyDetails[] = [
                'date'   => $dateStr,
                'price'  => round($dailyPrice, 2),
                'season' => $appliedSeason ?: 'Tarifa Base'
            ];

            // Avanzar al día siguiente
            $currentDate = strtotime('+1 day', $currentDate);
        }

        return [
            'total_price'   => round($totalPrice, 2),
            'nights'        => count($dailyDetails),
            'daily_details' => $dailyDetails // Ideal para el rate_snapshot_json de la reserva
        ];
    }
}