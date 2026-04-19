<?php
/**
 * onboarding/steps/step7_whatsapp.php
 *
 * Paso 7: Vincular cuenta de WhatsApp Business vía Meta Embedded Signup.
 * Reutiliza el flujo del settings.php existente, adaptado al wizard.
 * Paso opcional — no bloquea el avance.
 */

$waConfigured = $whatsapp_configured ?? false;
?>

<!-- ── Card principal ────────────────────────────────────────────────────── -->
<div class="wizard-card">
    <div class="card-eyebrow">Paso 7 · Opcional</div>
    <h5>Conectar WhatsApp Business</h5>
    <p class="card-hint">
        Vincula tu número oficial de WhatsApp para que el asistente IA
        pueda atender a tus huéspedes automáticamente.
    </p>

    <!-- ── Estado: ya configurado ───────────────────────────────────────── -->
    <?php if ($waConfigured): ?>
        <div class="d-flex align-items-center gap-3 p-4 rounded-3 mb-4"
             style="background:#f0fdf4;border:2px solid #86efac">
            <div style="width:52px;height:52px;border-radius:50%;
                        background:#22c55e;display:flex;align-items:center;
                        justify-content:center;flex-shrink:0">
                <i class="bi bi-check-lg text-white"
                   style="font-size:1.5rem"></i>
            </div>
            <div>
                <div class="fw-bold" style="color:#15803d">
                    ¡WhatsApp conectado exitosamente!
                </div>
                <div class="text-muted" style="font-size:.83rem">
                    Tu asistente IA ya puede recibir y responder mensajes
                    de tus huéspedes.
                </div>
            </div>
        </div>

        <!-- ── Estado: no configurado ───────────────────────────────────────── -->
    <?php else: ?>

        <!-- Beneficios -->
        <div class="row g-3 mb-4">
            <?php
            $benefits = [
                ['icon' => 'bi-robot',        'color' => '#6366f1',
                    'title'=> 'IA 24/7',
                    'desc' => 'Tu asistente responde consultas de disponibilidad y precios automáticamente'],
                ['icon' => 'bi-whatsapp',     'color' => '#22c55e',
                    'title'=> 'Canal oficial',
                    'desc' => 'Usa la API oficial de Meta, sin riesgo de bloqueo de número'],
                ['icon' => 'bi-bell',         'color' => '#f59e0b',
                    'title'=> 'Notificaciones',
                    'desc' => 'Envía confirmaciones y recordatorios automáticos a huéspedes'],
            ];
            foreach ($benefits as $b):
                ?>
                <div class="col-md-4">
                    <div class="text-center p-3 rounded-3 h-100"
                         style="background:#f8faff;border:1px solid #e0e7ff">
                        <i class="bi <?= $b['icon'] ?>"
                           style="font-size:1.6rem;color:<?= $b['color'] ?>"></i>
                        <div class="fw-semibold mt-2 mb-1"
                             style="font-size:.85rem">
                            <?= $b['title'] ?>
                        </div>
                        <div class="text-muted" style="font-size:.75rem;
                             line-height:1.4">
                            <?= $b['desc'] ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ── Pasos del proceso ─────────────────────────────────────────── -->
        <div class="mb-4 p-3 rounded-3"
             style="background:#fafafa;border:1px solid #e2e8f0">
            <p class="fw-semibold mb-3" style="font-size:.85rem">
                ¿Cómo funciona?
            </p>
            <div class="d-flex flex-column gap-2">
                <?php
                $steps = [
                    'Haz clic en "Conectar WhatsApp" — se abrirá una ventana de Meta',
                    'Inicia sesión con tu cuenta de Facebook Business',
                    'Selecciona o crea tu cuenta de WhatsApp Business',
                    'Confirma los permisos — el sistema se configura automáticamente',
                ];
                foreach ($steps as $i => $stepText):
                    ?>
                    <div class="d-flex align-items-start gap-3">
                        <div style="width:24px;height:24px;border-radius:50%;
                                    background:#6366f1;color:#fff;
                                    display:flex;align-items:center;
                                    justify-content:center;font-size:.72rem;
                                    font-weight:700;flex-shrink:0;margin-top:.1rem">
                            <?= $i + 1 ?>
                        </div>
                        <span style="font-size:.83rem;color:#374151;
                                     padding-top:.1rem">
                            <?= esc($stepText) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ── Botón principal de conexión ──────────────────────────────── -->
        <div id="connectSection">

            <button id="btnConnectWA"
                    type="button"
                    onclick="launchWhatsAppSignup()"
                    style="display:flex;align-items:center;justify-content:center;
                           gap:.65rem;width:100%;padding:1rem;
                           background:#25D366;color:#fff;border:none;
                           border-radius:12px;font-size:1rem;font-weight:700;
                           cursor:pointer;transition:background .2s,transform .1s">
                <i class="bi bi-whatsapp" style="font-size:1.3rem"></i>
                Conectar WhatsApp Business
            </button>

            <!-- Estado del proceso de conexión -->
            <div id="waStatusWrap" style="display:none;margin-top:1rem">
                <div id="waStatusConnecting"
                     class="d-flex align-items-center gap-2 p-3 rounded-3"
                     style="background:#f0f4ff;border:1px solid #c7d2fe;display:none!important">
                    <span class="spinner-border spinner-border-sm"
                          style="color:#6366f1"></span>
                    <span style="font-size:.85rem;color:#4338ca">
                        Verificando conexión con Meta...
                    </span>
                </div>

                <div id="waStatusSuccess"
                     class="d-flex align-items-center gap-2 p-3 rounded-3"
                     style="background:#f0fdf4;border:1px solid #86efac;display:none!important">
                    <i class="bi bi-check-circle-fill"
                       style="color:#22c55e;font-size:1.1rem"></i>
                    <span style="font-size:.85rem;color:#15803d;font-weight:600">
                        ¡Conectado! Tu número de WhatsApp está listo.
                    </span>
                </div>

                <div id="waStatusError"
                     class="d-flex align-items-center gap-2 p-3 rounded-3"
                     style="background:#fff1f2;border:1px solid #fecdd3;display:none!important">
                    <i class="bi bi-exclamation-circle-fill"
                       style="color:#e11d48;font-size:1.1rem"></i>
                    <div>
                        <span style="font-size:.85rem;color:#be123c;
                                     font-weight:600;display:block">
                            Error al conectar
                        </span>
                        <span id="waErrorMsg"
                              style="font-size:.78rem;color:#9f1239"></span>
                    </div>
                </div>
            </div>

        </div>

    <?php endif; ?>

    <!-- ── Formulario oculto para marcar el paso ─────────────────────────── -->
    <form action="/onboarding/step/7" method="POST" id="formStep7">
        <?= csrf_field() ?>
    </form>

    <!-- ── Navegación ────────────────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center pt-4 mt-3
                border-top" style="border-color:#f1f5f9!important">
        <a href="/onboarding/step/6" class="btn-wiz-secondary">
            <i class="bi bi-arrow-left me-1"></i> Anterior
        </a>
        <div class="d-flex align-items-center gap-3">
            <?php if (!$waConfigured): ?>
                <button type="button" class="btn-wiz-skip"
                        onclick="skipStep(<?= $currentStep ?>)">
                    Omitir por ahora
                </button>
            <?php endif; ?>
            <button type="button"
                    class="btn-wiz-primary"
                    id="btnNext7"
                    onclick="advanceStep7()">
                <?= $waConfigured ? 'Continuar' : 'Continuar sin WhatsApp' ?>
                <i class="bi bi-arrow-right ms-2"></i>
            </button>
        </div>
    </div>
</div>

<!-- ── Tip ──────────────────────────────────────────────────────────────── -->
<?php if (!$waConfigured): ?>
    <div class="d-flex align-items-start gap-3 p-3 rounded-3"
         style="background:#f0f9ff;border:1px solid #bae6fd">
        <i class="bi bi-clock-history mt-1"
           style="color:#0284c7;font-size:1.1rem"></i>
        <div>
            <strong style="font-size:.85rem;color:#0c4a6e">
                ¿No tienes lista tu cuenta de Meta?
            </strong>
            <p class="mb-0 text-muted" style="font-size:.82rem">
                Puedes completar el onboarding ahora y conectar WhatsApp después
                desde <strong>Configuración → WhatsApp</strong>. El asistente IA
                quedará listo en cuanto vincules el número.
            </p>
        </div>
    </div>
<?php endif; ?>

<!-- ── SDK de Facebook ───────────────────────────────────────────────────── -->
<script>
    window.fbAsyncInit = function () {
        FB.init({
            appId  : '871557255662274',
            cookie : true,
            xfbml  : true,
            version: 'v19.0'
        });
    };

    (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js      = d.createElement(s);
        js.id   = id;
        js.src  = 'https://connect.facebook.net/es_LA/sdk.js';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
</script>

<script>
    /**
     * IDs capturados por el evento postMessage de Meta
     * Se usan como fallback si no vienen en authResponse
     */
    window._wabaId        = null;
    window._phoneNumberId = null;

    // ── Listener de eventos Meta (postMessage) ────────────────────────────────
    window.addEventListener('message', function (event) {
        if (event.origin !== 'https://www.facebook.com') return;

        try {
            const data = JSON.parse(event.data);
            if (data.type !== 'WA_EMBEDDED_SIGNUP') return;

            if (data.event === 'FINISH') {
                // Guardar IDs para usarlos en saveWhatsAppConfig
                window._wabaId        = data.data.waba_id;
                window._phoneNumberId = data.data.phone_number_id;
                console.log('[WA/Wizard] FINISH recibido — waba_id:', window._wabaId);
            }

            if (data.event === 'CANCEL') {
                console.log('[WA/Wizard] Usuario canceló en paso:', data.data.current_step);
            }
        } catch (e) {}
    });

    // ── Flujo Meta Embedded Signup ────────────────────────────────────────────

    /**
     * Lanza el popup de Meta para vincular WhatsApp Business.
     * Adaptado del settings.php existente al contexto del wizard.
     */
    function launchWhatsAppSignup() {
        console.log('[WA/Wizard] Iniciando Embedded Signup...');

        FB.login(function (response) {
            console.log('[WA/Wizard] Respuesta Meta:', JSON.stringify(response));

            if (!response.authResponse) {
                console.warn('[WA/Wizard] Usuario canceló o no autorizó.');
                return;
            }

            const code        = response.authResponse.code;
            const accessToken = response.authResponse.accessToken;

            // Intentar extraer waba_id y phone_number_id del authResponse
            let wabaId        = null;
            let phoneNumberId = null;

            try {
                const setup = response.authResponse?.extras?.setup;
                if (setup) {
                    wabaId        = setup.waba_id         || null;
                    phoneNumberId = setup.phone_number_id || null;
                }

                if (!wabaId && response.authResponse?.extras) {
                    wabaId        = response.authResponse.extras.waba_id         || null;
                    phoneNumberId = response.authResponse.extras.phone_number_id || null;
                }
            } catch (e) {
                console.warn('[WA/Wizard] No se pudieron extraer extras:', e);
            }

            // Fallback a los IDs capturados por postMessage
            wabaId        = wabaId        || window._wabaId        || '';
            phoneNumberId = phoneNumberId || window._phoneNumberId || '';

            console.log('[WA/Wizard] Datos extraídos → waba_id:', wabaId,
                '| phone_number_id:', phoneNumberId);

            if (code || accessToken) {
                saveWhatsAppConfig(code || accessToken, wabaId, phoneNumberId);
            } else {
                showWaStatus('error', 'No se recibió código de autorización de Meta.');
            }

        }, {
            config_id                       : '885041491176064',
            response_type                   : 'code',
            override_default_response_type  : true,
            extras: {
                feature           : 'coexistence',
                sessionInfoVersion: 3
            }
        });
    }

    /**
     * Envía el token y los IDs al backend para guardar la configuración
     *
     * @param {string}      codeOrToken
     * @param {string|null} wabaId
     * @param {string|null} phoneNumberId
     */
    async function saveWhatsAppConfig(codeOrToken, wabaId, phoneNumberId) {
        // Deshabilitar botón y mostrar spinner
        const btn       = document.getElementById('btnConnectWA');
        btn.disabled    = true;
        btn.innerHTML   = '<span class="spinner-border spinner-border-sm me-2"></span>Conectando...';

        showWaStatus('connecting');

        try {
            const payload = new URLSearchParams({
                access_token   : codeOrToken,
                waba_id        : wabaId        || '',
                phone_number_id: phoneNumberId || '',
            });

            const res  = await fetch('/whatsapp/save_config', {
                method : 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body   : payload,
            });

            const data = await res.json();
            console.log('[WA/Wizard] Respuesta backend:', data);

            if (data.success) {
                showWaStatus('success');

                // Actualizar botón de avance
                const btnNext = document.getElementById('btnNext7');
                btnNext.innerHTML = 'Continuar <i class="bi bi-arrow-right ms-2"></i>';

                // Avanzar automáticamente tras 2 segundos
                setTimeout(() => advanceStep7(), 2000);

            } else {
                showWaStatus('error', data.message || 'Error al vincular WhatsApp.');
                btn.disabled  = false;
                btn.innerHTML = '<i class="bi bi-whatsapp" style="font-size:1.3rem"></i> Intentar de nuevo';
            }

        } catch (err) {
            console.error('[WA/Wizard] Error de red:', err);
            showWaStatus('error', 'Error de conexión. Intenta de nuevo.');
            btn.disabled  = false;
            btn.innerHTML = '<i class="bi bi-whatsapp" style="font-size:1.3rem"></i> Intentar de nuevo';
        }
    }

    /**
     * Controla qué estado de conexión mostrar
     * @param {string}      state   - 'connecting' | 'success' | 'error'
     * @param {string|null} message - mensaje de error (solo para state='error')
     */
    function showWaStatus(state, message = null) {
        const wrap = document.getElementById('waStatusWrap');
        wrap.style.display = 'block';

        // Ocultar todos los estados primero
        ['Connecting', 'Success', 'Error'].forEach(s => {
            document.getElementById(`waStatus${s}`).style.display = 'none';
        });

        // Mostrar el estado correcto
        const target = document.getElementById(`waStatus${capitalize(state)}`);
        if (target) target.style.display = 'flex';

        // Si es error, mostrar el mensaje
        if (state === 'error' && message) {
            const errMsg = document.getElementById('waErrorMsg');
            if (errMsg) errMsg.textContent = message;
        }
    }

    /**
     * Avanza al paso 8 enviando el form (solo marca el paso como visitado)
     */
    function advanceStep7() {
        document.getElementById('formStep7').submit();
    }

    /**
     * Capitaliza la primera letra de un string
     * @param {string} s
     * @returns {string}
     */
    function capitalize(s) {
        return s.charAt(0).toUpperCase() + s.slice(1);
    }
</script>