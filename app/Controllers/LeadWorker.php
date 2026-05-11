<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Services\LeadAssignmentService;
use App\Services\LeadNotificationService;

/**
 * LeadWorker
 * Worker CLI para tareas automáticas del módulo de leads.
 * Sigue el mismo patrón que tu Worker.php existente.
 *
 * Crontab sugerido:
 *   *5    * * * * cd /var/www && php public/index.php leadworker processReminders >> /var/log/leadworker.log 2>&1
 *   0    * * * *  cd /var/www && php public/index.php leadworker reassignInactive >> /var/log/leadworker.log 2>&1
 *   0   2 * * *   cd /var/www && php public/index.php leadworker detectColdLeads >> /var/log/leadworker.log 2>&1
 */
class LeadWorker extends Controller
{
    public function __construct()
    {
        if (!is_cli()) {
            exit("Acceso denegado. Este script es exclusivo de CLI.\n");
        }
    }

    /**
     * Recordatorios de próxima acción vencidos
     */
    public function processReminders()
    {
        echo "[".date('Y-m-d H:i:s')."] Procesando recordatorios...\n";
        $svc = new LeadNotificationService();
        $sent = $svc->processDueReminders();
        echo "Recordatorios enviados: {$sent}\n";
    }

    /**
     * Reasigna leads que llevan X horas sin actividad
     */
    public function reassignInactive()
    {
        echo "[".date('Y-m-d H:i:s')."] Reasignando leads inactivos...\n";
        $svc = new LeadAssignmentService();
        $n = $svc->reassignInactiveLeads();
        echo "Leads reasignados: {$n}\n";
    }

    /**
     * Marca leads sin actividad como fríos
     */
    public function detectColdLeads()
    {
        echo "[".date('Y-m-d H:i:s')."] Detectando leads fríos...\n";
        $svc = new LeadAssignmentService();
        $n = $svc->detectColdLeads();
        echo "Leads marcados como fríos: {$n}\n";
    }
}
