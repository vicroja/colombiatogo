<?php

namespace App\Services;

use App\Models\TenantSubscriptionModel;
use App\Models\SubscriptionPlanModel;
use App\Models\AccommodationUnitModel;
use App\Models\UserModel;

class PlanLimitService
{
    /**
     * Cache de la suscripción activa para no consultarla repetidas veces
     * dentro del mismo request.
     */
    private ?array $cachedSubscription = null;
    private ?array $cachedPlan = null;
    private ?array $cachedLimits = null;

    /**
     * Carga (con cache) la suscripción + plan + límites del tenant activo.
     */
    private function loadContext(): bool
    {
        if ($this->cachedSubscription !== null) {
            return !empty($this->cachedSubscription);
        }

        $tenantId = session('active_tenant_id');
        if (!$tenantId) {
            $this->cachedSubscription = [];
            return false;
        }

        $subModel = new TenantSubscriptionModel();
        $sub = $subModel->where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'trial', 'past_due'])
            ->first();

        if (!$sub) {
            $this->cachedSubscription = [];
            return false;
        }

        $planModel = new SubscriptionPlanModel();
        $plan = $planModel->find($sub['plan_id']);

        if (!$plan) {
            $this->cachedSubscription = [];
            return false;
        }

        $limits = is_string($plan['limits_json'])
            ? (json_decode($plan['limits_json'], true) ?? [])
            : (is_array($plan['limits_json']) ? $plan['limits_json'] : []);

        $this->cachedSubscription = $sub;
        $this->cachedPlan         = $plan;
        $this->cachedLimits       = $limits;

        return true;
    }

    /**
     * Lee un valor del limits_json con default seguro.
     * Si no existe la clave, asume 0 (restrictivo).
     */
    public function getLimit(string $key, $default = 0)
    {
        if (!$this->loadContext()) return $default;
        return $this->cachedLimits[$key] ?? $default;
    }

    public function isUnlimited(string $key): bool
    {
        return $this->getLimit($key) === -1;
    }

    /**
     * ¿Tiene activado un módulo? (claves has_*)
     */
    public function hasFeature(string $key): bool
    {
        if (!$this->loadContext()) return false;
        return !empty($this->cachedLimits[$key]);
    }

    // ============================================================
    // CHECKS específicos
    // ============================================================

    public function canAddUnit(): bool
    {
        return $this->canAddMore('max_units', $this->countUnits());
    }

    public function canAddUser(): bool
    {
        return $this->canAddMore('max_users', $this->countUsers());
    }

    public function canCreateReservationThisMonth(): bool
    {
        return $this->canAddMore('max_reservations_per_month', $this->countReservationsThisMonth());
    }

    /**
     * Lógica común: ¿puede agregar uno más bajo cierto límite?
     */
    private function canAddMore(string $limitKey, int $currentCount): bool
    {
        if (!$this->loadContext()) return false;

        $max = (int)($this->cachedLimits[$limitKey] ?? 0);
        if ($max === -1) return true;        // ilimitado
        return $currentCount < $max;
    }

    // ============================================================
    // INFO de uso (para vistas)
    // ============================================================

    public function getUnitUsageInfo(): array
    {
        return $this->buildUsageInfo('max_units', $this->countUnits());
    }

    public function getUserUsageInfo(): array
    {
        return $this->buildUsageInfo('max_users', $this->countUsers());
    }

    public function getReservationUsageInfo(): array
    {
        return $this->buildUsageInfo('max_reservations_per_month', $this->countReservationsThisMonth());
    }

    /**
     * Devuelve un resumen completo de TODOS los límites + uso actual.
     * Útil para una vista de "Mi plan" del tenant.
     */
    public function getFullUsageReport(): array
    {
        return [
            'plan'         => $this->cachedPlan,
            'units'        => $this->getUnitUsageInfo(),
            'users'        => $this->getUserUsageInfo(),
            'reservations' => $this->getReservationUsageInfo(),
            'features'     => [
                'api_access'        => $this->hasFeature('has_api_access'),
                'reports'           => $this->hasFeature('has_reports_module'),
                'website'           => $this->hasFeature('has_website_module'),
                'financial'         => $this->hasFeature('has_financial_module'),
                'maintenance'       => $this->hasFeature('has_maintenance_module'),
                'multi_user'        => $this->hasFeature('has_multi_user'),
            ],
            'support_level' => $this->getLimit('support_level', 'basic'),
        ];
    }

    private function buildUsageInfo(string $limitKey, int $used): array
    {
        if (!$this->loadContext()) {
            return ['used' => 0, 'limit' => 0, 'unlimited' => false, 'percentage' => 0];
        }

        $limit = (int)($this->cachedLimits[$limitKey] ?? 0);

        return [
            'used'       => $used,
            'limit'      => $limit,
            'unlimited'  => $limit === -1,
            'percentage' => ($limit > 0) ? min(100, round(($used / $limit) * 100)) : 0,
        ];
    }

    // ============================================================
    // COUNTERS (queries SQL directas, no usan el modelo multi-tenant
    // para no depender del filtro automático)
    // ============================================================

    private function countUnits(): int
    {
        $tenantId = session('active_tenant_id');
        if (!$tenantId) return 0;
        return (new AccommodationUnitModel())->where('tenant_id', $tenantId)->countAllResults();
    }

    private function countUsers(): int
    {
        $tenantId = session('active_tenant_id');
        if (!$tenantId) return 0;
        return (new UserModel())->where('tenant_id', $tenantId)->countAllResults();
    }

    private function countReservationsThisMonth(): int
    {
        $tenantId = session('active_tenant_id');
        if (!$tenantId) return 0;

        $db = \Config\Database::connect();
        // Asumo que existe una tabla 'reservations' con tenant_id y created_at.
        // Si tu tabla se llama distinto, ajústalo aquí.
        return $db->table('reservations')
            ->where('tenant_id', $tenantId)
            ->where('created_at >=', date('Y-m-01 00:00:00'))
            ->countAllResults();
    }
}