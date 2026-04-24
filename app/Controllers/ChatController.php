<?php

namespace App\Controllers;

use App\Models\WhatsappModel;
use App\Models\TenantModel;
use App\Models\GuestModel;
use CodeIgniter\HTTP\ResponseInterface;

class ChatController extends BaseController
{
    protected $whatsappModel;
    protected $tenantId;
    protected $db;

    public function __construct()
    {
        $this->whatsappModel = new WhatsappModel();
        $this->db = \Config\Database::connect();

        // Prioridad al tenant activo en la sesión
        $this->tenantId = session('active_tenant_id') ?? session('tenant_id');
    }

    /**
     * Carga la interfaz de chat.
     * Si se proporciona $contactPhone, carga el historial de esa conversación.
     */
    public function index($contactPhone = null)
    {
        if (!$this->tenantId) {
            log_message('error', '[ChatController] Intento de acceso sin tenant_id en sesión.');
            return redirect()->to('/login')->with('error', 'Sesión no válida.');
        }

        // 1. Obtener lista de contactos para el Sidebar
        // Traemos el último mensaje y el estado actual de la IA/Chat desde la tabla 'guests'
        $sidebarQuery = "
            SELECT 
                g.full_name as name, 
                g.phone, 
                g.ai_active,
                g.chat_state,
                m.message_body as last_msg, 
                m.created_at as last_time,
                m.direction as last_direction
            FROM guests g
            JOIN (
                SELECT MAX(id) as max_id, 
                       IF(direction = 'incoming', sender_phone, recipient_phone) as phone_key
                FROM whatsapp_messages 
                WHERE tenant_id = ?
                GROUP BY phone_key
            ) last_m ON (g.phone = last_m.phone_key)
            JOIN whatsapp_messages m ON m.id = last_m.max_id
            WHERE g.tenant_id = ?
            ORDER BY m.created_at DESC
        ";

        $data['sidebar_contacts'] = $this->db->query($sidebarQuery, [$this->tenantId, $this->tenantId])->getResultArray();
        $data['contact_phone'] = $contactPhone;
        $data['messages'] = [];
        $data['is_24h_window_open'] = false;
        $data['openai_thread'] = '';
        $data['is_saas_conversation'] = 0;

        // 2. Cargar historial si hay un contacto seleccionado
        if ($contactPhone) {
            $data['messages'] = $this->whatsappModel
                ->where('tenant_id', $this->tenantId)
                ->groupStart()
                ->where('sender_phone', $contactPhone)
                ->orWhere('recipient_phone', $contactPhone)
                ->groupEnd()
                ->orderBy('created_at', 'ASC')
                ->findAll();

            // Determinar si es una conversación vía línea SaaS o Local
            if (!empty($data['messages'])) {
                $data['is_saas_conversation'] = $data['messages'][0]->is_saas;
                $data['openai_thread'] = $data['messages'][0]->openai_thread;
            }

            // Lógica de ventana de 24 horas (Requisito Meta)
            $lastIncoming = $this->whatsappModel
                ->where('tenant_id', $this->tenantId)
                ->where('sender_phone', $contactPhone)
                ->where('direction', 'incoming')
                ->orderBy('created_at', 'DESC')
                ->first();

            if ($lastIncoming) {
                $lastTime = strtotime($lastIncoming->created_at);
                $data['is_24h_window_open'] = (time() - $lastTime) < 86400;
                $data['last_incoming_timestamp'] = $lastIncoming->created_at;
            }
        }

        // Datos del Tenant para configuración de la vista (moneda, nombre, etc.)
        $tenantModel = new TenantModel();
        $data['tenant'] = $tenantModel->find($this->tenantId);

        log_message('info', "[ChatController] Dashboard cargado para Tenant ID: {$this->tenantId} | Contacto: " . ($contactPhone ?? 'Ninguno'));

        return view('whatsapp/detalle_chat_view', $data);
    }

    /**
     * AJAX: Devuelve el control de la conversación a la IA.
     */
    public function returnToAiAjax(): ResponseInterface
    {
        $phone = $this->request->getPost('contact_phone');

        if (!$phone) {
            return $this->response->setJSON(['success' => false, 'message' => 'Número de teléfono no proporcionado.']);
        }

        $updated = $this->db->table('guests')
            ->where('phone', $phone)
            ->where('tenant_id', $this->tenantId)
            ->update([
                'ai_active'  => 1,
                'chat_state' => 'ACTIVE' // Aseguramos que el chat esté activo al retomar IA
            ]);

        if ($updated) {
            log_message('info', "[ChatController] Handoff: Control devuelto a IA para {$phone} (Tenant: {$this->tenantId})");
            return $this->response->setJSON(['success' => true, 'message' => 'La IA ha retomado el control de la conversación.']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'No se pudo actualizar el estado del huésped.']);
    }

    /**
     * AJAX: Finaliza la conversación (Resolver/Cerrar).
     * Reactiva la IA para futuras interacciones.
     */
    public function closeChatAjax(): ResponseInterface
    {
        $phone = $this->request->getPost('contact_phone');

        if (!$phone) {
            return $this->response->setJSON(['success' => false, 'message' => 'Número de teléfono no proporcionado.']);
        }

        $updated = $this->db->table('guests')
            ->where('phone', $phone)
            ->where('tenant_id', $this->tenantId)
            ->update([
                'chat_state' => 'CLOSED',
                'ai_active'  => 1 // Siempre reactivamos IA al cerrar para el próximo contacto
            ]);

        if ($updated) {
            log_message('info', "[ChatController] Conversación cerrada exitosamente para {$phone}.");
            return $this->response->setJSON(['success' => true, 'message' => 'Conversación marcada como RESUELTA.']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Error al cerrar la conversación.']);
    }

    /**
     * AJAX: Polling para obtener mensajes nuevos en tiempo real.
     */
    public function getNewMessagesAjax(): ResponseInterface
    {
        $phone = $this->request->getPost('contact_phone');
        $lastId = $this->request->getPost('last_message_id');

        if (!$phone || !$lastId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Parámetros insuficientes para polling.']);
        }

        $messages = $this->whatsappModel
            ->where('tenant_id', $this->tenantId)
            ->where('id >', $lastId)
            ->groupStart()
            ->where('sender_phone', $phone)
            ->orWhere('recipient_phone', $phone)
            ->groupEnd()
            ->orderBy('created_at', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'success'  => true,
            'messages' => $messages,
            'count'    => count($messages)
        ]);
    }

    /**
     * AJAX: Buscador dinámico de contactos para el sidebar.
     */
    public function ajaxSearchSidebarContacts(): ResponseInterface
    {
        $search = $this->request->getPost('search');

        // Reutilizamos la lógica del Sidebar pero con filtro LIKE
        $sql = "
            SELECT 
                g.full_name as name, g.phone, g.ai_active, g.chat_state,
                m.message_body as last_msg, m.created_at as last_time, m.direction as last_direction
            FROM guests g
            JOIN (
                SELECT MAX(id) as max_id, IF(direction = 'incoming', sender_phone, recipient_phone) as phone_key
                FROM whatsapp_messages 
                WHERE tenant_id = ?
                GROUP BY phone_key
            ) last_m ON (g.phone = last_m.phone_key)
            JOIN whatsapp_messages m ON m.id = last_m.max_id
            WHERE g.tenant_id = ?
            AND (g.full_name LIKE ? OR g.phone LIKE ?)
            ORDER BY m.created_at DESC
        ";

        $results = $this->db->query($sql, [
            $this->tenantId,
            $this->tenantId,
            "%$search%",
            "%$search%"
        ])->getResultArray();

        // Renderizamos solo el fragmento del sidebar para la respuesta AJAX
        $html = view('whatsapp/partials/_sidebar_items', ['sidebar_contacts' => $results, 'contact_phone' => null]);

        return $this->response->setJSON(['success' => true, 'html' => $html]);
    }

    /**
     * AJAX: Enviar un mensaje manual desde la interfaz de Chat.
     * Al guardar, el modelo se encargará de hacer el Handoff (apagar la IA).
     */
    public function sendCustomMessage(): ResponseInterface
    {
        $phone = $this->request->getPost('destination_phone_modal');
        $text  = $this->request->getPost('text_message');
        $type  = $this->request->getPost('message_type'); // 'text' o 'template'

        if (!$phone || empty($text)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Faltan datos obligatorios para el envío.']);
        }

        log_message('info', "[ChatController] Intentando enviar mensaje manual a {$phone} (Tenant: {$this->tenantId})");

        // Determinar si este tenant usa credenciales SaaS globales o propias
        $tenantModel = new TenantModel();
        $tenant = $tenantModel->find($this->tenantId);
        $settings = json_decode($tenant['settings_json'] ?? '{}', true);

        // Si no tiene token propio, asumimos que va por la línea global SaaS
        $isSaas = empty($settings['whatsapp_token']);

        if ($type === 'text') {
            // Llamada a la API de Meta a través del modelo
            $apiResponse = $this->whatsappModel->sendTextApi($phone, $text, $isSaas, $this->tenantId);

            if (isset($apiResponse['messages'][0]['id'])) {
                $wamid = $apiResponse['messages'][0]['id'];

                // Guardar en la base de datos local.
                // IMPORTANTE: Al ser direction = outgoing, el WhatsappModel detectará
                // que es un mensaje manual y ejecutará el UPDATE a ai_active = 0 automáticamente.
                $this->whatsappModel->saveMessage([
                    'whatsapp_message_id' => $wamid,
                    'direction'           => 'outgoing',
                    'recipient_phone'     => $phone,
                    'message_body'        => $text,
                    'message_type'        => 'text',
                    'tenant_id'           => $this->tenantId,
                    'is_saas'             => $isSaas ? 1 : 0,
                    'created_at'          => date('Y-m-d H:i:s')
                ]);

                return $this->response->setJSON(['success' => true, 'message' => 'Mensaje enviado correctamente.']);
            }

            log_message('error', "[ChatController] Fallo Meta API al enviar a {$phone}: " . json_encode($apiResponse));
            return $this->response->setJSON(['success' => false, 'message' => 'Error de la API de Meta al enviar el mensaje.']);
        }

        // Si se implementa envío de plantillas de reactivación, la lógica iría aquí
        if ($type === 'template') {
            // Ejemplo rápido para la plantilla de reactivación
            $templateName = $this->request->getPost('manual_template_name');
            $components = [
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $this->request->getPost('template_variables[body][1]')],
                        ['type' => 'text', 'text' => $this->request->getPost('template_variables[body][2]')],
                        ['type' => 'text', 'text' => $this->request->getPost('template_variables[body][3]')]
                    ]
                ]
            ];

            $apiResponse = $this->whatsappModel->sendTemplateApi($phone, $templateName, 'es', $components, $isSaas, $this->tenantId);

            if (isset($apiResponse['messages'][0]['id'])) {
                // Al enviar plantilla también pausamos IA
                $this->whatsappModel->saveMessage([
                    'whatsapp_message_id' => $apiResponse['messages'][0]['id'],
                    'direction'           => 'outgoing',
                    'recipient_phone'     => $phone,
                    'message_body'        => "[Plantilla de Reactivación Enviada]",
                    'message_type'        => 'template',
                    'tenant_id'           => $this->tenantId,
                    'is_saas'             => $isSaas ? 1 : 0,
                    'created_at'          => date('Y-m-d H:i:s')
                ]);
                return $this->response->setJSON(['success' => true]);
            }
            return $this->response->setJSON(['success' => false, 'message' => 'Fallo al enviar plantilla.']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Tipo de mensaje no soportado.']);
    }
}