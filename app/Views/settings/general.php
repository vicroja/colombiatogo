<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <style>
        /* ── Variables ─────────────────────────────────────────────── */
        :root {
            --doc-radius: 10px;
            --doc-border: #e4e7ec;
            --doc-surface: #ffffff;
            --doc-bg: #f8fafc;
            --doc-text: #101828;
            --doc-sub: #667085;
            --doc-accent: #6366f1;
            --doc-accent-lt: #eef2ff;
            --doc-green: #059669;
            --doc-green-lt: #ecfdf5;
            --doc-red: #dc2626;
            --doc-red-lt: #fef2f2;
            --doc-shadow: 0 1px 4px rgba(0,0,0,.07), 0 0 0 1px rgba(0,0,0,.04);
        }

        /* ── Tag pills ─────────────────────────────────────────────── */
        .tag-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 20px;
            letter-spacing: .03em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .tag-rut             { background: #eff6ff; color: #1d4ed8; }
        .tag-cuenta_bancaria { background: #ecfdf5; color: #059669; }
        .tag-seguro          { background: #fffbeb; color: #d97706; }
        .tag-contrato        { background: #fdf4ff; color: #9333ea; }
        .tag-otro            { background: #f1f5f9; color: #475569; }

        /* ── Drop zone ─────────────────────────────────────────────── */
        .doc-dropzone {
            border: 2px dashed var(--doc-border);
            border-radius: var(--doc-radius);
            padding: 28px 20px;
            text-align: center;
            background: var(--doc-bg);
            cursor: pointer;
            transition: border-color .2s, background .2s;
            position: relative;
        }
        .doc-dropzone.dragover {
            border-color: var(--doc-accent);
            background: var(--doc-accent-lt);
        }
        .doc-dropzone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        .doc-dropzone-icon {
            font-size: 32px;
            color: var(--doc-accent);
            display: block;
            margin-bottom: 8px;
        }
        .doc-dropzone-text { font-size: 13px; color: var(--doc-sub); }
        .doc-dropzone-text strong { color: var(--doc-text); }
        .doc-dropzone-hint { font-size: 11.5px; color: #94a3b8; margin-top: 4px; }

        /* ── Upload form card ──────────────────────────────────────── */
        .doc-upload-form {
            background: var(--doc-surface);
            border-radius: var(--doc-radius);
            box-shadow: var(--doc-shadow);
            overflow: hidden;
        }
        .doc-upload-form .form-header {
            padding: 14px 18px;
            border-bottom: 1px solid var(--doc-border);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .doc-upload-form .form-header h6 {
            margin: 0;
            font-size: 13.5px;
            font-weight: 700;
            color: var(--doc-text);
        }
        .doc-upload-form .form-body {
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        /* ── Document list ─────────────────────────────────────────── */
        .doc-list { display: flex; flex-direction: column; gap: 10px; }

        .doc-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--doc-surface);
            border-radius: var(--doc-radius);
            box-shadow: var(--doc-shadow);
            padding: 12px 14px;
            animation: docFadeIn .25s ease both;
            transition: box-shadow .15s;
        }
        .doc-item:hover { box-shadow: 0 3px 10px rgba(0,0,0,.1); }

        @keyframes docFadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .doc-item-icon {
            width: 38px; height: 38px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .doc-icon-pdf   { background: #fef2f2; color: #dc2626; }
        .doc-icon-image { background: var(--doc-accent-lt); color: var(--doc-accent); }

        .doc-item-info  { flex: 1; min-width: 0; }
        .doc-item-name  { font-size: 13px; font-weight: 600; color: var(--doc-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .doc-item-meta  { display: flex; align-items: center; gap: 8px; margin-top: 3px; flex-wrap: wrap; }
        .doc-item-desc  { font-size: 12px; color: var(--doc-sub); }
        .doc-item-date  { font-size: 11px; color: #94a3b8; }

        .doc-item-actions { display: flex; gap: 6px; flex-shrink: 0; }

        .btn-doc-view {
            width: 30px; height: 30px;
            border-radius: 7px;
            border: 1px solid var(--doc-border);
            background: #fff;
            color: var(--doc-sub);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
            text-decoration: none;
            transition: background .15s, color .15s;
        }
        .btn-doc-view:hover { background: var(--doc-accent-lt); color: var(--doc-accent); border-color: var(--doc-accent); }

        .btn-doc-delete {
            width: 30px; height: 30px;
            border-radius: 7px;
            border: 1px solid var(--doc-border);
            background: #fff;
            color: var(--doc-sub);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
            cursor: pointer;
            transition: background .15s, color .15s;
        }
        .btn-doc-delete:hover { background: var(--doc-red-lt); color: var(--doc-red); border-color: var(--doc-red); }

        /* ── Progress bar ──────────────────────────────────────────── */
        .doc-progress {
            display: none;
            height: 4px;
            background: #e4e7ec;
            border-radius: 99px;
            overflow: hidden;
            margin-top: 8px;
        }
        .doc-progress.active { display: block; }
        .doc-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--doc-accent), #818cf8);
            border-radius: 99px;
            transition: width .2s;
            width: 0%;
        }

        /* ── Empty state ───────────────────────────────────────────── */
        .doc-empty {
            text-align: center;
            padding: 32px 20px;
            color: var(--doc-sub);
            font-size: 13px;
        }
        .doc-empty i { font-size: 36px; display: block; margin-bottom: 10px; opacity: .35; color: var(--doc-text); }

        /* ── Toast ─────────────────────────────────────────────────── */
        .doc-toast-wrap {
            position: fixed;
            bottom: 24px; right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
            pointer-events: none;
        }
        .doc-toast {
            background: #1a1f2e;
            color: #fff;
            font-size: 13px;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 9px;
            box-shadow: 0 4px 16px rgba(0,0,0,.2);
            display: flex;
            align-items: center;
            gap: 8px;
            pointer-events: auto;
            animation: toastIn .2s ease;
        }
        .doc-toast.error { background: #991b1b; }
        @keyframes toastIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
    </style>

    <!-- ════════════════════════════════════════════════════════
         CABECERA DE PÁGINA
    ════════════════════════════════════════════════════════ -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Configuración de la Propiedad</h2>
    </div>

    <!-- ════════════════════════════════════════════════════════
         FORMULARIO PRINCIPAL (datos del tenant)
    ════════════════════════════════════════════════════════ -->
    <form action="<?= base_url('/settings/update') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="row">
            <div class="col-md-8">

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-building"></i> Información General</h5>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Nombre Comercial</label>
                                <input type="text" name="name" class="form-control" value="<?= esc($tenant['name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Correo de Contacto</label>
                                <input type="email" name="email" class="form-control" value="<?= esc($tenant['email']) ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Teléfono</label>
                                <input type="text" name="phone" class="form-control" value="<?= esc($tenant['phone']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Ciudad</label>
                                <input type="text" name="city" class="form-control" value="<?= esc($tenant['city']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">País</label>
                                <input type="text" name="country" class="form-control" value="<?= esc($tenant['country']) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Dirección Completa</label>
                            <input type="text" name="address" class="form-control" value="<?= esc($tenant['address']) ?>">
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-info"><i class="bi bi-clock-history"></i> Políticas Operativas</h5>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Hora Estándar de Check-in</label>
                                <input type="time" name="checkin_time" class="form-control" value="<?= esc($tenant['checkin_time']) ?>" required>
                                <small class="text-muted">Usada para prerrellenar nuevas reservas.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Hora Estándar de Check-out</label>
                                <input type="time" name="checkout_time" class="form-control" value="<?= esc($tenant['checkout_time']) ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-4">

                <div class="card shadow-sm border-0 mb-4 text-center">
                    <div class="card-header bg-white py-3 text-start">
                        <h5 class="mb-0 fw-bold text-secondary"><i class="bi bi-image"></i> Logotipo</h5>
                    </div>
                    <div class="card-body bg-light">
                        <?php if ($tenant['logo_path']): ?>
                            <img src="<?= base_url($tenant['logo_path']) ?>" alt="Logo" class="img-fluid mb-3 rounded shadow-sm" style="max-height:150px;">
                        <?php else: ?>
                            <div class="bg-white border rounded py-4 mb-3 text-muted">
                                <i class="bi bi-camera fs-1"></i><br>Sin logo
                            </div>
                        <?php endif; ?>
                        <input type="file" name="logo" class="form-control form-control-sm" accept="image/*">
                        <small class="text-muted d-block mt-2">JPG, PNG, WEBP. Máx 2 MB.</small>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-success"><i class="bi bi-globe-americas"></i> Localización</h5>
                    </div>
                    <div class="card-body bg-light">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Símbolo de Moneda</label>
                            <input type="text" name="currency_symbol" class="form-control fw-bold text-success" value="<?= esc($tenant['currency_symbol']) ?>" placeholder="$ o €" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Código ISO</label>
                            <input type="text" name="currency_code" class="form-control" value="<?= esc($tenant['currency_code']) ?>" placeholder="COP, USD…">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Zona Horaria</label>
                            <select name="timezone" class="form-select" required>
                                <option value="America/Bogota"    <?= $tenant['timezone'] === 'America/Bogota'    ? 'selected' : '' ?>>America/Bogotá (Colombia)</option>
                                <option value="America/Mexico_City" <?= $tenant['timezone'] === 'America/Mexico_City' ? 'selected' : '' ?>>America/Mexico City</option>
                                <option value="America/Lima"      <?= $tenant['timezone'] === 'America/Lima'      ? 'selected' : '' ?>>America/Lima</option>
                                <option value="America/New_York"  <?= $tenant['timezone'] === 'America/New_York'  ? 'selected' : '' ?>>America/New York</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="text-end mb-5">
            <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm">
                <i class="bi bi-save"></i> Guardar Configuración
            </button>
        </div>
    </form>

    <!-- ════════════════════════════════════════════════════════
         MÓDULO DE DOCUMENTOS
    ════════════════════════════════════════════════════════ -->
    <div class="row mb-5" id="documentos">
        <div class="col-12">
            <h5 class="fw-bold mb-3"><i class="bi bi-folder2-open text-primary"></i> Documentos del Establecimiento</h5>
            <p class="text-muted small mb-4">
                Sube RUT, certificaciones bancarias, contratos, pólizas, etc.
                Estos documentos podrán ser enviados automáticamente por el asistente virtual cuando un cliente los solicite.
            </p>
        </div>

        <!-- Columna izquierda: formulario de subida -->
        <div class="col-md-5 mb-4">
            <div class="doc-upload-form">
                <div class="form-header">
                    <i class="bi bi-cloud-upload text-primary"></i>
                    <h6>Subir nuevo documento</h6>
                </div>
                <div class="form-body">

                    <!-- Drop zone -->
                    <div class="doc-dropzone" id="docDropzone">
                        <input type="file" id="docFileInput" accept=".jpg,.jpeg,.png,.webp,.pdf">
                        <i class="bi bi-file-earmark-arrow-up doc-dropzone-icon"></i>
                        <div class="doc-dropzone-text">
                            <strong>Arrastra el archivo aquí</strong> o haz clic para seleccionar
                        </div>
                        <div class="doc-dropzone-hint">PDF, JPG, PNG, WEBP — máx. 5 MB</div>
                    </div>

                    <!-- Archivo seleccionado -->
                    <div id="docSelectedFile" style="display:none;" class="d-flex align-items-center gap-2 p-2 bg-light rounded border">
                        <i class="bi bi-file-earmark-check text-primary fs-5"></i>
                        <span id="docFileName" class="small fw-semibold text-truncate flex-1"></span>
                        <button type="button" id="docClearFile" class="btn btn-sm btn-link text-danger p-0 ms-auto">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <!-- Tag -->
                    <div>
                        <label class="form-label small fw-bold mb-1">Tipo de documento</label>
                        <div class="d-flex flex-wrap gap-2" id="docTagGroup">
                            <?php foreach ($tags as $value => $label): ?>
                                <label class="doc-tag-radio">
                                    <input type="radio" name="doc_tag" value="<?= $value ?>" <?= $value === 'otro' ? 'checked' : '' ?>>
                                    <span class="tag-pill tag-<?= $value ?>"><?= $label ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label class="form-label small fw-bold mb-1">Descripción <span class="text-muted fw-normal">(opcional)</span></label>
                        <input type="text" id="docDescription" class="form-control form-control-sm"
                               placeholder="Ej: RUT actualizado 2025, Cuenta Bancolombia…" maxlength="255">
                    </div>

                    <!-- Botón -->
                    <button type="button" id="docUploadBtn" class="btn btn-primary w-100" disabled>
                        <i class="bi bi-upload"></i> Subir documento
                    </button>

                    <!-- Progress -->
                    <div class="doc-progress" id="docProgress">
                        <div class="doc-progress-bar" id="docProgressBar"></div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Columna derecha: lista de documentos -->
        <div class="col-md-7">
            <div id="docList" class="doc-list">

                <?php if (empty($documents)): ?>
                    <div class="doc-empty" id="docEmpty">
                        <i class="bi bi-folder2"></i>
                        Todavía no has subido ningún documento.
                    </div>
                <?php else: ?>
                    <?php $docEmpty = true; ?>
                    <?php foreach ($documents as $doc):
                        $docEmpty = false;
                        $isImage  = $doc['file_type'] === 'image';
                        $tagClass = 'tag-' . ($doc['tag'] ?? 'otro');
                        $tagLabel = $tags[$doc['tag'] ?? 'otro'] ?? 'Otro';
                        $iconClass = $isImage ? 'doc-icon-image bi-file-image' : 'doc-icon-pdf bi-file-earmark-pdf';
                        ?>
                        <div class="doc-item" id="doc-item-<?= $doc['id'] ?>">
                            <div class="doc-item-icon <?= $isImage ? 'doc-icon-image' : 'doc-icon-pdf' ?>">
                                <i class="bi <?= $isImage ? 'bi-file-image' : 'bi-file-earmark-pdf' ?>"></i>
                            </div>
                            <div class="doc-item-info">
                                <div class="doc-item-name"><?= esc(basename($doc['file_path'])) ?></div>
                                <div class="doc-item-meta">
                                    <span class="tag-pill <?= $tagClass ?>"><?= $tagLabel ?></span>
                                    <?php if (!empty($doc['description'])): ?>
                                        <span class="doc-item-desc"><?= esc($doc['description']) ?></span>
                                    <?php endif; ?>
                                    <span class="doc-item-date"><?= date('d/m/Y', strtotime($doc['created_at'])) ?></span>
                                </div>
                            </div>
                            <div class="doc-item-actions">
                                <a href="<?= base_url($doc['file_path']) ?>" target="_blank" class="btn-doc-view" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button type="button" class="btn-doc-delete"
                                        data-id="<?= $doc['id'] ?>"
                                        title="Eliminar">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div class="doc-toast-wrap" id="docToastWrap"></div>

    <style>
        /* Radio buttons como pills seleccionables */
        .doc-tag-radio input[type="radio"] { display: none; }
        .doc-tag-radio .tag-pill {
            cursor: pointer;
            opacity: .55;
            transition: opacity .15s, transform .1s;
        }
        .doc-tag-radio input[type="radio"]:checked + .tag-pill {
            opacity: 1;
            transform: scale(1.08);
            box-shadow: 0 0 0 2px currentColor;
        }
    </style>

    <script>
        (function () {
            const UPLOAD_URL  = '<?= base_url('/settings/documents/upload') ?>';
            const DELETE_URL  = '<?= base_url('/settings/documents/delete') ?>';
            const CSRF_NAME   = '<?= csrf_token() ?>';
            let   csrfHash    = '<?= csrf_hash() ?>';

            // ── Elementos ────────────────────────────────────────────────
            const dropzone    = document.getElementById('docDropzone');
            const fileInput   = document.getElementById('docFileInput');
            const selectedBox = document.getElementById('docSelectedFile');
            const fileNameEl  = document.getElementById('docFileName');
            const clearBtn    = document.getElementById('docClearFile');
            const uploadBtn   = document.getElementById('docUploadBtn');
            const descInput   = document.getElementById('docDescription');
            const progressWrap = document.getElementById('docProgress');
            const progressBar  = document.getElementById('docProgressBar');
            const docList     = document.getElementById('docList');
            const docEmpty    = document.getElementById('docEmpty');
            const toastWrap   = document.getElementById('docToastWrap');

            let selectedFile = null;

            // ── Drop zone ────────────────────────────────────────────────
            dropzone.addEventListener('dragover',  e => { e.preventDefault(); dropzone.classList.add('dragover'); });
            dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
            dropzone.addEventListener('drop', e => {
                e.preventDefault();
                dropzone.classList.remove('dragover');
                const f = e.dataTransfer.files[0];
                if (f) setFile(f);
            });
            fileInput.addEventListener('change', () => {
                if (fileInput.files[0]) setFile(fileInput.files[0]);
            });

            function setFile(f) {
                selectedFile = f;
                fileNameEl.textContent = f.name;
                selectedBox.style.display = 'flex';
                uploadBtn.disabled = false;
                fileInput.value = '';
            }

            clearBtn.addEventListener('click', clearFile);
            function clearFile() {
                selectedFile = null;
                selectedBox.style.display = 'none';
                fileNameEl.textContent = '';
                uploadBtn.disabled = true;
                fileInput.value = '';
            }

            // ── Upload ───────────────────────────────────────────────────
            uploadBtn.addEventListener('click', () => {
                if (!selectedFile) return;

                const tag  = document.querySelector('input[name="doc_tag"]:checked')?.value ?? 'otro';
                const desc = descInput.value.trim();

                const fd = new FormData();
                fd.append('document', selectedFile);
                fd.append('tag', tag);
                fd.append('description', desc);
                fd.append(CSRF_NAME, csrfHash);

                uploadBtn.disabled = true;
                progressWrap.classList.add('active');
                progressBar.style.width = '0%';

                const xhr = new XMLHttpRequest();
                xhr.open('POST', UPLOAD_URL);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                xhr.upload.addEventListener('progress', e => {
                    if (e.lengthComputable) {
                        progressBar.style.width = Math.round(e.loaded / e.total * 90) + '%';
                    }
                });

                xhr.addEventListener('load', () => {
                    progressBar.style.width = '100%';
                    setTimeout(() => progressWrap.classList.remove('active'), 400);

                    let res;
                    try { res = JSON.parse(xhr.responseText); } catch { res = { success: false, message: 'Error inesperado.' }; }

                    // Actualizar CSRF
                    if (res.csrf_hash) csrfHash = res.csrf_hash;

                    if (res.success) {
                        prependDocItem(res);
                        clearFile();
                        descInput.value = '';
                        toast('Documento subido correctamente', 'ok');
                    } else {
                        toast(res.message || 'Error al subir.', 'error');
                        uploadBtn.disabled = false;
                    }
                });

                xhr.addEventListener('error', () => {
                    toast('Error de red. Intenta de nuevo.', 'error');
                    uploadBtn.disabled = false;
                    progressWrap.classList.remove('active');
                });

                xhr.send(fd);
            });

            // ── Agregar ítem al DOM ──────────────────────────────────────
            function prependDocItem(doc) {
                // Quitar empty state si existía
                const empty = document.getElementById('docEmpty');
                if (empty) empty.remove();

                const isImage = doc.file_type === 'image';
                const iconCls = isImage ? 'doc-icon-image' : 'doc-icon-pdf';
                const iconBi  = isImage ? 'bi-file-image'  : 'bi-file-earmark-pdf';
                const tagCls  = 'tag-' + doc.tag;
                const fname   = doc.file_path.split('/').pop();

                const div = document.createElement('div');
                div.className = 'doc-item';
                div.id = 'doc-item-' + doc.id;
                div.innerHTML = `
            <div class="doc-item-icon ${iconCls}">
                <i class="bi ${iconBi}"></i>
            </div>
            <div class="doc-item-info">
                <div class="doc-item-name">${escHtml(fname)}</div>
                <div class="doc-item-meta">
                    <span class="tag-pill ${tagCls}">${escHtml(doc.tag_label)}</span>
                    ${doc.description ? `<span class="doc-item-desc">${escHtml(doc.description)}</span>` : ''}
                    <span class="doc-item-date">${escHtml(doc.created_at)}</span>
                </div>
            </div>
            <div class="doc-item-actions">
                <a href="${escHtml(doc.file_path)}" target="_blank" class="btn-doc-view" title="Ver">
                    <i class="bi bi-eye"></i>
                </a>
                <button type="button" class="btn-doc-delete" data-id="${doc.id}" title="Eliminar">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>`;

                docList.prepend(div);
                bindDelete(div.querySelector('.btn-doc-delete'));
            }

            // ── Eliminar ─────────────────────────────────────────────────
            document.querySelectorAll('.btn-doc-delete').forEach(bindDelete);

            function bindDelete(btn) {
                btn.addEventListener('click', function () {
                    const id   = this.dataset.id;
                    const item = document.getElementById('doc-item-' + id);
                    if (!confirm('¿Eliminar este documento? Esta acción no se puede deshacer.')) return;

                    fetch(`${DELETE_URL}/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `${CSRF_NAME}=${csrfHash}`,
                    })
                        .then(r => r.json())
                        .then(res => {
                            if (res.csrf_hash) csrfHash = res.csrf_hash;
                            if (res.success) {
                                item.style.animation = 'none';
                                item.style.transition = 'opacity .2s, transform .2s';
                                item.style.opacity = '0';
                                item.style.transform = 'translateX(10px)';
                                setTimeout(() => {
                                    item.remove();
                                    if (!docList.querySelector('.doc-item')) {
                                        docList.innerHTML = `<div class="doc-empty" id="docEmpty">
                                <i class="bi bi-folder2"></i>
                                Todavía no has subido ningún documento.
                            </div>`;
                                    }
                                }, 220);
                                toast('Documento eliminado.', 'ok');
                            } else {
                                toast(res.message || 'No se pudo eliminar.', 'error');
                            }
                        })
                        .catch(() => toast('Error de red.', 'error'));
                });
            }

            // ── Toast ────────────────────────────────────────────────────
            function toast(msg, type = 'ok') {
                const t = document.createElement('div');
                t.className = 'doc-toast' + (type === 'error' ? ' error' : '');
                t.innerHTML = `<i class="bi ${type === 'ok' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'}"></i> ${escHtml(msg)}`;
                toastWrap.appendChild(t);
                setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .3s'; setTimeout(() => t.remove(), 300); }, 3500);
            }

            function escHtml(str) {
                return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
            }
        })();
    </script>

<?= $this->endSection() ?>