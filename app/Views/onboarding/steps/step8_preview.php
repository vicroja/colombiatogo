<?php
/**
 * onboarding/steps/step8_preview.php
 *
 * Paso 8: Vista previa del sitio web y publicación.
 * Muestra un resumen de todo lo configurado y permite
 * editar el hero/about antes de publicar.
 */

$website = $website ?? [];
$unit    = $unit    ?? null;

// Datos actuales del sitio web para pre-poblar
$heroTitle    = $website['hero_title']    ?? '';
$heroSubtitle = $website['hero_subtitle'] ?? '';
$aboutText    = $website['about_text']    ?? '';
$isPublished  = $website['is_published']  ?? 0;
?>

<!-- ── Card: Resumen de configuración ────────────────────────────────────── -->
<div class="wizard-card">
    <div class="card-eyebrow">Paso 8 · Último paso</div>
    <div class="d-flex align-items-center gap-2 mb-1">
        <h5 class="mb-0">¡Casi listo! Revisa tu configuración</h5>
        <span class="ai-badge"><i class="bi bi-stars"></i> Con IA</span>
    </div>
    <p class="card-hint">
        Verifica que todo esté correcto y configura tu sitio web público.
        Puedes editar cualquier sección después desde el panel de control.
    </p>

    <!-- ── Checklist de lo configurado ──────────────────────────────────── -->
    <div class="summary-grid mb-4">
        <?php
        // Construir checklist dinámico según lo que existe
        $checks = [
            [
                'icon'   => 'bi-building',
                'label'  => 'Identidad del hotel',
                'value'  => $tenant['name'] ?? null,
                'done'   => !empty($tenant['name']) && !empty($tenant['phone']),
                'link'   => '/onboarding/step/1',
            ],
            [
                'icon'   => 'bi-images',
                'label'  => 'Logo y fotos',
                'value'  => !empty($tenant['logo_path']) ? 'Logo cargado' : 'Sin logo aún',
                'done'   => !empty($tenant['logo_path']),
                'link'   => '/onboarding/step/2',
                'optional'=> true,
            ],
            [
                'icon'   => 'bi-door-open',
                'label'  => 'Primera unidad',
                'value'  => $unit['name'] ?? null,
                'done'   => !empty($unit),
                'link'   => '/onboarding/step/3',
            ],
            [
                'icon'   => 'bi-currency-dollar',
                'label'  => 'Plan tarifario',
                'value'  => !empty($unit) ? 'Configurado' : null,
                'done'   => !empty($unit),
                'link'   => '/onboarding/step/4',
            ],
            [
                'icon'   => 'bi-robot',
                'label'  => 'Asistente IA',
                'value'  => isset($stepData['existing_prompt']) ? 'Prompt configurado' : 'Sin configurar',
                'done'   => isset($stepData['existing_prompt']),
                'link'   => '/onboarding/step/5',
                'optional'=> true,
            ],
            [
                'icon'   => 'bi-whatsapp',
                'label'  => 'WhatsApp Business',
                'value'  => ($settings['whatsapp_phone_number_id'] ?? null)
                    ? 'Conectado' : 'Sin conectar',
                'done'   => !empty($settings['whatsapp_phone_number_id'] ?? null),
                'link'   => '/onboarding/step/7',
                'optional'=> true,
            ],
        ];
        ?>

        <div class="row g-2">
            <?php foreach ($checks as $check): ?>
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3"
                         style="background:<?= $check['done'] ? '#f0fdf4' : '#fafafa' ?>;
                             border:1px solid <?= $check['done'] ? '#86efac' : '#e2e8f0' ?>">

                        <!-- Icono de estado -->
                        <div style="width:36px;height:36px;border-radius:50%;flex-shrink:0;
                            background:<?= $check['done'] ? '#22c55e' : '#e2e8f0' ?>;
                            display:flex;align-items:center;justify-content:center">
                            <?php if ($check['done']): ?>
                                <i class="bi bi-check-lg text-white"
                                   style="font-size:.9rem"></i>
                            <?php else: ?>
                                <i class="bi <?= $check['icon'] ?>"
                                   style="font-size:.85rem;color:#94a3b8"></i>
                            <?php endif; ?>
                        </div>

                        <!-- Info -->
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-semibold" style="font-size:.83rem;
                                 color:#0f172a">
                                <?= esc($check['label']) ?>
                                <?php if ($check['optional'] ?? false): ?>
                                    <span class="text-muted fw-normal"
                                          style="font-size:.72rem">(opcional)</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($check['value']): ?>
                                <div class="text-truncate"
                                     style="font-size:.75rem;
                                            color:<?= $check['done'] ? '#16a34a' : '#94a3b8' ?>">
                                    <?= esc($check['value']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Link editar -->
                        <a href="<?= $check['link'] ?>"
                           style="font-size:.72rem;color:#6366f1;
                                  white-space:nowrap;text-decoration:none">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- ── Card: Sitio web público ───────────────────────────────────────────── -->
<div class="wizard-card">
    <div class="card-eyebrow">Sitio web público</div>
    <h5>Personaliza tu página de reservas</h5>
    <p class="card-hint">
        Esta es la página que verán los huéspedes al buscar tu hotel.
        La IA puede generar el texto basándose en todo lo que configuraste.
    </p>

    <form action="/onboarding/step/8" method="POST" id="formStep8">
        <?= csrf_field() ?>

        <!-- ── Hero title ────────────────────────────────────────────────── -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-semibold mb-0" for="hero_title">
                    Título principal del sitio
                </label>
                <div class="d-flex align-items-center gap-2">
                    <div class="ai-loading" id="aiHeroLoading">
                        <span class="spinner-border spinner-border-sm"
                              style="color:#6366f1"></span>
                        <span style="font-size:.8rem;color:#6366f1">
                            Generando...
                        </span>
                    </div>
                    <button type="button" class="btn-ai"
                            id="btnAiHero" onclick="generateHero()">
                        <i class="bi bi-stars"></i>
                        Generar con IA
                    </button>
                </div>
            </div>
            <input
                type="text"
                class="form-control form-control-lg"
                id="hero_title"
                name="hero_title"
                value="<?= esc($heroTitle) ?>"
                placeholder="Ej: Tu refugio perfecto en el corazón de la naturaleza"
                maxlength="150"
                oninput="updateWebPreview()"
            >
        </div>

        <!-- ── Hero subtitle ─────────────────────────────────────────────── -->
        <div class="mb-4">
            <label class="form-label fw-semibold" for="hero_subtitle">
                Subtítulo
            </label>
            <input
                type="text"
                class="form-control"
                id="hero_subtitle"
                name="hero_subtitle"
                value="<?= esc($heroSubtitle) ?>"
                placeholder="Ej: Reserva directo y obtén el mejor precio garantizado"
                maxlength="255"
                oninput="updateWebPreview()"
            >
        </div>

        <!-- ── About text ────────────────────────────────────────────────── -->
        <div class="mb-4">
            <label class="form-label fw-semibold" for="about_text">
                Descripción del hotel
            </label>
            <textarea
                class="form-control"
                id="about_text"
                name="about_text"
                rows="4"
                placeholder="Cuéntale a tus huéspedes qué hace especial a tu hotel..."
                maxlength="1500"
                oninput="updateWebPreview()"
            ><?= esc($aboutText) ?></textarea>
        </div>

        <!-- ── Vista previa del hero ─────────────────────────────────────── -->
        <div class="mb-4">
            <p class="fw-semibold small mb-2">
                <i class="bi bi-eye me-1 text-primary"></i>
                Vista previa del hero
            </p>

            <div id="webPreview"
                 style="border-radius:14px;overflow:hidden;
                        border:1px solid #e2e8f0;background:#0f172a;
                        min-height:180px;position:relative;
                        display:flex;align-items:center;justify-content:center">

                <!-- Imagen de fondo difuminada si hay logo -->
                <?php if (!empty($tenant['logo_path'])): ?>
                    <div style="position:absolute;inset:0;
                                background:linear-gradient(135deg,#0f172a 60%,#1e3a5f);
                                opacity:.95"></div>
                <?php else: ?>
                    <div style="position:absolute;inset:0;
                                background:linear-gradient(135deg,#0f172a,#1e293b)"></div>
                <?php endif; ?>

                <!-- Contenido del hero -->
                <div style="position:relative;text-align:center;
                            padding:2rem;max-width:500px">
                    <?php if (!empty($tenant['logo_path'])): ?>
                        <img src="<?= base_url($tenant['logo_path']) ?>"
                             style="height:48px;margin-bottom:1rem;
                                    border-radius:8px;opacity:.95"
                             alt="Logo">
                    <?php endif; ?>
                    <h3 id="previewTitle"
                        style="color:#fff;font-size:1.2rem;
                               font-weight:800;margin-bottom:.5rem;
                               line-height:1.3">
                        <?= $heroTitle ?: esc($tenant['name'] ?? 'Tu hotel') ?>
                    </h3>
                    <p id="previewSubtitle"
                       style="color:#94a3b8;font-size:.85rem;margin-bottom:1.25rem">
                        <?= $heroSubtitle ?: 'Tu subtítulo aquí' ?>
                    </p>
                    <div style="display:inline-block;background:#6366f1;
                                color:#fff;padding:.5rem 1.5rem;
                                border-radius:99px;font-size:.8rem;
                                font-weight:700">
                        Reservar ahora
                    </div>
                </div>
            </div>
        </div>

        <hr style="border-color:#f1f5f9">

        <!-- ── Toggle publicación ────────────────────────────────────────── -->
        <div class="p-4 rounded-3 mb-2"
             style="background:#f8faff;border:2px solid #e0e7ff">
            <div class="d-flex align-items-start gap-3">
                <div class="form-check form-switch mt-1">
                    <input class="form-check-input" type="checkbox"
                           id="publish" name="publish" value="1"
                           style="width:2.5rem;height:1.25rem"
                        <?= $isPublished ? 'checked' : '' ?>
                           onchange="togglePublishInfo(this.checked)">
                </div>
                <div>
                    <label class="form-check-label fw-bold"
                           for="publish" style="font-size:.95rem">
                        Publicar sitio web ahora
                    </label>
                    <p class="mb-0 text-muted" style="font-size:.82rem">
                        Tu página de reservas estará visible en
                        <code style="background:#e0e7ff;padding:.1rem .4rem;
                                     border-radius:4px;font-size:.8rem">
                            <?= base_url('book/' . ($tenant['slug'] ?? 'mi-hotel')) ?>
                        </code>
                    </p>
                </div>
            </div>

            <!-- Info adicional al activar publicación -->
            <div id="publishInfo"
                 style="display:<?= $isPublished ? 'block' : 'none' ?>;
                     margin-top:.75rem;padding-top:.75rem;
                     border-top:1px solid #e0e7ff">
                <div class="d-flex align-items-center gap-2"
                     style="font-size:.82rem;color:#4338ca">
                    <i class="bi bi-globe2"></i>
                    <span>
                        Los huéspedes podrán encontrar tu hotel y hacer
                        reservas directamente desde tu sitio web.
                    </span>
                </div>
            </div>
        </div>

        <!-- ── Navegación ────────────────────────────────────────────────── -->
        <div class="d-flex justify-content-between align-items-center pt-4 mt-2
                    border-top" style="border-color:#f1f5f9!important">
            <a href="/onboarding/step/7" class="btn-wiz-secondary">
                <i class="bi bi-arrow-left me-1"></i> Anterior
            </a>
            <button type="submit" class="btn-wiz-primary btn-lg"
                    id="btnSubmit8"
                    style="background:linear-gradient(135deg,#6366f1,#8b5cf6);
                           padding:.8rem 2rem;font-size:1rem">
                <i class="bi bi-rocket-takeoff me-2"></i>
                Completar configuración
            </button>
        </div>

    </form>
</div>

<script>
    /**
     * Actualiza la vista previa del hero en tiempo real
     */
    function updateWebPreview() {
        const title    = document.getElementById('hero_title').value.trim();
        const subtitle = document.getElementById('hero_subtitle').value.trim();

        document.getElementById('previewTitle').textContent =
            title    || '<?= esc($tenant['name'] ?? 'Tu hotel') ?>';
        document.getElementById('previewSubtitle').textContent =
            subtitle || 'Tu subtítulo aquí';
    }

    /**
     * Muestra u oculta el bloque de info de publicación
     * @param {boolean} checked
     */
    function togglePublishInfo(checked) {
        document.getElementById('publishInfo').style.display =
            checked ? 'block' : 'none';
    }

    /**
     * Genera hero title y subtitle con IA basándose
     * en el nombre del hotel, ciudad y about_text actual
     */
    async function generateHero() {
        const about   = document.getElementById('about_text').value.trim();
        const btn     = document.getElementById('btnAiHero');

        btn.disabled  = true;
        setAiLoading('aiHeroLoading', true);

        try {
            const result = await wizardAI('generate_hero', { about });

            if (result.success && result.data) {
                const { hero_title, hero_subtitle } = result.data;

                // Animar el llenado de campos
                await typewriterFill('hero_title',    hero_title    ?? '');
                await typewriterFill('hero_subtitle', hero_subtitle ?? '');

                updateWebPreview();
                showFlash('success', 'Textos del sitio generados. Puedes editarlos.');
            } else {
                showFlash('danger', result.message || 'No se pudo generar el texto.');
            }
        } catch (err) {
            console.error('[AI/Hero] Error:', err);
            showFlash('danger', 'Error de conexión con el servicio de IA.');
        } finally {
            btn.disabled = false;
            setAiLoading('aiHeroLoading', false);
        }
    }

    /**
     * Efecto typewriter para llenar un campo de texto suavemente
     * @param {string} fieldId  - ID del input/textarea
     * @param {string} text     - texto a escribir
     * @param {number} delay    - delay entre caracteres en ms
     * @returns {Promise<void>}
     */
    function typewriterFill(fieldId, text, delay = 18) {
        return new Promise(resolve => {
            const field = document.getElementById(fieldId);
            field.value = '';
            let i       = 0;

            const interval = setInterval(() => {
                field.value += text[i];
                i++;
                if (i >= text.length) {
                    clearInterval(interval);
                    resolve();
                }
            }, delay);
        });
    }

    /**
     * Submit con loader y mensaje de cierre
     */
    document.getElementById('formStep8').addEventListener('submit', function () {
        const btn     = document.getElementById('btnSubmit8');
        btn.disabled  = true;
        btn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>' +
            'Finalizando configuración...';
    });
</script>