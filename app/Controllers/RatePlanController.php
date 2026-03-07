<?php

namespace App\Controllers;

use App\Models\RatePlanModel;
use App\Models\AccommodationUnitModel;
use App\Models\UnitRateModel;

class RatePlanController extends BaseController
{
    // Listado de Planes Tarifarios
    public function index()
    {
        $planModel = new RatePlanModel();

        // Creamos un plan por defecto si no existe ninguno para facilitar el inicio
        if ($planModel->countAllResults() == 0) {
            $planModel->createForTenant([
                'name' => 'Tarifa Estándar (Solo Alojamiento)',
                'is_default' => 1
            ]);
        }

        $data['plans'] = $planModel->findAll();
        return view('rate_plans/index', $data);
    }

    // Guardar un nuevo plan
    public function store()
    {
        $planModel = new RatePlanModel();
        $planModel->createForTenant([
            'name'               => $this->request->getPost('name'),
            'description'        => $this->request->getPost('description'),
            'includes_breakfast' => $this->request->getPost('includes_breakfast') ? 1 : 0
        ]);

        return redirect()->to('/rate-plans')->with('success', 'Plan Tarifario creado con éxito.');
    }

    // Vista de la Matriz Intersectada (Unidades vs Planes)
    public function matrix()
    {
        $unitModel = new AccommodationUnitModel();
        $planModel = new RatePlanModel();
        $rateModel = new UnitRateModel();

        $units = $unitModel->where('status !=', 'maintenance')->findAll();
        $plans = $planModel->where('is_active', 1)->findAll();

        // Traemos todas las tarifas existentes y las organizamos en un array clave-valor
        $allRates = $rateModel->findAll();
        $ratesMatrix = [];
        foreach ($allRates as $r) {
            $ratesMatrix[$r['unit_id']][$r['rate_plan_id']] = $r;
        }

        $data = [
            'units'       => $units,
            'plans'       => $plans,
            'ratesMatrix' => $ratesMatrix
        ];

        return view('rate_plans/rates_matrix', $data);
    }

    // Actualizar los precios en masa desde la matriz
    public function updateMatrix()
    {
        $rateModel = new UnitRateModel();
        $prices = $this->request->getPost('prices'); // Array [unit_id][plan_id] = price

        $rateModel->db->transStart();

        foreach ($prices as $unitId => $plans) {
            foreach ($plans as $planId => $price) {
                if ($price !== '' && $price >= 0) {
                    // Buscamos si ya existe esta combinación
                    $existing = $rateModel->where('unit_id', $unitId)
                        ->where('rate_plan_id', $planId)
                        ->first();

                    if ($existing) {
                        $rateModel->update($existing['id'], ['price_per_night' => $price]);
                    } else {
                        $rateModel->createForTenant([
                            'unit_id'         => $unitId,
                            'rate_plan_id'    => $planId,
                            'price_per_night' => $price
                        ]);
                    }
                }
            }
        }

        $rateModel->db->transComplete();

        if ($rateModel->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Error al guardar la matriz de precios.');
        }

        return redirect()->to('/rate-plans/matrix')->with('success', 'Matriz de tarifas actualizada correctamente.');
    }
}