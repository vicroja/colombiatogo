<?php

namespace App\Controllers\Super;

use App\Controllers\BaseController;
use App\Models\TenantModel;
use App\Models\SubscriptionPlanModel;
use App\Models\TenantSubscriptionModel;

class TenantController extends BaseController
{
    // Muestra la lista de todas las propiedades (tenants)
    public function index()
    {
        $tenantModel = new TenantModel();

        $data = [
            'title'   => 'Gestión de Propiedades',
            'tenants' => $tenantModel->findAll()
        ];

        return view('super/tenants/index', $data);
    }

    // Muestra el formulario para crear una nueva propiedad
    public function create()
    {
        $planModel = new SubscriptionPlanModel();

        $data = [
            'title' => 'Crear Nueva Propiedad',
            // Solo traemos los planes activos para mostrar en el select
            'plans' => $planModel->where('is_active', 1)->findAll()
        ];

        return view('super/tenants/create', $data);
    }

    // Procesa el formulario y guarda la propiedad y su suscripción en la BD
    public function store()
    {
        // 1. Instanciamos los modelos
        $tenantModel = new TenantModel();
        $subscriptionModel = new TenantSubscriptionModel();
        $planModel = new SubscriptionPlanModel();

        // 2. Recibimos los datos básicos
        $planId = $this->request->getPost('plan_id');
        $plan = $planModel->find($planId);

        if (!$plan) {
            return redirect()->back()->withInput()->with('error', 'El plan seleccionado no es válido.');
        }

        // Calculamos la fecha de fin del trial (si el plan tiene días de prueba)
        $trialEndsAt = null;
        if ($plan['trial_days'] > 0) {
            $trialEndsAt = date('Y-m-d', strtotime("+{$plan['trial_days']} days"));
        }

        // 3. Iniciamos la transacción (Todo o Nada)
        $tenantModel->db->transStart();

        // A. Insertamos el Tenant
        $tenantData = [
            'name'              => $this->request->getPost('name'),
            'slug'              => strtolower(trim($this->request->getPost('slug'))),
            'email'             => $this->request->getPost('email'),
            'current_plan_slug' => $plan['slug'],
            'trial_ends_at'     => $trialEndsAt,
            'is_active'         => 1,
            'onboarding_status' => 'pending'
        ];

        $tenantId = $tenantModel->insert($tenantData);

        // B. Insertamos la suscripción inicial del Tenant
        $subscriptionData = [
            'tenant_id'            => $tenantId,
            'plan_id'              => $planId,
            'status'               => $plan['trial_days'] > 0 ? 'trial' : 'active',
            'started_at'           => date('Y-m-d'),
            'trial_ends_at'        => $trialEndsAt,
            'current_period_start' => date('Y-m-d'),
            // Por defecto damos 1 mes de periodo si es mensual.
            // En la fase de facturación esto se calculará dinámicamente.
            'current_period_end'   => date('Y-m-d', strtotime('+1 month')),
            'created_by'           => session('superadmin_id')
        ];

        $subscriptionModel->insert($subscriptionData);

        // 4. Completamos la transacción
        $tenantModel->db->transComplete();

        // Verificamos si hubo algún error en las consultas
        if ($tenantModel->db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Error en la base de datos al crear la propiedad.');
        }

        return redirect()->to('/super/tenants')->with('success', 'Propiedad creada y suscripción asignada con éxito.');
    }
}