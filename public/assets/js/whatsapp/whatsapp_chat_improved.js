$(document).ready(function() {

    // --- 1. Buscador de Contactos (AJAX con Debounce) ---
    let searchTimeout;

    $('#contact-search').on('keyup', function() {
        var value = $(this).val().trim();

        // Limpiar timeout anterior para no hacer llamadas en cada tecla
        clearTimeout(searchTimeout);

        // Si el campo está vacío, recargar lista original inmediatamente
        if (value === '') {
            loadSidebarContacts('');
            return;
        }

        // Esperar 300ms antes de buscar
        searchTimeout = setTimeout(function() {
            loadSidebarContacts(value);
        }, 350);
    });

    function loadSidebarContacts(query) {
        // Indicador de carga visual
        if(query.length > 0) {
            $('#contacts-list').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin text-muted"></i> Buscando...</div>');
        }

        $.ajax({
            url: base_url + 'whatsapp/ajax_search_sidebar_contacts',
            type: 'POST',
            data: { search: query },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#contacts-list').html(response.html);
                }
            },
            error: function() {
                console.error("Error buscando contactos");
                $('#contacts-list').html('<div class="text-center p-3 text-danger">Error de conexión</div>');
            }
        });
    }

    // Scroll al fondo del chat al cargar
    var chatContainer = document.getElementById("chatMessagesContainer");
    chatContainer.scrollTop = chatContainer.scrollHeight;

    // --- 2. Envío Rápido de Texto (Ventana Abierta) ---

    $('#quickSendForm').on('submit', function(e) {
        e.preventDefault();

        let text = $('#quick_message_text').val().trim();

        // CORRECCIÓN: Buscar archivo en AMBOS inputs (imagen O documento)
        let fileImage = $('#quick_media_file')[0].files[0];
        let fileDoc = $('#quick_doc_file')[0].files[0];
        let file = fileImage || fileDoc; // Toma el que no sea undefined



        if (!text && !file) return;



        // Deshabilitar UI
        $('#btnQuickSend').prop('disabled', true);

        let formData = new FormData();
        formData.append('destination_phone_modal', $('#chat_contact_phone_for_modal').val());
        formData.append('openai_thread_modal', $('#chat_openai_thread_for_modal').val());
        formData.append('is_saas', $('#chat_is_saas_for_modal').val()); // <-- NUEVO

        if (file) {
            formData.append('message_type', 'media');
            // IMPORTANTE: El backend espera 'media_file', sin importar si es PDF o Imagen
            formData.append('media_file', file);
            formData.append('media_caption', text);
        } else {
            formData.append('message_type', 'text');
            formData.append('text_message', text);
        }

        $.ajax({
            url: base_url + 'whatsapp/send_custom_message',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    // Limpiar inputs
                    $('#quick_message_text').val('');
                    $('#quick_media_file').val('');
                    $('#quick_doc_file').val('');

                    // Recargar chat (o usar tu función de renderizado manual si prefieres no recargar)
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error de conexión con el servidor.');
            },
            complete: function() {
                $('#btnQuickSend').prop('disabled', false);
            }
        });
    });

    // --- 3. Lógica de Audio (Grabar -> Transcribir -> Editar) ---
    let mediaRecorder;
    let audioChunks = [];

    $('#btnRecordAudio').on('click', function() {
        navigator.mediaDevices.getUserMedia({ audio: true })
            .then(stream => {
                mediaRecorder = new MediaRecorder(stream);
                mediaRecorder.start();

                $('#quickSendForm').addClass('d-none');
                $('#recordingContainer').removeClass('d-none').addClass('d-flex');

                audioChunks = [];
                mediaRecorder.addEventListener("dataavailable", event => {
                    audioChunks.push(event.data);
                });

                mediaRecorder.addEventListener("stop", () => {
                    const audioBlob = new Blob(audioChunks, { type: 'audio/webm' }); // O audio/ogg
                    transcribeAudio(audioBlob);
                });
            });
    });

    $('#btnCancelRecording').on('click', function() {
        if(mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
            // Hack para no procesar: limpiar chunks antes del evento stop o usar flag
            audioChunks = [];
        }
        resetAudioUI();
    });

    $('#btnFinishRecording').on('click', function() {
        if(mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop(); // Esto disparará el evento 'stop' definido arriba
        }
    });

    function resetAudioUI() {
        $('#recordingContainer').removeClass('d-flex').addClass('d-none');
        $('#quickSendForm').removeClass('d-none');
    }

    function transcribeAudio(blob) {
        if (blob.size === 0) return resetAudioUI();

        // Mostrar loading en el textarea
        $('#quick_message_text').val('Transcribiendo audio...').prop('disabled', true);
        resetAudioUI();

        let formData = new FormData();
        formData.append('audio_blob', blob, 'recording.webm');

        $.ajax({
            url: base_url + 'whatsapp/ajax_transcribe_audio',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                let data = JSON.parse(res);
                if (data.success) {
                    $('#quick_message_text').val(data.text).prop('disabled', false).focus();
                } else {
                    $('#quick_message_text').val('').prop('disabled', false);
                    alert('Error transcripción: ' + (data.message || 'Desconocido'));
                }
            },
            error: function() {
                $('#quick_message_text').val('').prop('disabled', false);
                alert('Error de conexión al transcribir.');
            }
        });
    }

    // --- 4. Lógica de Ventana Cerrada (Plantilla) ---
    $('#btnSendReactivationTemplate').on('click', function() {
        // Intentar obtener datos básicos de la página o llamar a una API rápida
        // Por ahora, abrimos el modal
        $('#reactivationModal').modal('show');
    });


    $(document).on('click', '#btnConfirmTemplateSend', function(e) {
        e.preventDefault(); // Previene comportamientos extraños
        console.log("Botón presionado: Iniciando envío...");

        let ownerName = $('#tpl_owner_name').val();
        let petName = $('#tpl_pet_name').val();
        let Mensaje = $('#tpl_mensaje').val();
        let btn = $(this);

        // Validación visual rápida
        if (!ownerName || !petName || !Mensaje) {
            alert('Por favor completa los 3 campos (Propietario, Mascota y Mensaje).');
            return;
        }

        // Bloquear botón para evitar doble envío
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');

        let formData = {
            destination_phone_modal: $('#chat_contact_phone_for_modal').val(),
            is_saas: $('#chat_is_saas_for_modal').val(),
            openai_thread_modal: $('#chat_openai_thread_for_modal').val(),
            message_type: 'template',
            manual_template_name: 'aviso_propietario_gen_v1',
            'template_variables[body][1]': ownerName,
            'template_variables[body][2]': petName,
            'template_variables[body][3]': Mensaje
        };

        $.ajax({
            url: base_url + 'whatsapp/send_custom_message',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log("Respuesta servidor:", response);
                if (response.success) {
                    $('#reactivationModal').modal('hide');
                    // Limpiar campos
                    $('#tpl_owner_name').val('');
                    $('#tpl_pet_name').val('');
                    $('#tpl_mensaje').val('');

                    // Recargar página
                    location.reload();
                } else {
                    alert('Error del servidor: ' + response.message);
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-2"></i> Enviar');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX:", error);
                alert('Error de conexión. Revisa la consola para más detalles.');
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-2"></i> Enviar');
            }
        });
    });

    // ============================================================
    // 5. SISTEMA DE POLLING (REFRESCO AUTOMÁTICO)
    // ============================================================

    let isFetching = false; // Semáforo para evitar llamadas superpuestas

    function getLastMessageId() {
        // Buscamos el último elemento que tenga un data-message-id
        // Nota: En la vista PHP, asegúrate de que el div contenedor del mensaje tenga data-message-id
        // Si usaste mi código de vista anterior, los mensajes no tenían data-message-id explícito en el HTML nuevo.
        // Vamos a asumir que el último mensaje renderizado es el último del array PHP.
        // Lo mejor es buscar el ID más alto presente en el DOM.

        let maxId = 0;
        // Buscamos en los divs de mensajes. Si no tienen atributo, intentamos inferirlo o
        // necesitamos que la vista PHP lo incluya.
        // FIX: Vamos a agregar data-msg-id al HTML generado por PHP en el paso siguiente.
        // Por ahora, asumimos que existe el atributo data-msg-id en el div .rounded

        $('#chatMessagesContainer').find('[data-msg-id]').each(function() {
            let id = parseInt($(this).data('msg-id'));
            if (id > maxId) maxId = id;
        });
        return maxId;
    }

    function renderMessageHtml(msg) {
        // Lógica para determinar estilos (Espejo de PHP)
        let isOutgoing = (msg.direction === 'outgoing');
        let alignClass = isOutgoing ? 'justify-content-end' : 'justify-content-start';
        let bubbleColor = isOutgoing ? '#dcf8c6' : '#ffffff';
        let metaClass = isOutgoing ? 'text-right' : 'text-left';

        // Formateo de hora
        let dateObj = new Date(msg.whatsapp_timestamp.replace(/-/g, "/")); // Fix compatibilidad safari
        let timeStr = dateObj.getHours().toString().padStart(2, '0') + ':' + dateObj.getMinutes().toString().padStart(2, '0');

        // Icono de estado
        let statusIcon = '';
        if (isOutgoing) {
            if (msg.status === 'read') statusIcon = '<i class="fas fa-check-double text-primary"></i>';
            else if (msg.status === 'delivered') statusIcon = '<i class="fas fa-check-double text-secondary"></i>';
            else if (msg.status === 'sent_to_api') statusIcon = '<i class="fas fa-check text-secondary"></i>';
            else statusIcon = '<i class="far fa-clock"></i>';
        }

        // Contenido Multimedia
        let mediaHtml = '';
        if (msg.message_type === 'image' && msg.media_url) {
            mediaHtml = `
                <div class="mb-1 text-center">
                    <a href="${msg.media_url}" target="_blank" data-toggle="lightbox">
                        <img src="${msg.media_url}" class="img-fluid rounded" style="max-height: 250px; object-fit: cover;">
                    </a>
                </div>`;
        } else if ((msg.message_type === 'audio' || msg.message_type === 'ptt') && msg.media_url) {
            mediaHtml = `
                <div class="mb-1" style="min-width: 250px;">
                    <audio controls class="w-100 mt-1">
                        <source src="${msg.media_url}" type="audio/ogg">
                    </audio>
                </div>`;
        } else if (msg.message_type === 'document' && msg.media_url) {
            mediaHtml = `
                <div class="p-2 bg-light rounded border mb-1 d-flex align-items-center">
                    <i class="fas fa-file-pdf text-danger fa-2x mr-3"></i>
                    <div class="overflow-hidden">
                        <a href="${msg.media_url}" target="_blank" class="text-dark font-weight-bold text-truncate d-block" style="max-width: 200px;">Ver Documento</a>
                        <small class="text-muted">PDF/Doc</small>
                    </div>
                </div>`;
        }

        // Cuerpo del mensaje (usamos parsed_body que viene del backend)
        let bodyText = msg.parsed_body ? msg.parsed_body : msg.message_body;

        // Construcción del HTML final
        return `
            <div class="d-flex mb-2 ${alignClass} new-message-animate">
                <div class="position-relative rounded p-2 shadow-sm" 
                     style="max-width: 75%; min-width: 120px; background-color: ${bubbleColor}; border-radius: 7px;"
                     data-msg-id="${msg.id}">
                    
                    ${mediaHtml}

                    <div class="message-text text-break" style="font-size: 0.95rem; white-space: pre-wrap; line-height: 1.4;">${bodyText}</div>
                    
                    <div class="${metaClass} mt-1 unselectable" style="font-size: 0.68rem; color: #999;">
                        <span>${timeStr}</span>
                        <span class="ml-1">${statusIcon}</span>
                    </div>
                </div>
            </div>
        `;
    }

    // Timer: Ejecutar cada 5 segundos
    setInterval(function() {
        if (isFetching) return; // Evitar colas si la red está lenta

        let phone = $('#chat_contact_phone_for_modal').val();
        let lastId = getLastMessageId();

        if (!phone || lastId === 0) return; // No hay contexto

        isFetching = true;

        $.ajax({
            url: base_url + 'whatsapp/get_new_messages_ajax',
            type: 'POST',
            data: {
                contact_phone: phone,
                last_message_id: lastId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.messages.length > 0) {
                    console.log(`Recibidos ${response.messages.length} mensajes nuevos.`);

                    let container = $('#chatMessagesContainer');
                    // Verificar si el usuario está al final del chat para hacer autoscroll
                    let isAtBottom = (container[0].scrollHeight - container[0].scrollTop) <= (container[0].clientHeight + 100);

                    response.messages.forEach(msg => {
                        // Evitar duplicados por si acaso
                        if (container.find(`[data-msg-id="${msg.id}"]`).length === 0) {
                            container.append(renderMessageHtml(msg));
                        }
                    });

                    // Si el usuario estaba abajo o si el mensaje es saliente (nuestro), hacer scroll
                    // Si el usuario estaba leyendo arriba, no movemos el scroll abruptamente (buena UX)
                    if (isAtBottom || response.messages[0].direction === 'outgoing') {
                        container.animate({ scrollTop: container[0].scrollHeight }, 300);
                    }
                }
            },
            complete: function() {
                isFetching = false;
            }
        });

    }, 5000); // 5000 ms = 5 segundos

    $('#returnToAiBtn').on('click', function() {


        const btn = $(this);
        const contactPhone = $('#chat_contact_phone_for_modal').val();

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

        $.ajax({
            url: base_url + 'whatsapp/return_conversation_to_ai_ajax',
            type: 'POST',
            data: {
                contact_phone: contactPhone
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    btn.removeClass('btn-primary').addClass('btn-info').html('<i class="fas fa-check"></i> Control devuelto');
                } else {
                    alert('Error: ' + response.message);
                    btn.prop('disabled', false).html('<i class="fas fa-robot"></i> Devolver a la IA');
                }
            },
            error: function(xhr) {
                alert('Error de comunicación con el servidor. Inténtalo de nuevo.');
                btn.prop('disabled', false).html('<i class="fas fa-robot"></i> Devolver a la IA');
                console.error(xhr.responseText);
            }
        });
    });
});