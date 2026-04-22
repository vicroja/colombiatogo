<?php

namespace App\Models;

class RatePlanModel extends BaseMultiTenantModel
{
    protected $table         = 'rate_plans';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'tenant_id',
        'name',
        'description',
        'amenities_json',
        'cancellation_policy',
        'min_nights_default',
        'is_default',
        'is_active',
    ];

    /**
     * Lista canónica de beneficios disponibles.
     * Úsala en vistas para renderizar checkboxes y chips.
     */
    public static function availableAmenities(): array
    {
        return [
            'breakfast'         => ['label' => 'Desayuno',             'icon' => 'bi-cup-hot-fill',     'color' => '#f59e0b'],
            'lunch'             => ['label' => 'Almuerzo',             'icon' => 'bi-sun-fill',          'color' => '#10b981'],
            'dinner'            => ['label' => 'Cena',                 'icon' => 'bi-moon-stars-fill',   'color' => '#6366f1'],
            'all_inclusive'     => ['label' => 'Todo Incluido',        'icon' => 'bi-stars',             'color' => '#ec4899'],
            'airport_transfer'  => ['label' => 'Traslado Aeropuerto',  'icon' => 'bi-airplane-fill',     'color' => '#0ea5e9'],
            'late_checkout'     => ['label' => 'Late Check-out',       'icon' => 'bi-clock-history',     'color' => '#8b5cf6'],
            'free_cancellation' => ['label' => 'Cancelación Gratuita', 'icon' => 'bi-shield-check-fill', 'color' => '#059669'],
            'non_refundable'    => ['label' => 'No Reembolsable',      'icon' => 'bi-shield-x-fill',     'color' => '#ef4444'],
            'wifi_premium'      => ['label' => 'WiFi Premium',         'icon' => 'bi-wifi',              'color' => '#06b6d4'],
            'parking'           => ['label' => 'Parking Incluido',     'icon' => 'bi-car-front-fill',    'color' => '#64748b'],
        ];
    }

    /**
     * Devuelve los amenities activos de un plan como array de keys.
     * Maneja tanto string JSON como array (MySQL puede devolver ambos).
     */
    public function getActiveAmenities(array $plan): array
    {
        $json = $plan['amenities_json'] ?? [];
        if (is_string($json)) {
            $json = json_decode($json, true) ?? [];
        }
        return array_keys(array_filter($json));
    }
}