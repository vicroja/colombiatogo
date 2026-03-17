<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class ChatPanel extends BaseController
{
    /**
     * Carga la vista principal del Chat (equivalente a tu detalle_chat_view)
     */
    public function index()
    {
        // Obtener el Tenant logueado en la sesión web
        $tenantId = session()->get('tenant_id') ?? 1;

        $data = [
            'title' => 'Panel de WhatsApp',
            'tenant_id' => $tenantId
        ];

        return view('chat/detalle_chat_view', $data);
    }

    /**
     * Endpoint AJAX: Enviar mensaje MANUAL desde el panel
     */
    public function sendManualMessage()
    {
        $tenantId = session()->get('tenant_id') ?? 1;
        $recipientPhone = $this->request->getPost('phone');
        $messageText = $this->request->getPost('message');

        // Asumimos que si envías desde la web principal, es la línea maestra (SaaS)
        $isSaas = true;

        $whatsappModel = model('App\Models\WhatsappModel');

        // 1. Enviar el mensaje por la API de Meta
        $apiResponse = $whatsappModel->sendTextApi($recipientPhone, $messageText, $isSaas, $tenantId);

        if ($apiResponse && isset($apiResponse['messages'][0]['id'])) {
            $wamid = $apiResponse['messages'][0]['id'];

            // 2. Guardar en BD asegurando que el estado cambia a 'HUMAN_MODE'
            // Esto pausa automáticamente a la IA
            $whatsappModel->saveMessage([
                'whatsapp_message_id' => $wamid,
                'direction'           => 'outgoing',
                'sender_phone'        => getenv('SAAS_WA_PHONE_ID'), // O el número del tenant
                'recipient_phone'     => $recipientPhone,
                'message_body'        => $messageText,
                'message_type'        => 'text',
                'tenant_id'           => $tenantId,
                'is_saas'             => $isSaas ? 1 : 0,
                'conversation_state'  => 'HUMAN_MODE', // <--- ¡LA MAGIA!
                'created_at'          => date('Y-m-d H:i:s')
            ]);

            return $this->response->setJSON(['success' => true, 'message' => 'Mensaje enviado y bot pausado.']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Error enviando el mensaje a Meta.']);
    }

    /**
     * Endpoint AJAX: Devolver el control a la IA (Tu antiguo return_conversation_to_ai_ajax)
     */
    public function returnToAi()
    {
        $tenantId = session()->get('tenant_id') ?? 1;
        $contactPhone = $this->request->getPost('contact_phone');

        // Para devolver a la IA, simplemente insertamos un registro de sistema o
        // actualizamos el último mensaje para que el state vuelva a 'AI_MODE'
        $db = \Config\Database::connect();

        $db->table('whatsapp_messages')
            ->where('tenant_id', $tenantId)
            ->groupStart()
            ->where('sender_phone', $contactPhone)
            ->orWhere('recipient_phone', $contactPhone)
            ->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->update(['conversation_state' => 'AI_MODE']);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'El control ha sido devuelto a Gemini. Responderá al próximo mensaje del huésped.'
        ]);
    }
}