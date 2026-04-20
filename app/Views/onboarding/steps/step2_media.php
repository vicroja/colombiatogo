<?php
/**
 * onboarding/steps/step2_media.php
 *
 * Paso 2: Subida de logo y fotos del hotel.
 * Paso opcional. Usa preview inmediato vía FileReader JS.
 */

$logoPath  = $logo  ?? null;
$photos    = $photos ?? [];
?>

<!-- ── Logo del hotel ────────────────────────────────────────────────────── -->
<div class="wizard-card">
    <div class="card-eyebrow">Paso 2 · Opcional</div>
    <h5>Logo del hotel</h5>
    <p class="card-hint">
        Aparecerá en el sidebar, correos de confirmación y tu sitio web.
        Formato recomendado: PNG o SVG con fondo transparente, mínimo 200×200px.
    </p>

    <form action="/onboarding/step/2" method="POST"
          enctype="multipart/form-data" id="formStep2">
        <?= csrf_field() ?>


        <!-- ── Zona de logo ─────────────────────────────────────────────── -->
        <div class="d-flex align-items-start gap-4 mb-5 flex-wrap">

            <!-- Preview actual -->
            <div id="logoPreviewWrap"
                 style="width:110px;height:110px;border-radius:14px;
                border:2px dashed #c7d2fe;background:#f0f4ff;
                display:flex;align-items:center;justify-content:center;
                overflow:hidden;flex-shrink:0;cursor:pointer;"
                 onclick="document.getElementById('logoInput').click()">
                <?php if ($logoPath): ?>
                    <img id="logoPreview"
                         src="<?= base_url($logoPath) ?>"
                         style="width:100%;height:100%;object-fit:cover">
                <?php else: ?>
                    <div id="logoPlaceholder"
                         style="text-align:center;color:#6366f1">
                        <i class="bi bi-building"
                           style="font-size:1.8rem"></i>
                        <div style="font-size:.7rem;margin-top:.25rem">
                            Tu logo
                        </div>
                    </div>
                    <img id="logoPreview" src=""
                         style="width:100%;height:100%;object-fit:cover;display:none">
                <?php endif; ?>
            </div>

            <!-- Acciones -->
            <div class="flex-grow-1">
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <button type="button"
                            class="btn btn-outline-primary btn-sm"
                            onclick="document.getElementById('logoInput').click()">
                        <i class="bi bi-upload me-1"></i>
                        Subir mi logo
                    </button>
                    <button type="button"
                            class="btn-ai btn-sm"
                            id="btnAiLogo"
                            onclick="openLogoGenerator()">
                        <i class="bi bi-stars me-1"></i>
                        Crear logo con IA
                    </button>
                </div>
                <p class="text-muted mb-0" style="font-size:.78rem">
                    PNG, JPG o WEBP · Máx. 2MB · Fondo transparente recomendado
                </p>
                <?php if ($logoPath): ?>
                    <p class="mb-0 mt-1" style="font-size:.78rem;color:#22c55e">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        Logo actual cargado
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <input type="file" id="logoInput" name="logo"
               accept="image/png,image/jpeg,image/webp,image/svg+xml"
               class="d-none">

        <!-- ── Modal generador de logos ─────────────────────────────────── -->
        <div id="logoGenModal"
             style="display:none;position:fixed;inset:0;z-index:9999;
            background:rgba(15,23,42,.6);
            align-items:center;justify-content:center;padding:1rem">
            <div style="background:#fff;border-radius:20px;width:100%;
                max-width:640px;max-height:90vh;overflow-y:auto;
                padding:2rem;position:relative">

                <!-- Cerrar -->
                <button onclick="closeLogoGenerator()"
                        style="position:absolute;top:1rem;right:1rem;
                       background:none;border:none;font-size:1.3rem;
                       color:#94a3b8;cursor:pointer">
                    <i class="bi bi-x-lg"></i>
                </button>

                <div class="d-flex align-items-center gap-2 mb-1">
                    <h5 class="mb-0">Crear logo con IA</h5>
                    <span class="ai-badge">
                <i class="bi bi-stars"></i> Gemini
            </span>
                </div>
                <p style="font-size:.83rem;color:#64748b;margin-bottom:1.5rem">
                    La IA generará 3 opciones de logo para
                    <strong><?= esc($tenant['name'] ?? 'tu hotel') ?></strong>.
                    Elige la que más te guste.
                </p>

                <!-- Selector de estilo -->
                <div class="mb-3">
                    <label class="form-label fw-semibold small">
                        Estilo de logo
                    </label>
                    <div class="d-flex gap-2 flex-wrap">
                        <label class="style-pill active" data-style="both">
                            <input type="radio" name="logo_style"
                                   value="both" checked class="d-none">
                            Ícono + Texto
                        </label>
                        <label class="style-pill" data-style="wordmark">
                            <input type="radio" name="logo_style"
                                   value="wordmark" class="d-none">
                            Solo texto
                        </label>
                        <label class="style-pill" data-style="icon">
                            <input type="radio" name="logo_style"
                                   value="icon" class="d-none">
                            Solo ícono
                        </label>
                    </div>
                </div>

                <!-- Botón generar -->
                <button type="button" class="btn-ai w-100 mb-3"
                        id="btnGenLogos" onclick="generateLogos()">
                    <i class="bi bi-stars me-1"></i>
                    Generar 3 opciones
                </button>

                <!-- Loading state -->
                <div id="logoGenLoading"
                     style="display:none;text-align:center;padding:2rem">
                    <div class="spinner-border"
                         style="color:#6366f1;width:2.5rem;height:2.5rem">
                    </div>
                    <p style="margin-top:1rem;font-size:.85rem;color:#6366f1">
                        Generando opciones de logo...<br>
                        <span style="font-size:.75rem;color:#94a3b8">
                    Esto puede tomar 20-40 segundos
                </span>
                    </p>
                </div>

                <!-- Grid de opciones -->
                <div id="logoOptionsGrid"
                     style="display:none;
                    grid-template-columns:repeat(3,1fr);gap:1rem">
                </div>

                <!-- Botón usar seleccionado -->
                <div id="logoSelectActions"
                     style="display:none;margin-top:1.25rem">
                    <button type="button"
                            class="btn-wiz-primary w-100"
                            id="btnUseLogo"
                            onclick="useSelectedLogo()">
                        <i class="bi bi-check-lg me-1"></i>
                        Usar este logo
                    </button>
                    <button type="button"
                            class="btn-wiz-skip w-100 mt-2"
                            onclick="generateLogos()">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        Regenerar opciones
                    </button>
                </div>

                <!-- Error state -->
                <div id="logoGenError"
                     style="display:none"
                     class="alert alert-danger mt-2">
                </div>
            </div>
        </div>

        <style>
            .style-pill {
                display      : inline-flex;
                align-items  : center;
                padding      : .35rem .9rem;
                border-radius: 99px;
                border       : 1.5px solid #e2e8f0;
                font-size    : .8rem;
                cursor       : pointer;
                font-weight  : 500;
                color        : #64748b;
                transition   : all .15s;
                user-select  : none;
            }
            .style-pill.active {
                border-color : #6366f1;
                background   : #f0f4ff;
                color        : #4338ca;
            }
            .logo-option {
                border        : 2px solid #e2e8f0;
                border-radius : 12px;
                padding       : .75rem;
                cursor        : pointer;
                text-align    : center;
                transition    : all .2s;
                background    : #fafafa;
                position      : relative;
            }
            .logo-option:hover  { border-color: #a5b4fc }
            .logo-option.selected {
                border-color : #6366f1;
                background   : #f0f4ff;
            }
            .logo-option img {
                width        : 100%;
                height       : 100px;
                object-fit   : contain;
                border-radius: 6px;
            }
            .logo-option .opt-label {
                font-size    : .72rem;
                color        : #64748b;
                margin-top   : .4rem;
            }
            .logo-option .opt-check {
                position     : absolute;
                top          : 8px;
                right        : 8px;
                width        : 20px;
                height       : 20px;
                border-radius: 50%;
                background   : #6366f1;
                color        : #fff;
                font-size    : .7rem;
                display      : none;
                align-items  : center;
                justify-content: center;
            }
            .logo-option.selected .opt-check { display: flex }
        </style>

        <script>
            // ── Estado del generador de logos ─────────────────────────────────
            let selectedLogoBase64   = null;
            let selectedLogoMimeType = 'image/png';

            // ── Abrir / cerrar modal ──────────────────────────────────────────
            function openLogoGenerator() {
                const modal = document.getElementById('logoGenModal');
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }

            function closeLogoGenerator() {
                const modal = document.getElementById('logoGenModal');
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }

            // Cerrar al hacer clic fuera del panel
            document.getElementById('logoGenModal')
                .addEventListener('click', function (e) {
                    if (e.target === this) closeLogoGenerator();
                });

            // Selector de estilo — pills
            document.querySelectorAll('.style-pill').forEach(pill => {
                pill.addEventListener('click', function () {
                    document.querySelectorAll('.style-pill')
                        .forEach(p => p.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // ── Generar logos ─────────────────────────────────────────────────
            async function generateLogos() {
                const style = document.querySelector(
                    'input[name="logo_style"]:checked')?.value ?? 'both';

                // Reset UI
                document.getElementById('logoOptionsGrid').style.display  = 'none';
                document.getElementById('logoSelectActions').style.display= 'none';
                document.getElementById('logoGenError').style.display     = 'none';
                document.getElementById('logoGenLoading').style.display   = 'block';
                document.getElementById('btnGenLogos').disabled           = true;

                selectedLogoBase64 = null;

                try {
                    const result = await wizardAI('generate_logo', { style });

                    document.getElementById('logoGenLoading').style.display = 'none';
                    document.getElementById('btnGenLogos').disabled         = false;

                    if (result.success && result.logos?.length > 0) {
                        renderLogoOptions(result.logos);
                    } else {
                        showLogoError(result.message ||
                            'No se pudieron generar logos. Intenta de nuevo.');
                    }
                } catch (err) {
                    console.error('[AI/Logo]', err);
                    document.getElementById('logoGenLoading').style.display = 'none';
                    document.getElementById('btnGenLogos').disabled         = false;
                    showLogoError('Error de conexión. Intenta de nuevo.');
                }
            }

            // ── Renderizar las 3 opciones ─────────────────────────────────────
            function renderLogoOptions(logos) {
                const grid = document.getElementById('logoOptionsGrid');
                grid.innerHTML = '';

                logos.forEach((logo, i) => {
                    const div       = document.createElement('div');
                    div.className   = 'logo-option';
                    div.dataset.idx = i;
                    div.innerHTML   = `
            <div class="opt-check"><i class="bi bi-check-lg"></i></div>
            <img src="data:${logo.mimeType};base64,${logo.base64}"
                 alt="Opción ${i + 1}">
            <div class="opt-label">Opción ${i + 1}</div>`;

                    div.addEventListener('click', () => selectLogoOption(div, logo));
                    grid.appendChild(div);
                });

                grid.style.display = 'grid';
                document.getElementById('logoSelectActions').style.display = 'block';
            }

            // ── Seleccionar una opción ────────────────────────────────────────
            function selectLogoOption(el, logo) {
                document.querySelectorAll('.logo-option')
                    .forEach(o => o.classList.remove('selected'));
                el.classList.add('selected');
                selectedLogoBase64   = logo.base64;
                selectedLogoMimeType = logo.mimeType;
            }

            // ── Usar el logo seleccionado ─────────────────────────────────────
            function useSelectedLogo() {
                if (!selectedLogoBase64) {
                    showFlash('warning', 'Selecciona una de las opciones primero.');
                    return;
                }

                // Mostrar en el preview
                const img         = document.getElementById('logoPreview');
                const placeholder = document.getElementById('logoPlaceholder');
                const dataUrl     = `data:${selectedLogoMimeType};base64,${selectedLogoBase64}`;

                img.src           = dataUrl;
                img.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';

                // Convertir base64 a File y asignarlo al input[file]
                // para que se suba al guardar el formulario
                fetch(dataUrl)
                    .then(r  => r.blob())
                    .then(blob => {
                        const ext  = selectedLogoMimeType.split('/')[1] || 'png';
                        const file = new File([blob], `ai_logo.${ext}`,
                            { type: selectedLogoMimeType });
                        const dt   = new DataTransfer();
                        dt.items.add(file);
                        document.getElementById('logoInput').files = dt.files;
                    });

                closeLogoGenerator();
                showFlash('success', 'Logo seleccionado. Guarda el paso para aplicarlo.');
            }

            // ── Error ─────────────────────────────────────────────────────────
            function showLogoError(msg) {
                const el      = document.getElementById('logoGenError');
                el.textContent= msg;
                el.style.display = 'block';
            }
        </script>

        <hr style="border-color:#f1f5f9">

        <!-- ── Fotos del hotel ──────────────────────────────────────────── -->
        <h5 class="mt-4 mb-1">Fotos del hotel</h5>
        <p class="card-hint">
            Sube hasta 8 fotos. La primera será la imagen de portada de tu sitio web.
            Puedes arrastrarlas para reordenarlas.
        </p>

        <!-- Zona de drag & drop -->
        <div id="dropZone"
             style="border:2px dashed #c7d2fe;border-radius:14px;
                    background:#f8faff;padding:2rem;text-align:center;
                    cursor:pointer;transition:background .2s,border-color .2s"
             onclick="document.getElementById('photosInput').click()"
             ondragover="handleDragOver(event)"
             ondragleave="handleDragLeave(event)"
             ondrop="handleDrop(event)">
            <i class="bi bi-images"
               style="font-size:2.5rem;color:#a5b4fc;display:block;margin-bottom:.75rem"></i>
            <p class="mb-1 fw-semibold" style="color:#4338ca;font-size:.9rem">
                Arrastra tus fotos aquí o haz clic para seleccionar
            </p>
            <p class="mb-0 text-muted" style="font-size:.78rem">
                JPG, PNG, WEBP · Máx. 5MB por foto · Hasta 8 fotos
            </p>
        </div>

        <input type="file" id="photosInput" name="photos[]"
               accept="image/jpeg,image/png,image/webp"
               multiple class="d-none">

        <!-- Grid de previews -->
        <div id="photoGrid"
             style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));
                    gap:1rem;margin-top:1.25rem">

            <!-- Fotos ya guardadas en BD -->
            <?php foreach ($photos as $idx => $photo): ?>
                <div class="photo-thumb" data-id="<?= $photo['id'] ?>"
                     style="position:relative;border-radius:10px;overflow:hidden;
                            aspect-ratio:4/3;background:#f1f5f9">
                    <img src="<?= base_url($photo['file_path']) ?>"
                         style="width:100%;height:100%;object-fit:cover">
                    <?php if ($photo['is_main']): ?>
                        <span style="position:absolute;top:6px;left:6px;
                                     background:#6366f1;color:#fff;
                                     font-size:.65rem;font-weight:700;
                                     padding:2px 7px;border-radius:99px">
                            Portada
                        </span>
                    <?php endif; ?>
                    <!-- Botón eliminar foto existente -->
                    <button type="button"
                            onclick="deleteExistingPhoto(<?= $photo['id'] ?>, this)"
                            style="position:absolute;top:5px;right:5px;
                                   background:rgba(0,0,0,.55);color:#fff;
                                   border:none;border-radius:50%;
                                   width:24px;height:24px;font-size:.7rem;
                                   display:flex;align-items:center;justify-content:center;
                                   cursor:pointer">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Contador de fotos -->
        <p id="photoCount" class="text-muted mt-2 mb-0" style="font-size:.78rem">
            <?= count($photos) ?> foto(s) guardada(s)
        </p>

        <!-- ── Navegación ────────────────────────────────────────────────── -->
        <div class="d-flex justify-content-between align-items-center pt-4 mt-2
                    border-top" style="border-color:#f1f5f9!important">
            <a href="/onboarding/step/1" class="btn-wiz-secondary">
                <i class="bi bi-arrow-left me-1"></i> Anterior
            </a>

            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn-wiz-skip"
                        onclick="skipStep(<?= $currentStep ?>)">
                    Omitir por ahora
                </button>
                <button type="submit" class="btn-wiz-primary" id="btnSubmit2">
                    Guardar y continuar
                    <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>

    </form>
</div>

<!-- ── Tip ──────────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-start gap-3 p-3 rounded-3"
     style="background:#f0fdf4;border:1px solid #bbf7d0">
    <i class="bi bi-camera-fill mt-1" style="color:#22c55e;font-size:1.1rem"></i>
    <div>
        <strong style="font-size:.85rem;color:#15803d">Consejo de fotografía</strong>
        <p class="mb-0 text-muted" style="font-size:.82rem">
            Las fotos con buena iluminación natural aumentan hasta un 40% las
            conversiones en reservas directas. Prioriza espacios comunes y la
            habitación principal.
        </p>
    </div>
</div>

<script>
    /**
     * ── Estado local de nuevas fotos seleccionadas ──────────────────────────
     * Array de objetos { file, url } pendientes de subir con el form
     */
    let newPhotos      = [];
    const MAX_PHOTOS   = 8;

    // Fotos ya guardadas en BD (contamos las que hay en el grid al cargar)
    let savedCount     = document.querySelectorAll('.photo-thumb[data-id]').length;

    // ── Listeners ────────────────────────────────────────────────────────────

    document.getElementById('logoInput').addEventListener('change', function () {
        previewLogo(this.files[0]);
    });

    document.getElementById('photosInput').addEventListener('change', function () {
        addPhotos(Array.from(this.files));
        // Limpiar el input para permitir seleccionar los mismos archivos de nuevo
        this.value = '';
    });

    // ── Logo preview ─────────────────────────────────────────────────────────

    function previewLogo(file) {
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            showFlash('danger', 'El logo no debe superar 2MB.');
            return;
        }

        const reader  = new FileReader();
        reader.onload = e => {
            const img         = document.getElementById('logoPreview');
            const placeholder = document.getElementById('logoPlaceholder');
            img.src           = e.target.result;
            img.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }

    // ── Drag & Drop ───────────────────────────────────────────────────────────

    function handleDragOver(e) {
        e.preventDefault();
        const zone = document.getElementById('dropZone');
        zone.style.background    = '#eef2ff';
        zone.style.borderColor   = '#6366f1';
    }

    function handleDragLeave(e) {
        const zone = document.getElementById('dropZone');
        zone.style.background    = '#f8faff';
        zone.style.borderColor   = '#c7d2fe';
    }

    function handleDrop(e) {
        e.preventDefault();
        handleDragLeave(e);
        const files = Array.from(e.dataTransfer.files)
            .filter(f => f.type.startsWith('image/'));
        addPhotos(files);
    }

    // ── Agregar fotos al grid ─────────────────────────────────────────────────

    function addPhotos(files) {
        const total = savedCount + newPhotos.length;

        if (total >= MAX_PHOTOS) {
            showFlash('warning', `Máximo ${MAX_PHOTOS} fotos permitidas.`);
            return;
        }

        // Cuántas podemos agregar aún
        const available = MAX_PHOTOS - total;
        const toAdd     = files.slice(0, available);

        if (files.length > available) {
            showFlash('warning', `Solo se agregaron ${available} foto(s) para no superar el límite.`);
        }

        toAdd.forEach((file, i) => {
            if (file.size > 5 * 1024 * 1024) {
                showFlash('danger', `"${file.name}" supera 5MB y fue omitida.`);
                return;
            }

            const reader  = new FileReader();
            const idx     = newPhotos.length; // índice en el array local
            newPhotos.push({ file, url: '' });

            reader.onload = e => {
                newPhotos[idx].url = e.target.result;
                renderPhotoThumb(idx, e.target.result, newPhotos.length === 1 && savedCount === 0);
                updatePhotoCount();
            };
            reader.readAsDataURL(file);
        });

        // Sincronizar el input file con los archivos seleccionados
        syncFileInput();
    }

    /**
     * Renderiza un thumb nuevo en el grid
     * @param {number}  idx       - índice en newPhotos[]
     * @param {string}  dataUrl   - base64 para preview
     * @param {boolean} isFirst   - si es la primera foto, mostrar badge Portada
     */
    function renderPhotoThumb(idx, dataUrl, isFirst) {
        const grid  = document.getElementById('photoGrid');
        const div   = document.createElement('div');
        div.className        = 'photo-thumb-new';
        div.dataset.newIdx   = idx;
        div.style.cssText    = `position:relative;border-radius:10px;overflow:hidden;
                            aspect-ratio:4/3;background:#f1f5f9`;

        div.innerHTML = `
        <img src="${dataUrl}"
             style="width:100%;height:100%;object-fit:cover">
        ${isFirst ? `<span style="position:absolute;top:6px;left:6px;
                          background:#6366f1;color:#fff;font-size:.65rem;
                          font-weight:700;padding:2px 7px;border-radius:99px">
                        Portada</span>` : ''}
        <button type="button"
                onclick="removeNewPhoto(${idx}, this.parentElement)"
                style="position:absolute;top:5px;right:5px;
                       background:rgba(0,0,0,.55);color:#fff;border:none;
                       border-radius:50%;width:24px;height:24px;
                       font-size:.7rem;display:flex;align-items:center;
                       justify-content:center;cursor:pointer">
            <i class="bi bi-x"></i>
        </button>`;

        grid.appendChild(div);
    }

    /**
     * Elimina una foto nueva (aún no guardada) del grid y del array
     */
    function removeNewPhoto(idx, thumbEl) {
        newPhotos[idx] = null; // marcar como eliminada (null)
        thumbEl.remove();
        updatePhotoCount();
        syncFileInput();
    }

    /**
     * Elimina una foto ya guardada en BD vía fetch
     */
    async function deleteExistingPhoto(mediaId, btn) {
        if (!confirm('¿Eliminar esta foto?')) return;

        btn.disabled = true;

        try {
            const res  = await fetch(`/website/delete-media/${mediaId}`, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (data.success) {
                btn.closest('.photo-thumb').remove();
                savedCount = Math.max(0, savedCount - 1);
                updatePhotoCount();
            } else {
                showFlash('danger', 'No se pudo eliminar la foto.');
                btn.disabled = false;
            }
        } catch (err) {
            console.error('[Media] Error eliminando foto:', err);
            showFlash('danger', 'Error de conexión.');
            btn.disabled = false;
        }
    }

    /**
     * Actualiza el texto contador de fotos
     */
    function updatePhotoCount() {
        const validNew = newPhotos.filter(Boolean).length;
        const total    = savedCount + validNew;
        document.getElementById('photoCount').textContent =
            `${total} foto(s) · ${MAX_PHOTOS - total} espacio(s) disponible(s)`;
    }

    /**
     * Sincroniza el input[type=file] con el array newPhotos
     * usando DataTransfer para mantener los archivos seleccionados
     */
    function syncFileInput() {
        const input    = document.getElementById('photosInput');
        const dt       = new DataTransfer();
        newPhotos.filter(Boolean).forEach(p => dt.items.add(p.file));
        input.files    = dt.files;
    }

    // ── Submit con loader ─────────────────────────────────────────────────────

    document.getElementById('formStep2').addEventListener('submit', function () {
        const btn       = document.getElementById('btnSubmit2');
        btn.disabled    = true;
        btn.innerHTML   = '<span class="spinner-border spinner-border-sm me-2"></span>Subiendo...';
    });
</script>