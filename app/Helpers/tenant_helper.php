<?php
// app/Helpers/tenant_helper.php

/**
 * Helper para acceder a las features del tenant activo.
 *
 * Las features se leen UNA VEZ desde la BD y se cachean en la sesión
 * bajo la clave 'tenant_features'. Esto evita golpear la BD en cada
 * render del sidebar.
 *
 * Si necesitas refrescar las features (p.ej. tras cambiar settings),
 * llama a tenant_features_refresh().
 */

if (!function_exists('tenant_features')) {
    /**
     * Devuelve las features del tenant activo.
     *
     * @return array<string, bool> ej: ['tours' => true, 'accommodation' => false]
     */
    function tenant_features(): array
    {
        // 1. Si ya están en sesión, las devolvemos
        $cached = session('tenant_features');
        if (is_array($cached)) {
            return $cached;
        }

        // 2. Si no, las leemos del tenant y guardamos en sesión
        return tenant_features_refresh();
    }
}

if (!function_exists('tenant_features_refresh')) {
    /**
     * Re-lee las features desde la BD y actualiza la sesión.
     * Usar después de modificar settings_json del tenant.
     */
    function tenant_features_refresh(): array
    {
        $tenantId = (int) session('active_tenant_id');
        if ($tenantId === 0) {
            return ['tours' => false, 'accommodation' => false];
        }

        $tenantModel = new \App\Models\TenantModel();
        $tenant      = $tenantModel->find($tenantId);

        $settings = json_decode($tenant['settings_json'] ?? '{}', true) ?: [];

        $features = [
            'tours'         => (bool) ($settings['has_tours']         ?? false),
            'accommodation' => (bool) ($settings['has_accommodation'] ?? false),
        ];

        // Fallback: si por alguna razón no tiene nada, asumimos hotel
        if (!$features['tours'] && !$features['accommodation']) {
            $features['accommodation'] = true;
        }

        session()->set('tenant_features', $features);
        return $features;
    }
}

if (!function_exists('tenant_has_feature')) {
    /**
     * Atajo para preguntar si el tenant tiene una feature específica.
     */
    function tenant_has_feature(string $feature): bool
    {
        $features = tenant_features();
        return $features[$feature] ?? false;
    }
}

if (!function_exists('menu_can_see_features')) {
    /**
     * Verifica si un item del menú es visible según las features del tenant.
     *
     * Reglas:
     * - Si el item no declara 'features' (o está vacío), es siempre visible.
     * - Si declara features, el tenant debe tener AL MENOS UNA de ellas.
     */
    function menu_can_see_features(array $item): bool
    {
        $required = $item['features'] ?? [];
        if (empty($required)) {
            return true;
        }

        $tenantFeatures = tenant_features();
        foreach ($required as $f) {
            if (!empty($tenantFeatures[$f])) {
                return true;
            }
        }
        return false;
    }
}