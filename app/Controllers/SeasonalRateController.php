<?php

namespace App\Controllers;

use App\Models\SeasonalRateModel;
use App\Services\PriceCalculatorService;
use App\Models\AccommodationUnitModel;
use App\Models\RatePlanModel;

class SeasonalRateController extends BaseController
{
    public function index()
    {
        $seasonModel = new SeasonalRateModel();

        $data = [
            'seasons' => $seasonModel->orderBy('start_date', 'ASC')->findAll()
        ];

        return view('seasonal_rates/index', $data);
    }

    public function store()
    {
        $seasonModel = new SeasonalRateModel();

        $seasonModel->createForTenant([
            'name'           => $this->request->getPost('name'),
            'start_date'     => $this->request->getPost('start_date'),
            'end_date'       => $this->request->getPost('end_date'),
            'modifier_type'  => $this->request->getPost('modifier_type'),
            'modifier_value' => $this->request->getPost('modifier_value'),
            'priority'       => $this->request->getPost('priority') ?: 10,
            'unit_id'        => null, // Por ahora globales para simplificar la UI
            'rate_plan_id'   => null
        ]);

        return redirect()->to('/seasonal-rates')->with('success', 'Temporada creada exitosamente.');
    }

    public function delete($id)
    {
        $seasonModel = new SeasonalRateModel();
        $seasonModel->delete($id);
        return redirect()->to('/seasonal-rates')->with('success', 'Temporada eliminada.');
    }

    // Ruta de Prueba: Para ver cómo piensa la calculadora
    public function testCalculator()
    {
        $unitModel = new AccommodationUnitModel();
        $planModel = new RatePlanModel();

        $unit = $unitModel->first();
        $plan = $planModel->first();

        if(!$unit || !$plan) return "Faltan unidades o planes para probar.";

        $calculator = new PriceCalculatorService();

        // Simulamos una reserva de 5 días
        $checkIn = date('Y-m-d');
        $checkOut = date('Y-m-d', strtotime('+5 days'));

        $result = $calculator->calculateStay($unit['id'], $plan['id'], $checkIn, $checkOut);

        return $this->response->setJSON([
            'habitacion' => $unit['name'],
            'plan' => $plan['name'],
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'resultado' => $result
        ]);
    }
}