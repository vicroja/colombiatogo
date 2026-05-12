<?php

namespace App\Services;

use App\Models\CommissionModel;
use App\Models\SalesUserModel;
use App\Models\LeadModel;
use App\Models\LeadActivityModel;

/**
 * CommissionService
 * Calcula y registra automáticamente las comisiones cuando se gana un lead.
 *
 * Comisión directa: la del vendedor que cerró.
 * Override: la del gerente del vendedor (si existe y tiene override_rate > 0).
 */
class CommissionService
{
    /**
     * Genera comisiones para un lead ganado.
     *
     * @param int      $leadId    ID del lead ganado
     * @param int|null $tenantId  Tenant creado (puede ir null si la comisión se calcula antes)
     * @return array  ['direct_id'=>?int, 'override_id'=>?int, 'total'=>float]
     */
    public function generateForWonLead(int $leadId, ?int $tenantId = null): array
    {
        $leadModel = new LeadModel();
        $userModel = new SalesUserModel();
        $commModel = new CommissionModel();
        $activity  = new LeadActivityModel();

        $lead = $leadModel->find($leadId);
        if (!$lead) {
            return ['direct_id'=>null, 'override_id'=>null, 'total'=>0, 'msg'=>'Lead no encontrado'];
        }

        $value = (float)($lead['estimated_value'] ?? 0);
        if ($value <= 0) {
            log_message('info', "[Commission] Lead {$leadId} sin estimated_value, no se generan comisiones");
            return ['direct_id'=>null, 'override_id'=>null, 'total'=>0, 'msg'=>'Sin valor estimado'];
        }
        if (empty($lead['assigned_to'])) {
            log_message('info', "[Commission] Lead {$leadId} sin vendedor asignado");
            return ['direct_id'=>null, 'override_id'=>null, 'total'=>0, 'msg'=>'Sin vendedor asignado'];
        }

        $seller = $userModel->find($lead['assigned_to']);
        if (!$seller) {
            return ['direct_id'=>null, 'override_id'=>null, 'total'=>0, 'msg'=>'Vendedor no encontrado'];
        }

        $directId   = null;
        $overrideId = null;
        $total      = 0;

        // 1. Comisión directa del vendedor
        if ($seller['commission_rate'] > 0) {
            $amount = round($value * ($seller['commission_rate'] / 100), 2);
            $directId = $commModel->insert([
                'lead_id'              => $leadId,
                'tenant_id'            => $tenantId,
                'sales_user_id'        => $seller['id'],
                'source_sales_user_id' => $seller['id'],
                'type'                 => 'direct',
                'base_amount'          => $value,
                'rate'                 => $seller['commission_rate'],
                'amount'                => $amount,
                'status'               => 'pending',
                'earned_at'            => date('Y-m-d H:i:s'),
            ]);
            $total += $amount;

            log_message('info', "[Commission] Directa generada: \${$amount} para vendedor #{$seller['id']}");
        }

        // 2. Override del gerente (si aplica)
        if (!empty($seller['manager_id'])) {
            $manager = $userModel->find($seller['manager_id']);
            if ($manager && $manager['override_rate'] > 0 && $manager['is_active']) {
                $amount = round($value * ($manager['override_rate'] / 100), 2);
                $overrideId = $commModel->insert([
                    'lead_id'              => $leadId,
                    'tenant_id'            => $tenantId,
                    'sales_user_id'        => $manager['id'],
                    'source_sales_user_id' => $seller['id'],
                    'type'                 => 'override',
                    'base_amount'          => $value,
                    'rate'                 => $manager['override_rate'],
                    'amount'                => $amount,
                    'status'               => 'pending',
                    'earned_at'            => date('Y-m-d H:i:s'),
                    'notes'                => "Override por venta de {$seller['name']}",
                ]);
                $total += $amount;

                log_message('info', "[Commission] Override generado: \${$amount} para gerente #{$manager['id']} (venta de #{$seller['id']})");
            }
        }

        // Log en el timeline del lead
        if ($total > 0) {
            $activity->log(
                $leadId,
                'system',
                "Comisiones generadas por total \${$total} (directa+override)",
                null,
                'Comisiones'
            );
        }

        return [
            'direct_id'   => $directId,
            'override_id' => $overrideId,
            'total'       => $total,
            'msg'         => 'ok',
        ];
    }
}
