<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\TenantModel;
use App\Models\TenantSubscriptionModel;

class CheckSubscriptions extends BaseCommand
{
    // El nombre con el que llamaremos al comando en la terminal
    protected $group       = 'SaaS MAVILUSA';
    protected $name        = 'saas:check-subscriptions';
    protected $description = 'Verifica suscripciones vencidas y suspende los hoteles morosos automáticamente.';

    public function run(array $params)
    {
        CLI::write('Iniciando el Ejecutor MAVILUSA...', 'cyan');

        $tenantModel = new TenantModel();
        $subModel = new TenantSubscriptionModel();

        $today = date('Y-m-d');

        // 1. Buscamos todas las suscripciones cuya fecha de corte ya pasó y que NO estén suspendidas aún
        $expiredSubs = $subModel->select('tenant_subscriptions.*, tenants.name as tenant_name')
            ->join('tenants', 'tenants.id = tenant_subscriptions.tenant_id')
            ->where('current_period_end <', $today)
            ->where('tenants.is_suspended', 0)
            ->findAll();



        if (empty($expiredSubs)) {
            CLI::write('Todos los clientes están al día. Ningún hotel suspendido.', 'green');
            return;
        }

        // 2. Ejecutamos la suspensión
        $count = 0;
        foreach ($expiredSubs as $sub) {
            CLI::write("Suspendiendo hotel: {$sub['tenant_name']} (Venció el {$sub['current_period_end']})", 'yellow');

            // Suspendemos la cuenta a nivel base de datos
            $tenantModel->update($sub['tenant_id'], [
                'is_suspended'     => 1,
                'suspended_reason' => 'Falta de pago de mensualidad SaaS.'
            ]);

            // Cambiamos el estado de la suscripción
            $subModel->update($sub['id'], ['status' => 'past_due']);

            $count++;
        }

        CLI::write("Proceso finalizado. $count hoteles fueron suspendidos.", 'green');
    }
}