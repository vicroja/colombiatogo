<?php

namespace App\Services;

class WhatsappRouterService
{
    /**
     * Enruta el mensaje entrante basado en el ID del teléfono receptor.
     * @param string $jsonPayload El JSON crudo de la cola.
     * @throws \Exception
     */
    public function routeMessage(string $jsonPayload)
    {
        $payload = json_decode($jsonPayload, true);

        // 1. Extraer el ID del teléfono receptor (Metadatos de Meta)
        $targetPhoneId = $payload['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'] ?? null;

        if (!$targetPhoneId) {
            log_message('warning', '[Router] No se encontró phone_number_id en el payload. Ignorando.');
            return;
        }

        // 2. Identificar al Tenant (Inquilino)
        $tenantData = $this->identifyTenantByPhoneId($targetPhoneId);
        $isSaas = ($targetPhoneId === getenv('SAAS_WA_PHONE_ID'));

        if ($isSaas && !$tenantData) {
            // Caso: Es la línea maestra de SmartVet, asignamos un tenant temporal/maestro (ej. 99)
            log_message('info', "[Router] Mensaje de Línea SaaS Global. ID: {$targetPhoneId}");
            $webhookService = new WhatsappWebhookService();
            $webhookService->processNotification($payload, $jsonPayload, true, 99);
            return;
        }

        if ($tenantData) {
            // Caso: Es un Tenant específico
            $tenantId = (int) $tenantData->id;
            log_message('info', "[Router] Mensaje enrutado al Tenant ID: {$tenantId}");

            $webhookService = new WhatsappWebhookService();
            $webhookService->processNotification($payload, $jsonPayload, $isSaas, $tenantId);
        } else {
            // Caso: ID desconocido
            log_message('error', "[Router] ID DESCONOCIDO ({$targetPhoneId}). Ningún Tenant lo tiene configurado.");
            throw new \Exception("Phone ID no registrado en el sistema: {$targetPhoneId}");
        }
    }

    private function identifyTenantByPhoneId(string $phoneId)
    {
        $db = \Config\Database::connect();

        // Busca en la configuración JSON del Tenant
        return $db->table('tenants')
            ->select('id, name')
            ->where("JSON_UNQUOTE(JSON_EXTRACT(settings_json, '$.whatsapp_phone_number_id')) = ", $phoneId)
            ->where('is_active', 1)
            ->get()
            ->getRow();
    }
}