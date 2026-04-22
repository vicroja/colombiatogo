<?php

namespace App\Controllers;

use App\Models\RatePlanModel;
use App\Models\AccommodationUnitModel;
use App\Models\UnitRateModel;

class RatePlanController extends BaseController
{
    public function index()
    {
        $planModel = new RatePlanModel();

        if ($planModel->countAllResults() == 0) {
            $planModel->createForTenant([
                'name'                => 'Tarifa Estándar',
                'cancellation_policy' => 'flexible',
                'min_nights_default'  => 1,
                'is_default'          => 1,
                'amenities_json'      => json_encode([]),
            ]);
        }

        $data['plans']     = $planModel->findAll();
        $data['amenities'] = RatePlanModel::availableAmenities();
        return view('rate_plans/index', $data);
    }

    public function store()
    {
        $planModel = new RatePlanModel();

        // Construir amenities_json desde los checkboxes
        $selected  = $this->request->getPost('amenities') ?? [];
        $available = array_keys(RatePlanModel::availableAmenities());
        $amenities = [];
        foreach ($available as $key) {
            $amenities[$key] = in_array($key, $selected);
        }

        $planModel->createForTenant([
            'name'                => $this->request->getPost('name'),
            'description'         => $this->request->getPost('description'),
            'amenities_json'      => json_encode($amenities),
            'cancellation_policy' => $this->request->getPost('cancellation_policy') ?? 'flexible',
            'min_nights_default'  => (int) ($this->request->getPost('min_nights_default') ?? 1),
            'is_default'          => 0,
            'is_active'           => 1,
        ]);

        return redirect()->to('/rate-plans')->with('success', 'Plan creado correctamente.');
    }

    /** Marcar un plan como el predeterminado (uno solo a la vez) */
    public function setDefault($id)
    {
        $planModel = new RatePlanModel();
        $tenantId  = session('active_tenant_id');

        // Quitar default a todos
        $planModel->where('tenant_id', $tenantId)->set('is_default', 0)->update();
        // Poner default al elegido
        $planModel->where('tenant_id', $tenantId)->where('id', $id)->set('is_default', 1)->update();

        return redirect()->to('/rate-plans')->with('success', 'Plan predeterminado actualizado.');
    }

    /** Activar / desactivar un plan */
    public function toggleActive($id)
    {
        $planModel = new RatePlanModel();
        $plan      = $planModel->find($id);
        if (!$plan) {
            return redirect()->to('/rate-plans')->with('error', 'Plan no encontrado.');
        }

        $planModel->update($id, ['is_active' => $plan['is_active'] ? 0 : 1]);
        return redirect()->to('/rate-plans')->with('success', 'Estado del plan actualizado.');
    }

    public function matrix()
    {
        $unitModel = new AccommodationUnitModel();
        $planModel = new RatePlanModel();
        $rateModel = new UnitRateModel();

        $tenantId = session('active_tenant_id');

        $units = $unitModel->where('tenant_id', $tenantId)
            ->where('status !=', 'maintenance')
            ->orderBy('COALESCE(parent_id, id) ASC', '', false)
            ->orderBy('parent_id', 'ASC')
            ->findAll();

        $plans = $planModel->where('tenant_id', $tenantId)->where('is_active', 1)->findAll();

        // Decodificar amenities_json de cada plan
        foreach ($plans as &$p) {
            if (is_string($p['amenities_json'])) {
                $p['amenities_json'] = json_decode($p['amenities_json'], true) ?? [];
            }
        }
        unset($p);

        $allRates    = $rateModel->where('tenant_id', $tenantId)->findAll();
        $ratesMatrix = [];
        foreach ($allRates as $r) {
            $ratesMatrix[$r['unit_id']][$r['rate_plan_id']] = $r;
        }

        return view('rate_plans/rates_matrix', [
            'units'       => $units,
            'plans'       => $plans,
            'ratesMatrix' => $ratesMatrix,
            'amenities'   => RatePlanModel::availableAmenities(),
        ]);
    }

    public function updateMatrix()
    {
        $rateModel = new UnitRateModel();
        $tenantId  = session('active_tenant_id');
        $prices    = $this->request->getPost('prices');

        $rateModel->db->transStart();

        foreach ($prices as $unitId => $plans) {
            foreach ($plans as $planId => $rateData) {
                $basePrice  = $rateData['base'] ?? '';
                $extraAdult = !empty($rateData['adult']) ? (float)$rateData['adult'] : 0;
                $extraChild = !empty($rateData['child']) ? (float)$rateData['child'] : 0;
                $minNights  = !empty($rateData['min_nights']) ? (int)$rateData['min_nights'] : 1;

                if ($basePrice !== '' && $basePrice >= 0) {
                    $existing = $rateModel->where('unit_id', $unitId)
                        ->where('rate_plan_id', $planId)
                        ->first();

                    $updateData = [
                        'price_per_night'    => (float)$basePrice,
                        'extra_person_price' => $extraAdult,
                        'extra_child_price'  => $extraChild,
                        'min_nights'         => $minNights,
                    ];

                    if ($existing) {
                        $rateModel->update($existing['id'], $updateData);
                    } else {
                        $rateModel->insert(array_merge($updateData, [
                            'tenant_id'    => $tenantId,
                            'unit_id'      => $unitId,
                            'rate_plan_id' => $planId,
                            'is_active'    => 1,
                        ]));
                    }
                }
            }
        }

        $rateModel->db->transComplete();

        if ($rateModel->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Error al guardar la matriz.');
        }

        return redirect()->to('/rate-plans/matrix')->with('success', 'Tarifario actualizado correctamente.');
    }
}