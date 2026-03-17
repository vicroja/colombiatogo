<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Services\WhatsappRouterService;

class Worker extends Controller
{
    public function __construct()
    {
        if (!is_cli()) {
            exit("Acceso denegado. Este script es exclusivo de CLI.\n");
        }
    }

    /**
     * 1. EL WORKER PRINCIPAL (Daemon)
     * Se ejecuta continuamente verificando la cola.
     * Comando: php public/index.php worker start
     */
    public function start()
    {
        echo "[" . date('Y-m-d H:i:s') . "] Iniciando Worker Principal de WhatsApp...\n";

        $db = \Config\Database::connect();
        $router = new WhatsappRouterService();

        // Medida de seguridad: Reiniciar el worker cada 1 hora para limpiar memoria (Memory Leaks)
        // El watchdog lo volverá a levantar inmediatamente en el siguiente minuto.
        $startTime = time();
        $maxExecutionTime = 3600;

        while (true) {
            // Verificar si debe reiniciarse por tiempo
            if ((time() - $startTime) > $maxExecutionTime) {
                echo "Reiniciando worker por mantenimiento de memoria (1 hora alcanzada).\n";
                exit(0);
            }

            // 1. Iniciar transacción y bloquear fila (Evita colisiones si hay múltiples workers)
            $db->transBegin();
            $job = $db->query("SELECT id, payload FROM whatsapp_incoming_queue WHERE status = 'PENDING' ORDER BY id ASC LIMIT 1 FOR UPDATE")->getRow();

            if ($job) {
                // Marcar en proceso y liberar la tabla para otros
                $db->table('whatsapp_incoming_queue')->where('id', $job->id)->update(['status' => 'PROCESSING']);
                $db->transCommit();

                echo "[" . date('H:i:s') . "] Procesando Job ID: {$job->id}...\n";

                try {
                    // Enviar al router y a la IA
                    $router->routeMessage($job->payload);

                    // Marcar completado
                    $db->table('whatsapp_incoming_queue')->where('id', $job->id)->update([
                        'status' => 'COMPLETED',
                        'processed_at' => date('Y-m-d H:i:s')
                    ]);
                    echo " -> Éxito.\n";

                } catch (\Exception $e) {
                    // Marcar como fallido
                    $db->table('whatsapp_incoming_queue')->where('id', $job->id)
                        ->set('attempts', 'attempts + 1', false)
                        ->update([
                            'status' => 'FAILED',
                            'error_details' => $e->getMessage(),
                            'processed_at' => date('Y-m-d H:i:s')
                        ]);
                    echo " -> Error: " . $e->getMessage() . "\n";
                }
            } else {
                $db->transRollback();

                // Reconexión preventiva por si el servidor MySQL corta la conexión inactiva ("MySQL has gone away")
                $db->reconnect();

                // Dormir el ciclo 2 segundos para no saturar la CPU del servidor
                sleep(2);
            }
        }
    }

    /**
     * 2. EL WATCHDOG (Perro Guardián)
     * Revisa si el worker está corriendo en los procesos del sistema operativo.
     * Comando: php public/index.php worker watchdog
     */
    public function watchdog()
    {
        // Comando exacto con el que ejecutamos el worker
        $command = "php public/index.php worker start";

        // Buscar en los procesos de Linux (pgrep) si existe este comando corriendo
        $pids = [];
        exec("pgrep -f '$command'", $pids);

        // pgrep se encontrará a sí mismo a veces, validamos si hay procesos reales
        if (empty($pids)) {
            echo "[" . date('Y-m-d H:i:s') . "] ALERTA: El Worker estaba caído. Reiniciando en segundo plano...\n";

            // Iniciar el worker desvinculado de la terminal (nohup y &)
            $path = FCPATH . 'index.php'; // Ruta absoluta a public/index.php
            exec("nohup php $path worker start > /dev/null 2>&1 &");

            log_message('critical', '[Watchdog] Worker reiniciado automáticamente.');
        } else {
            echo "[" . date('Y-m-d H:i:s') . "] El Worker está operando correctamente. (PID: " . implode(', ', $pids) . ")\n";
        }
    }

    /**
     * 3. EL WORKER DE SALIDA (Outbound Queue Processor)
     * Procesa los mensajes programados que ya están listos para enviarse.
     * Comando: php public/index.php worker processOutgoingQueue
     */
    public function processOutgoingQueue()
    {
        echo "[" . date('Y-m-d H:i:s') . "] Iniciando procesamiento de Cola de Salida...\n";

        $db = \Config\Database::connect();
        $whatsappModel = model('App\Models\WhatsappModel');
        $queueModel = model('App\Models\WhatsappMessageQueueModel');

        // 1. Buscar mensajes pendientes cuya fecha programada (UTC) ya se cumplió
        $currentUtcTime = gmdate('Y-m-d H:i:s');

        $builder = $db->table('whatsapp_message_queue q');
        $builder->select('q.*, t.meta_template_name, t.meta_template_language_code, t.whatsapp_message_format, t.message_body_text, t.meta_template_components_config_json');
        $builder->join('autowhatsapptemplate t', 't.id = q.autowhatsapptemplate_id', 'left');
        $builder->where('q.processing_status', 'PENDING');
        $builder->where('q.scheduled_send_datetime_utc <=', $currentUtcTime);
        $builder->where('q.send_attempts <', 3); // Límite de reintentos
        $builder->orderBy('q.scheduled_send_datetime_utc', 'ASC');
        $builder->limit(50); // Procesar en lotes de 50 para no saturar la memoria

        $mensajes = $builder->get()->getResult();

        if (empty($mensajes)) {
            echo "No hay mensajes programados pendientes en este momento.\n";
            return;
        }

        echo "Se encontraron " . count($mensajes) . " mensajes para procesar.\n";

        foreach ($mensajes as $msg) {
            echo " -> Procesando Cola ID {$msg->id} para {$msg->recipient_phone}... ";

            // Reutilizamos el método del modelo
            $resultado = $queueModel->processSingleMessage($msg->id);

            if ($resultado) {
                echo "ENVIADO EXITOSAMENTE.\n";
            } else {
                echo "FALLO EN EL ENVÍO (ver logs).\n";
            }
        }

        echo "[" . date('Y-m-d H:i:s') . "] Lote procesado.\n";
    }

}