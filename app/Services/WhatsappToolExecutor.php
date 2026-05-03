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
                case 'enviar_fotos_cabana':              // ← FALTABA ESTE
                    $output = $this->webhookService->toolEnviarFotosCabana($arguments);
                    break;
                case 'consultar_tours_disponibles':
                    $output = $this->webhookService->toolConsultarToursDisponibles($arguments);
                    break;
                case 'reservar_tour':
                    // FIX B2: Recalcular precio desde BD antes de persistir.
                    // Gemini puede haber calculado el precio en un turno anterior —
                    // recalcularlo aquí garantiza que el precio guardado sea siempre correcto.
                    $scheduleIdForPrice = (int)($arguments['schedule_id'] ?? 0);
                    $numAdultsForPrice  = (int)($arguments['num_adults']  ?? 1);
                    $numChildrenForPrice= (int)($arguments['num_children']?? 0);

                    if ($scheduleIdForPrice > 0) {
                        try {
                            $calculator   = new \App\Services\TourPriceCalculatorService();
                            $priceData    = $calculator->calculate($scheduleIdForPrice, $numAdultsForPrice, $numChildrenForPrice);
                            if ($priceData['price_source'] !== 'error') {
                                $arguments['precio_total_acordado'] = $priceData['total_price'];
                                log_message('info', "[ToolExecutor/reservar_tour] Precio recalculado desde BD: {$priceData['total_price']} (era: " . ($arguments['precio_total_acordado'] ?? 'null') . ")");
                            }
                        } catch (\Exception $e) {
                            log_message('warning', "[ToolExecutor/reservar_tour] No se pudo recalcular precio: " . $e->getMessage() . " — usando precio de Gemini.");
                        }
                    }

                    $output = $this->webhookService->toolReservarTour($arguments);
                    break;
                case 'enviar_fotos_tour':
                    $output = $this->webhookService->toolEnviarFotosTour($arguments);
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