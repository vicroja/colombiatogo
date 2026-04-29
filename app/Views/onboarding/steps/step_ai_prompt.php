<?php
/**
 * onboarding/steps/step5_ai_prompt.php
 *
 * Paso 5: Configurar el prompt del asistente IA del hotel.
 * Tres modos: estilo predefinido, descripción libre, o pegar chats reales.
 * Llama a Gemini para generar el system_instruction.
 */

$existingPrompt = $existing_prompt['system_instruction'] ?? '';
$tenantName     = $tenant['name'] ?? 'tu hotel';
?>

<!-- ── Card principal ────────────────────────────────────────────────────── -->
<div class="wizard-card">
    <div class="card-eyebrow">Paso 5 · Opcional</div>

    <div class="d-flex align-items-center gap-2 mb-1">
        <h5 class="mb-0">Tu asistente de WhatsApp</h5>
        <span class="ai-badge"><i class="bi bi-stars"></i> Con IA</span>
    </div>
    <p class="card-hint">
        Configura cómo responderá tu asistente a los huéspedes por WhatsApp.
        Puedes elegir un estilo, describir cómo atiende tu hotel, o pegar
        conversaciones reales y la IA aprenderá tu tono automáticamente.
    </p>

    <!-- ── Selector de modo ─────────────────────────────────────────────── -->
    <div class="mode-selector mb-4">
        <div class="row g-2">

            <!-- Modo A: Estilo rápido -->
            <div class="col-md-4">
                <input type="radio" class="btn-check" name="ai_mode"
                       id="modeStyle" value="style" checked>
                <label class="mode-card" for="modeStyle">
                    <i class="bi bi-sliders2"
                       style="font-size:1.5rem;color:#6366f1"></i>
                    <div class="mode-title">Estilo rápido</div>
                    <div class="mode-desc">
                        Elige el tono y personalidad en segundos
                    </div>
                </label>
            </div>

            <!-- Modo B: Descripción libre -->
            <div class="col-md-4">
                <input type="radio" class="btn-check" name="ai_mode"
                       id="modeDesc" value="description">
                <label class="mode-card" for="modeDesc">
                    <i class="bi bi-pencil-square"
                       style="font-size:1.5rem;color:#6366f1"></i>
                    <div class="mode-title">Descripción libre</div>
                    <div class="mode-desc">
                        Cuéntanos cómo es tu hotel y cómo atiende
                    </div>
                </label>
            </div>

            <!-- Modo C: Pegar chats -->
            <div class="col-md-4">
                <input type="radio" class="btn-check" name="ai_mode"
                       id="modeChats" value="chats">
                <label class="mode-card" for="modeChats">
                    <i class="bi bi-chat-left-text"
                       style="font-size:1.5rem;color:#6366f1"></i>
                    <div class="mode-title">Pegar chats reales</div>
                    <div class="mode-desc">
                        La IA aprende tu estilo de conversaciones reales
                    </div>
                </label>
            </div>

        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════
         PANEL MODO A — Estilo rápido
    ══════════════════════════════════════════════════════════════════ -->
    <div id="panelStyle" class="mode-panel">

        <p class="fw-semibold mb-3" style="font-size:.9rem">
            ¿Cómo quieres que atienda tu asistente?
        </p>

        <!-- Grilla de estilos -->
        <div class="row g-2 mb-4" id="styleGrid">
            <?php
            $styles = [
                ['id' => 'formal',    'icon' => 'bi-tie',
                    'label' => 'Formal y elegante',
                    'desc'  => 'Trato respetuoso, lenguaje corporativo'],
                ['id' => 'warm',      'icon' => 'bi-heart',
                    'label' => 'Cálido y familiar',
                    'desc'  => 'Cercano, usa el nombre del huésped'],
                ['id' => 'fun',       'icon' => 'bi-emoji-smile',
                    'label' => 'Amigable y descomplicado',
                    'desc'  => 'Informal, usa emojis, muy accesible'],
                ['id' => 'adventure', 'icon' => 'bi-compass',
                    'label' => 'Aventurero y activo',
                    'desc'  => 'Perfecto para ecohoteles y glamping'],
                ['id' => 'luxury',    'icon' => 'bi-gem',
                    'label' => 'Lujoso y exclusivo',
                    'desc'  => 'Sofisticado, para propiedades premium'],
                ['id' => 'local',     'icon' => 'bi-house-heart',
                    'label' => 'Local y auténtico',
                    'desc'  => 'Resalta la cultura y lo regional'],
            ];
            foreach ($styles as $s):
                ?>
                <div class="col-6 col-md-4">
                    <div class="style-option" data-style="<?= $s['id'] ?>"
                         onclick="selectStyle(this)">
                        <i class="bi <?= $s['icon'] ?>"
                           style="font-size:1.2rem;color:#6366f1"></i>
                        <div class="style-label"><?= $s['label'] ?></div>
                        <div class="style-desc"><?= $s['desc'] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Idioma -->
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold small" for="styleLanguage">
                    Idioma principal
                </label>
                <select class="form-select" id="styleLanguage">
                    <option value="español" selected>Español</option>
                    <option value="inglés">Inglés</option>
                    <option value="español e inglés">Español e Inglés</option>
                    <option value="portugués">Portugués</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold small" for="styleEmoji">
                    Uso de emojis
                </label>
                <select class="form-select" id="styleEmoji">
                    <option value="moderado" selected>Moderado</option>
                    <option value="ninguno">Ninguno</option>
                    <option value="frecuente">Frecuente</option>
                </select>
            </div>
        </div>

        <button type="button" class="btn-ai w-100 mt-1" id="btnGenStyle"
                onclick="generateFromStyle()">
            <i class="bi bi-stars me-1"></i>
            Generar prompt con este estilo
        </button>
        <div class="ai-loading mt-2 justify-content-center" id="aiStyleLoading">
            <span class="spinner-border spinner-border-sm"
                  style="color:#6366f1"></span>
            <span style="font-size:.82rem;color:#6366f1">
                Configurando tu asistente...
            </span>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════
         PANEL MODO B — Descripción libre
    ══════════════════════════════════════════════════════════════════ -->
    <div id="panelDesc" class="mode-panel" style="display:none">

        <label class="form-label fw-semibold" for="hotelDescription">
            Cuéntanos sobre tu hotel y cómo atienden a los huéspedes
        </label>
        <textarea
            class="form-control mb-2"
            id="hotelDescription"
            rows="5"
            placeholder="Ej: Somos un hotel boutique familiar en Cartagena. Nuestro equipo es cálido y siempre usamos el nombre del huésped. Ofrecemos tours locales, el desayuno es casero y nos gusta dar recomendaciones de sitios auténticos que los turistas no conocen..."
            maxlength="1500"
        ></textarea>
        <small class="text-muted" id="descFreeCount">0 / 1500</small>

        <button type="button" class="btn-ai w-100 mt-3" id="btnGenDesc"
                onclick="generateFromDescription()">
            <i class="bi bi-stars me-1"></i>
            Generar prompt desde mi descripción
        </button>
        <div class="ai-loading mt-2 justify-content-center" id="aiDescLoading">
            <span class="spinner-border spinner-border-sm"
                  style="color:#6366f1"></span>
            <span style="font-size:.82rem;color:#6366f1">
                Analizando tu descripción...
            </span>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════
         PANEL MODO C — Pegar chats reales
    ══════════════════════════════════════════════════════════════════ -->
    <div id="panelChats" class="mode-panel" style="display:none">

        <div class="alert alert-light border d-flex gap-2 mb-3"
             style="font-size:.82rem">
            <i class="bi bi-shield-lock-fill text-primary mt-1"
               style="flex-shrink:0"></i>
            <span>
                Los chats que pegues <strong>no se almacenan</strong>.
                Solo se usan en este momento para que la IA aprenda tu
                estilo y genere el prompt. Puedes anonimizar nombres si prefieres.
            </span>
        </div>

        <label class="form-label fw-semibold" for="realChats">
            Pega aquí conversaciones reales de WhatsApp
        </label>
        <textarea
            class="form-control mb-1"
            id="realChats"
            rows="8"
            placeholder="Pega aquí el texto copiado de tus chats de WhatsApp. Por ejemplo:

Huésped: Buenos días! tienen disponible para el 15 de diciembre?
Hotel: Hola Mariela! 😊 Claro que sí, tenemos la cabaña Río Verde disponible esa fecha...
Huésped: Qué precio tiene?
Hotel: Son $280.000 por noche para 2 personas, incluye desayuno casero 🍳

Mientras más conversaciones pegues, mejor aprenderá la IA tu estilo."
            maxlength="5000"
        ></textarea>
        <small class="text-muted d-block mb-3" id="chatsCount">0 / 5000</small>

        <button type="button" class="btn-ai w-100" id="btnGenChats"
                onclick="generateFromChats()">
            <i class="bi bi-stars me-1"></i>
            Analizar chats y generar prompt
        </button>
        <div class="ai-loading mt-2 justify-content-center" id="aiChatsLoading">
            <span class="spinner-border spinner-border-sm"
                  style="color:#6366f1"></span>
            <span style="font-size:.82rem;color:#6366f1">
                Analizando conversaciones... esto puede tomar unos segundos
            </span>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════
         RESULTADO — Prompt generado (o manual)
         Aparece tras generar con IA o si ya existe uno guardado
    ══════════════════════════════════════════════════════════════════ -->
    <div id="promptResultWrap"
         style="<?= $existingPrompt ? '' : 'display:none' ?>;
             margin-top:1.5rem">

        <hr style="border-color:#f1f5f9">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label fw-semibold mb-0" for="system_instruction">
                <i class="bi bi-robot me-1" style="color:#6366f1"></i>
                Prompt del asistente
            </label>
            <div class="d-flex align-items-center gap-2">
                <span id="aiGeneratedBadge"
                      class="badge"
                      style="<?= $existingPrompt ? 'display:inline-block' : 'display:none' ?>;
                          background:#f0f4ff;color:#4338ca;font-size:.72rem">
                    <i class="bi bi-stars me-1"></i>Generado por IA
                </span>
                <button type="button"
                        class="btn btn-outline-secondary btn-sm"
                        onclick="togglePromptHelp()">
                    <i class="bi bi-question-circle"></i>
                </button>
            </div>
        </div>

        <!-- Ayuda colapsable -->
        <div id="promptHelp"
             class="alert alert-light border mb-2"
             style="display:none;font-size:.8rem">
            <strong>¿Qué es el prompt del asistente?</strong><br>
            Es el conjunto de instrucciones que definen la personalidad, tono y
            comportamiento de tu asistente de WhatsApp. La IA lo usará cada vez
            que responda a un huésped. Puedes editarlo libremente — los cambios
            se aplican de inmediato.
        </div>

        <textarea
            class="form-control font-monospace"
            id="system_instruction"
            name="system_instruction"
            rows="12"
            placeholder="El prompt aparecerá aquí una vez lo generes con IA, o puedes escribirlo manualmente..."
            style="font-size:.82rem;line-height:1.6;resize:vertical"
        ><?= esc($existingPrompt) ?></textarea>

        <div class="d-flex justify-content-between align-items-center mt-1">
            <small class="text-muted" id="promptCharCount">
                <?= strlen($existingPrompt) ?> caracteres
            </small>
            <button type="button"
                    class="btn btn-link btn-sm p-0 text-danger"
                    onclick="clearPrompt()"
                    style="font-size:.78rem">
                <i class="bi bi-trash me-1"></i>Limpiar y regenerar
            </button>
        </div>
    </div>

    <!-- ── Formulario oculto para submit ────────────────────────────────── -->
    <form action="/onboarding/step/5" method="POST" id="formStep5">
        <?= csrf_field() ?>
        <!-- El valor del textarea se copia aquí antes del submit -->
        <input type="hidden" name="system_instruction" id="hiddenPrompt"
               value="<?= esc($existingPrompt) ?>">
    </form>

    <!-- ── Navegación ────────────────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center pt-4 mt-3
                border-top" style="border-color:#f1f5f9!important">
        <a href="/onboarding/step/4" class="btn-wiz-secondary">
            <i class="bi bi-arrow-left me-1"></i> Anterior
        </a>
        <div class="d-flex align-items-center gap-3">
            <button type="button" class="btn-wiz-skip"
                    onclick="skipStep(<?= $currentStep ?>)">
                Omitir por ahora
            </button>
            <button type="button" class="btn-wiz-primary"
                    id="btnSubmit5" onclick="submitStep5()">
                Guardar y continuar
                <i class="bi bi-arrow-right ms-2"></i>
            </button>
        </div>
    </div>

</div>

<!-- ── Tip ──────────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-start gap-3 p-3 rounded-3"
     style="background:#faf5ff;border:1px solid #e9d5ff">
    <i class="bi bi-lightbulb-fill mt-1"
       style="color:#9333ea;font-size:1.1rem"></i>
    <div>
        <strong style="font-size:.85rem;color:#6b21a8">
            El modo "Pegar chats" es el más poderoso
        </strong>
        <p class="mb-0 text-muted" style="font-size:.82rem">
            Si pegas 5 o más conversaciones reales, la IA detectará
            automáticamente tu vocabulario, uso de emojis, longitud de
            respuestas y estilo de cierre. El resultado será un asistente
            que suena exactamente como tú.
        </p>
    </div>
</div>

<style>
    /* ── Modo cards ───────────────────────────────────────────────────────── */
    .mode-card {
        display       : flex;
        flex-direction: column;
        align-items   : center;
        text-align    : center;
        gap           : .4rem;
        padding       : 1rem .75rem;
        border        : 2px solid #e2e8f0;
        border-radius : 12px;
        cursor        : pointer;
        transition    : border-color .2s, background .2s;
        background    : #fff;
        width         : 100%;
        height        : 100%;
    }

    .btn-check:checked + .mode-card {
        border-color: #6366f1;
        background  : #f0f4ff;
    }

    .mode-title {
        font-weight: 700;
        font-size  : .85rem;
        color      : #0f172a;
    }

    .mode-desc {
        font-size : .75rem;
        color     : #64748b;
        line-height: 1.3;
    }

    /* ── Style options ────────────────────────────────────────────────────── */
    .style-option {
        border       : 2px solid #e2e8f0;
        border-radius: 10px;
        padding      : .85rem .75rem;
        text-align   : center;
        cursor       : pointer;
        transition   : border-color .15s, background .15s;
        background   : #fff;
        height       : 100%;
    }

    .style-option:hover {
        border-color: #a5b4fc;
        background  : #fafbff;
    }

    .style-option.selected {
        border-color: #6366f1;
        background  : #f0f4ff;
    }

    .style-label {
        font-weight: 700;
        font-size  : .82rem;
        color      : #0f172a;
        margin     : .35rem 0 .2rem;
    }

    .style-desc {
        font-size  : .72rem;
        color      : #64748b;
        line-height: 1.3;
    }
</style>

<script>
    // ── Estado local del paso ─────────────────────────────────────────────────
    let selectedStyle = null;  // estilo seleccionado en modo A

    // ── Cambio de modo ────────────────────────────────────────────────────────

    document.querySelectorAll('input[name="ai_mode"]').forEach(radio => {
        radio.addEventListener('change', function () {
            switchMode(this.value);
        });
    });

    /**
     * Muestra el panel del modo seleccionado y oculta los demás
     * @param {string} mode - 'style' | 'description' | 'chats'
     */
    function switchMode(mode) {
        document.getElementById('panelStyle').style.display =
            mode === 'style'       ? 'block' : 'none';
        document.getElementById('panelDesc').style.display  =
            mode === 'description' ? 'block' : 'none';
        document.getElementById('panelChats').style.display =
            mode === 'chats'       ? 'block' : 'none';
    }

    // ── Selección de estilo ───────────────────────────────────────────────────

    /**
     * Marca visualmente el estilo seleccionado
     * @param {HTMLElement} el
     */
    function selectStyle(el) {
        document.querySelectorAll('.style-option').forEach(o =>
            o.classList.remove('selected'));
        el.classList.add('selected');
        selectedStyle = el.dataset.style;
    }

    // ── Contadores de caracteres ──────────────────────────────────────────────

    document.getElementById('hotelDescription').addEventListener('input', function () {
        document.getElementById('descFreeCount').textContent =
            `${this.value.length} / 1500`;
    });

    document.getElementById('realChats').addEventListener('input', function () {
        document.getElementById('chatsCount').textContent =
            `${this.value.length} / 5000`;
    });

    document.getElementById('system_instruction')?.addEventListener('input', function () {
        document.getElementById('promptCharCount').textContent =
            `${this.value.length} caracteres`;
        // Sincronizar con el input hidden
        document.getElementById('hiddenPrompt').value = this.value;
    });

    // ── Generadores ───────────────────────────────────────────────────────────

    /**
     * Modo A: genera prompt desde estilo predefinido
     */
    async function generateFromStyle() {
        if (!selectedStyle) {
            showFlash('warning', 'Selecciona un estilo antes de continuar.');
            return;
        }

        const language = document.getElementById('styleLanguage').value;
        const emoji    = document.getElementById('styleEmoji').value;

        const styleLabels = {
            formal   : 'formal y elegante, trato respetuoso y corporativo',
            warm     : 'cálido y familiar, cercano, usa el nombre del huésped',
            fun      : 'amigable y descomplicado, informal, usa emojis libremente',
            adventure: 'aventurero y activo, entusiasta, perfecto para ecoturismo',
            luxury   : 'lujoso y exclusivo, sofisticado, lenguaje refinado',
            local    : 'local y auténtico, resalta la cultura regional y el sabor propio',
        };

        const styleDesc = styleLabels[selectedStyle] ?? selectedStyle;

        setAiLoading('aiStyleLoading', true);
        document.getElementById('btnGenStyle').disabled = true;

        const result = await wizardAI('generate_prompt', {
            style            : `${styleDesc}, idioma: ${language}, emojis: ${emoji}`,
            hotel_description: '',
            chats            : '',
        });

        handlePromptResult(result, 'aiStyleLoading', 'btnGenStyle');
    }

    /**
     * Modo B: genera prompt desde descripción libre
     */
    async function generateFromDescription() {
        const desc = document.getElementById('hotelDescription').value.trim();

        if (desc.length < 30) {
            showFlash('warning', 'Escribe al menos 30 caracteres para obtener un buen resultado.');
            return;
        }

        setAiLoading('aiDescLoading', true);
        document.getElementById('btnGenDesc').disabled = true;

        const result = await wizardAI('generate_prompt', {
            style            : '',
            hotel_description: desc,
            chats            : '',
        });

        handlePromptResult(result, 'aiDescLoading', 'btnGenDesc');
    }

    /**
     * Modo C: genera prompt analizando chats reales
     */
    async function generateFromChats() {
        const chats = document.getElementById('realChats').value.trim();

        if (chats.length < 100) {
            showFlash('warning', 'Pega al menos un par de conversaciones para que la IA pueda analizar tu estilo.');
            return;
        }

        setAiLoading('aiChatsLoading', true);
        document.getElementById('btnGenChats').disabled = true;

        const result = await wizardAI('generate_prompt', {
            style            : '',
            hotel_description: '',
            chats            : chats,
        });

        handlePromptResult(result, 'aiChatsLoading', 'btnGenChats');
    }

    /**
     * Maneja el resultado de cualquier generación de prompt
     * Muestra el resultado en el textarea y lo sincroniza con el hidden input
     *
     * @param {object}  result      - respuesta del endpoint AI
     * @param {string}  loadingId   - ID del spinner a ocultar
     * @param {string}  btnId       - ID del botón a re-habilitar
     */
    function handlePromptResult(result, loadingId, btnId) {
        setAiLoading(loadingId, false);
        document.getElementById(btnId).disabled = false;

        if (result.success && result.text) {
            // Mostrar área de resultado
            const wrap = document.getElementById('promptResultWrap');
            wrap.style.display = 'block';

            // Animar la aparición
            wrap.style.opacity = '0';
            wrap.style.transition = 'opacity .4s';
            setTimeout(() => wrap.style.opacity = '1', 50);

            // Llenar el textarea visible y el hidden
            document.getElementById('system_instruction').value = result.text;
            document.getElementById('hiddenPrompt').value        = result.text;

            // Actualizar contador
            document.getElementById('promptCharCount').textContent =
                `${result.text.length} caracteres`;

            // Mostrar badge
            document.getElementById('aiGeneratedBadge').style.display = 'inline-block';

            // Scroll suave al resultado
            wrap.scrollIntoView({ behavior: 'smooth', block: 'start' });

            showFlash('success', '¡Prompt generado! Revísalo y edítalo si lo deseas.');
        } else {
            showFlash('danger', result.message || 'No se pudo generar el prompt. Intenta de nuevo.');
        }
    }

    /**
     * Limpia el prompt generado para volver a intentar
     */
    function clearPrompt() {
        document.getElementById('system_instruction').value = '';
        document.getElementById('hiddenPrompt').value        = '';
        document.getElementById('promptCharCount').textContent = '0 caracteres';
        document.getElementById('aiGeneratedBadge').style.display = 'none';
        document.getElementById('promptResultWrap').style.display  = 'none';
    }

    /**
     * Muestra/oculta el texto de ayuda del prompt
     */
    function togglePromptHelp() {
        const help = document.getElementById('promptHelp');
        help.style.display = help.style.display === 'none' ? 'block' : 'none';
    }

    /**
     * Copia el valor del textarea al hidden input antes de submitear
     */
    function submitStep5() {
        const promptText = document.getElementById('system_instruction')?.value ?? '';
        document.getElementById('hiddenPrompt').value = promptText;

        const btn     = document.getElementById('btnSubmit5');
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

        document.getElementById('formStep5').submit();
    }
</script>