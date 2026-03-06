<?php

namespace App\Controllers;

use App\Models\AccommodationTypeModel;
use App\Models\AccommodationUnitModel;
use App\Services\PlanLimitService;

class InventoryController extends BaseController
{
    public function index()
    {
        $unitModel = new AccommodationUnitModel();
        $limitService = new PlanLimitService();

        // Join manual para traer el nombre del tipo de habitación en la misma consulta
        $units = $unitModel->select('accommodation_units.*, accommodation_types.name as type_name')
            ->join('accommodation_types', 'accommodation_types.id = accommodation_units.type_id')
            ->findAll();

        $data = [
            'units'     => $units,
            'limitInfo' => $limitService->getUnitUsageInfo()
        ];

        return view('inventory/index', $data);
    }

    public function create()
    {
        $limitService = new PlanLimitService();

        // Bloqueo de UI si ya no puede agregar más
        if (!$limitService->canAddUnit()) {
            return redirect()->to('/inventory')->with('error', 'Has alcanzado el límite de unidades de tu plan. Contacta a soporte para mejorar tu suscripción.');
        }

        $typeModel = new AccommodationTypeModel();
        // Si no hay tipos, creamos uno genérico automáticamente para facilitar la prueba
        if ($typeModel->countAllResults() == 0) {
            $typeModel->createForTenant([
                'name' => 'Habitación Estándar',
                'base_capacity' => 2,
                'max_capacity' => 2
            ]);
        }

        $data = [
            'types' => $typeModel->findAll()
        ];

        return view('inventory/create', $data);
    }

    public function store()
    {
        $limitService = new PlanLimitService();

        // El guardián de backend: Validación estricta por si intentan saltarse la UI
        if (!$limitService->canAddUnit()) {
            return redirect()->to('/inventory')->with('error', 'Límite de unidades excedido según su plan actual.');
        }

        $unitModel = new AccommodationUnitModel();

        $unitModel->createForTenant([
            'type_id' => $this->request->getPost('type_id'),
            'name'    => $this->request->getPost('name'),
            'status'  => 'available'
        ]);

        return redirect()->to('/inventory')->with('success', 'Habitación añadida al inventario con éxito.');
    }
}