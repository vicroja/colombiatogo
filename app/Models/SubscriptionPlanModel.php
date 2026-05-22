<?php

namespace App\Models;

use CodeIgniter\Model;

class SubscriptionPlanModel extends Model
{
    protected $table            = 'subscription_plans';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'name', 'slug', 'description', 'price', 'currency',
        'billing_cycle', 'trial_days', 'limits_json',
        'is_public', 'is_active', 'sort_order', 'color'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Catálogo canónico de límites disponibles.
     * Define qué campos se renderizan en el formulario y de qué tipo son.
     * Si en el futuro agregas un nuevo límite a tu sistema, lo agregas aquí
     * y la UI se actualiza sola.
     *
     * Tipos soportados:
     *  - 'int'    : número entero (-1 = ilimitado)
     *  - 'bool'   : checkbox
     *  - 'select' : dropdown (requiere 'options')
     */
    public static function limitsCatalog(): array
    {
        return [
            // Cuotas numéricas
            'max_units' => [
                'label' => 'Máximo de unidades / habitaciones',
                'type'  => 'int',
                'group' => 'Cuotas',
                'help'  => 'Usa -1 para ilimitado',
            ],
            'max_users' => [
                'label' => 'Máximo de usuarios',
                'type'  => 'int',
                'group' => 'Cuotas',
                'help'  => 'Usa -1 para ilimitado',
            ],
            'max_products' => [
                'label' => 'Máximo de productos',
                'type'  => 'int',
                'group' => 'Cuotas',
                'help'  => 'Usa -1 para ilimitado',
            ],
            'max_reservations_per_month' => [
                'label' => 'Máximo de reservas / mes',
                'type'  => 'int',
                'group' => 'Cuotas',
                'help'  => 'Usa -1 para ilimitado',
            ],
            'storage_mb' => [
                'label' => 'Almacenamiento (MB)',
                'type'  => 'int',
                'group' => 'Cuotas',
                'help'  => 'Usa -1 para ilimitado',
            ],
            'calendar_sources' => [
                'label' => 'Fuentes de calendario (iCal)',
                'type'  => 'int',
                'group' => 'Cuotas',
                'help'  => 'Usa -1 para ilimitado',
            ],
            'data_retention_months' => [
                'label' => 'Retención de datos (meses)',
                'type'  => 'int',
                'group' => 'Cuotas',
                'help'  => 'Usa -1 para ilimitado',
            ],

            // Módulos / Features
            'has_multi_user' => [
                'label' => 'Multi-usuario habilitado',
                'type'  => 'bool',
                'group' => 'Módulos',
            ],
            'has_api_access' => [
                'label' => 'Acceso a API',
                'type'  => 'bool',
                'group' => 'Módulos',
            ],
            'has_reports_module' => [
                'label' => 'Módulo de reportes',
                'type'  => 'bool',
                'group' => 'Módulos',
            ],
            'has_website_module' => [
                'label' => 'Módulo de sitio web',
                'type'  => 'bool',
                'group' => 'Módulos',
            ],
            'has_financial_module' => [
                'label' => 'Módulo financiero',
                'type'  => 'bool',
                'group' => 'Módulos',
            ],
            'has_maintenance_module' => [
                'label' => 'Módulo de mantenimiento',
                'type'  => 'bool',
                'group' => 'Módulos',
            ],

            // Soporte
            'support_level' => [
                'label'   => 'Nivel de soporte',
                'type'    => 'select',
                'group'   => 'Soporte',
                'options' => [
                    'basic'    => 'Básico (email, 48h)',
                    'standard' => 'Estándar (email + chat, 24h)',
                    'premium'  => 'Premium (24/7, dedicado)',
                ],
            ],
        ];
    }

    /**
     * Decodifica limits_json de forma segura.
     */
    public function decodeLimits(array $plan): array
    {
        $json = $plan['limits_json'] ?? '{}';
        if (is_string($json)) {
            $json = json_decode($json, true) ?? [];
        }
        return is_array($json) ? $json : [];
    }

    /**
     * Construye el limits_json desde el array $_POST['limits'] del formulario.
     * Valida tipos según el catálogo para evitar JSON corrupto.
     */
    public function buildLimitsJson(array $rawLimits): string
    {
        $catalog = self::limitsCatalog();
        $clean   = [];

        foreach ($catalog as $key => $meta) {
            $value = $rawLimits[$key] ?? null;

            switch ($meta['type']) {
                case 'int':
                    // Si viene vacío o no numérico, asume 0 (no ilimitado)
                    $clean[$key] = is_numeric($value) ? (int) $value : 0;
                    break;

                case 'bool':
                    // Checkbox: si no viene en POST, es false
                    $clean[$key] = !empty($value);
                    break;

                case 'select':
                    $opts = $meta['options'] ?? [];
                    $clean[$key] = array_key_exists($value, $opts)
                        ? $value
                        : array_key_first($opts);
                    break;
            }
        }

        return json_encode($clean, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Cuenta cuántos tenants están suscritos a cada plan.
     * Útil para el listado y para impedir borrados accidentales.
     */
    public function getTenantCountByPlan(): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('tenant_subscriptions')
            ->select('plan_id, COUNT(*) as total')
            ->whereIn('status', ['active', 'trial', 'past_due'])
            ->groupBy('plan_id')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['plan_id']] = (int)$r['total'];
        }
        return $map;
    }
}