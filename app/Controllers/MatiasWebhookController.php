<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class MatiasWebhookController extends ResourceController
{
    use ResponseTrait;

    public function handle()
    {
        $request = \Config\Services::request();

        // 1. Obtener la firma y el payload crudo
        $signature = $request->getHeaderLine('X-Webhook-Signature');
        $rawPayload = $request->getBody();

        // Log crucial para saber que el webhook llegó
        log_message('info', '[MatiasWebhook] Petición recibida. Firma: ' . $signature);

        // 2. Verificar la Firma HMAC-SHA256 (Seguridad Crítica)
        $secret = getenv('MATIAS_WEBHOOK_SECRET'); // Debe estar en tu archivo .env

        if (empty($secret)) {
            log_message('critical', '[MatiasWebhook] Error: MATIAS_WEBHOOK_SECRET no configurado en el .env');
            return $this->failServerError('Webhook secret not configured');
        }

        if (!$this->verifySignature($rawPayload, $signature, $secret)) {
            log_message('error', '[MatiasWebhook] Firma inválida rechazada. Payload: ' . $rawPayload);
            return $this->failUnauthorized('Invalid signature');
        }

        // 3. Procesar el Payload
        $payload = json_decode($rawPayload, true);
        $eventId = $payload['id'] ?? 'unknown_id';
        $eventType = $payload['event'] ?? '';
        $data = $payload['data'] ?? [];

        log_message('info', "[MatiasWebhook] Procesando evento {$eventType} (ID: {$eventId})");

        // 4. Actualizar la base de datos según el evento
        $db = \Config\Database::connect();
        $invoiceTable = $db->table('tenant_invoices');

        // Extraemos el CUFE/CUDE que la API llama 'track_id'
        $trackId = $data['track_id'] ?? null;

        if (!$trackId) {
            log_message('error', "[MatiasWebhook] El payload no contiene track_id. Evento ignorado.");
            return $this->respond(['status' => 'ignored', 'reason' => 'missing track_id'], 200);
        }

        // Buscamos la factura por su UUID (CUFE)
        $invoice = $invoiceTable->where('uuid', $trackId)->get()->getRowArray();

        if (!$invoice) {
            log_message('warning', "[MatiasWebhook] Factura no encontrada para el UUID: {$trackId}");
            return $this->respond(['status' => 'ignored', 'reason' => 'invoice not found'], 200);
        }

        // Máquina de estados según el evento de Matias
        switch ($eventType) {
            case 'document.accepted': // DIAN Validó exitosamente
                $invoiceTable->where('id', $invoice['id'])->update([
                    'status' => 'validated_dian',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                log_message('info', "[MatiasWebhook] Factura ID {$invoice['id']} validada por la DIAN.");
                break;

            case 'document.rejected': // DIAN Rechazó por errores (NIT inválido, fechas, etc)
                $invoiceTable->where('id', $invoice['id'])->update([
                    'status' => 'rejected_dian',
                    'updated_at' => date('Y-m-d H:i:s')
                    // Aquí podrías guardar el motivo del rechazo si viene en el payload
                ]);
                log_message('error', "[MatiasWebhook] Factura ID {$invoice['id']} RECHAZADA por la DIAN.");
                break;

            // Puedes agregar más casos en el futuro: 'email.delivered', 'document.voided', etc.
            default:
                log_message('info', "[MatiasWebhook] Evento no manejado: {$eventType}");
                break;
        }

        // 5. Responder rápido a la API con 200 OK (Importante para que no reintente)
        return $this->respond(['success' => true, 'message' => 'Webhook procesado'], 200);
    }

    /**
     * Función privada para validar el HMAC
     */
    private function verifySignature($rawPayload, $signature, $secret)
    {
        // Se usa el rawPayload (texto crudo) exacto que entra para no alterar espacios
        $hash = hash_hmac('sha256', $rawPayload, $secret);
        $expectedSignature = "sha256={$hash}";

        return hash_equals($expectedSignature, $signature);
    }
}