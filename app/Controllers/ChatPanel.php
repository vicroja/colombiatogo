<?php

namespace App\Controllers;

class ChatPanel extends BaseController
{
    /**
     * Vista de la tabla de conversaciones (DataTables)
     */
    public function conversations()
    {
        return view('chat/whatsapp_list_view', ['title' => 'Lista de Conversaciones']);
    }

    /**
     * AJAX: Fuente de datos para DataTables
     */
    public function getConversationsListAjax()
    {
        $tenantId = session()->get('tenant_id') ?? 1;
        $whatsappModel = model('App\Models\WhatsappModel');

        $start  = $this->request->getPost('start') ?? 0;
        $length = $this->request->getPost('length') ?? 10;
        $search = $this->request->getPost('search')['value'] ?? '';

        $filters = [
            'estado' => $this->request->getPost('filtro_estado_conv'),
            'desde'  => $this->request->getPost('filtro_fecha_desde_conv'),
            'hasta'  => $this->request->getPost('filtro_fecha_hasta_conv'),
        ];

        $data = $whatsappModel->getConversationsDatatables($tenantId, $start, $length, $search, $filters);

        $rows = [];
        foreach ($data as $item) {
            $estadoIA = ($item->conversation_state === 'HUMAN_MODE')
                ? '<span class="badge badge-warning">Humano</span>'
                : '<span class="badge badge-success">IA Activa</span>';

            $rows[] = [
                $item->last_id,
                $item->phone_key,
                $item->guest_name ?? '<i>Desconocido</i>',
                $item->tenant_name,
                mb_substr($item->message_body, 0, 50) . '...',
                $estadoIA,
                '<a href="' . site_url("chatpanel/room/{$item->phone_key}") . '" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> Ver Chat</a>'
            ];
        }

        return $this->response->setJSON([
            "draw"            => intval($this->request->getPost('draw')),
            "recordsTotal"    => $whatsappModel->countAllConversations($tenantId),
            "recordsFiltered" => count($data), // Para simplificar en este ejemplo
            "data"            => $rows
        ]);
    }

    /**
     * AJAX: Buscador rápido de la barra lateral (Sidebar)
     */
    public function ajax_search_sidebar_contacts()
    {
        $tenantId = session()->get('tenant_id') ?? 1;
        $search = $this->request->getPost('search') ?? '';

        $whatsappModel = model('App\Models\WhatsappModel');
        // Reutilizamos la lógica de búsqueda con un límite pequeño para el sidebar
        $contacts = $whatsappModel->getConversationsDatatables($tenantId, 0, 15, $search, []);

        $html = '';
        foreach ($contacts as $c) {
            $name = $c->guest_name ?? $c->phone_key;
            $lastMsg = mb_substr($c->message_body, 0, 30) . '...';

            $html .= "
            <div class='contact-item p-3 border-bottom' style='cursor:pointer;' onclick='loadChat(\"{$c->phone_key}\", \"{$name}\")'>
                <div class='d-flex align-items-center'>
                    <div class='rounded-circle bg-success text-white d-flex justify-content-center align-items-center' style='width:40px; height:40px;'>
                        <i class='fas fa-user'></i>
                    </div>
                    <div class='ml-3'>
                        <h6 class='mb-0 font-weight-bold'>{$name}</h6>
                        <small class='text-muted'>{$lastMsg}</small>
                    </div>
                </div>
            </div>";
        }

        return $this->response->setJSON(['success' => true, 'html' => $html]);
    }

    /**
     * AJAX: Cargar mensajes de un chat específico
     */
    public function getMessagesAjax()
    {
        $phone = $this->request->getPost('phone');
        $tenantId = session()->get('tenant_id') ?? 1;

        $db = \Config\Database::connect();
        $messages = $db->table('whatsapp_messages')
            ->where('tenant_id', $tenantId)
            ->groupStart()
            ->where('sender_phone', $phone)
            ->orWhere('recipient_phone', $phone)
            ->groupEnd()
            ->orderBy('created_at', 'ASC')
            ->get()->getResult();

        return view('chat/messages_list_partial', ['messages' => $messages]);
    }
}