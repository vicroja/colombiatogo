<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\TenantSubscriptionModel;
use App\Models\TenantModel;

/**
 * Revisa diariamente las suscripciones y mueve a:
 *   - 'past_due'  : current_period_end < hoy  (dentro del periodo de gracia)
 *   - 'suspended' : current_period_end + grace_period_days < hoy
 *
 * Uso: php spark subs:check
 * Cron sugerido (cada día a las 02:00):
 *   0 2 * * * cd /ruta/proyecto && php spark subs:check >> writable/logs/subs.log 2>&1
 */
class SubscriptionsCheck extends BaseCommand
{
    protected $group       = 'SaaS';
    protected $name        = 'subs:check';
    protected $description = 'Revisa vencimientos y aplica past_due / suspended según corresponda.';

    public function run(array $params)
    {
        $subModel    = new TenantSubscriptionModel();
        $tenantModel = new TenantModel();

        $today = date('Y-m-d');
        CLI::write("=== Subscription check: $today ===", 'yellow');

        // 1. Buscar todas las suscripciones activas o trial
        $subs = $subModel->whereIn('status', ['active', 'trial', 'past_due'])->findAll();

        $movedToPastDue   = 0;
        $movedToSuspended = 0;
        $skipped          = 0;

        foreach ($subs as $sub) {
            $endDate     = $sub['current_period_end'];
            $grace       = (int)($sub['grace_period_days'] ?? 5);
            $graceLimit  = date('Y-m-d', strtotime("$endDate +$grace days"));

            // Caso A: Pasado el periodo de gracia → suspender
            if ($today > $graceLimit) {
                $subModel->update($sub['id'], [
                    'status'       => 'suspended',
                    'suspended_at' => date('Y-m-d H:i:s'),
                ]);

                $tenantModel->update($sub['tenant_id'], [
                    'is_suspended'     => 1,
                    'suspended_reason' => "Suspendido automáticamente por falta de pago (vencido el $endDate).",
                ]);

                CLI::write("  → Tenant {$sub['tenant_id']} SUSPENDIDO (venció el $endDate + $grace días)", 'red');
                $movedToSuspended++;
                continue;
            }

            // Caso B: Vencido pero aún en gracia → past_due
            if ($today > $endDate && $sub['status'] !== 'past_due') {
                $subModel->update($sub['id'], ['status' => 'past_due']);
                CLI::write("  → Tenant {$sub['tenant_id']} en MORA (venció el $endDate, gracia hasta $graceLimit)", 'yellow');
                $movedToPastDue++;
                continue;
            }

            $skipped++;
        }

        CLI::newLine();
        CLI::write("Resumen:", 'green');
        CLI::write("  Suspendidos:    $movedToSuspended");
        CLI::write("  En mora:        $movedToPastDue");
        CLI::write("  Sin cambios:    $skipped");
        CLI::write("  Total revisado: " . count($subs));
        CLI::newLine();
    }
}