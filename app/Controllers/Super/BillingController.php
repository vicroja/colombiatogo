<?php

namespace App\Controllers\Super;

use App\Controllers\BaseController;
use App\Models\TenantModel;
use App\Models\TenantSubscriptionModel;

class BillingController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // Hacemos un JOIN maestro para traer al hotel, su plan y su fecha de corte
        $builder = $db->table('tenants');
        $builder->select('tenants.id, tenants.name, tenants.is_suspended, tenant_subscriptions.current_period_end, tenant_subscriptions.status as sub_status, subscription_plans.name as plan_name, subscription_plans.price');
        $builder->join('tenant_subscriptions', 'tenant_subscriptions.tenant_id = tenants.id');
        $builder->join('subscription_plans', 'subscription_plans.id = tenant_subscriptions.plan_id');
        // Ordenamos para ver primero los que están por vencer o ya vencieron
        $builder->orderBy('tenant_subscriptions.current_period_end', 'ASC');

        $tenants = $builder->get()->getResultArray();

        $data = [
            'title'   => 'Facturación SaaS - MAVILUSA',
            'tenants' => $tenants
        ];

        return view('super/billing/index', $data);
    }

    public function renew($tenantId)
    {
        $subModel = new TenantSubscriptionModel();
        $tenantModel = new TenantModel();

        // Buscamos la suscripción activa o suspendida de este hotel
        $subscription = $subModel->where('tenant_id', $tenantId)->first();

        if ($subscription) {
            $subModel->db->transStart();

            // 1. Calculamos la nueva fecha (Le sumamos 1 mes exacto a la fecha actual de corte)
            $currentEndDate = $subscription['current_period_end'];
            // Si estaba súper vencido (ej. hace 3 meses), el nuevo mes empieza desde HOY para no regalarle días
            if (strtotime($currentEndDate) < time()) {
                $newEndDate = date('Y-m-d', strtotime('+1 month'));
            } else {
                $newEndDate = date('Y-m-d', strtotime($currentEndDate . ' +1 month'));
            }

            // 2. Actualizamos la suscripción
            $subModel->update($subscription['id'], [
                'current_period_end' => $newEndDate,
                'status'             => 'active'
            ]);

            // 3. Levantamos el castigo (si estaba suspendido, le devolvemos el acceso)
            $tenantModel->update($tenantId, [
                'is_suspended'     => 0,
                'suspended_reason' => null
            ]);

            $subModel->db->transComplete();

            if ($subModel->db->transStatus() === false) {
                return redirect()->back()->with('error', 'Error al procesar la renovación en BD.');
            }

            return redirect()->back()->with('success', 'Suscripción renovada con éxito. El hotel tiene 1 mes más de servicio.');
        }

        return redirect()->back()->with('error', 'No se encontró la suscripción.');
    }
}