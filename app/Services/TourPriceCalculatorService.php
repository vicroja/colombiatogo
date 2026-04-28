<?php
// app/Services/TourPriceCalculatorService.php

namespace App\Services;

use App\Models\TourModel;
use App\Models\TourScheduleModel;
use App\Models\SeasonalRateModel;

class TourPriceCalculatorService
{
    /**
     * Calcula el precio total de un tour para un grupo de personas.
     *
     * Jerarquía de precios (de menor a mayor prioridad):
     *  1. Precio base del tour (tours.price_adult / price_child)
     *  2. Override del schedule (tour_schedules.price_adult_override)
     *  3. Temporada activa de seasonal_rates que coincida con la fecha de salida
     *
     * @param int $scheduleId  ID de la salida específica
     * @param int $numAdults
     * @param int $numChildren
     * @return array           Desglose completo para mostrar en UI y guardar en price_snapshot_json
     */
    public function calculate(int $scheduleId, int $numAdults = 1, int $numChildren = 0): array
    {
        $scheduleModel = new TourScheduleModel();
        $tourModel     = new TourModel();
        $seasonModel   = new SeasonalRateModel();

        // 1. Cargar schedule y tour padre
        $schedule = $scheduleModel->find($scheduleId);
        if (!$schedule) {
            log_message('error', "[TourPriceCalc] Schedule {$scheduleId} no encontrado.");
            return $this->emptyResult();
        }

        $tour = $tourModel->find($schedule['tour_id']);
        if (!$tour) {
            log_message('error', "[TourPriceCalc] Tour {$schedule['tour_id']} no encontrado para schedule {$scheduleId}.");
            return $this->emptyResult();
        }

        // 2. Precio base: usar override del schedule si existe, si no el del tour
        $priceAdult = $schedule['price_adult_override'] !== null
            ? (float)$schedule['price_adult_override']
            : (float)$tour['price_adult'];

        $priceChild = $schedule['price_child_override'] !== null
            ? (float)$schedule['price_child_override']
            : (float)$tour['price_child'];

        $priceSource = $schedule['price_adult_override'] !== null ? 'schedule_override' : 'tour_base';

        // 3. Buscar temporada activa para la fecha de salida del schedule
        $departureDateStr = date('Y-m-d', strtotime($schedule['start_datetime']));
        $appliedSeason    = null;

        $seasons = $seasonModel
            ->where('is_active', 1)
            ->where('start_date <=', $departureDateStr)
            ->where('end_date >=',   $departureDateStr)
            ->groupStart()
            ->where('tour_id', $tour['id'])    // temporada específica para este tour
            ->orWhere('tour_id IS NULL')        // o temporada global (aplica a todos)
            ->groupEnd()
            // Excluimos las que son exclusivas de unidades de alojamiento
            ->where('unit_id IS NULL')
            ->orderBy('priority', 'DESC')
            ->findAll();

        if (!empty($seasons)) {
            $season = $seasons[0]; // la de mayor prioridad
            $appliedSeason = $season['name'];

            // El modificador aplica SOLO al precio adulto base como referencia
            // El precio niño mantiene su proporción respecto al adulto
            $ratio = ($priceAdult > 0) ? ($priceChild / $priceAdult) : 0;

            if ($season['modifier_type'] === 'fixed') {
                // Precio fijo absoluto para adulto; niño mantiene ratio
                $priceAdult = (float)$season['modifier_value'];
                $priceChild = round($priceAdult * $ratio, 2);
            } elseif ($season['modifier_type'] === 'percent_increase') {
                $factor     = $season['modifier_value'] / 100;
                $priceAdult = $priceAdult + ($priceAdult * $factor);
                $priceChild = $priceChild + ($priceChild * $factor);
            } elseif ($season['modifier_type'] === 'percent_decrease') {
                $factor     = $season['modifier_value'] / 100;
                $priceAdult = $priceAdult - ($priceAdult * $factor);
                $priceChild = $priceChild - ($priceChild * $factor);
            }

            $priceSource = 'seasonal_rate';

            log_message('info', "[TourPriceCalc] Temporada '{$season['name']}' aplicada al schedule {$scheduleId}. Tipo: {$season['modifier_type']}, Valor: {$season['modifier_value']}.");
        }

        // 4. Calcular totales
        $totalAdults   = round($priceAdult * $numAdults, 2);
        $totalChildren = round($priceChild * $numChildren, 2);
        $totalPrice    = round($totalAdults + $totalChildren, 2);

        log_message('info', implode(' | ', [
            "[TourPriceCalc] Schedule {$scheduleId}",
            "Tour: {$tour['name']}",
            "Salida: {$departureDateStr}",
            "Adultos: {$numAdults} x $" . number_format($priceAdult, 2),
            "Niños: {$numChildren} x $" . number_format($priceChild, 2),
            "Total: $" . number_format($totalPrice, 2),
            "Fuente precio: {$priceSource}",
            "Temporada: " . ($appliedSeason ?? 'Ninguna'),
        ]));

        // 5. Retornar desglose completo — se guarda en price_snapshot_json
        return [
            'total_price'     => $totalPrice,
            'price_adult'     => round($priceAdult, 2),
            'price_child'     => round($priceChild, 2),
            'total_adults'    => $totalAdults,
            'total_children'  => $totalChildren,
            'num_adults'      => $numAdults,
            'num_children'    => $numChildren,
            'price_source'    => $priceSource,      // para auditoría
            'applied_season'  => $appliedSeason,
            'departure_date'  => $departureDateStr,
            'tour_name'       => $tour['name'],
            'schedule_id'     => $scheduleId,
        ];
    }

    /**
     * Resultado vacío para casos de error, evita null en el controller.
     */
    private function emptyResult(): array
    {
        return [
            'total_price'    => 0,
            'price_adult'    => 0,
            'price_child'    => 0,
            'total_adults'   => 0,
            'total_children' => 0,
            'num_adults'     => 0,
            'num_children'   => 0,
            'price_source'   => 'error',
            'applied_season' => null,
            'departure_date' => null,
            'tour_name'      => null,
            'schedule_id'    => null,
        ];
    }
}