<?php

namespace App\Controllers\Super;

use App\Controllers\BaseController;
use App\Models\TenantModel;
use App\Models\SubscriptionPlanModel;
use App\Models\TenantSubscriptionModel;

class TenantController extends BaseController
{
    public function index()
    {
        $tenantModel = new TenantModel();

        $data = [
            'title'   => 'Gestión de Propiedades',
            'tenants' => $tenantModel->orderBy('name', 'ASC')->findAll()
        ];

        return view('super/tenants/index', $data);
    }

    public function create()
    {
        $planModel = new SubscriptionPlanModel();

        $data = [
            'title' => 'Crear Nueva Propiedad',
            'plans' => $planModel->where('is_active', 1)->orderBy('sort_order')->findAll()
        ];

        return view('super/tenants/create', $data);
    }

    public function store()
    {
        $tenantModel       = new TenantModel();
        $subscriptionModel = new TenantSubscriptionModel();
        $planModel         = new SubscriptionPlanModel();

        $planId = $this->request->getPost('plan_id');
        $plan   = $planModel->find($planId);

        if (!$plan) {
            return redirect()->back()->withInput()->with('error', 'El plan seleccionado no es válido.');
        }

        $trialEndsAt = null;
        if ($plan['trial_days'] > 0) {
            $trialEndsAt = date('Y-m-d', strtotime("+{$plan['trial_days']} days"));
        }

        $tenantModel->db->transStart();

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

        $subscriptionData = [
            'tenant_id'            => $tenantId,
            'plan_id'              => $planId,
            'status'               => $plan['trial_days'] > 0 ? 'trial' : 'active',
            'started_at'           => date('Y-m-d'),
            'trial_ends_at'        => $trialEndsAt,
            'current_period_start' => date('Y-m-d'),
            'current_period_end'   => date('Y-m-d', strtotime('+1 month')),
            'created_by'           => session('superadmin_id')
        ];

        $subscriptionModel->insert($subscriptionData);

        $tenantModel->db->transComplete();

        if ($tenantModel->db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Error en la base de datos al crear la propiedad.');
        }

        return redirect()->to('/super/tenants')->with('success', 'Propiedad creada y suscripción asignada con éxito.');
    }

    /**
     * Vista de edición / detalle del tenant.
     */
    public function edit($id)
    {
        $tenantModel = new TenantModel();
        $subModel    = new TenantSubscriptionModel();
        $planModel   = new SubscriptionPlanModel();

        $tenant = $tenantModel->find($id);
        if (!$tenant) {
            return redirect()->to('/super/tenants')->with('error', 'Propiedad no encontrada.');
        }

        $subscription = $subModel->where('tenant_id', $id)->first();
        $currentPlan  = $subscription ? $planModel->find($subscription['plan_id']) : null;
        $allPlans     = $planModel->where('is_active', 1)->orderBy('sort_order')->findAll();

        $data = [
            'title'        => 'Editar Propiedad: ' . $tenant['name'],
            'tenant'       => $tenant,
            'subscription' => $subscription,
            'currentPlan'  => $currentPlan,
            'allPlans'     => $allPlans,
        ];

        return view('super/tenants/edit', $data);
    }

    /**
     * Procesa la actualización de datos del tenant.
     */
    public function update($id)
    {
        $tenantModel = new TenantModel();
        $tenant = $tenantModel->find($id);

        if (!$tenant) {
            return redirect()->to('/super/tenants')->with('error', 'Propiedad no encontrada.');
        }

        $data = [
            'name'              => $this->request->getPost('name'),
            'email'             => $this->request->getPost('email'),
            'phone'             => $this->request->getPost('phone'),
            'address'           => $this->request->getPost('address'),
            'city'              => $this->request->getPost('city'),
            'country'           => $this->request->getPost('country'),
            'website'           => $this->request->getPost('website'),
            'is_active'         => $this->request->getPost('is_active') ? 1 : 0,
            'onboarding_status' => $this->request->getPost('onboarding_status'),
        ];

        $tenantModel->update($id, $data);

        return redirect()->to('/super/tenants/edit/' . $id)
            ->with('success', 'Datos del tenant actualizados.');
    }

    /**
     * Cambia el plan de un tenant (upgrade / downgrade).
     * El nuevo plan aplica desde el siguiente periodo (no rompe el actual).
     */
    public function changePlan($id)
    {
        $tenantModel = new TenantModel();
        $subModel    = new TenantSubscriptionModel();
        $planModel   = new SubscriptionPlanModel();

        $newPlanId = $this->request->getPost('new_plan_id');
        $applyMode = $this->request->getPost('apply_mode'); // 'immediate' o 'next_period'

        $tenant = $tenantModel->find($id);
        $newPlan = $planModel->find($newPlanId);
        $subscription = $subModel->where('tenant_id', $id)->first();

        if (!$tenant || !$newPlan || !$subscription) {
            return redirect()->back()->with('error', 'Datos inválidos para el cambio de plan.');
        }

        $subModel->db->transStart();

        $updateData = [
            'plan_id' => $newPlanId,
            'notes'   => "Cambio de plan a {$newPlan['name']} el " . date('Y-m-d H:i:s')
                . " por " . session('superadmin_name'),
        ];

        // Si el cambio es inmediato, reiniciamos el periodo
        if ($applyMode === 'immediate') {
            $updateData['current_period_start'] = date('Y-m-d');
            $updateData['current_period_end']   = date('Y-m-d', strtotime('+1 month'));
            $updateData['status']               = 'active';
        }
        // Si es "next_period", mantenemos las fechas actuales y el plan nuevo
        // entrará en vigor cuando se renueve.

        $subModel->update($subscription['id'], $updateData);

        // Actualizar el slug del plan en el tenant (para consistencia)
        $tenantModel->update($id, [
            'current_plan_slug' => $newPlan['slug'],
        ]);

        $subModel->db->transComplete();

        if ($subModel->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Error al cambiar el plan.');
        }

        return redirect()->to('/super/tenants/edit/' . $id)
            ->with('success', "Plan cambiado a {$newPlan['name']} correctamente.");
    }

    /**
     * Suspender o reactivar manualmente un tenant.
     */
    public function toggleSuspend($id)
    {
        $tenantModel = new TenantModel();
        $subModel    = new TenantSubscriptionModel();

        $tenant = $tenantModel->find($id);
        if (!$tenant) {
            return redirect()->to('/super/tenants')->with('error', 'No encontrado.');
        }

        $newState = $tenant['is_suspended'] ? 0 : 1;
        $reason   = $this->request->getPost('reason') ?: 'Suspensión manual por super-admin';

        $tenantModel->update($id, [
            'is_suspended'     => $newState,
            'suspended_reason' => $newState ? $reason : null,
        ]);

        // Sincronizamos también la suscripción
        $subscription = $subModel->where('tenant_id', $id)->first();
        if ($subscription) {
            $subModel->update($subscription['id'], [
                'status'       => $newState ? 'suspended' : 'active',
                'suspended_at' => $newState ? date('Y-m-d H:i:s') : null,
            ]);
        }

        $msg = $newState ? 'Tenant suspendido manualmente.' : 'Tenant reactivado.';
        return redirect()->back()->with('success', $msg);
    }
}