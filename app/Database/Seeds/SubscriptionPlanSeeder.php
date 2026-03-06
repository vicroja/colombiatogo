<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run()
    {
        // Definimos la estructura de los planes según tu especificación
        $plans = [
            [
                'name'          => 'Trial',
                'slug'          => 'trial',
                'description'   => 'Periodo de prueba gratuito con límites reducidos.',
                'price'         => 0.00,
                'trial_days'    => 14,
                'is_public'     => 1,
                'sort_order'    => 1,
                'color'         => '#6B7280', // Gris
                // Convertimos el array de límites a JSON
                'limits_json'   => json_encode([
                    'max_units' => 3,
                    'max_reservations_per_month' => 20,
                    'max_users' => 1,
                    'max_products' => 5,
                    'has_website_module' => false,
                    'has_maintenance_module' => false,
                    'has_financial_module' => false,
                    'has_reports_module' => false,
                    'has_api_access' => false,
                    'has_multi_user' => false,
                    'storage_mb' => 100,
                    'calendar_sources' => 1,
                    'data_retention_months' => 1,
                    'support_level' => 'basic'
                ]),
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'name'          => 'Starter',
                'slug'          => 'starter',
                'description'   => 'Ideal para propietarios de 1-3 unidades. Funcionalidad esencial.',
                'price'         => 29.00,
                'trial_days'    => 0,
                'is_public'     => 1,
                'sort_order'    => 2,
                'color'         => '#3B82F6', // Azul
                'limits_json'   => json_encode([
                    'max_units' => 5,
                    'max_reservations_per_month' => 50,
                    'max_users' => 2,
                    'max_products' => 20,
                    'has_website_module' => true,
                    'has_maintenance_module' => false,
                    'has_financial_module' => false,
                    'has_reports_module' => false,
                    'has_api_access' => false,
                    'has_multi_user' => true,
                    'storage_mb' => 500,
                    'calendar_sources' => 2,
                    'data_retention_months' => 6,
                    'support_level' => 'basic'
                ]),
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'name'          => 'Professional',
                'slug'          => 'professional',
                'description'   => 'Propiedad mediana con equipo de trabajo y módulo financiero.',
                'price'         => 79.00,
                'trial_days'    => 0,
                'is_public'     => 1,
                'sort_order'    => 3,
                'color'         => '#8B5CF6', // Morado
                'limits_json'   => json_encode([
                    'max_units' => 20,
                    'max_reservations_per_month' => 200,
                    'max_users' => 5,
                    'max_products' => 100,
                    'has_website_module' => true,
                    'has_maintenance_module' => true,
                    'has_financial_module' => true,
                    'has_reports_module' => true,
                    'has_api_access' => false,
                    'has_multi_user' => true,
                    'storage_mb' => 2048,
                    'calendar_sources' => -1, // Ilimitado
                    'data_retention_months' => 24,
                    'support_level' => 'standard'
                ]),
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'name'          => 'Enterprise',
                'slug'          => 'enterprise',
                'description'   => 'Sin límites. Soporte premium. Onboarding dedicado.',
                'price'         => 299.00,
                'trial_days'    => 0,
                'is_public'     => 0, // Plan privado, asignado a mano
                'sort_order'    => 4,
                'color'         => '#10B981', // Verde
                'limits_json'   => json_encode([
                    'max_units' => -1, // Ilimitado
                    'max_reservations_per_month' => -1,
                    'max_users' => -1,
                    'max_products' => -1,
                    'has_website_module' => true,
                    'has_maintenance_module' => true,
                    'has_financial_module' => true,
                    'has_reports_module' => true,
                    'has_api_access' => true,
                    'has_multi_user' => true,
                    'storage_mb' => -1,
                    'calendar_sources' => -1,
                    'data_retention_months' => -1,
                    'support_level' => 'premium'
                ]),
                'created_at'    => date('Y-m-d H:i:s'),
            ]
        ];

        // Insertar en lote
        $this->db->table('subscription_plans')->insertBatch($plans);
    }
}