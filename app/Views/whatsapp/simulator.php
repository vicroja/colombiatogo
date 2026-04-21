<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

<?php
$currencySymbol = $tenant['currency_symbol'] ?? '$';
$existingPrompt = $prompt['system_instruction'] ?? '';
$modelVersion   = $prompt['model_version']      ?? 'gemini-2.5-flash';
?>

    <style>
        /* ── Layout principal ─────────────────────────────────────────────────── */
        .sim-wrap {
            display               : grid;
            grid-template-columns : 480px 1fr;
            gap                   : 1.25rem;
            height                : calc(100vh - 120px);
            align-items           : start;
        }

        /* ── Panel izquierdo: editor del prompt ───────────────────────────────── */
        .prompt-panel {
            background    : #fff;
            border        : 1px solid #e2e8f0;
            border-radius : 16px;
            overflow      : hidden;
            display       : flex;
            flex-direction: column;
            max-height    : calc(100vh - 120px);
        }
        .prompt-header {
            padding        : 1rem 1.25rem;
            border-bottom  : 1px solid #f1f5f9;
            background     : #fff;
        }
        .prompt-header h5 {
            font-size   : .9rem;
            font-weight : 700;
            color       : #0f172a;
            margin      : 0;
        }
        .prompt-header p {
            font-size : .75rem;
            color     : #64748b;
            margin    : .15rem 0 0;
        }
        .prompt-body {
            flex    : 1;
            padding : 1rem 1.25rem;
            overflow-y: auto;
        }
        .prompt-textarea {
            width         : 100%;
            min-height    : 320px;
            border        : 1.5px solid #e2e8f0;
            border-radius : 10px;
            padding       : .85rem 1rem;
            font-family   : 'Courier New', monospace;
            font-size     : .78rem;
            line-height   : 1.6;
            color         : #1e293b;
            resize        : vertical;
            outline       : none;
            transition    : border-color .2s;
            background    : #fafafa;
        }
        .prompt-textarea:focus { border-color: #6366f1; background: #fff }

        .char-count {
            font-size  : .7rem;
            color      : #94a3b8;
            text-align : right;
            margin-top : .35rem;
        }

        /* Tips de variables */
        .var-tips {
            background    : #f0f4ff;
            border        : 1px solid #c7d2fe;
            border-radius : 8px;
            padding       : .75rem 1rem;
            margin-top    : .85rem;
        }
        .var-tips-title {
            font-size   : .72rem;
            font-weight : 700;
            color       : #4338ca;
            margin-bottom: .35rem;
        }
        .var-chip {
            display       : inline-block;
            background    : #e0e7ff;
            color         : #3730a3;
            font-size     : .68rem;
            font-family   : monospace;
            padding       : .1rem .45rem;
            border-radius : 4px;
            margin        : .15rem .15rem 0 0;
            cursor        : pointer;
            transition    : background .15s;
        }
        .var-chip:hover { background: #c7d2fe }

        /* Unidades de referencia */
        .units-ref {
            background    : #f8fafc;
            border        : 1px solid #e2e8f0;
            border-radius : 8px;
            padding       : .75rem 1rem;
            margin-top    : .75rem;
            font-size     : .78rem;
        }
        .units-ref-title {
            font-size   : .7rem;
            font-weight : 700;
            color       : #64748b;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: .5rem;
        }

        .prompt-footer {
            padding        : .85rem 1.25rem;
            border-top     : 1px solid #f1f5f9;
            display        : flex;
            justify-content: space-between;
            align-items    : center;
            background     : #fff;
        }
        .btn-save-prompt {
            background    : #6366f1;
            color         : #fff;
            border        : none;
            border-radius : 8px;
            padding       : .55rem 1.25rem;
            font-size     : .85rem;
            font-weight   : 600;
            cursor        : pointer;
            transition    : background .2s;
        }
        .btn-save-prompt:hover { background: #4f46e5 }

        /* ── Panel derecho: simulador ─────────────────────────────────────────── */
        .sim-panel {
            background    : #fff;
            border        : 1px solid #e2e8f0;
            border-radius : 16px;
            overflow      : hidden;
            display       : flex;
            flex-direction: column;
            max-height    : calc(100vh - 120px);
        }

        /* Header del simulador */
        .sim-header {
            background     : #075E54;
            padding        : .85rem 1.25rem;
            display        : flex;
            align-items    : center;
            justify-content: space-between;
            flex-shrink    : 0;
        }
        .sim-header-info {
            display     : flex;
            align-items : center;
            gap         : .75rem;
        }
        .sim-avatar {
            width           : 38px;
            height          : 38px;
            border-radius   : 50%;
            background      : #25D366;
            display         : flex;
            align-items     : center;
            justify-content : center;
            font-size       : 1.1rem;
            color           : #fff;
            flex-shrink     : 0;
        }
        .sim-name {
            font-size   : .875rem;
            font-weight : 600;
            color       : #fff;
        }
        .sim-status {
            font-size : .72rem;
            color     : rgba(255,255,255,.7);
        }
        .sim-header-actions {
            display     : flex;
            align-items : center;
            gap         : .5rem;
        }

        /* Config de simulación */
        .sim-config {
            background     : #f8fafc;
            border-bottom  : 1px solid #e2e8f0;
            padding        : .75rem 1.25rem;
            display        : flex;
            gap            : .6rem;
            align-items    : center;
            flex-wrap      : wrap;
            flex-shrink    : 0;
        }
        .config-label {
            font-size   : .72rem;
            color       : #64748b;
            font-weight : 600;
            white-space : nowrap;
        }
        .sim-select {
            background    : #fff;
            border        : 1.5px solid #e2e8f0;
            border-radius : 8px;
            padding       : .35rem .65rem;
            font-size     : .8rem;
            color         : #0f172a;
            outline       : none;
            flex          : 1;
            min-width     : 180px;
            cursor        : pointer;
        }
        .sim-select:focus { border-color: #075E54 }

        /* Área de mensajes */
        .sim-messages {
            flex       : 1;
            overflow-y : auto;
            padding    : 1rem;
            background : #e5ddd5;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23c9c9c9' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            display    : flex;
            flex-direction: column;
            gap        : .4rem;
            scroll-behavior: smooth;
        }

        /* Burbujas estilo WhatsApp */
        .msg-row {
            display     : flex;
            align-items : flex-end;
            gap         : .4rem;
            animation   : msgIn .2s ease;
        }
        .msg-row.hotel  { justify-content: flex-end }
        .msg-row.client { justify-content: flex-start }

        @keyframes msgIn {
            from { opacity: 0; transform: translateY(8px) }
            to   { opacity: 1; transform: translateY(0) }
        }

        .msg-bubble {
            max-width     : 72%;
            padding       : .55rem .85rem .35rem;
            border-radius : 8px;
            position      : relative;
            box-shadow    : 0 1px 2px rgba(0,0,0,.15);
        }
        .msg-row.hotel  .msg-bubble {
            background    : #dcf8c6;
            border-radius : 8px 0 8px 8px;
        }
        .msg-row.client .msg-bubble {
            background    : #fff;
            border-radius : 0 8px 8px 8px;
        }

        .msg-text {
            font-size   : .875rem;
            color       : #111;
            line-height : 1.45;
            white-space : pre-wrap;
            word-break  : break-word;
        }
        .msg-meta {
            display     : flex;
            justify-content: flex-end;
            align-items : center;
            gap         : .25rem;
            margin-top  : .15rem;
        }
        .msg-time {
            font-size : .65rem;
            color     : #94a3b8;
        }
        .msg-check { font-size: .7rem; color: #53bdeb }

        /* Sender label */
        .msg-sender {
            font-size   : .65rem;
            font-weight : 700;
            color       : #2563eb;
            margin-bottom: .15rem;
        }
        .msg-row.hotel .msg-sender { color: #075E54 }

        /* Tool call badge */
        .tool-badge {
            display       : inline-flex;
            align-items   : center;
            gap           : .3rem;
            background    : #fef3c7;
            border        : 1px solid #fde68a;
            color         : #92400e;
            font-size     : .65rem;
            font-weight   : 600;
            padding       : .15rem .5rem;
            border-radius : 4px;
            margin-bottom : .25rem;
        }

        /* Typing indicator */
        .typing-indicator {
            display     : flex;
            align-items : center;
            gap         : .35rem;
            padding     : .5rem .75rem;
        }
        .typing-dots {
            display     : flex;
            gap         : 3px;
            align-items : center;
        }
        .typing-dot {
            width         : 7px;
            height        : 7px;
            border-radius : 50%;
            background    : #94a3b8;
            animation     : typingBounce 1.2s infinite;
        }
        .typing-dot:nth-child(2) { animation-delay: .2s }
        .typing-dot:nth-child(3) { animation-delay: .4s }
        @keyframes typingBounce {
            0%, 60%, 100% { transform: translateY(0) }
            30%           { transform: translateY(-6px) }
        }

        /* Barra inferior del simulador */
        .sim-controls {
            background     : #f0f0f0;
            border-top     : 1px solid #e2e8f0;
            padding        : .75rem 1rem;
            display        : flex;
            gap            : .6rem;
            align-items    : center;
            flex-shrink    : 0;
        }
        .manual-input {
            flex          : 1;
            background    : #fff;
            border        : 1px solid #e2e8f0;
            border-radius : 22px;
            padding       : .55rem 1rem;
            font-size     : .875rem;
            outline       : none;
            transition    : border-color .2s;
        }
        .manual-input:focus { border-color: #075E54 }
        .manual-input::placeholder { color: #94a3b8 }

        .btn-sim {
            border        : none;
            border-radius : 50%;
            width         : 40px;
            height        : 40px;
            display       : flex;
            align-items   : center;
            justify-content: center;
            cursor        : pointer;
            flex-shrink   : 0;
            transition    : all .2s;
            font-size     : .9rem;
        }
        .btn-send-manual {
            background : #075E54;
            color      : #fff;
        }
        .btn-send-manual:hover { background: #054d45 }
        .btn-next-bot {
            background : #25D366;
            color      : #fff;
        }
        .btn-next-bot:hover { background: #1aab53 }
        .btn-next-bot:disabled {
            background : #a7f3d0;
            cursor     : not-allowed;
        }

        /* Botones de control */
        .btn-sim-control {
            display       : inline-flex;
            align-items   : center;
            gap           : .35rem;
            border        : none;
            border-radius : 8px;
            padding       : .4rem .85rem;
            font-size     : .78rem;
            font-weight   : 600;
            cursor        : pointer;
            transition    : all .2s;
        }
        .btn-start  { background: #25D366; color: #fff }
        .btn-start:hover  { background: #1aab53 }
        .btn-stop   { background: #ef4444; color: #fff }
        .btn-stop:hover   { background: #dc2626 }
        .btn-clear  { background: #f1f5f9; color: #475569 }
        .btn-clear:hover  { background: #e2e8f0 }

        /* Estado del simulador */
        .sim-state-badge {
            display       : inline-flex;
            align-items   : center;
            gap           : .3rem;
            padding       : .25rem .65rem;
            border-radius : 99px;
            font-size     : .7rem;
            font-weight   : 600;
        }
        .state-idle     { background: #f1f5f9; color: #64748b }
        .state-running  { background: #dcfce7; color: #166534 }
        .state-stopped  { background: #fee2e2; color: #991b1b }
        .state-waiting  { background: #fef3c7; color: #92400e }

        /* Empty state */
        .sim-empty {
            flex           : 1;
            display        : flex;
            flex-direction : column;
            align-items    : center;
            justify-content: center;
            gap            : .75rem;
            color          : #94a3b8;
            font-size      : .85rem;
            padding        : 3rem;
            text-align     : center;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .sim-wrap { grid-template-columns: 1fr }
            .prompt-panel { max-height: 60vh }
        }
    </style>

    <!-- ── Header ────────────────────────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="bi bi-robot me-2 text-success"></i>
                Asistente IA &mdash; Prompt y Simulador
            </h4>
            <p class="text-muted small mb-0">
                Edita el prompt del asistente y prueba conversaciones simuladas
            </p>
        </div>
    </div>

    <div class="sim-wrap">

        <!-- ════════════════════════════════
             PANEL IZQUIERDO — Editor de prompt
        ════════════════════════════════ -->
        <div class="prompt-panel">
            <div class="prompt-header">
                <h5>
                    <i class="bi bi-pencil-square me-1 text-primary"></i>
                    Prompt del asistente
                </h5>
                <p>
                    Este es el corazón del asistente. Define su nombre, tono,
                    herramientas y cómo responde a tus huéspedes.
                </p>
            </div>

            <div class="prompt-body">
                <form action="/whatsapp/simulator/save" method="POST"
                      id="promptForm">
                    <?= csrf_field() ?>

                    <textarea
                        class="prompt-textarea"
                        name="system_instruction"
                        id="promptTextarea"
                        placeholder="Define aquí cómo se comportará tu asistente de WhatsApp..."
                        oninput="updateCharCount()"
                    ><?= esc($existingPrompt) ?></textarea>

                    <div class="char-count" id="charCount">
                        <?= strlen($existingPrompt) ?> caracteres
                    </div>

                    <!-- Tips de formato -->
                    <div class="var-tips">
                        <div class="var-tips-title">
                            <i class="bi bi-lightbulb me-1"></i>
                            Estructura recomendada del prompt
                        </div>
                        <div style="font-size:.72rem;color:#4338ca;line-height:1.6">
                            El prompt debe incluir: nombre del asistente,
                            tono y forma de hablar, instrucciones de formato JSON,
                            y guía de cuándo usar cada herramienta.
                        </div>
                        <div style="margin-top:.5rem">
                        <span class="var-chip"
                              onclick="insertText('REGLA CRÍTICA DE FORMATO JSON')">
                            JSON format
                        </span>
                            <span class="var-chip"
                                  onclick="insertText('consultar_disponibilidad')">
                            tool: disponibilidad
                        </span>
                            <span class="var-chip"
                                  onclick="insertText('crear_reserva')">
                            tool: reserva
                        </span>
                            <span class="var-chip"
                                  onclick="insertText('notificar_administrador')">
                            tool: admin
                        </span>
                        </div>
                    </div>

                    <!-- Unidades de referencia -->
                    <?php if (!empty($units)): ?>
                        <div class="units-ref">
                            <div class="units-ref-title">
                                Unidades activas (para incluir en el prompt)
                            </div>
                            <?php foreach ($units as $u): ?>
                                <div style="display:flex;justify-content:space-between;
                                    padding:.2rem 0;border-bottom:1px solid #f1f5f9;
                                    font-size:.75rem">
                            <span style="color:#0f172a;font-weight:500">
                                <?= esc($u['name']) ?>
                            </span>
                                    <span style="color:#64748b">
                                <?php if ($u['price_per_night']): ?>
                                    <?= $currencySymbol ?>
                                    <?= number_format($u['price_per_night'], 0, ',', '.') ?>/noche
                                    &middot; máx <?= $u['max_occupancy'] ?> pers.
                                <?php else: ?>
                                    Sin tarifa
                                <?php endif; ?>
                            </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </form>
            </div>

            <div class="prompt-footer">
                <div>
                <span style="font-size:.72rem;color:#64748b">
                    Modelo:
                    <strong style="color:#0f172a">
                        <?= esc($modelVersion) ?>
                    </strong>
                </span>
                </div>
                <button type="submit" form="promptForm"
                        class="btn-save-prompt"
                        id="btnSavePrompt">
                    <i class="bi bi-floppy me-1"></i>
                    Guardar prompt
                </button>
            </div>
        </div>

        <!-- ════════════════════════════════
             PANEL DERECHO — Simulador WhatsApp
        ════════════════════════════════ -->
        <div class="sim-panel">

            <!-- Header estilo WhatsApp -->
            <div class="sim-header">
                <div class="sim-header-info">
                    <div class="sim-avatar">
                        <i class="bi bi-robot"></i>
                    </div>
                    <div>
                        <div class="sim-name">
                            <?= esc($tenant['name'] ?? 'Mi Hotel') ?>
                        </div>
                        <div class="sim-status" id="simStatus">
                            Simulador inactivo
                        </div>
                    </div>
                </div>
                <div class="sim-header-actions">
                <span class="sim-state-badge state-idle" id="simBadge">
                    <i class="bi bi-circle"></i>
                    Inactivo
                </span>
                </div>
            </div>

            <!-- Configuración de la simulación -->
            <div class="sim-config">
                <span class="config-label">Rol del cliente:</span>
                <select id="clientRole" class="sim-select">
                    <option value="cliente curioso que pregunta mucho sobre disponibilidad y precios pero al final reserva">
                        Curioso que al final reserva
                    </option>
                    <option value="cliente indeciso que necesita convencerse con detalles del lugar antes de reservar">
                        Indeciso — necesita detalles
                    </option>
                    <option value="cliente problemático que se queja del precio pero eventualmente acepta una oferta">
                        Regatero — quiere descuento
                    </option>
                    <option value="cliente impaciente que quiere reservar rápido para una fecha específica">
                        Urgente — quiere reservar ya
                    </option>
                    <option value="cliente desconfiado que hace muchas preguntas antes de decidirse">
                        Desconfiado — muchas preguntas
                    </option>
                    <option value="turista extranjero que escribe en inglés y español mezclado buscando información del lugar">
                        Turista extranjero (spanglish)
                    </option>
                    <option value="CUSTOM">✏️ Personalizado...</option>
                </select>
                <div id="customRoleWrap" style="display:none;flex:1">
                    <input type="text" id="customRole"
                           class="sim-select"
                           placeholder="Describe el rol del cliente...">
                </div>

                <!-- Controles -->
                <button class="btn-sim-control btn-start"
                        id="btnStart"
                        onclick="startSimulation()">
                    <i class="bi bi-play-fill"></i> Iniciar
                </button>
                <button class="btn-sim-control btn-stop"
                        id="btnStop"
                        onclick="stopSimulation()"
                        style="display:none">
                    <i class="bi bi-stop-fill"></i> Parar
                </button>
                <button class="btn-sim-control btn-clear"
                        onclick="clearSimulation()">
                    <i class="bi bi-trash"></i>
                </button>
            </div>

            <!-- Área de mensajes -->
            <div class="sim-messages" id="simMessages">
                <div class="sim-empty" id="simEmpty">
                    <i class="bi bi-chat-dots"
                       style="font-size:2.5rem;opacity:.4"></i>
                    <p class="mb-0 fw-semibold" style="color:#64748b">
                        Configura el rol del cliente y presiona Iniciar
                    </p>
                    <p style="font-size:.78rem;color:#94a3b8">
                        La simulación muestra cómo responder&#225; tu asistente
                        a distintos tipos de huéspedes
                    </p>
                </div>
            </div>

            <!-- Controles inferiores -->
            <div class="sim-controls">
                <input type="text"
                       class="manual-input"
                       id="manualInput"
                       placeholder="Escribe como cliente (Enter para enviar)..."
                       disabled>
                <button class="btn-sim btn-send-manual"
                        id="btnSendManual"
                        onclick="sendManual()"
                        disabled
                        title="Enviar mensaje manual">
                    <i class="bi bi-send-fill"></i>
                </button>
                <button class="btn-sim btn-next-bot"
                        id="btnNextBot"
                        onclick="triggerClientBot()"
                        disabled
                        title="Siguiente mensaje del bot cliente">
                    <i class="bi bi-robot"></i>
                </button>
            </div>

        </div>
    </div>

    <script>
        // ── Estado de la simulación ───────────────────────────────────────────────
        const STATE = {
            running    : false,
            history    : [],          // [{role:'user'|'model', parts:[{text:'...'}]}]
            turnCount  : 0,
            maxTurns   : 20,          // límite de seguridad por simulación
            autoMode   : false,       // bot vs bot automático
            autoTimer  : null,        // timer del modo automático
        };

        const CSRF_NAME = '<?= csrf_token() ?>';
        const CSRF_HASH = '<?= csrf_hash() ?>';

        // ── Helpers de UI ─────────────────────────────────────────────────────────

        function getClientRole() {
            const sel = document.getElementById('clientRole').value;
            if (sel === 'CUSTOM') {
                return document.getElementById('customRole').value.trim()
                    || 'cliente interesado en reservar';
            }
            return sel;
        }

        function setSimStatus(text, badgeClass, badgeIcon, badgeText) {
            document.getElementById('simStatus').textContent = text;
            const badge = document.getElementById('simBadge');
            badge.className = 'sim-state-badge ' + badgeClass;
            badge.innerHTML = `<i class="bi bi-${badgeIcon}"></i> ${badgeText}`;
        }

        function setControlsEnabled(running) {
            document.getElementById('btnStart').style.display    = running ? 'none'  : 'inline-flex';
            document.getElementById('btnStop').style.display     = running ? 'inline-flex' : 'none';
            document.getElementById('manualInput').disabled      = !running;
            document.getElementById('btnSendManual').disabled    = !running;
            document.getElementById('btnNextBot').disabled       = !running;
        }

        function scrollToBottom() {
            const el = document.getElementById('simMessages');
            el.scrollTop = el.scrollHeight;
        }

        function showTyping(role) {
            removeTyping();
            const div  = document.createElement('div');
            div.id     = 'typingIndicator';
            div.className = `msg-row ${role}`;
            div.innerHTML = `
        <div class="msg-bubble" style="padding:.5rem .85rem">
            <div class="typing-indicator">
                <div class="typing-dots">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        </div>`;
            document.getElementById('simMessages').appendChild(div);
            scrollToBottom();
        }

        function removeTyping() {
            const el = document.getElementById('typingIndicator');
            if (el) el.remove();
        }

        function addMessage(role, text, toolCalls = []) {
            removeTyping();

            const container = document.getElementById('simMessages');

            // Ocultar empty state
            const empty = document.getElementById('simEmpty');
            if (empty) empty.style.display = 'none';

            const now = new Date();
            const time = now.getHours().toString().padStart(2,'0')
                + ':' + now.getMinutes().toString().padStart(2,'0');

            const isHotel    = role === 'hotel';
            const senderName = isHotel ? '🤖 Alfonso (Asistente)' : '👤 Cliente simulado';
            const rowClass   = isHotel ? 'hotel' : 'client';

            // Tool call badges
            let toolHtml = '';
            if (toolCalls && toolCalls.length > 0) {
                toolCalls.forEach(tc => {
                    toolHtml += `<div class="tool-badge">
                <i class="bi bi-tools"></i>
                ${tc.tool}(${JSON.stringify(tc.args).substring(0, 60)}...)
            </div>`;
                });
            }

            const div = document.createElement('div');
            div.className = `msg-row ${rowClass}`;
            div.innerHTML = `
        <div>
            <div class="msg-sender">${senderName}</div>
            ${toolHtml}
            <div class="msg-bubble">
                <div class="msg-text">${escapeHtml(text)}</div>
                <div class="msg-meta">
                    <span class="msg-time">${time}</span>
                    ${isHotel ? '<i class="bi bi-check2-all msg-check"></i>' : ''}
                </div>
            </div>
        </div>`;

            container.appendChild(div);
            scrollToBottom();
        }

        function addSystemMessage(text) {
            const container = document.getElementById('simMessages');
            const div = document.createElement('div');
            div.style.cssText = 'text-align:center;margin:.5rem 0';
            div.innerHTML = `<span style="background:rgba(0,0,0,.12);color:#666;
                         font-size:.7rem;padding:.2rem .75rem;border-radius:99px">
                         ${text}</span>`;
            container.appendChild(div);
            scrollToBottom();
        }

        function escapeHtml(text) {
            const d = document.createElement('div');
            d.appendChild(document.createTextNode(String(text)));
            return d.innerHTML;
        }

        function showFlash(msg, type = 'success') {
            const el = document.createElement('div');
            el.className = `alert alert-${type} alert-dismissible position-fixed shadow`;
            el.style.cssText = 'top:1rem;right:1rem;z-index:9999;min-width:260px;font-size:.85rem';
            el.innerHTML = `${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 3500);
        }

        // ── Fetch helper ──────────────────────────────────────────────────────────

        async function simFetch(body) {
            // Obtener token CSRF fresco del DOM
            const csrfInput = document.querySelector('input[name="csrf_test_name"]');
            const csrfToken = csrfInput ? csrfInput.value : '';

            const res = await fetch('/whatsapp/simulator/turn', {
                method      : 'POST',
                credentials : 'same-origin',
                headers     : {
                    'Content-Type'     : 'application/json',
                    'X-Requested-With' : 'XMLHttpRequest',
                    'X-CSRF-TOKEN'     : csrfToken,
                },
                body: JSON.stringify(body)
            });
            return await res.json();
        }

        // ── Control principal ─────────────────────────────────────────────────────

        function startSimulation() {
            if (STATE.running) return;

            STATE.running   = true;
            STATE.history   = [];
            STATE.turnCount = 0;

            // Limpiar mensajes anteriores
            const container = document.getElementById('simMessages');
            container.innerHTML = '';

            setControlsEnabled(true);
            setSimStatus('Simulación activa', 'state-running', 'circle-fill', 'Activo');
            addSystemMessage('Simulación iniciada — el cliente enviará el primer mensaje');

            // El cliente empieza la conversación
            triggerClientBot();
        }

        function stopSimulation() {
            STATE.running  = false;
            STATE.autoMode = false;
            if (STATE.autoTimer) {
                clearTimeout(STATE.autoTimer);
                STATE.autoTimer = null;
            }
            removeTyping();
            setControlsEnabled(false);
            setSimStatus('Simulación detenida', 'state-stopped', 'stop-circle', 'Detenido');
            addSystemMessage('Simulación detenida por el usuario');
        }

        function clearSimulation() {
            stopSimulation();
            STATE.history   = [];
            STATE.turnCount = 0;
            document.getElementById('simMessages').innerHTML = '';

            // Mostrar empty state de nuevo
            const empty = document.createElement('div');
            empty.id        = 'simEmpty';
            empty.className = 'sim-empty';
            empty.innerHTML = `
        <i class="bi bi-chat-dots" style="font-size:2.5rem;opacity:.4"></i>
        <p class="mb-0 fw-semibold" style="color:#64748b">
            Configura el rol del cliente y presiona Iniciar
        </p>
        <p style="font-size:.78rem;color:#94a3b8">
            La simulación muestra cómo responderá tu asistente a distintos tipos de huéspedes
        </p>`;
            document.getElementById('simMessages').appendChild(empty);
            setSimStatus('Simulador inactivo', 'state-idle', 'circle', 'Inactivo');
        }

        // ── Turno del bot cliente ─────────────────────────────────────────────────

        async function triggerClientBot() {
            if (!STATE.running) return;
            if (STATE.turnCount >= STATE.maxTurns) {
                addSystemMessage(`Límite de ${STATE.maxTurns} turnos alcanzado`);
                stopSimulation();
                return;
            }

            showTyping('client');
            document.getElementById('btnNextBot').disabled = true;

            try {
                const data = await simFetch({
                    role        : 'client',
                    history     : STATE.history,
                    client_role : getClientRole(),
                });

                removeTyping();

                if (!data.success) {
                    addSystemMessage('Error del bot cliente: ' + (data.message || 'Error desconocido'));
                    stopSimulation();
                    return;
                }

                const text = data.text;
                addMessage('client', text);

                // Agregar al historial como mensaje del usuario
                STATE.history.push({
                    role  : 'user',
                    parts : [{ text }]
                });

                STATE.turnCount++;

                // Después del cliente, el hotel responde automáticamente
                STATE.autoTimer = setTimeout(() => triggerHotelBot(), 1200);

            } catch (err) {
                console.error('[Sim/Client]', err);
                addSystemMessage('Error de conexión en bot cliente');
                stopSimulation();
            }
        }

        // ── Turno del bot hotel ───────────────────────────────────────────────────

        async function triggerHotelBot() {
            if (!STATE.running) return;

            showTyping('hotel');
            document.getElementById('btnNextBot').disabled = true;

            try {
                const data = await simFetch({
                    role    : 'hotel',
                    history : STATE.history,
                });

                removeTyping();

                if (!data.success) {
                    addSystemMessage('Error del asistente hotel: ' + (data.message || ''));
                    stopSimulation();
                    return;
                }

                addMessage('hotel', data.text, data.tool_calls || []);

                // Actualizar historial con la respuesta del hotel
                // Usar new_history si viene (incluye los turnos de tool calls)
                if (data.new_history && data.new_history.length > STATE.history.length) {
                    STATE.history = data.new_history;
                }

                // Agregar la respuesta final del modelo
                STATE.history.push({
                    role  : 'model',
                    parts : [{ text: data.raw || data.text }]
                });

                STATE.turnCount++;

                // Habilitar botón para próximo turno del cliente
                if (STATE.running) {
                    document.getElementById('btnNextBot').disabled = false;

                    // Detectar si la conversación llegó a un final natural
                    const finalWords = ['reserva creada', 'folio', 'hasta pronto', 'gracias por',
                        'nos vemos', 'confirmad', 'sim-'];
                    const textLower  = data.text.toLowerCase();
                    const isEnd      = finalWords.some(w => textLower.includes(w));

                    if (isEnd) {
                        addSystemMessage('La conversación parece haber llegado a su fin natural');
                        document.getElementById('btnNextBot').disabled = false;
                    }
                }

            } catch (err) {
                console.error('[Sim/Hotel]', err);
                addSystemMessage('Error de conexión en asistente hotel');
                stopSimulation();
            }
        }

        // ── Mensaje manual del usuario ────────────────────────────────────────────

        function sendManual() {
            if (!STATE.running) return;

            const input = document.getElementById('manualInput');
            const text  = input.value.trim();
            if (!text) return;

            input.value = '';

            addMessage('client', text);

            STATE.history.push({
                role  : 'user',
                parts : [{ text }]
            });

            STATE.turnCount++;

            // El hotel responde automáticamente al mensaje manual
            setTimeout(() => triggerHotelBot(), 800);
        }

        // ── Event listeners ───────────────────────────────────────────────────────

        document.getElementById('manualInput').addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendManual();
            }
        });

        document.getElementById('clientRole').addEventListener('change', function () {
            const customWrap = document.getElementById('customRoleWrap');
            customWrap.style.display = this.value === 'CUSTOM' ? 'flex' : 'none';
        });

        // ── Editor de prompt ──────────────────────────────────────────────────────

        function updateCharCount() {
            const ta = document.getElementById('promptTextarea');
            document.getElementById('charCount').textContent =
                ta.value.length + ' caracteres';
        }

        function insertText(text) {
            const ta    = document.getElementById('promptTextarea');
            const start = ta.selectionStart;
            const end   = ta.selectionEnd;
            ta.value    = ta.value.substring(0, start) + text + ta.value.substring(end);
            ta.selectionStart = ta.selectionEnd = start + text.length;
            ta.focus();
            updateCharCount();
        }

        // Confirmar antes de salir si hay cambios sin guardar
        let promptSaved = true;
        document.getElementById('promptTextarea').addEventListener('input', () => {
            promptSaved = false;
        });
        document.getElementById('promptForm').addEventListener('submit', () => {
            promptSaved = true;
        });
        window.addEventListener('beforeunload', (e) => {
            if (!promptSaved) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Auto-save al guardar con Ctrl+S
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                document.getElementById('promptForm').submit();
            }
        });
    </script>

<?= $this->endSection() ?>