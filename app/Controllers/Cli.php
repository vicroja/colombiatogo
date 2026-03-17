<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Services\WhatsappRouterService;

class Cli extends Controller
{
    public function __construct()
    {
        // Seguridad: Evitar que alguien ejecute este controlador desde el navegador web
        if (!is_cli()) {
            exit("Acceso denegado. Este script solo se puede ejecutar desde la línea de comandos (CLI).\n");
        }
    }

    /**
     * Comando a ejecutar desde el Cronjob del servidor (Ej: cada 1 minuto)
     * php spark cli processIncomingQueue
     * php public/index.php cli processIncomingQueue
     */
    public function processIncomingQueue()
    {
        echo "Buscando mensajes pendientes en whatsapp_incoming_queue...\n";

        $db = \Config\Database::connect();

        // 1. Iniciar transacción para bloquear la fila
        $db->transBegin();

        // 2. Buscar el trabajo más antiguo (Exclusivo para MySQL/MariaDB usando FOR UPDATE)
        $query = $db->query("SELECT id, payload FROM whatsapp_incoming_queue WHERE status = 'PENDING' ORDER BY id ASC LIMIT 1 FOR UPDATE");
        $job = $query->getRow();

        if (!$job) {
            $db->transRollback();
            echo "No hay mensajes en estado PENDING en la cola.\n";
            return;
        }

        // 3. Marcar como procesando para liberar el bloqueo a otros workers
        $db->table('whatsapp_incoming_queue')
            ->where('id', $job->id)
            ->update(['status' => 'PROCESSING']);

        $db->transCommit();

        echo "¡Job encontrado! ID: {$job->id}\nEnrutando mensaje...\n";

        // 4. Pasar el payload al enrutador
        try {
            $router = new WhatsappRouterService();
            $router->routeMessage($job->payload);

            // 5. Marcar como completado
            $db->table('whatsapp_incoming_queue')
                ->where('id', $job->id)
                ->update([
                    'status' => 'COMPLETED',
                    'processed_at' => date('Y-m-d H:i:s')
                ]);

            echo "Job {$job->id} procesado y completado exitosamente.\n";

        } catch (\Exception $e) {
            // 6. Marcar como fallido si ocurre un error
            $db->table('whatsapp_incoming_queue')
                ->where('id', $job->id)
                ->set('attempts', 'attempts + 1', false) // Incrementar contador
                ->update([
                    'status' => 'FAILED',
                    'error_details' => $e->getMessage(),
                    'processed_at' => date('Y-m-d H:i:s')
                ]);

            echo "Error al procesar el Job {$job->id}: " . $e->getMessage() . "\n";
            log_message('error', "[CLI Worker] Falló Job ID {$job->id}: " . $e->getMessage());
        }
    }
}