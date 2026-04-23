<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

<?php
$currencySymbol = $tenant['currency_symbol'] ?? '$';
$existingPrompt = $prompt['system_instruction'] ?? '';
$modelVersion   = $prompt['model_version']      ?? 'gemini-2.5-flash';
?>

    <style>
        .sim-wrap {
            display               : grid;
            grid-template-columns : 460px 1fr;
            gap                   : 1.25rem;
            height                : calc(100vh - 120px);
            align-items           : start;
        }

        /* ── Panel izquierdo ──────────────────────────────────────────────────── */
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
            padding       : 1rem 1.25rem;
            border-bottom : 1px solid #f1f5f9;
        }
        .prompt-header h5 { font-size:.9rem; font-weight:700; color:#0f172a; margin:0 }
        .prompt-header p  { font-size:.75rem; color:#64748b; margin:.15rem 0 0 }
        .prompt-body      { flex:1; padding:1rem 1.25rem; overflow-y:auto }
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
        .prompt-textarea:focus { border-color:#6366f1; background:#fff }
        .char-count { font-size:.7rem; color:#94a3b8; text-align:right; margin-top:.35rem }
        .var-tips {
            background    : #f0f4ff;
            border        : 1px solid #c7d2fe;
            border-radius : 8px;
            padding       : .75rem 1rem;
            margin-top    : .85rem;
        }
        .var-tips-title { font-size:.72rem; font-weight:700; color:#4338ca; margin-bottom:.35rem }
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
        .var-chip:hover { background:#c7d2fe }
        .units-ref {
            background    : #f8fafc;
            border        : 1px solid #e2e8f0;
            border-radius : 8px;
            padding       : .75rem 1rem;
            margin-top    : .75rem;
            font-size     : .78rem;
        }
        .units-ref-title {
            font-size     : .7rem;
            font-weight   : 700;
            color         : #64748b;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom : .5rem;
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
        .btn-save-prompt:hover { background:#4f46e5 }

        /* ── Panel derecho ────────────────────────────────────────────────────── */
        .sim-panel {
            background    : #fff;
            border        : 1px solid #e2e8f0;
            border-radius : 16px;
            overflow      : hidden;
            display       : flex;
            flex-direction: column;
            max-height    : calc(100vh - 120px);
        }
        .sim-header {
            background     : #075E54;
            padding        : .85rem 1.25rem;
            display        : flex;
            align-items    : center;
            justify-content: space-between;
            flex-shrink    : 0;
        }
        .sim-header-info { display:flex; align-items:center; gap:.75rem }
        .sim-avatar {
            width:38px; height:38px; border-radius:50%;
            background:#25D366; display:flex; align-items:center;
            justify-content:center; font-size:1.1rem; color:#fff; flex-shrink:0;
        }
        .sim-name   { font-size:.875rem; font-weight:600; color:#fff }
        .sim-status { font-size:.72rem; color:rgba(255,255,255,.7) }
        .sim-header-actions { display:flex; align-items:center; gap:.5rem }
        .real-mode-badge {
            display       : inline-flex;
            align-items   : center;
            gap           : .3rem;
            background    : rgba(37,211,102,.2);
            border        : 1px solid rgba(37,211,102,.4);
            color         : #a7f3d0;
            font-size     : .65rem;
            font-weight   : 700;
            padding       : .2rem .55rem;
            border-radius : 99px;
            letter-spacing: .04em;
        }
        .sim-state-badge {
            display       : inline-flex;
            align-items   : center;
            gap           : .3rem;
            padding       : .25rem .65rem;
            border-radius : 99px;
            font-size     : .7rem;
            font-weight   : 600;
        }
        .state-idle    { background:#f1f5f9; color:#64748b }
        .state-running { background:#dcfce7; color:#166534 }
        .state-stopped { background:#fee2e2; color:#991b1b }
        .state-waiting { background:#fef3c7; color:#92400e }

        /* Config */
        .sim-config {
            background    : #f8fafc;
            border-bottom : 1px solid #e2e8f0;
            padding       : .65rem 1.25rem;
            display       : flex;
            gap           : .5rem;
            align-items   : center;
            flex-wrap     : wrap;
            flex-shrink   : 0;
        }
        .config-label { font-size:.72rem; color:#64748b; font-weight:600; white-space:nowrap }
        .sim-select {
            background    : #fff;
            border        : 1.5px solid #e2e8f0;
            border-radius : 8px;
            padding       : .35rem .65rem;
            font-size     : .8rem;
            color         : #0f172a;
            outline       : none;
            flex          : 1;
            min-width     : 150px;
            cursor        : pointer;
        }
        .sim-select:focus { border-color:#075E54 }
        .phone-wrap {
            display       : flex;
            align-items   : center;
            gap           : .35rem;
            background    : #fff;
            border        : 1.5px solid #e2e8f0;
            border-radius : 8px;
            padding       : .3rem .65rem;
            transition    : border-color .2s;
        }
        .phone-wrap:focus-within { border-color:#25D366 }
        .phone-wrap.error        { border-color:#ef4444; background:#fff5f5 }
        .phone-wrap input {
            border:none; outline:none; font-size:.8rem;
            color:#0f172a; width:130px; background:transparent;
        }

        /* Mensajes */
        .sim-messages {
            flex          : 1;
            overflow-y    : auto;
            padding       : 1rem;
            background    : #e5ddd5;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23c9c9c9' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            display       : flex;
            flex-direction: column;
            gap           : .4rem;
            scroll-behavior: smooth;
        }
        .msg-row { display:flex; align-items:flex-end; gap:.4rem; animation:msgIn .2s ease }
        .msg-row.hotel  { justify-content:flex-end }
        .msg-row.client { justify-content:flex-start }
        @keyframes msgIn {
            from { opacity:0; transform:translateY(8px) }
            to   { opacity:1; transform:translateY(0) }
        }
        .msg-bubble {
            max-width  : 72%;
            padding    : .55rem .85rem .35rem;
            border-radius: 8px;
            box-shadow : 0 1px 2px rgba(0,0,0,.15);
        }
        .msg-row.hotel  .msg-bubble { background:#dcf8c6; border-radius:8px 0 8px 8px }
        .msg-row.client .msg-bubble { background:#fff;    border-radius:0 8px 8px 8px }
        .msg-text  { font-size:.875rem; color:#111; line-height:1.45; white-space:pre-wrap; word-break:break-word }
        .msg-meta  { display:flex; justify-content:flex-end; align-items:center; gap:.25rem; margin-top:.15rem }
        .msg-time  { font-size:.65rem; color:#94a3b8 }
        .msg-check { font-size:.7rem; color:#53bdeb }
        .msg-sender { font-size:.65rem; font-weight:700; margin-bottom:.15rem }
        .msg-row.hotel  .msg-sender { color:#075E54 }
        .msg-row.client .msg-sender { color:#2563eb }
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

        /* Typing */
        .typing-indicator { display:flex; align-items:center; padding:.5rem .75rem }
        .typing-dots      { display:flex; gap:3px; align-items:center }
        .typing-dot {
            width:7px; height:7px; border-radius:50%;
            background:#94a3b8; animation:typingBounce 1.2s infinite;
        }
        .typing-dot:nth-child(2) { animation-delay:.2s }
        .typing-dot:nth-child(3) { animation-delay:.4s }
        @keyframes typingBounce {
            0%,60%,100% { transform:translateY(0) }
            30%         { transform:translateY(-6px) }
        }

        /* Controles */
        .sim-controls {
            background : #f0f0f0;
            border-top : 1px solid #e2e8f0;
            padding    : .75rem 1rem;
            display    : flex;
            gap        : .6rem;
            align-items: center;
            flex-shrink: 0;
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
        .manual-input:focus       { border-color:#075E54 }
        .manual-input::placeholder { color:#94a3b8 }
        .btn-sim {
            border:none; border-radius:50%;
            width:40px; height:40px;
            display:flex; align-items:center; justify-content:center;
            cursor:pointer; flex-shrink:0; transition:all .2s; font-size:.9rem;
        }
        .btn-send-manual           { background:#075E54; color:#fff }
        .btn-send-manual:hover     { background:#054d45 }
        .btn-next-bot              { background:#25D366; color:#fff }
        .btn-next-bot:hover        { background:#1aab53 }
        .btn-next-bot:disabled     { background:#a7f3d0; cursor:not-allowed }
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
            white-space   : nowrap;
        }
        .btn-start       { background:#25D366; color:#fff }
        .btn-start:hover { background:#1aab53 }
        .btn-stop        { background:#ef4444; color:#fff }
        .btn-stop:hover  { background:#dc2626 }
        .btn-clear       { background:#f1f5f9; color:#475569 }
        .btn-clear:hover { background:#e2e8f0 }
        .btn-purge       { background:#fff1f2; color:#be123c; border:1px solid #fecdd3 }
        .btn-purge:hover { background:#ffe4e6 }

        /* Empty */
        .sim-empty {
            flex:1; display:flex; flex-direction:column;
            align-items:center; justify-content:center;
            gap:.75rem; padding:3rem; text-align:center;
        }

        /* Countdown */
        #autoCountdown {
            display:none; position:absolute; bottom:0; left:0;
            height:3px; background:#25D366;
            border-radius:0 0 22px 22px;
            transition:width linear; width:100%;
        }
        #autoCountdownLabel {
            display:none; position:absolute; top:-22px; right:8px;
            font-size:.65rem; color:#94a3b8; white-space:nowrap;
        }

        @media (max-width: 900px) {
            .sim-wrap { grid-template-columns:1fr }
            .prompt-panel { max-height:60vh }
        }
    </style>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="bi bi-robot me-2 text-success"></i>
                Asistente IA &mdash; Prompt y Simulador
            </h4>
            <p class="text-muted small mb-0">
                Edita el prompt y prueba conversaciones con el flujo real del sistema
            </p>
        </div>
    </div>

    <div class="sim-wrap">

        <!-- ══════════════════════════════════════
             PANEL IZQUIERDO — Editor de prompt
        ══════════════════════════════════════ -->
        <div class="prompt-panel">
            <div class="prompt-header">
                <h5><i class="bi bi-pencil-square me-1 text-primary"></i> Prompt del asistente</h5>
                <p>Define nombre, tono, instrucciones JSON y cuándo usar cada herramienta.</p>
            </div>

            <div class="prompt-body">
                <form action="/whatsapp/simulator/save" method="POST" id="promptForm">
                    <?= csrf_field() ?>
                    <textarea
                            class="prompt-textarea"
                            name="system_instruction"
                            id="promptTextarea"
                            placeholder="Define aquí cómo se comportará tu asistente de WhatsApp..."
                            oninput="updateCharCount()"
                    ><?= esc($existingPrompt) ?></textarea>
                    <div class="char-count" id="charCount"><?= strlen($existingPrompt) ?> caracteres</div>

                    <div class="var-tips">
                        <div class="var-tips-title">
                            <i class="bi bi-lightbulb me-1"></i> Estructura recomendada
                        </div>
                        <div style="font-size:.72rem;color:#4338ca;line-height:1.6">
                            Incluye: nombre del asistente, tono,
                            <strong>instrucción crítica de formato JSON</strong>,
                            y guía de cuándo usar cada herramienta.
                        </div>
                        <div style="margin-top:.5rem">
                            <span class="var-chip" onclick="insertText('INSTRUCCIÓN CRÍTICA: Siempre responde SOLO en JSON. Usa {\"final_response\":\"...\"} o {\"tool_calls\":[...]}')">JSON format</span>
                            <span class="var-chip" onclick="insertText('consultar_disponibilidad')">tool: disponibilidad</span>
                            <span class="var-chip" onclick="insertText('crear_reserva')">tool: reserva</span>
                            <span class="var-chip" onclick="insertText('notificar_administrador')">tool: admin</span>
                            <span class="var-chip" onclick="insertText('enviar_fotos_cabana')">tool: fotos</span>
                        </div>
                    </div>

                    <?php if (!empty($units)): ?>
                        <div class="units-ref">
                            <div class="units-ref-title">Unidades activas</div>
                            <?php foreach ($units as $u): ?>
                                <div style="display:flex;justify-content:space-between;padding:.2rem 0;border-bottom:1px solid #f1f5f9;font-size:.75rem">
                                    <span style="color:#0f172a;font-weight:500"><?= esc($u['name']) ?></span>
                                    <span style="color:#64748b">
                                    <?php if ($u['price_per_night']): ?>
                                        <?= $currencySymbol ?><?= number_format($u['price_per_night'], 0, ',', '.') ?>/noche
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
            <span style="font-size:.72rem;color:#64748b">
                Modelo: <strong style="color:#0f172a"><?= esc($modelVersion) ?></strong>
            </span>
                <button type="submit" form="promptForm" class="btn-save-prompt">
                    <i class="bi bi-floppy me-1"></i> Guardar prompt
                </button>
            </div>
        </div>

        <!-- ══════════════════════════════════════
             PANEL DERECHO — Simulador
        ══════════════════════════════════════ -->
        <div class="sim-panel">

            <!-- Header estilo WhatsApp -->
            <div class="sim-header">
                <div class="sim-header-info">
                    <div class="sim-avatar"><i class="bi bi-robot"></i></div>
                    <div>
                        <div class="sim-name"><?= esc($tenant['name'] ?? 'Mi Hotel') ?></div>
                        <div class="sim-status" id="simStatus">Simulador inactivo</div>
                    </div>
                </div>
                <div class="sim-header-actions">
                <span class="real-mode-badge">
                    <i class="bi bi-lightning-fill"></i> FLUJO REAL
                </span>
                    <span class="sim-state-badge state-idle" id="simBadge">
                    <i class="bi bi-circle"></i> Inactivo
                </span>
                </div>
            </div>

            <!-- Configuración -->
            <div class="sim-config">
                <!-- Teléfono obligatorio -->
                <div class="phone-wrap" id="phoneWrap" title="Requerido para identificar al huésped en el sistema">
                    <i class="bi bi-whatsapp" style="color:#25D366;font-size:.9rem;flex-shrink:0"></i>
                    <input type="text" id="simPhone" placeholder="573001234567" maxlength="15">
                    <i class="bi bi-asterisk" style="color:#ef4444;font-size:.55rem" title="Requerido"></i>
                </div>

                <!-- Rol del cliente -->
                <select id="clientRole" class="sim-select">
                    <option value="cliente curioso que pregunta mucho sobre disponibilidad y precios pero al final reserva">Curioso que reserva</option>
                    <option value="cliente indeciso que necesita convencerse con detalles del lugar antes de reservar">Indeciso</option>
                    <option value="cliente que se queja del precio pero eventualmente acepta una oferta razonable">Regatero</option>
                    <option value="cliente impaciente que quiere reservar rápido para una fecha específica">Urgente</option>
                    <option value="cliente desconfiado que hace muchas preguntas antes de decidirse">Desconfiado</option>
                    <option value="turista extranjero que escribe en inglés y español mezclado buscando información">Turista (spanglish)</option>
                    <option value="CUSTOM">✏️ Personalizado...</option>
                </select>
                <div id="customRoleWrap" style="display:none;flex:1">
                    <input type="text" id="customRole" class="sim-select" placeholder="Describe el rol del cliente...">
                </div>

                <!-- Controles -->
                <button class="btn-sim-control btn-start" id="btnStart" onclick="startSimulation()">
                    <i class="bi bi-play-fill"></i> Iniciar
                </button>
                <button class="btn-sim-control btn-stop" id="btnStop" onclick="stopSimulation()" style="display:none">
                    <i class="bi bi-stop-fill"></i> Parar
                </button>
                <button class="btn-sim-control btn-clear" onclick="clearChat()" title="Limpiar pantalla">
                    <i class="bi bi-x-lg"></i>
                </button>
                <button class="btn-sim-control btn-purge" onclick="purgeSimData()" title="Borrar mensajes de simulación de la BD">
                    <i class="bi bi-database-x"></i> BD
                </button>
            </div>

            <!-- Área de mensajes -->
            <div class="sim-messages" id="simMessages">
                <div class="sim-empty" id="simEmpty">
                    <i class="bi bi-chat-dots" style="font-size:2.5rem;opacity:.4;color:#94a3b8"></i>
                    <p class="mb-0 fw-semibold" style="color:#64748b">Ingresa un teléfono y presiona Iniciar</p>
                    <p style="font-size:.78rem;color:#94a3b8">
                        Flujo real: Router → Contexto → Gemini → Tools → BD
                    </p>
                    <div style="font-size:.7rem;color:#94a3b8;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem .85rem;margin-top:.25rem;line-height:1.7">
                        🛡️ Mensajes marcados como simulación en BD<br>
                        🚫 Sin envíos reales por WhatsApp<br>
                        🗑️ Usa <strong>BD</strong> para limpiar al terminar
                    </div>
                </div>
            </div>

            <!-- Controles inferiores -->
            <div class="sim-controls">
                <div style="position:relative;flex:1">
                    <input type="text"
                           class="manual-input"
                           id="manualInput"
                           placeholder="Escribe como cliente (Enter para enviar)..."
                           disabled>
                    <div id="autoCountdown"></div>
                    <div id="autoCountdownLabel">Continuando en <span id="autoSecs">5</span>s...</div>
                </div>
                <button class="btn-sim btn-send-manual" id="btnSendManual"
                        onclick="sendManual()" disabled title="Enviar mensaje manual">
                    <i class="bi bi-send-fill"></i>
                </button>
                <button class="btn-sim btn-next-bot" id="btnNextBot"
                        onclick="forceClientTurn()" disabled title="Forzar turno del cliente bot">
                    <i class="bi bi-robot"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        // ═══════════════════════════════════════════════════════════════════
        // ESTADO
        // ═══════════════════════════════════════════════════════════════════
        const STATE = {
            running        : false,
            busy           : false,
            lastHotelMsg   : '',      // Solo el último mensaje del hotel para el bot-cliente
            conversationLog: [],   // ← nuevo: historial liviano para el bot-cliente
            turnCount      : 0,
            maxTurns       : 24,
            countdownTimer : null,
            countdownSecs  : 5,
            waitingForInput: false,
        };

        // ═══════════════════════════════════════════════════════════════════
        // HELPERS UI
        // ═══════════════════════════════════════════════════════════════════
        function getPhone() {
            return document.getElementById('simPhone').value.trim();
        }

        function getClientRole() {
            const sel = document.getElementById('clientRole').value;
            return sel === 'CUSTOM'
                ? (document.getElementById('customRole').value.trim() || 'cliente interesado en reservar')
                : sel;
        }

        function setSimStatus(text, badgeClass, icon, label) {
            document.getElementById('simStatus').textContent = text;
            const b = document.getElementById('simBadge');
            b.className = 'sim-state-badge ' + badgeClass;
            b.innerHTML = `<i class="bi bi-${icon}"></i> ${label}`;
        }

        function setControlsEnabled(on) {
            document.getElementById('btnStart').style.display = on ? 'none'        : 'inline-flex';
            document.getElementById('btnStop').style.display  = on ? 'inline-flex' : 'none';
            document.getElementById('manualInput').disabled   = !on;
            document.getElementById('btnSendManual').disabled = !on;
            document.getElementById('btnNextBot').disabled    = !on;
            document.getElementById('simPhone').disabled      = on;
            document.getElementById('clientRole').disabled    = on;
        }

        function scrollBottom() {
            const el = document.getElementById('simMessages');
            el.scrollTop = el.scrollHeight;
        }

        // ═══════════════════════════════════════════════════════════════════
        // TYPING INDICATOR
        // ═══════════════════════════════════════════════════════════════════
        function showTyping(role) {
            removeTyping();
            const d = document.createElement('div');
            d.id = 'typingIndicator';
            d.className = `msg-row ${role}`;
            d.innerHTML = `
        <div class="msg-bubble" style="padding:.5rem .85rem">
            <div class="typing-indicator">
                <div class="typing-dots">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        </div>`;
            document.getElementById('simMessages').appendChild(d);
            scrollBottom();
        }

        function removeTyping() {
            const el = document.getElementById('typingIndicator');
            if (el) el.remove();
        }

        // ═══════════════════════════════════════════════════════════════════
        // MENSAJES
        // ═══════════════════════════════════════════════════════════════════
        function addMessage(role, text, toolCalls = []) {
            removeTyping();

            // Eliminar empty state si existe
            const empty = document.getElementById('simEmpty');
            if (empty) empty.remove();

            const container = document.getElementById('simMessages');
            const now  = new Date();
            const time = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
            const isHotel = (role === 'hotel');

            let toolHtml = '';
            (toolCalls || []).forEach(tc => {
                const name = tc.tool || tc.name || String(tc);
                const args = JSON.stringify(tc.args || tc.arguments || {}).substring(0, 60);
                toolHtml += `<div class="tool-badge"><i class="bi bi-tools"></i> ${escHtml(name)}(${escHtml(args)}...)</div>`;
            });

            const div = document.createElement('div');
            div.className = `msg-row ${isHotel ? 'hotel' : 'client'}`;
            div.innerHTML = `
        <div>
            <div class="msg-sender">${isHotel ? '🤖 Asistente' : '👤 Cliente simulado'}</div>
            ${toolHtml}
            <div class="msg-bubble">
                <div class="msg-text">${escHtml(text)}</div>
                <div class="msg-meta">
                    <span class="msg-time">${time}</span>
                    ${isHotel ? '<i class="bi bi-check2-all msg-check"></i>' : ''}
                </div>
            </div>
        </div>`;
            container.appendChild(div);
            scrollBottom();
        }

        function addSystemMsg(text, type = '') {
            removeTyping();
            const container = document.getElementById('simMessages');
            const colors = {
                error : { bg: '#fee2e2', fg: '#991b1b' },
                warn  : { bg: '#fef3c7', fg: '#92400e' },
                ''    : { bg: 'rgba(0,0,0,.1)', fg: '#555' },
            };
            const c   = colors[type] || colors[''];
            const div = document.createElement('div');
            div.style.cssText = 'text-align:center;margin:.4rem 0';
            div.innerHTML = `<span style="background:${c.bg};color:${c.fg};font-size:.7rem;padding:.2rem .75rem;border-radius:99px">${text}</span>`;
            container.appendChild(div);
            scrollBottom();
        }

        function escHtml(t) {
            const d = document.createElement('div');
            d.appendChild(document.createTextNode(String(t)));
            return d.innerHTML;
        }

        function showFlash(msg, type = 'success') {
            const el = document.createElement('div');
            el.className = `alert alert-${type} alert-dismissible position-fixed shadow`;
            el.style.cssText = 'top:1rem;right:1rem;z-index:9999;min-width:260px;font-size:.85rem';
            el.innerHTML = `${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 4000);
        }

        // ═══════════════════════════════════════════════════════════════════
        // COUNTDOWN
        // ═══════════════════════════════════════════════════════════════════
        function startCountdown(onComplete) {
            if (!STATE.running) return;

            STATE.waitingForInput = true;
            let secsLeft = STATE.countdownSecs;

            const bar   = document.getElementById('autoCountdown');
            const label = document.getElementById('autoCountdownLabel');
            const secs  = document.getElementById('autoSecs');

            bar.style.display = label.style.display = 'block';
            bar.style.transition = `width ${STATE.countdownSecs}s linear`;
            secs.textContent = secsLeft;
            bar.getBoundingClientRect(); // force reflow
            bar.style.width = '0%';

            STATE.countdownTimer = setInterval(() => {
                secsLeft--;
                secs.textContent = secsLeft;
                if (secsLeft <= 0) {
                    stopCountdown();
                    if (STATE.running && STATE.waitingForInput) {
                        STATE.waitingForInput = false;
                        onComplete();
                    }
                }
            }, 1000);
        }

        function stopCountdown() {
            STATE.waitingForInput = false;
            clearInterval(STATE.countdownTimer);
            STATE.countdownTimer = null;
            const bar   = document.getElementById('autoCountdown');
            const label = document.getElementById('autoCountdownLabel');
            if (bar)   { bar.style.transition = ''; bar.style.width = '100%'; bar.style.display = 'none' }
            if (label)   label.style.display = 'none';
        }

        // ═══════════════════════════════════════════════════════════════════
        // FETCH HELPERS
        // ═══════════════════════════════════════════════════════════════════
        function getCsrfToken() {
            const el = document.querySelector('input[name="csrf_test_name"]');
            return el ? el.value : '';
        }

        async function simFetch(body) {
            const res = await fetch('/whatsapp/simulator/turn', {
                method     : 'POST',
                credentials: 'same-origin',
                headers    : {
                    'Content-Type'    : 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN'    : getCsrfToken(),
                },
                body: JSON.stringify(body)
            });
            return await res.json();
        }

        async function apiFetch(url) {
            const res = await fetch(url, {
                method     : 'POST',
                credentials: 'same-origin',
                headers    : {
                    'Content-Type'    : 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN'    : getCsrfToken(),
                }
            });
            return await res.json();
        }

        // ═══════════════════════════════════════════════════════════════════
        // CONTROL PRINCIPAL
        // ═══════════════════════════════════════════════════════════════════
        function startSimulation() {
            if (STATE.running) return;
            STATE.conversationLog = []; // ← resetear
            const phone = getPhone();
            if (!phone || phone.length < 10) {
                document.getElementById('phoneWrap').classList.add('error');
                showFlash('⚠️ Ingresa un número de teléfono válido (ej: 573001234567)', 'warning');
                document.getElementById('simPhone').focus();
                return;
            }
            document.getElementById('phoneWrap').classList.remove('error');

            STATE.running       = true;
            STATE.busy          = false;
            STATE.lastHotelMsg  = '';
            STATE.turnCount     = 0;
            STATE.waitingForInput = false;

            document.getElementById('simMessages').innerHTML = '';
            setControlsEnabled(true);
            setSimStatus('Simulación activa', 'state-running', 'circle-fill', 'Activo');
            addSystemMsg(`🚀 Iniciada · Tel: ${phone} · Flujo: REAL`);

            // Primer turno: el cliente bot abre la conversación
            triggerClientTurn(true);
        }

        function stopSimulation() {
            STATE.running       = false;
            STATE.busy          = false;
            STATE.waitingForInput = false;
            stopCountdown();
            removeTyping();
            setControlsEnabled(false);
            setSimStatus('Detenida', 'state-stopped', 'stop-circle', 'Detenido');
            addSystemMsg('🛑 Detenida · Usa 🗑 BD para limpiar mensajes');
        }

        function clearChat() {
            stopSimulation();
            STATE.lastHotelMsg = '';
            STATE.turnCount    = 0;
            STATE.conversationLog = []; // ← resetear

            const container = document.getElementById('simMessages');
            container.innerHTML = '';

            const d = document.createElement('div');
            d.id = 'simEmpty';
            d.className = 'sim-empty';
            d.innerHTML = `
        <i class="bi bi-chat-dots" style="font-size:2.5rem;opacity:.4;color:#94a3b8"></i>
        <p class="mb-0 fw-semibold" style="color:#64748b">Ingresa un teléfono y presiona Iniciar</p>
        <p style="font-size:.78rem;color:#94a3b8">Flujo real: Router → Contexto → Gemini → Tools → BD</p>`;
            container.appendChild(d);
            setSimStatus('Inactivo', 'state-idle', 'circle', 'Inactivo');
        }

        async function purgeSimData() {
            if (!confirm('¿Borrar todos los mensajes de simulación de la base de datos?')) return;
            try {
                const data = await apiFetch('/whatsapp/simulator/clear');
                if (data.success) {
                    showFlash(`🗑️ BD limpia — ${data.deleted} mensaje(s) eliminados`, 'success');
                    addSystemMsg(`🗑️ BD limpiada: ${data.deleted} mensajes eliminados`);
                } else {
                    showFlash('Error al limpiar la BD', 'danger');
                }
            } catch(e) {
                showFlash('Error de conexión al limpiar', 'danger');
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // TURNO DEL CLIENTE BOT
        // Solo usa el último mensaje del hotel para generar la respuesta.
        // NO acumula historial — el historial real vive en la BD.
        // ═══════════════════════════════════════════════════════════════════
        async function triggerClientTurn(isFirst = false, forced = false) {
            if (!STATE.running) return;
            if (!forced && STATE.busy) return; // ← si viene del countdown, ignora busy
            STATE.busy = true;

            showTyping('client');

            try {
                const data = await simFetch({
                    role          : 'client',
                    is_first      : isFirst,
                    last_hotel_msg: STATE.lastHotelMsg,
                    conversation_log: STATE.conversationLog.slice(-10),
                    client_role   : getClientRole(),
                    phone         : getPhone(),
                });

                removeTyping();
                if (!STATE.running) { STATE.busy = false; return; }

                if (!data.success) {
                    addSystemMsg('⚠️ Error generando mensaje del cliente: ' + (data.message || ''), 'warn');
                    STATE.busy = false;
                    // Reintento automático tras 2s en lugar de detener la simulación
                    setTimeout(() => triggerClientTurn(isFirst), 2000);
                    return;
                }

                const clientText = data.text || '';
                addMessage('client', clientText);
                STATE.turnCount++;
                STATE.conversationLog.push(`Cliente: ${clientText}`); // ← acumular
                STATE.busy = false;

                // Pequeña pausa natural antes de que el hotel responda
                setTimeout(() => triggerHotelTurn(clientText), 900);

            } catch(e) {
                console.error('[SimClient]', e);
                addSystemMsg('Error de conexión (cliente)', 'error');
                STATE.busy = false;
                stopSimulation();
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // TURNO DEL HOTEL (FLUJO REAL)
        // Inyecta el mensaje al WebhookService real y espera la respuesta en BD.
        // ═══════════════════════════════════════════════════════════════════
        async function triggerHotelTurn(clientMessage) {
            if (!STATE.running || STATE.busy) return;
            STATE.busy = true;
            stopCountdown();

            showTyping('hotel');
            document.getElementById('btnNextBot').disabled = true;

            try {
                const data = await simFetch({
                    role       : 'hotel',
                    message    : clientMessage,
                    phone      : getPhone(),
                    client_role: getClientRole(),
                    is_first   : false,
                });

                removeTyping();
                if (!STATE.running) { STATE.busy = false; return; }

                if (!data.success) {
                    addSystemMsg('⚠️ ' + (data.message || 'Sin respuesta del sistema'), 'warn');
                    STATE.busy = false;
                    document.getElementById('btnNextBot').disabled = false;
                    setSimStatus('Esperando...', 'state-waiting', 'hourglass-split', 'Esperando');
                    return;
                }

                const hotelText = data.text || data.system_bot_msg || '';
                addMessage('hotel', hotelText, data.tool_calls || []);

                // Guardar SOLO el último mensaje del hotel para el siguiente turno del cliente
                STATE.lastHotelMsg = hotelText;
                STATE.conversationLog.push(`Hotel: ${hotelText}`); // ← acumular
                STATE.turnCount++;
                STATE.busy = false;

                if (!STATE.running) return;

                // Detectar fin natural de conversación
                const endings = [
                    'reserva creada', 'folio', 'hasta pronto',
                    'gracias por contactar', 'nos vemos', 'confirmad',
                    'sim-', 'buen viaje', 'que tengas'
                ];
                if (endings.some(w => hotelText.toLowerCase().includes(w)) || STATE.turnCount >= STATE.maxTurns) {
                    addSystemMsg('✅ Conversación finalizada · Usa 🗑 BD para limpiar');
                    setControlsEnabled(false);
                    STATE.running = false;
                    setSimStatus('Finalizado', 'state-stopped', 'check-circle', 'Finalizado');
                    return;
                }

                // Esperar 5s input manual, si no → siguiente turno del cliente bot
                document.getElementById('btnNextBot').disabled = false;
                setSimStatus('Esperando tu respuesta o continúa...', 'state-waiting', 'hourglass-split', 'Esperando');

                startCountdown(() => {
                    // Forzar reset de busy antes de continuar — el countdown garantiza
                    // que no hay nada procesándose porque esperamos N segundos sin actividad
                    STATE.busy = false;
                    STATE.waitingForInput = false;
                    setSimStatus('Simulación activa', 'state-running', 'circle-fill', 'Activo');

                    if (!STATE.running) return;
                    triggerClientTurn(false, true); // ← forced = true

                });

            } catch(e) {
                console.error('[SimHotel]', e);
                addSystemMsg('Error de conexión (hotel)', 'error');
                STATE.busy = false;
                stopSimulation();
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // MENSAJE MANUAL DEL USUARIO
        // ═══════════════════════════════════════════════════════════════════
        function sendManual() {
            if (!STATE.running || STATE.busy) return;

            const input = document.getElementById('manualInput');
            const text  = input.value.trim();
            if (!text) return;

            stopCountdown();
            input.value = '';

            addMessage('client', text);
            STATE.conversationLog.push(`Cliente: ${text}`); // ← acumular
            STATE.turnCount++;
            setSimStatus('Simulación activa', 'state-running', 'circle-fill', 'Activo');

            setTimeout(() => triggerHotelTurn(text), 600);
        }

        // Botón para forzar el turno del cliente cuando el countdown ya pasó
        function forceClientTurn() {
            stopCountdown();
            STATE.busy = false;
            triggerClientTurn(false, true); // ← forced = true
        }

        // ═══════════════════════════════════════════════════════════════════
        // EVENT LISTENERS
        // ═══════════════════════════════════════════════════════════════════
        document.getElementById('manualInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendManual();
                return;
            }
            // Cualquier tecla cancela el countdown
            if (STATE.waitingForInput && e.key.length === 1) {
                stopCountdown();
                setSimStatus('Simulación activa', 'state-running', 'circle-fill', 'Activo');
            }
        });

        document.getElementById('clientRole').addEventListener('change', function() {
            document.getElementById('customRoleWrap').style.display = this.value === 'CUSTOM' ? 'flex' : 'none';
        });

        document.getElementById('simPhone').addEventListener('input', function() {
            document.getElementById('phoneWrap').classList.remove('error');
        });

        // ═══════════════════════════════════════════════════════════════════
        // EDITOR DE PROMPT
        // ═══════════════════════════════════════════════════════════════════
        function updateCharCount() {
            const ta = document.getElementById('promptTextarea');
            document.getElementById('charCount').textContent = ta.value.length + ' caracteres';
        }

        function insertText(text) {
            const ta = document.getElementById('promptTextarea');
            const s  = ta.selectionStart;
            const e  = ta.selectionEnd;
            ta.value = ta.value.substring(0, s) + text + ta.value.substring(e);
            ta.selectionStart = ta.selectionEnd = s + text.length;
            ta.focus();
            updateCharCount();
        }

        let promptSaved = true;
        document.getElementById('promptTextarea').addEventListener('input', () => promptSaved = false);
        document.getElementById('promptForm').addEventListener('submit', () => promptSaved = true);
        window.addEventListener('beforeunload', e => {
            if (!promptSaved) { e.preventDefault(); e.returnValue = '' }
        });
        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                document.getElementById('promptForm').submit();
            }
        });
    </script>

<?= $this->endSection() ?>