<?php

namespace App\Services;

use App\Models\UnitRateModel;
use App\Models\SeasonalRateModel;

class PriceCalculatorService
{


/**
* Calcula el precio total de una estancia, día por día.
* Ahora soporta capacidad base y cobro por personas y niños extra.
*/
    public function calculateStay($unitId, $ratePlanId, $checkIn, $checkOut, $numAdults = 1, $numChildren = 0)
    {
        $rateModel = new UnitRateModel();
        $seasonModel = new SeasonalRateModel();
        $unitModel = new \App\Models\AccommodationUnitModel();

        // 1. Obtener la capacidad base de la unidad
        $unit = $unitModel->find($unitId);
        // Si no se ha configurado, asumimos 2 personas por defecto
        $baseOccupancy = $unit ? (int)($unit['base_occupancy'] ?? 2) : 2;

        // 2. Obtener Tarifas Base (Habitación y Extras)
        $baseRate = $rateModel->where('unit_id', $unitId)
            ->where('rate_plan_id', $ratePlanId)
            ->first();

        $roomPrice       = $baseRate ? (float)$baseRate['price_per_night'] : 0;
        $extraAdultPrice = $baseRate ? (float)$baseRate['extra_person_price'] : 0;
        $extraChildPrice = $baseRate ? (float)($baseRate['extra_child_price'] ?? 0) : 0;

        // 3. Calcular cantidad de personas extra (El Algoritmo Core)
        $extraAdults = 0;
        $extraChildren = 0;

        if ($numAdults >= $baseOccupancy) {
            // Si los adultos llenan (o superan) la capacidad base:
            // Todo adulto por encima del base paga extra, y TODOS los niños pagan extra.
            $extraAdults = $numAdults - $baseOccupancy;
            $extraChildren = $numChildren;
        } else {
            // Si los adultos NO llenan la capacidad base, quedan "cupos libres".
            // Los niños pueden usar esos cupos libres sin costo adicional.
            $remainingBaseSpots = $baseOccupancy - $numAdults;
            if ($numChildren > $remainingBaseSpots) {
                // Solo pagan extra los niños que superen los cupos libres
                $extraChildren = $numChildren - $remainingBaseSpots;
            }
        }

        // Costo fijo diario de todos los extras combinados
        $totalExtraDaily = ($extraAdults * $extraAdultPrice) + ($extraChildren * $extraChildPrice);

        // LOG CLAVE: Por si un cliente reclama un cobro, aquí sabemos exactamente la matemática que se hizo

        log_message('info', implode(' | ', [
            "[PriceCalculator] Unidad ID {$unitId}",
            "Rate Plan ID {$ratePlanId}",          // ← nuevo
            "Ocupación Base: {$baseOccupancy}",
            "Huéspedes: {$numAdults} Ad / {$numChildren} Ni",
            "Extras cobrados: {$extraAdults} Ad / {$extraChildren} Ni",
            "Costo Extra Diario: $" . number_format($totalExtraDaily, 2),  // ← formato de moneda
            "Check-in: {$checkIn} → Check-out: {$checkOut}"               // ← nuevo, fechas completas
        ]));

        $totalPrice = 0;
        $dailyDetails = [];

        // 4. Iterar día por día
        $currentDate = strtotime($checkIn);
        $endDate = strtotime($checkOut);

        while ($currentDate < $endDate) {
            $dateStr = date('Y-m-d', $currentDate);
            $dailyRoomPrice = $roomPrice;
            $appliedSeason = null;

            // 5. Buscar si hay una temporada para ESTE día específico
            $seasons = $seasonModel->where('is_active', 1)
                ->where('start_date <=', $dateStr)
                ->where('end_date >=', $dateStr)
                ->orderBy('priority', 'DESC')
                ->findAll();

            if (!empty($seasons)) {
                foreach ($seasons as $season) {
                    $unitMatches = empty($season['unit_id']) || $season['unit_id'] == $unitId;
                    $planMatches = empty($season['rate_plan_id']) || $season['rate_plan_id'] == $ratePlanId;

                    if ($unitMatches && $planMatches) {
                        $appliedSeason = $season['name'];

                        // Aplicar descuento/aumento SOLO al precio base de la habitación, no a los extras
                        if ($season['modifier_type'] == 'fixed') {
                            $dailyRoomPrice = $season['modifier_value'];
                        } elseif ($season['modifier_type'] == 'percent_increase') {
                            $dailyRoomPrice += ($roomPrice * ($season['modifier_value'] / 100));
                        } elseif ($season['modifier_type'] == 'percent_decrease') {
                            $dailyRoomPrice -= ($roomPrice * ($season['modifier_value'] / 100));
                        }
                        break; // Tomamos solo la temporada de mayor prioridad y salimos del loop
                    }
                }
            }

            // El precio de hoy es la habitación (con o sin descuento de temporada) + los consumos extra
            $dailyPrice = $dailyRoomPrice + $totalExtraDaily;
            $totalPrice += $dailyPrice;

            // Guardamos el detalle para auditoría (rate_snapshot_json)
            $dailyDetails[] = [
                'date'   => $dateStr,
                'price'  => round($dailyPrice, 2),
                'season' => $appliedSeason ?: 'Tarifa Base'
            ];



            // Avanzar al día siguiente
            $currentDate = strtotime('+1 day', $currentDate);
        }

        // Calculamos los totales separados para el front-end
        $nights = count($dailyDetails);
        $totalExtras = $totalExtraDaily * $nights;
        $totalRoom = $totalPrice - $totalExtras;

        return [
            'total_price'    => round($totalPrice, 2),
            'room_total'     => round($totalRoom, 2),
            'extra_total'    => round($totalExtras, 2),
            'extra_adults'   => $extraAdults,
            'extra_children' => $extraChildren,
            'base_capacity'  => $baseOccupancy,
            'nights'         => $nights,
            'daily_details'  => $dailyDetails
        ];
    }
}