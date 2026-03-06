<?php

namespace App\Services;

use App\Models\TenantSubscriptionModel;
use App\Models\SubscriptionPlanModel;
use App\Models\AccommodationUnitModel;

class PlanLimitService
{
    /**
     * Verifica si la propiedad actual tiene permitido agregar una unidad más.
     */
    public function canAddUnit(): bool
    {
        $tenantId = session('active_tenant_id');

        // 1. Buscar la suscripción activa
        $subModel = new TenantSubscriptionModel();
        $subscription = $subModel->where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'trial'])
            ->first();

        if (!$subscription) {
            return false; // Sin suscripción activa no puede operar
        }

        // 2. Traer los detalles del plan
        $planModel = new SubscriptionPlanModel();
        $plan = $planModel->find($subscription['plan_id']);

        // Decodificamos el JSON de límites
        $limits = json_decode($plan['limits_json'], true);

        // Si el límite es -1 (Enterprise), es ilimitado
        if ($limits['max_units'] == -1) {
            return true;
        }

        // 3. Contar cuántas unidades tiene actualmente
        $unitModel = new AccommodationUnitModel();
        // Nota: countAllResults() aplicará automáticamente el filtro tenant_id gracias al BaseMultiTenantModel
        $currentUnitsCount = $unitModel->countAllResults();

        // 4. Evaluar
        return $currentUnitsCount < $limits['max_units'];
    }

    /**
     * Devuelve información de uso para mostrar en la interfaz (Ej. "3 de 5 unidades usadas")
     */
    public function getUnitUsageInfo(): array
    {
        $tenantId = session('active_tenant_id');

        $subModel = new TenantSubscriptionModel();
        $subscription = $subModel->where('tenant_id', $tenantId)->whereIn('status', ['active', 'trial'])->first();

        if (!$subscription) return ['used' => 0, 'limit' => 0, 'unlimited' => false];

        $planModel = new SubscriptionPlanModel();
        $plan = $planModel->find($subscription['plan_id']);
        $limits = json_decode($plan['limits_json'], true);

        $unitModel = new AccommodationUnitModel();
        $used = $unitModel->countAllResults();

        return [
            'used'      => $used,
            'limit'     => $limits['max_units'],
            'unlimited' => ($limits['max_units'] == -1)
        ];
    }
}