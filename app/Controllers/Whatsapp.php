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

        $verifyToken = env('META_WEBHOOK_VERIFY_TOKEN', '96155826');

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

    // Va antes del último } de la clase Whatsapp
// ─────────────────────────────────────────────────────────────────────────
// Endpoint llamado por el wizard de onboarding (y por settings) tras
// completar el flujo Meta Embedded Signup.
// Recibe el code OAuth + waba_id + phone_number_id y los persiste
// en tenants.settings_json del tenant activo.
// ─────────────────────────────────────────────────────────────────────────
    public function saveConfig(): ResponseInterface
    {
        // Solo acepta POST autenticado
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)
                ->setJSON(['success' => false, 'message' => 'Method Not Allowed']);
        }

        $tenantId = session('active_tenant_id');

        if (!$tenantId) {
            log_message('error', '[WA/saveConfig] Intento sin sesión activa.');
            return $this->response->setStatusCode(401)
                ->setJSON(['success' => false, 'message' => 'No autorizado.']);
        }

        $codeOrToken  = trim($this->request->getPost('access_token')    ?? '');
        $wabaId       = trim($this->request->getPost('waba_id')         ?? '');
        $phoneNumberId= trim($this->request->getPost('phone_number_id') ?? '');

        if (empty($codeOrToken)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se recibió código de autorización.'
            ]);
        }

        log_message('info', "[WA/saveConfig] Tenant {$tenantId} — waba_id: {$wabaId} | phone_number_id: {$phoneNumberId}");

        // ── Intercambiar el code por un access_token permanente ───────────────
        // Si Meta envió un 'code' OAuth (response_type: 'code'), hay que
        // canjearlo por el token real. Si ya es un token directo, lo usamos tal cual.
        $accessToken = $codeOrToken;

        if (strlen($codeOrToken) < 100) {
            // Parece un code corto OAuth — intercambiarlo
            $exchanged = $this->exchangeCodeForToken($codeOrToken);

            if (!$exchanged['success']) {
                log_message('error', "[WA/saveConfig] Error intercambiando code: " . $exchanged['message']);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al obtener el token de Meta: ' . $exchanged['message']
                ]);
            }

            $accessToken = $exchanged['token'];
        }

        // ── Persistir en settings_json del tenant ─────────────────────────────
        $tenantModel = new \App\Models\TenantModel();
        $tenant      = $tenantModel->find($tenantId);

        if (!$tenant) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant no encontrado.'
            ]);
        }

        // Merge con settings_json existente para no pisar otros valores
        $settings = json_decode($tenant['settings_json'] ?? '{}', true) ?? [];

        $settings['whatsapp_access_token']    = $accessToken;
        $settings['whatsapp_waba_id']         = $wabaId;
        $settings['whatsapp_phone_number_id'] = $phoneNumberId;
        $settings['whatsapp_connected_at']    = date('Y-m-d H:i:s');

        $updated = $tenantModel->update($tenantId, [
            'settings_json' => json_encode($settings)
        ]);

        if (!$updated) {
            log_message('error', "[WA/saveConfig] Error actualizando settings_json para tenant {$tenantId}");
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar la configuración.'
            ]);
        }

        log_message('info', "[WA/saveConfig] WhatsApp configurado exitosamente para tenant {$tenantId}");

        return $this->response->setJSON([
            'success' => true,
            'message' => '¡WhatsApp conectado correctamente!'
        ]);
    }

    /**
     * Intercambia un code OAuth de Meta por un access_token de sistema.
     * Solo se llama cuando Meta responde con response_type: 'code'.
     *
     * @param  string $code
     * @return array  ['success' => bool, 'token' => string, 'message' => string]
     */
    private function exchangeCodeForToken(string $code): array
    {
        $appId     = env('META_APP_ID',     '871557255662274');
        $appSecret = env('META_APP_SECRET', '');
        $redirectUri = ''; // Meta no requiere redirect_uri en este flujo

        if (empty($appSecret)) {
            log_message('error', '[WA/exchangeCode] META_APP_SECRET no configurado en .env');
            return ['success' => false, 'token' => '', 'message' => 'Configuración de Meta incompleta.'];
        }

        $url = 'https://graph.facebook.com/v19.0/oauth/access_token?' . http_build_query([
                'client_id'     => $appId,
                'client_secret' => $appSecret,
                'code'          => $code,
            ]);

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error    = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return ['success' => false, 'token' => '', 'message' => "cURL error: {$error}"];
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200 || empty($data['access_token'])) {
            $errMsg = $data['error']['message'] ?? "HTTP {$httpCode}";
            return ['success' => false, 'token' => '', 'message' => $errMsg];
        }

        return ['success' => true, 'token' => $data['access_token'], 'message' => ''];
    }
}