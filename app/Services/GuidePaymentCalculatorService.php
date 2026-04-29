<?php
// app/Services/GuidePaymentCalculatorService.php

namespace App\Services;

use App\Models\TourGuideModel;
use App\Models\TourScheduleModel;
use App\Models\TourReservationModel;
use App\Models\GuidePaymentModel;

class GuidePaymentCalculatorService
{
    /**
     * Calcula y registra automáticamente el pago al guía
     * cuando un schedule pasa a estado 'completed'.
     *
     * Llamar desde TourController::updateScheduleStatus()
     * al detectar transición → 'completed'.
     *
     * @param int $scheduleId
     * @param int $tenantId
     * @return array  ['success', 'amount', 'detail'] o ['success' => false, 'message']
     */
    public function calculateAndStore(int $scheduleId, int $tenantId): array
    {
        $scheduleModel    = new TourScheduleModel();
        $guideModel       = new TourGuideModel();
        $tourResModel     = new TourReservationModel();
        $paymentModel     = new GuidePaymentModel();

        // 1. Cargar el schedule con datos del tour
        $schedule = $scheduleModel
            ->select('tour_schedules.*, tours.name AS tour_name')
            ->join('tours', 'tours.id = tour_schedules.tour_id')
            ->where('tour_schedules.id', $scheduleId)
            ->first();

        if (!$schedule || empty($schedule['guide_id'])) {
            log_message('info', "[GuidePayCalc] Schedule {$scheduleId} sin guía asignado — sin pago generado.");
            return ['success' => true, 'amount' => 0, 'detail' => null];
        }

        // 2. Cargar guía
        $guide = $guideModel->find($schedule['guide_id']);
        if (!$guide) {
            log_message('warning', "[GuidePayCalc] Guía {$schedule['guide_id']} no encontrado.");
            return ['success' => false, 'message' => 'Guía no encontrado.'];
        }

        // 3. Guía con salario fijo mensual — no genera pago por tour
        if ($guide['payment_model'] === 'salary') {
            log_message('info', "[GuidePayCalc] Guía {$guide['name']} es salary — sin pago por tour.");
            return ['success' => true, 'amount' => 0, 'detail' => ['model' => 'salary']];
        }

        // 4. Obtener totales del schedule para los cálculos
        $reservations = $tourResModel
            ->where('schedule_id', $scheduleId)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->findAll();

        $totalAdults   = array_sum(array_column($reservations, 'num_adults'));
        $totalChildren = array_sum(array_column($reservations, 'num_children'));
        $totalPax      = $totalAdults + $totalChildren;
        $totalRevenue  = array_sum(array_column($reservations, 'total_price'));

        // 5. Calcular según modelo
        $amount = 0;
        $detail = [
            'model'          => $guide['payment_model'],
            'total_adults'   => $totalAdults,
            'total_children' => $totalChildren,
            'total_pax'      => $totalPax,
            'total_revenue'  => $totalRevenue,
            'tour_name'      => $schedule['tour_name'],
            'schedule_date'  => $schedule['start_datetime'],
        ];

        switch ($guide['payment_model']) {

            case 'fixed_per_tour':
                // Tarifa fija por salida sin importar cuántos pasajeros
                $amount = (float)($guide['rate_fixed'] ?? 0);
                $detail['rate_fixed'] = $amount;
                break;

            case 'per_pax':
                // Cobro por cada pasajero adulto y niño
                $adultTotal = (float)($guide['rate_per_adult'] ?? 0) * $totalAdults;
                $childTotal = (float)($guide['rate_per_child'] ?? 0) * $totalChildren;
                $amount     = $adultTotal + $childTotal;
                $detail['rate_per_adult']  = $guide['rate_per_adult'];
                $detail['rate_per_child']  = $guide['rate_per_child'];
                $detail['adult_subtotal']  = $adultTotal;
                $detail['child_subtotal']  = $childTotal;
                break;

            case 'commission_pct':
                // Porcentaje sobre el total vendido en la salida
                $amount = round($totalRevenue * ((float)($guide['commission_pct'] ?? 0) / 100), 2);
                $detail['commission_pct']    = $guide['commission_pct'];
                $detail['commission_amount'] = $amount;
                break;

            case 'mixed':
                // Tarifa base + cobro por pax que supere el mínimo configurado
                $baseAmount  = (float)($guide['rate_fixed'] ?? 0);
                $minPax      = (int)($guide['min_pax_for_bonus'] ?? 0);
                $bonusPax    = max(0, $totalPax - $minPax);
                $bonusAdults = min($bonusPax, $totalAdults);
                $bonusChildren = max(0, $bonusPax - $bonusAdults);
                $bonusAmount = ($bonusAdults   * (float)($guide['rate_per_adult'] ?? 0))
                    + ($bonusChildren * (float)($guide['rate_per_child'] ?? 0));
                $amount      = $baseAmount + $bonusAmount;

                $detail['rate_fixed']       = $baseAmount;
                $detail['min_pax_for_bonus']= $minPax;
                $detail['bonus_pax']        = $bonusPax;
                $detail['bonus_amount']     = $bonusAmount;
                break;
        }

        $amount = round($amount, 2);

        // 6. Verificar que no exista ya un pago para este schedule y guía
        $existing = $paymentModel
            ->where('schedule_id', $scheduleId)
            ->where('guide_id',    $guide['id'])
            ->whereNotIn('status', ['cancelled'])
            ->first();

        if ($existing) {
            log_message('warning', "[GuidePayCalc] Ya existe pago para schedule {$scheduleId} guía {$guide['id']} — omitiendo.");
            return ['success' => true, 'amount' => $existing['amount'], 'detail' => $detail, 'existing' => true];
        }

        // 7. Insertar el pago en estado 'pending'
        $paymentId = $paymentModel->insert([
            'tenant_id'                => $tenantId,
            'guide_id'                 => $guide['id'],
            'schedule_id'              => $scheduleId,
            'amount'                   => $amount,
            'payment_model_snapshot'   => $guide['payment_model'],
            'calculation_detail_json'  => json_encode($detail),
            'status'                   => 'pending',
        ]);

        log_message('info', implode(' | ', [
            "[GuidePayCalc] Pago generado ID #{$paymentId}",
            "Guía: {$guide['name']}",
            "Modelo: {$guide['payment_model']}",
            "Monto: $" . number_format($amount, 2),
            "Schedule: {$scheduleId}",
            "Pax: {$totalPax} (Ad:{$totalAdults} Ni:{$totalChildren})",
        ]));

        return [
            'success'    => true,
            'payment_id' => $paymentId,
            'amount'     => $amount,
            'detail'     => $detail,
        ];
    }
}