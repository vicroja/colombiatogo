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

        if (!$plan || empty($plan['limits_json'])) {
            return false;
        }

        // Decodificamos el JSON de límites
        $limits = json_decode($plan['limits_json'], true);
        $maxUnits = $limits['max_units'] ?? 0;

        // Si el límite es -1 (Enterprise), es ilimitado
        if ($maxUnits == -1) {
            return true;
        }

        // 3. EL ARREGLO CRÍTICO: Contar SOLO las unidades de este hotel en específico
        $unitModel = new AccommodationUnitModel();
        $currentUnitsCount = $unitModel->where('tenant_id', $tenantId)->countAllResults();

        // 4. Evaluar
        return $currentUnitsCount < $maxUnits;
    }

    /**
     * Devuelve información de uso para mostrar en la interfaz (Ej. "3 de 5 unidades usadas")
     */

    public function getUnitUsageInfo(): array
    {
        $tenantId = session('active_tenant_id');

        // 1. Seguridad básica: Si por alguna razón se pierde la sesión, devolver 0
        if (!$tenantId) {
            return ['used' => 0, 'limit' => 0, 'unlimited' => false];
        }

        // 2. Buscar la suscripción activa o en periodo de prueba
        $subModel = new \App\Models\TenantSubscriptionModel(); // Asegura el namespace correcto si es necesario
        $subscription = $subModel->where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'trial'])
            ->first();

        if (!$subscription) {
            return ['used' => 0, 'limit' => 0, 'unlimited' => false];
        }

        // 3. Buscar el plan y extraer los límites de forma segura
        $planModel = new \App\Models\SubscriptionPlanModel();
        $plan = $planModel->find($subscription['plan_id']);

        // Si no hay plan o el JSON está vacío, devolvemos 0 por seguridad
        if (!$plan || empty($plan['limits_json'])) {
            return ['used' => 0, 'limit' => 0, 'unlimited' => false];
        }

        $limits = json_decode($plan['limits_json'], true);

        // Operador null coalescing: Si no existe 'max_units' en el JSON, asume 0
        $maxUnits = $limits['max_units'] ?? 0;

        // 4. EL ARREGLO CRÍTICO: Contar SOLO las unidades de este hotel en específico
        $unitModel = new \App\Models\AccommodationUnitModel();
        $used = $unitModel->where('tenant_id', $tenantId)->countAllResults();

        // 5. Retornar la matemática lista para la vista
        return [
            'used'      => $used,
            'limit'     => $maxUnits,
            'unlimited' => ($maxUnits == -1) // Si el límite es -1, es un plan ilimitado
        ];
    }
}