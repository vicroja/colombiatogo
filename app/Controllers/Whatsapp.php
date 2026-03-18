<?php

namespace App\Controllers;

use App\Services\WhatsappWebhookService;
use CodeIgniter\HTTP\ResponseInterface;

class Whatsapp extends BaseController
{
    /**
     * Endpoint unificado para el Webhook de Meta.
     * Soporta GET (para verificación) y POST (para recepción de mensajes).
     */
    public function webhook()
    {
        $method = strtolower($this->request->getMethod());

        if ($method === 'get') {
            return $this->verifyWebhook();
        } elseif ($method === 'post') {
            return $this->receiveMessage();
        }

        return $this->response->setStatusCode(405)->setBody('Method Not Allowed');
    }

    /**
     * Verificación del Webhook requerida por Meta (Solo GET)
     */
    private function verifyWebhook()
    {
        // Token global de verificación definido en tu archivo .env
        $verifyToken = getenv('META_WEBHOOK_VERIFY_TOKEN') ?: 'SmartVet2026_SecureToken';
        $verifyToken='96155826';
        $mode      = $this->request->getGet('hub_mode');
        $token     = $this->request->getGet('hub_verify_token');
        $challenge = $this->request->getGet('hub_challenge');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            log_message('info', '[WhatsApp Controller] Webhook verificado correctamente por Meta.');
            // Meta exige que se retorne el challenge en texto plano
            return $this->response->setStatusCode(200)->setBody($challenge);
        }

        log_message('error', '[WhatsApp Controller] Fallo en la verificación del Webhook. Token inválido.');
        return $this->response->setStatusCode(403)->setBody('Forbidden');
    }

    /**
     * Recepción de eventos y mensajes de Meta (Solo POST)
     */
    private function receiveMessage()
    {
        $jsonPayload = $this->request->getBody();
        $payload = json_decode($jsonPayload, true);

        if (!$payload) {
            return $this->response->setStatusCode(400)->setBody('Bad Request');
        }

        // 1. Guardar el payload en la cola de entrada para procesarlo asíncronamente
        $db = \Config\Database::connect();
        $db->table('whatsapp_incoming_queue')->insert([
            'payload'    => $jsonPayload,
            'status'     => 'PENDING',
            'attempts'   => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // 2. Responder a Meta INMEDIATAMENTE con un HTTP 200 OK
        return $this->response->setStatusCode(200)->setBody('EVENT_RECEIVED');
    }

    /**
     * Busca en la tabla 'tenants' cuál inquilino tiene asignado este WABA Phone ID
     * dentro de su columna 'settings_json'.
     */
    private function identifyTenantByPhoneId(string $phoneId)
    {
        $db = \Config\Database::connect();

        // Buscamos en la columna JSON (MySQL 5.7+ / MariaDB)
        $query = $db->table('tenants')
            ->select('id, name')
            ->where("JSON_UNQUOTE(JSON_EXTRACT(settings_json, '$.whatsapp_phone_number_id')) = ", $phoneId)
            ->where('is_active', 1)
            ->get();

        return $query->getRow();
    }
}