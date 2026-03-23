<?php

namespace App\Services;

class WhatsappToolExecutor
{
    protected $webhookService;

    public function initialize(WhatsappWebhookService $webhookServiceInstance)
    {
        $this->webhookService = $webhookServiceInstance;
    }

    public function execute(string $toolCallId, string $functionName, array $arguments): array
    {
        if (empty($this->webhookService)) {
            return [
                'tool_call_id' => $toolCallId,
                'output'       => json_encode(['error' => 'Tool Executor no fue inicializado.'])
            ];
        }

        log_message('info', "[ToolExecutor] Ejecutando: '{$functionName}' con args: " . json_encode($arguments));

        try {
            switch ($functionName) {
                case 'consultar_disponibilidad':
                    $output = $this->webhookService->toolConsultarDisponibilidad($arguments);
                    break;
                case 'crear_reserva':
                    $output = $this->webhookService->toolCrearReserva($arguments);
                    break;
                case 'notificar_administrador':
                    $output = $this->webhookService->toolNotificarAdministrador($arguments);
                    break;
                default:
                    $output = json_encode(['error' => "Herramienta '{$functionName}' desconocida."]);
                    break;
            }
        } catch (\Exception $e) {
            log_message('error', "[ToolExecutor] Excepción en '{$functionName}': " . $e->getMessage());
            $output = json_encode(['error' => 'Excepción en la base de datos: ' . $e->getMessage()]);
        }
        log_message('info', "[ToolExecutor] Resultado:" . json_encode($output));

        return [
            'tool_call_id' => $toolCallId,
            'output'       => is_string($output) ? $output : json_encode($output)
        ];
    }
}