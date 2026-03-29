<?php

namespace App\Controllers;

use App\Models\RatePlanModel;
use App\Models\AccommodationUnitModel;
use App\Models\UnitRateModel;

class RatePlanController extends BaseController
{
    // Listado de Planes Tarifarios
    // Vista de la Matriz Intersectada (Unidades vs Planes)
    public function matrix()
    {
        $unitModel = new AccommodationUnitModel();
        $planModel = new RatePlanModel();
        $rateModel = new UnitRateModel();

        $tenantId = session('active_tenant_id');

        // 1. Obtener unidades ordenadas jerárquicamente (Cabaña -> Habitaciones)
        $units = $unitModel->where('tenant_id', $tenantId)
            ->where('status !=', 'maintenance')
            ->orderBy('COALESCE(parent_id, id) ASC', '', false)
            ->orderBy('parent_id', 'ASC')
            ->findAll();

        $plans = $planModel->where('tenant_id', $tenantId)->where('is_active', 1)->findAll();

        // 2. Traemos todas las tarifas existentes
        $allRates = $rateModel->where('tenant_id', $tenantId)->findAll();
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
        $tenantId = session('active_tenant_id');

        // Ahora $prices es [unit_id][plan_id]['base', 'adult', 'child']
        $prices = $this->request->getPost('prices');

        $rateModel->db->transStart();

        log_message('info', "[RatePlanController] Iniciando actualización masiva de matriz de tarifas para Tenant {$tenantId}");

        foreach ($prices as $unitId => $plans) {
            foreach ($plans as $planId => $rateData) {
                $basePrice = $rateData['base'] ?? '';
                $extraAdult = !empty($rateData['adult']) ? $rateData['adult'] : 0;
                $extraChild = !empty($rateData['child']) ? $rateData['child'] : 0;

                // Solo guardamos si definieron al menos la tarifa base
                if ($basePrice !== '' && $basePrice >= 0) {

                    $existing = $rateModel->where('unit_id', $unitId)
                        ->where('rate_plan_id', $planId)
                        ->first();

                    $updateData = [
                        'price_per_night'    => $basePrice,
                        'extra_person_price' => $extraAdult,
                        'extra_child_price'  => $extraChild
                    ];

                    if ($existing) {
                        $rateModel->update($existing['id'], $updateData);
                    } else {
                        $updateData['tenant_id'] = $tenantId;
                        $updateData['unit_id'] = $unitId;
                        $updateData['rate_plan_id'] = $planId;
                        $updateData['min_nights'] = 1; // Por defecto
                        $updateData['is_active'] = 1;

                        $rateModel->insert($updateData);
                    }
                }
            }
        }

        $rateModel->db->transComplete();

        if ($rateModel->db->transStatus() === false) {
            log_message('error', "[RatePlanController] Error en DB al guardar la matriz de tarifas.");
            return redirect()->back()->with('error', 'Error al guardar la matriz de precios.');
        }

        return redirect()->to('/rate-plans/matrix')->with('success', 'Matriz de tarifas actualizada correctamente.');
    }

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


}