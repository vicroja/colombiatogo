<?php

namespace App\Services;

use App\Models\LeadModel;
use App\Models\LeadActivityModel;
use App\Models\TenantModel;
use App\Models\TenantSubscriptionModel;

/**
 * LeadConversionService
 * Convierte un lead "Ganado" en un tenant real, reutilizando la lógica
 * que ya existe en AuthController::processRegister.
 */
class LeadConversionService
{
    /**
     * Convierte un lead en tenant + admin user + suscripción.
     *
     * @param int   $leadId
     * @param array $params ['plan_id'=>int, 'admin_name'=>str, 'admin_email'=>str, 'admin_password'=>str]
     * @return array ['success'=>bool, 'tenant_id'=>?int, 'message'=>str]
     */
    public function convert(int $leadId, array $params): array
    {
        $db          = \Config\Database::connect();
        $leadModel   = new LeadModel();
        $tenantModel = new TenantModel();
        $subModel    = new TenantSubscriptionModel();
        $activityM   = new LeadActivityModel();

        $lead = $leadModel->find($leadId);
        if (!$lead) {
            return ['success'=>false, 'tenant_id'=>null, 'message'=>'Lead no encontrado'];
        }
        if (!empty($lead['converted_tenant_id'])) {
            return ['success'=>false, 'tenant_id'=>$lead['converted_tenant_id'], 'message'=>'Este lead ya fue convertido'];
        }

        $db->transBegin();

        try {
            // 1. Generar slug único para el tenant a partir del nombre del hotel
            $slug = url_title(strtolower($lead['property_name']), '-', true);
            $base = $slug; $i = 1;
            while ($tenantModel->where('slug', $slug)->first()) {
                $slug = $base . '-' . $i++;
            }

            // 2. Crear tenant
            $tenantId = $tenantModel->insert([
                'name'              => $lead['property_name'],
                'slug'              => $slug,
                'email'             => $lead['contact_email'],
                'phone'             => $lead['contact_phone'],
                'city'              => $lead['property_city'],
                'country'           => $lead['property_country'],
                'website'           => $lead['property_website'],
                'timezone'          => 'America/Bogota',
                'currency_code'     => 'COP',
                'currency_symbol'   => '$',
                'checkin_time'      => '15:00:00',
                'checkout_time'     => '12:00:00',
                'is_active'         => 1,
                'onboarding_status' => 'pending',
            ]);

            if (!$tenantId) throw new \Exception('Error creando tenant');

            // 3. Crear usuario admin del nuevo hotel
            $userId = $db->table('users')->insert([
                'tenant_id'     => $tenantId,
                'name'          => $params['admin_name'] ?? $lead['contact_name'],
                'email'         => $params['admin_email'] ?? $lead['contact_email'],
                'password_hash' => password_hash($params['admin_password'] ?? bin2hex(random_bytes(6)), PASSWORD_BCRYPT),
                'role'          => 'admin',
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
            if (!$userId) throw new \Exception('Error creando usuario admin');

            // 4. Crear suscripción inicial si se pasó plan
            if (!empty($params['plan_id'])) {
                $subModel->insert([
                    'tenant_id'            => $tenantId,
                    'plan_id'              => $params['plan_id'],
                    'status'               => 'active',
                    'started_at'           => date('Y-m-d'),
                    'current_period_start' => date('Y-m-d'),
                    'current_period_end'   => date('Y-m-d', strtotime('+1 month')),
                    'created_by'           => session('superadmin_id') ?? null,
                ]);
            }

            // 5. Marcar el lead como ganado y vincular al tenant
            $leadModel->update($leadId, [
                'converted_tenant_id' => $tenantId,
                'won_at'              => date('Y-m-d H:i:s'),
            ]);

            $activityM->log(
                $leadId, 'system',
                "Lead convertido en tenant #{$tenantId} ({$lead['property_name']})",
                session('sales_user_id'),
                'Conversión a cliente'
            );

            $db->transCommit();
            return ['success'=>true, 'tenant_id'=>$tenantId, 'message'=>'Cliente creado correctamente'];

        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[LeadConversion] ' . $e->getMessage());
            return ['success'=>false, 'tenant_id'=>null, 'message'=>$e->getMessage()];
        }
    }
}
