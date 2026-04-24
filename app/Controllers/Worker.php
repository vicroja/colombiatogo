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
        $maxExecutionTime = 300;

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

    /**
     * 4. RECUPERADOR AUTOMÁTICO DE CONVERSACIONES (Follow-ups con IA)
     * Revisa chats inactivos (30m - 24h), evalúa el contexto con Gemini
     * y decide si cerrarlos o enviar un mensaje de seguimiento.
     * Comando: php public/index.php worker processFollowUps
     */
    public function processFollowUps()
    {
        echo "[" . date('Y-m-d H:i:s') . "] Iniciando Recuperador de Conversaciones...\n";

        $db = \Config\Database::connect();
        $whatsappModel = model('App\Models\WhatsappModel');
        $geminiModel = model('App\Models\GeminiModel');

        // 1. Buscar candidatos: Chats activos, IA encendida, último mensaje fue nuestro (outgoing)
        // y ocurrió entre hace 30 minutos y 24 horas.
        $sql = "
            SELECT 
                g.id as guest_id, g.phone, g.tenant_id, g.full_name,
                last_m.created_at as last_time,
                last_m.is_saas
            FROM guests g
            JOIN (
                SELECT tenant_id, 
                       IF(direction = 'incoming', sender_phone, recipient_phone) as phone_key, 
                       MAX(id) as max_id
                FROM whatsapp_messages 
                GROUP BY tenant_id, phone_key
            ) as lm ON lm.tenant_id = g.tenant_id AND lm.phone_key = g.phone
            JOIN whatsapp_messages last_m ON last_m.id = lm.max_id
            WHERE g.chat_state = 'ACTIVE'
              AND g.ai_active = 1
              AND last_m.direction = 'outgoing'
              AND last_m.created_at <= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
              AND last_m.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ";

        $candidatos = $db->query($sql)->getResult();

        if (empty($candidatos)) {
            echo "No hay conversaciones estancadas para evaluar.\n";
            return;
        }

        echo "Se encontraron " . count($candidatos) . " conversaciones en pausa.\n";

        foreach ($candidatos as $cliente) {
            echo " -> Evaluando cliente {$cliente->phone} (Tenant: {$cliente->tenant_id})... ";

            // 2. Extraer los últimos 5 mensajes para darle contexto a la IA
            $ultimosMensajes = $db->table('whatsapp_messages')
                ->where('tenant_id', $cliente->tenant_id)
                ->groupStart()
                ->where('sender_phone', $cliente->phone)
                ->orWhere('recipient_phone', $cliente->phone)
                ->groupEnd()
                ->orderBy('id', 'DESC')
                ->limit(5)
                ->get()
                ->getResult();

            $ultimosMensajes = array_reverse($ultimosMensajes); // Orden cronológico para Gemini

            $history = [];
            foreach ($ultimosMensajes as $msg) {
                $role = ($msg->direction === 'incoming') ? 'user' : 'model';
                $history[] = [
                    'role'  => $role,
                    'parts' => [['text' => $msg->message_body]]
                ];
            }

            // 3. Prompt estricto del sistema para esta tarea específica
            $systemInstruction = "
                Eres el gerente de ventas del hotel. Tu tarea es analizar los últimos mensajes de una conversación con el cliente '{$cliente->full_name}'.
                Determina la acción a tomar:
                - Si la conversación llegó a una conclusión natural (el cliente ya reservó, se despidió, dio las gracias finales o dijo expresamente que no le interesa), responde exactamente con: {\"action\": \"CLOSE\"}
                - Si la conversación quedó abierta o en pausa (ej. le diste precios, información, o respondiste una duda y el cliente no volvió a contestar), redacta un mensaje de seguimiento muy corto (1 o 2 oraciones máximo), empático y sin sonar desesperado para incentivar la reserva. Responde con: {\"action\": \"FOLLOWUP\", \"message\": \"Tu mensaje de seguimiento aquí\"}
                
                IMPORTANTE: Tu respuesta debe ser ÚNICAMENTE un JSON válido.
            ";

            // 4. Llamar a Gemini
            $respuestaIa = $geminiModel->generateChatResponse($history, $systemInstruction, 'gemini-2.5-flash');

            if (isset($respuestaIa['error'])) {
                echo "Error de IA: {$respuestaIa['error']}\n";
                continue;
            }

            // Limpiar y Parsear JSON
            $cleanJson = $geminiModel->cleanJsonResponse($respuestaIa['text']);
            $decision = json_decode($cleanJson, true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($decision['action'])) {
                echo "JSON inválido devuelto por IA.\n";
                continue;
            }

            // 5. Ejecutar la decisión de la IA
            if ($decision['action'] === 'CLOSE') {
                $db->table('guests')->where('id', $cliente->guest_id)->update(['chat_state' => 'CLOSED']);
                echo "CERRADA.\n";
            }
            elseif ($decision['action'] === 'FOLLOWUP' && !empty($decision['message'])) {
                $mensajeSeguimiento = $decision['message'];

                // Enviar vía WhatsApp API
                $apiResponse = $whatsappModel->sendTextApi(
                    $cliente->phone,
                    $mensajeSeguimiento,
                    (bool)$cliente->is_saas,
                    $cliente->tenant_id
                );

                if (isset($apiResponse['messages'][0]['id'])) {
                    $wamid = $apiResponse['messages'][0]['id'];

                    // Guardar en base de datos con la etiqueta especial para debug
                    $whatsappModel->saveMessage([
                        'whatsapp_message_id' => $wamid,
                        'direction'           => 'outgoing',
                        'recipient_phone'     => $cliente->phone,
                        'message_body'        => $mensajeSeguimiento,
                        'message_type'        => 'text',
                        'tenant_id'           => $cliente->tenant_id,
                        'is_saas'             => $cliente->is_saas,
                        'conversation_state'  => 'AUTO_FOLLOWUP', // <-- Etiqueta clave para debug
                        'created_at'          => date('Y-m-d H:i:s')
                    ]);

                    // Cambiar estado a WAITING_USER para que este script no lo vuelva a impactar
                    $db->table('guests')->where('id', $cliente->guest_id)->update(['chat_state' => 'WAITING_USER']);
                    echo "SEGUIMIENTO ENVIADO.\n";
                } else {
                    echo "FALLO ENVÍO API Meta.\n";
                }
            } else {
                echo "ACCIÓN DESCONOCIDA: {$decision['action']}\n";
            }
        }
        echo "[" . date('Y-m-d H:i:s') . "] Ciclo de recuperador finalizado.\n";
    }

}