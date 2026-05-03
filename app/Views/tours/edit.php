<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

<?php
$mediaItems = json_decode($tour['media_json'] ?? '[]', true) ?? [];
$included   = json_decode($tour['included_json'] ?? '[]', true) ?? [];
$excluded   = json_decode($tour['excluded_json'] ?? '[]', true) ?? [];
?>

    <style>
        /* ── Variables de color ───────────────────────────────────────── */
        :root {
            --media-bg:        #0f1117;
            --media-surface:   #1a1d27;
            --media-border:    rgba(255,255,255,.08);
            --media-accent:    #6c63ff;
            --media-accent2:   #00d4aa;
            --media-danger:    #ff4d6d;
            --media-text:      #e8eaf0;
            --media-muted:     #7b82a0;
            --media-radius:    14px;
            --media-radius-sm: 8px;
            --media-shadow:    0 8px 32px rgba(0,0,0,.4);
        }

        /* ── Zona de drop ─────────────────────────────────────────────── */
        #drop-zone {
            border: 2px dashed var(--media-border);
            border-radius: var(--media-radius);
            background: var(--media-surface);
            padding: 2.5rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: border-color .25s, background .25s, transform .15s;
            position: relative;
            overflow: hidden;
        }
        #drop-zone::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 50% 0%, rgba(108,99,255,.12), transparent 70%);
            pointer-events: none;
            opacity: 0;
            transition: opacity .3s;
        }
        #drop-zone.drag-over {
            border-color: var(--media-accent);
            background: rgba(108,99,255,.06);
            transform: scale(1.01);
        }
        #drop-zone.drag-over::before { opacity: 1; }
        #drop-zone .drop-icon {
            font-size: 2.4rem;
            margin-bottom: .6rem;
            display: block;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-6px); }
        }
        #drop-zone .drop-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--media-text);
            margin-bottom: .25rem;
        }
        #drop-zone .drop-sub {
            font-size: .8rem;
            color: var(--media-muted);
        }

        /* ── Grid de previews ─────────────────────────────────────────── */
        #media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        /* ── Card de media ────────────────────────────────────────────── */
        .media-card {
            background: var(--media-surface);
            border: 1px solid var(--media-border);
            border-radius: var(--media-radius);
            overflow: hidden;
            position: relative;
            transition: transform .2s, box-shadow .2s;
            animation: cardIn .35s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes cardIn {
            from { opacity:0; transform:translateY(18px) scale(.97); }
            to   { opacity:1; transform:translateY(0) scale(1); }
        }
        .media-card:hover { transform: translateY(-3px); box-shadow: var(--media-shadow); }

        .media-card .media-thumb {
            width: 100%;
            aspect-ratio: 16/9;
            object-fit: cover;
            display: block;
            background: #0a0c12;
        }
        .media-card .video-thumb {
            width: 100%;
            aspect-ratio: 16/9;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0a0c12, #1a1d27);
            font-size: 2.5rem;
            color: var(--media-accent);
        }

        /* Badge tipo */
        .media-type-badge {
            position: absolute;
            top: .5rem;
            left: .5rem;
            background: rgba(0,0,0,.65);
            backdrop-filter: blur(6px);
            color: #fff;
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            padding: .25rem .6rem;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,.15);
        }
        .media-type-badge.video { background: rgba(108,99,255,.75); }
        .media-type-badge.image { background: rgba(0,212,170,.55); }

        /* Botón eliminar */
        .btn-delete-media {
            position: absolute;
            top: .5rem;
            right: .5rem;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255,77,109,.85);
            border: none;
            color: #fff;
            font-size: .9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transform: scale(.8);
            transition: opacity .2s, transform .2s;
        }
        .media-card:hover .btn-delete-media {
            opacity: 1;
            transform: scale(1);
        }

        /* Footer de la card */
        .media-card-footer {
            padding: .6rem .75rem;
            border-top: 1px solid var(--media-border);
        }
        .media-desc-input {
            width: 100%;
            background: transparent;
            border: none;
            border-bottom: 1px dashed var(--media-border);
            color: var(--media-text);
            font-size: .78rem;
            padding: .2rem 0;
            outline: none;
            transition: border-color .2s;
        }
        .media-desc-input::placeholder { color: var(--media-muted); }
        .media-desc-input:focus { border-color: var(--media-accent); }
        .media-desc-status {
            font-size: .65rem;
            color: var(--media-muted);
            margin-top: .2rem;
            min-height: .9rem;
            transition: color .3s;
        }
        .media-desc-status.saved { color: var(--media-accent2); }

        /* ── Barra de progreso upload ─────────────────────────────────── */
        .upload-progress-wrap {
            display: none;
            margin-top: .75rem;
        }
        .upload-progress-wrap.visible { display: block; }
        .upload-bar-track {
            background: rgba(255,255,255,.08);
            border-radius: 20px;
            height: 6px;
            overflow: hidden;
        }
        .upload-bar-fill {
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(90deg, var(--media-accent), var(--media-accent2));
            width: 0%;
            transition: width .2s;
        }
        .upload-bar-label {
            font-size: .72rem;
            color: var(--media-muted);
            margin-top: .3rem;
        }

        /* ── Toast de feedback ────────────────────────────────────────── */
        #media-toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: .5rem;
            pointer-events: none;
        }
        .toast-item {
            background: var(--media-surface);
            border: 1px solid var(--media-border);
            border-left: 4px solid var(--media-accent2);
            color: var(--media-text);
            padding: .7rem 1.1rem;
            border-radius: var(--media-radius-sm);
            font-size: .82rem;
            box-shadow: var(--media-shadow);
            animation: toastIn .3s ease both;
            pointer-events: auto;
        }
        .toast-item.error { border-left-color: var(--media-danger); }
        @keyframes toastIn {
            from { opacity:0; transform: translateX(20px); }
            to   { opacity:1; transform: translateX(0); }
        }

        /* ── Drag handle ──────────────────────────────────────────────── */
        .drag-handle {
            position: absolute;
            bottom: .5rem;
            right: .5rem;
            color: var(--media-muted);
            font-size: .85rem;
            cursor: grab;
            opacity: 0;
            transition: opacity .2s;
        }
        .media-card:hover .drag-handle { opacity: 1; }
        .media-card.sortable-ghost { opacity: .4; }

        /* ── Estado vacío ─────────────────────────────────────────────── */
        #empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--media-muted);
            font-size: .85rem;
            display: none;
        }
        #empty-state.visible { display: block; }
    </style>

    <div class="d-flex align-items-center mb-4">
        <a href="<?= base_url('/tours') ?>" class="btn btn-sm btn-outline-secondary me-3">&larr; Volver</a>
        <h2 class="mb-0">Editar Tour</h2>
    </div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

    <!-- ════════════════════════════════════════════════════
         FORMULARIO PRINCIPAL
    ═════════════════════════════════════════════════════ -->
    <form action="<?= base_url("/tours/{$tour['id']}/update") ?>" method="post">
        <?= csrf_field() ?>

        <div class="row g-3 mb-4">

            <!-- Información básica -->
            <div class="col-md-8">
                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-bold">Información General</div>
                    <div class="card-body row g-3">

                        <div class="col-12">
                            <label class="form-label">Nombre del Tour <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="<?= esc($tour['name']) ?>" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="description" class="form-control" rows="3"><?= esc($tour['description']) ?></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Duración (minutos)</label>
                            <input type="number" name="duration_minutes" class="form-control"
                                   value="<?= $tour['duration_minutes'] ?>" min="15">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Mínimo de personas</label>
                            <input type="number" name="min_pax" class="form-control"
                                   value="<?= $tour['min_pax'] ?>" min="1">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Dificultad</label>
                            <select name="difficulty_level" class="form-select">
                                <?php foreach (['easy' => 'Fácil', 'moderate' => 'Moderado', 'hard' => 'Difícil'] as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $tour['difficulty_level'] === $val ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Punto de encuentro</label>
                            <input type="text" name="meeting_point" class="form-control"
                                   value="<?= esc($tour['meeting_point'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Política de cancelación</label>
                            <select name="cancellation_policy" class="form-select">
                                <?php
                                $policies = ['flexible'=>'Flexible','moderate'=>'Moderada','strict'=>'Estricta','non_refundable'=>'No reembolsable'];
                                foreach ($policies as $val => $label):
                                    ?>
                                    <option value="<?= $val ?>" <?= $tour['cancellation_policy'] === $val ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Precios + Incluye/No incluye -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-bold">Precios Base</div>
                    <div class="card-body row g-3">
                        <div class="col-12">
                            <label class="form-label">Precio Adulto <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="price_adult" class="form-control"
                                       value="<?= $tour['price_adult'] ?>" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Precio Niño</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="price_child" class="form-control"
                                       value="<?= $tour['price_child'] ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header fw-bold">¿Qué incluye?</div>
                    <div class="card-body">
                        <div id="included-list">
                            <?php foreach ($included as $item): ?>
                                <div class="input-group mb-2">
                                    <input type="text" name="included[]" class="form-control form-control-sm"
                                           value="<?= esc($item) ?>">
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            onclick="this.parentElement.remove()">×</button>
                                </div>
                            <?php endforeach; ?>
                            <div class="input-group mb-2">
                                <input type="text" name="included[]" class="form-control form-control-sm"
                                       placeholder="Ej: Almuerzo">
                                <button type="button" class="btn btn-sm btn-outline-success"
                                        onclick="addItem('included-list','included[]')">+</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-header fw-bold border-top">No incluye</div>
                    <div class="card-body">
                        <div id="excluded-list">
                            <?php foreach ($excluded as $item): ?>
                                <div class="input-group mb-2">
                                    <input type="text" name="excluded[]" class="form-control form-control-sm"
                                           value="<?= esc($item) ?>">
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            onclick="this.parentElement.remove()">×</button>
                                </div>
                            <?php endforeach; ?>
                            <div class="input-group mb-2">
                                <input type="text" name="excluded[]" class="form-control form-control-sm"
                                       placeholder="Ej: Transporte">
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="addItem('excluded-list','excluded[]')">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /.row formulario -->

        <div class="mb-4 text-end">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-circle"></i> Guardar cambios
            </button>
        </div>
    </form>

    <!-- ════════════════════════════════════════════════════
         GESTOR DE MEDIA — INDEPENDIENTE DEL FORM
    ═════════════════════════════════════════════════════ -->
    <div class="card mb-5" style="background:var(--media-bg);border:1px solid var(--media-border);border-radius:var(--media-radius);">
        <div class="card-body p-4">

            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h5 class="mb-0 fw-bold" style="color:var(--media-text);">
                        <i class="bi bi-images me-2" style="color:var(--media-accent);"></i>Fotos y Videos
                    </h5>
                    <p class="mb-0 mt-1" style="font-size:.8rem;color:var(--media-muted);">
                        Arrastra para reordenar · JPG, PNG, WEBP, MP4, MOV · Máx 50 MB por archivo
                    </p>
                </div>
                <span id="media-count-badge" class="badge rounded-pill"
                      style="background:rgba(108,99,255,.2);color:var(--media-accent);font-size:.8rem;padding:.4rem .9rem;">
                <?= count($mediaItems) ?> archivo<?= count($mediaItems) !== 1 ? 's' : '' ?>
            </span>
            </div>

            <!-- Zona de drop -->
            <div id="drop-zone" onclick="document.getElementById('file-input').click()">
                <span class="drop-icon">📂</span>
                <p class="drop-title">Arrastra tus archivos aquí o haz clic para seleccionar</p>
                <p class="drop-sub">Puedes subir varios archivos a la vez</p>
            </div>
            <input type="file" id="file-input" multiple accept="image/*,video/*" style="display:none">

            <!-- Barra de progreso -->
            <div class="upload-progress-wrap" id="progress-wrap">
                <div class="upload-bar-track">
                    <div class="upload-bar-fill" id="progress-fill"></div>
                </div>
                <p class="upload-bar-label" id="progress-label">Subiendo...</p>
            </div>

            <!-- Grid de items -->
            <div id="empty-state" class="<?= empty($mediaItems) ? 'visible' : '' ?>">
                <i class="bi bi-camera" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
                Aún no hay fotos ni videos en este tour
            </div>

            <div id="media-grid">
                <?php foreach ($mediaItems as $item): ?>
                    <?php
                    $isVideo   = ($item['type'] ?? '') === 'video';
                    $publicUrl = base_url('writable/' . ($item['path'] ?? ''));
                    ?>
                    <div class="media-card" data-media-id="<?= esc($item['id']) ?>">

                        <?php if ($isVideo): ?>
                            <div class="video-thumb">
                                <i class="bi bi-play-circle-fill"></i>
                            </div>
                        <?php else: ?>
                            <img src="<?= $publicUrl ?>" alt="<?= esc($item['description'] ?? '') ?>"
                                 class="media-thumb" loading="lazy">
                        <?php endif; ?>

                        <span class="media-type-badge <?= $isVideo ? 'video' : 'image' ?>">
                        <?= $isVideo ? '▶ Video' : '🖼 Foto' ?>
                    </span>

                        <button type="button" class="btn-delete-media"
                                title="Eliminar"
                                onclick="deleteMedia('<?= esc($item['id']) ?>', this)">
                            <i class="bi bi-x"></i>
                        </button>

                        <i class="bi bi-grip-vertical drag-handle" title="Arrastrar para reordenar"></i>

                        <div class="media-card-footer">
                            <input type="text"
                                   class="media-desc-input"
                                   placeholder="Añadir descripción…"
                                   value="<?= esc($item['description'] ?? '') ?>"
                                   data-media-id="<?= esc($item['id']) ?>"
                                   onchange="saveDescription(this)">
                            <p class="media-desc-status" id="status-<?= esc($item['id']) ?>"></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>

    <!-- Toast container -->
    <div id="media-toast"></div>

    <!-- SortableJS para reordenar -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>

    <script>
        const TOUR_ID   = <?= $tour['id'] ?>;
        const CSRF_NAME = '<?= csrf_token() ?>';
        let   csrfValue = '<?= csrf_hash() ?>';

        // ── Helpers ────────────────────────────────────────────────────
        function getHeaders(extra = {}) {
            return { 'X-Requested-With': 'XMLHttpRequest', [CSRF_NAME]: csrfValue, ...extra };
        }

        function updateCsrf(response) {
            const newToken = response.headers.get('X-CSRF-TOKEN');
            if (newToken) csrfValue = newToken;
        }

        function toast(msg, type = 'success') {
            const el = document.createElement('div');
            el.className = 'toast-item' + (type === 'error' ? ' error' : '');
            el.textContent = msg;
            document.getElementById('media-toast').appendChild(el);
            setTimeout(() => el.remove(), 3500);
        }

        function updateBadge() {
            const n = document.querySelectorAll('#media-grid .media-card').length;
            const badge = document.getElementById('media-count-badge');
            badge.textContent = n + ' archivo' + (n !== 1 ? 's' : '');

            const empty = document.getElementById('empty-state');
            n === 0 ? empty.classList.add('visible') : empty.classList.remove('visible');
        }

        // ── Incluye / No incluye (heredado del create) ─────────────────
        function addItem(containerId, fieldName) {
            const container = document.getElementById(containerId);
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            const isIncluded = fieldName === 'included[]';
            div.innerHTML = `
        <input type="text" name="${fieldName}" class="form-control form-control-sm"
               placeholder="${isIncluded ? 'Ej: Seguro' : 'Ej: Bebidas'}">
        <button type="button" class="btn btn-sm btn-outline-secondary"
                onclick="this.parentElement.remove()">×</button>
    `;
            container.appendChild(div);
        }

        // ── Crear card de media (para uploads nuevos) ──────────────────
        function createMediaCard(item) {
            const isVideo  = item.type === 'video';
            const publicUrl = '/' + item.path;

            const card = document.createElement('div');
            card.className = 'media-card';
            card.dataset.mediaId = item.id;

            card.innerHTML = `
        ${isVideo
                ? `<div class="video-thumb"><i class="bi bi-play-circle-fill"></i></div>`
                : `<img src="${publicUrl}" alt="" class="media-thumb" loading="lazy">`
            }
        <span class="media-type-badge ${isVideo ? 'video' : 'image'}">
            ${isVideo ? '▶ Video' : '🖼 Foto'}
        </span>
        <button type="button" class="btn-delete-media" title="Eliminar"
                onclick="deleteMedia('${item.id}', this)">
            <i class="bi bi-x"></i>
        </button>
        <i class="bi bi-grip-vertical drag-handle" title="Arrastrar para reordenar"></i>
        <div class="media-card-footer">
            <input type="text" class="media-desc-input"
                   placeholder="Añadir descripción…"
                   value=""
                   data-media-id="${item.id}"
                   onchange="saveDescription(this)">
            <p class="media-desc-status" id="status-${item.id}"></p>
        </div>
    `;

            return card;
        }

        // ── Upload de archivos ─────────────────────────────────────────
        async function uploadFiles(files) {
            if (!files.length) return;

            const progressWrap = document.getElementById('progress-wrap');
            const progressFill = document.getElementById('progress-fill');
            const progressLabel = document.getElementById('progress-label');

            progressWrap.classList.add('visible');

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const pct  = Math.round(((i) / files.length) * 100);

                progressFill.style.width  = pct + '%';
                progressLabel.textContent = `Subiendo ${i + 1} de ${files.length}: ${file.name}`;

                const formData = new FormData();
                formData.append('file', file);
                formData.append(CSRF_NAME, csrfValue);  // ← ya está bien aquí

                try {
                    const res = await fetch(`/tours/${TOUR_ID}/media/upload`, {
                        method:  'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },  // solo esto, sin el token
                        body:    formData,
                    });

                    updateCsrf(res);
                    const data = await res.json();

                    if (data.success) {
                        const card = createMediaCard(data.item);
                        document.getElementById('media-grid').appendChild(card);
                        updateBadge();
                        toast(`✓ ${file.name} subido`);
                    } else {
                        toast(`✗ ${file.name}: ${data.error}`, 'error');
                    }
                } catch (e) {
                    toast(`✗ Error de red al subir ${file.name}`, 'error');
                }
            }

            progressFill.style.width = '100%';
            progressLabel.textContent = 'Listo';
            setTimeout(() => {
                progressWrap.classList.remove('visible');
                progressFill.style.width = '0%';
            }, 1200);
        }

        // ── Drag & Drop en la zona ─────────────────────────────────────
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');

        dropZone.addEventListener('dragover', e => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            uploadFiles([...e.dataTransfer.files]);
        });
        fileInput.addEventListener('change', () => {
            uploadFiles([...fileInput.files]);
            fileInput.value = ''; // reset para poder volver a seleccionar los mismos
        });

        async function deleteMedia(mediaId, btn) {
            if (!confirm('¿Eliminar este archivo? Esta acción no se puede deshacer.')) return;

            const card = btn.closest('.media-card');
            card.style.opacity = '.4';
            card.style.pointerEvents = 'none';

            const formData = new FormData();
            formData.append(CSRF_NAME, csrfValue);

            try {
                const res = await fetch(`/tours/${TOUR_ID}/media/${mediaId}/delete`, {
                    method:  'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body:    formData,
                });

                updateCsrf(res);
                const data = await res.json();

                if (data.success) {
                    card.style.transition = 'all .3s';
                    card.style.transform  = 'scale(.9)';
                    card.style.opacity    = '0';
                    setTimeout(() => { card.remove(); updateBadge(); }, 300);
                    toast('Archivo eliminado');
                } else {
                    toast(data.error || 'Error al eliminar', 'error');
                    card.style.opacity = '1';
                    card.style.pointerEvents = 'auto';
                }
            } catch {
                toast('Error de red', 'error');
                card.style.opacity = '1';
                card.style.pointerEvents = 'auto';
            }
        }

        // ── Guardar descripción (debounce) ─────────────────────────────
        const descTimers = {};
        function saveDescription(input) {
            const mediaId = input.dataset.mediaId;
            const statusEl = document.getElementById('status-' + mediaId);

            clearTimeout(descTimers[mediaId]);
            statusEl.textContent = 'Guardando…';
            statusEl.className = 'media-desc-status';

            descTimers[mediaId] = setTimeout(async () => {
                try {
                    const formData = new FormData();
                    formData.append('description', input.value);
                    formData.append(CSRF_NAME, csrfValue);

                    const res = await fetch(`/tours/${TOUR_ID}/media/${mediaId}/description`, {
                        method:  'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },  // ← sin token aquí
                        body:    formData,
                    });

                    updateCsrf(res);
                    const data = await res.json();

                    if (data.success) {
                        statusEl.textContent = '✓ Guardado';
                        statusEl.className = 'media-desc-status saved';
                        setTimeout(() => { statusEl.textContent = ''; }, 2000);
                    } else {
                        statusEl.textContent = '✗ Error';
                    }
                } catch {
                    statusEl.textContent = '✗ Sin conexión';
                }
            }, 800);
        }


        // ── Reordenar con SortableJS ───────────────────────────────────
        const grid = document.getElementById('media-grid');

        Sortable.create(grid, {
            animation: 180,
            ghostClass: 'sortable-ghost',
            handle: '.drag-handle',
            onEnd: async () => {
                const order = [...grid.querySelectorAll('.media-card')].map(c => c.dataset.mediaId);

                try {
                    const res = await fetch(`/tours/${TOUR_ID}/media/reorder`, {
                        method:  'POST',
                        headers: getHeaders({ 'Content-Type': 'application/json' }),
                        body:    JSON.stringify({ order }),
                    });
                    updateCsrf(res);
                } catch {
                    toast('No se pudo guardar el orden', 'error');
                }
            },
        });
    </script>

<?= $this->endSection() ?>