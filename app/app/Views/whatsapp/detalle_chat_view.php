<?= $this->extend('layouts/pms') ?> <?= $this->section('content') ?>

    <style>
        .chat-wrapper {
            background-color: #d1d7db;
            height: calc(100vh - 65px);
            overflow: hidden;
        }
        .chat-sidebar {
            background: #fff;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .contact-item {
            transition: background 0.2s;
            cursor: pointer;
            border-left: 4px solid transparent;
        }
        .contact-item:hover {
            background-color: #f8fafc;
        }
        .contact-item.active {
            background-color: #f1f5f9;
            border-left-color: #25d366;
        }
        .contact-item.pending-reply {
            background-color: #fff5f5 !important;
        }
        .chat-area {
            background-color: #efeae2;
            background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .messages-container {
            flex-grow: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        .msg-bubble {
            max-width: 70%;
            padding: 8px 12px;
            border-radius: 7.5px;
            margin-bottom: 8px;
            position: relative;
            box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
            font-size: 0.95rem;
            line-height: 1.4;
        }
        .msg-incoming {
            align-self: flex-start;
            background-color: #fff;
            border-top-left-radius: 0;
        }
        .msg-outgoing {
            align-self: flex-end;
            background-color: #dcf8c6;
            border-top-right-radius: 0;
        }
        .msg-meta {
            font-size: 0.7rem;
            color: #667781;
            text-align: right;
            margin-top: 4px;
        }
        .chat-input-area {
            background: #f0f2f5;
            padding: 10px 15px;
            border-top: 1px solid #e2e8f0;
        }
        .btn-record.recording {
            color: #ef4444;
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>

    <div class="chat-wrapper">
        <div class="container-fluid h-100 p-0">
            <div class="row no-gutters h-100">

                <div class="col-md-4 col-lg-3 chat-sidebar shadow-lg">
                    <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold text-secondary">Chats</h5>
                        <span class="badge badge-pill badge-primary"><?= count($sidebar_contacts) ?></span>
                    </div>

                    <div class="p-2">
                        <input type="text" id="sidebarSearch" class="form-control form-control-sm rounded-pill" placeholder="Buscar nombre o teléfono...">
                    </div>

                    <div class="flex-grow-1 overflow-auto" id="contactsList">
                        <?php if(!empty($sidebar_contacts)): ?>
                            <?php foreach($sidebar_contacts as $contact): ?>
                                <?php
                                $isActive = ($contact_phone == $contact['phone']) ? 'active' : '';
                                $isPending = ($contact['last_direction'] == 'incoming' && $contact['chat_state'] != 'CLOSED') ? 'pending-reply' : '';
                                ?>
                                <a href="<?= site_url('whatsapp/chat/' . $contact['phone']) ?>"
                                   class="d-flex align-items-center p-3 border-bottom text-decoration-none text-dark contact-item <?= $isActive ?> <?= $isPending ?>">

                                    <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center mr-3 shadow-sm" style="width: 45px; height: 45px; flex-shrink:0;">
                                        <?= strtoupper(substr(esc($contact['name']), 0, 1)) ?>
                                    </div>

                                    <div class="w-100 overflow-hidden">
                                        <div class="d-flex justify-content-between align-items-baseline">
                                            <h6 class="mb-0 text-truncate font-weight-bold" style="max-width: 70%;"><?= esc($contact['name']) ?></h6>
                                            <small class="text-muted" style="font-size: 0.7rem;"><?= date('H:i', strtotime($contact['last_time'])) ?></small>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <small class="text-muted text-truncate"><?= esc($contact['phone']) ?></small>
                                            <?php if($contact['ai_active'] == 0): ?>
                                                <span class="badge badge-warning" style="font-size: 0.6rem;">MANUAL</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center p-5 text-muted">
                                <i class="fas fa-comments fa-3x mb-3 opacity-25"></i>
                                <p>No hay conversaciones activas.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-8 col-lg-9 chat-area">

                    <?php if($contact_phone): ?>
                        <div class="p-2 bg-light border-bottom d-flex align-items-center justify-content-between shadow-sm sticky-top" style="z-index: 10;">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-info text-white d-flex justify-content-center align-items-center mr-3" style="width: 40px; height: 40px;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 font-weight-bold"><?= esc($contact_phone) ?></h6>
                                    <small class="d-block">
                                        <?php if ($is_24h_window_open): ?>
                                            <span class="text-success font-weight-bold"><i class="fas fa-check-circle"></i> Ventana Abierta</span>
                                        <?php else: ?>
                                            <span class="text-danger font-weight-bold"><i class="fas fa-clock"></i> Ventana Cerrada</span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                            <div class="d-flex">
                                <button type="button" class="btn btn-sm btn-outline-danger mr-2" id="btnCloseChat" title="Resolver y activar IA">
                                    <i class="fas fa-check-double mr-1"></i> Resolver
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnReturnAi" title="Devolver control a la IA">
                                    <i class="fas fa-robot mr-1"></i> Activar IA
                                </button>
                            </div>
                        </div>

                        <div class="messages-container" id="messagesContainer">
                            <?php foreach($messages as $msg): ?>
                                <div class="msg-bubble <?= ($msg->direction == 'outgoing') ? 'msg-outgoing' : 'msg-incoming' ?>" data-msg-id="<?= $msg->id ?>">

                                    <?php if($msg->message_type == 'image' && !empty($msg->media_url)): ?>
                                        <div class="mb-2">
                                            <a href="<?= $msg->media_url ?>" target="_blank">
                                                <img src="<?= $msg->media_url ?>" class="img-fluid rounded" style="max-height: 200px;">
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <div class="msg-text"><?= nl2br(esc($msg->message_body)) ?></div>

                                    <div class="msg-meta">
                                        <?= date('H:i', strtotime($msg->created_at)) ?>
                                        <?php if($msg->direction == 'outgoing'): ?>
                                            <i class="fas fa-check-double <?= ($msg->status == 'read') ? 'text-primary' : '' ?> ml-1"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="chat-input-area">
                            <?php if($is_24h_window_open): ?>
                                <form id="chatForm" class="d-flex align-items-center">
                                    <input type="hidden" id="chat_phone" value="<?= esc($contact_phone) ?>">

                                    <div class="flex-grow-1 bg-white rounded-pill px-3 py-1 shadow-sm d-flex align-items-center">
                                        <textarea id="chat_message" class="form-control border-0 bg-transparent" rows="1" placeholder="Escribe un mensaje manual..." style="resize: none; box-shadow: none;"></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-success rounded-circle ml-2 shadow" style="width: 45px; height: 45px; flex-shrink: 0;">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="text-center p-2">
                                    <div class="alert alert-warning d-inline-block py-1 px-4 small mb-2">
                                        <i class="fas fa-exclamation-triangle"></i> Ventana de 24h expirada.
                                    </div>
                                    <br>
                                    <button type="button" class="btn btn-success btn-sm rounded-pill px-4 shadow-sm" data-toggle="modal" data-target="#reactivationModal">
                                        <i class="fab fa-whatsapp mr-2"></i> Reactivar con Plantilla
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php else: ?>
                        <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                            <div class="bg-white p-5 rounded-circle shadow-sm mb-4">
                                <i class="fab fa-whatsapp fa-5x text-success"></i>
                            </div>
                            <h4 class="font-weight-bold">WhatsApp Live Chat</h4>
                            <p>Selecciona una conversación a la izquierda para empezar.</p>
                            <small class="opacity-50"><i class="fas fa-lock"></i> Extremo a extremo a través de Meta API</small>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reactivationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold">Reactivar Conversación</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body bg-light">
                    <div class="form-group">
                        <label class="small font-weight-bold">Nombre del Cliente</label>
                        <input type="text" id="tpl_owner" class="form-control" placeholder="Ej: Juan">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Nombre Mascota/Referencia</label>
                        <input type="text" id="tpl_pet" class="form-control" placeholder="Ej: Toby">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Motivo del contacto</label>
                        <input type="text" id="tpl_reason" class="form-control" placeholder="Ej: la cita de mañana">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link text-muted" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success shadow" id="btnSendTemplate">Enviar Plantilla</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const container = $("#messagesContainer");
            const phone = "<?= $contact_phone ?>";
            const baseUrl = "<?= base_url() ?>";

            // 1. Auto-scroll al fondo al cargar
            if(container.length) {
                container.scrollTop(container[0].scrollHeight);
            }

            // 2. Envío de Mensaje Manual
            $("#chatForm").on('submit', function(e) {
                e.preventDefault();
                const text = $("#chat_message").val().trim();
                if(!text) return;

                const btn = $(this).find('button');
                btn.prop('disabled', true);

                $.ajax({
                    url: `${baseUrl}/whatsapp/send_custom_message`,
                    type: 'POST',
                    data: {
                        destination_phone_modal: $("#chat_phone").val(),
                        text_message: text,
                        message_type: 'text'
                    },
                    success: function(res) {
                        if(res.success) {
                            $("#chat_message").val("");
                            // El polling cargará el mensaje enviado
                        } else {
                            alert("Error: " + res.message);
                        }
                    },
                    complete: () => btn.prop('disabled', false)
                });
            });

            // 3. Devolver Control a la IA
            $("#btnReturnAi").on('click', function() {
                if(!confirm("¿Deseas que la IA retome el control de este chat?")) return;

                $.post(`${baseUrl}/whatsapp/chat/return_ai`, { contact_phone: phone }, function(res) {
                    if(res.success) location.reload();
                });
            });

            // 4. Cerrar / Resolver Conversación
            $("#btnCloseChat").on('click', function() {
                if(!confirm("¿Marcar conversación como RESUELTA? Esto reactivará la IA automáticamente.")) return;

                $.post(`${baseUrl}/whatsapp/chat/close_chat`, { contact_phone: phone }, function(res) {
                    if(res.success) window.location.href = `${baseUrl}/whatsapp/chat`;
                });
            });

            // 5. Polling de Mensajes Nuevos (Cada 5 seg)
            if(phone) {
                setInterval(function() {
                    const lastId = container.find('.msg-bubble').last().data('msg-id') || 0;

                    $.post(`${baseUrl}/whatsapp/chat/get_new_messages`, {
                        contact_phone: phone,
                        last_message_id: lastId
                    }, function(res) {
                        if(res.success && res.messages.length > 0) {
                            res.messages.forEach(msg => {
                                if(container.find(`[data-msg-id="${msg.id}"]`).length === 0) {
                                    renderNewMessage(msg);
                                }
                            });
                        }
                    });
                }, 5000);
            }

            // Función para renderizar mensaje nuevo en el DOM
            function renderNewMessage(msg) {
                const side = msg.direction === 'outgoing' ? 'msg-outgoing' : 'msg-incoming';
                const time = new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', hour12: false});
                const check = msg.direction === 'outgoing' ? '<i class="fas fa-check-double ml-1"></i>' : '';

                const html = `
            <div class="msg-bubble ${side}" data-msg-id="${msg.id}">
                <div class="msg-text">${msg.message_body}</div>
                <div class="msg-meta">${time} ${check}</div>
            </div>
        `;

                container.append(html);
                container.animate({ scrollTop: container[0].scrollHeight }, 500);
            }

            // 6. Buscador del Sidebar (Local)
            $("#sidebarSearch").on("keyup", function() {
                const val = $(this).val().toLowerCase();
                $("#contactsList .contact-item").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1)
                });
            });

            // 7. Envío de Plantilla de Reactivación
            $("#btnSendTemplate").on('click', function() {
                const owner = $("#tpl_owner").val();
                const pet = $("#tpl_pet").val();
                const reason = $("#tpl_reason").val();

                if(!owner || !pet || !reason) return alert("Completa todos los campos");

                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.post(`${baseUrl}/whatsapp/send_custom_message`, {
                    destination_phone_modal: phone,
                    message_type: 'template',
                    manual_template_name: 'aviso_propietario_gen_v1', // Tu nombre de plantilla Meta
                    'template_variables[body][1]': owner,
                    'template_variables[body][2]': pet,
                    'template_variables[body][3]': reason
                }, function(res) {
                    if(res.success) location.reload();
                    else alert(res.message);
                });
            });
        });
    </script>

<?= $this->endSection() ?>