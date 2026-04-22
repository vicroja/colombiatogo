<?php /** inventory/wizard/step3_photos.php */ ?>

<div class="iw-step-header">
    <div class="iw-step-eyebrow">Paso 3 de 3 · Opcional</div>
    <h1 class="iw-step-title-main">Fotos de la unidad</h1>
    <p class="iw-step-hint">
        Sube imágenes de «<?= esc($unitName ?? 'la unidad') ?>». Puedes hacerlo ahora o después desde la edición.
    </p>
</div>

<div class="iw-card" style="padding:14px 20px;background:#f0fdf4;border-color:#6ee7b7;margin-bottom:20px;">
    <i class="bi bi-check-circle-fill" style="color:#059669;font-size:18px;margin-right:8px;"></i>
    <strong style="color:#065f46;">«<?= esc($unitName ?? 'La unidad') ?>» fue creada correctamente.</strong>
    <span style="color:#065f46;font-size:13px;"> Agrega fotos o ve al inventario ahora.</span>
</div>

<form action="<?= base_url('/inventory/wizard/save/3') ?>" method="post"
      enctype="multipart/form-data" id="step3-form">
    <?= csrf_field() ?>
    <input type="hidden" name="unit_id" value="<?= $unitId ?? '' ?>">

    <div class="iw-card">
        <div class="iw-card-title"><i class="bi bi-images"></i> Galería</div>

        <!-- Zona de drop -->
        <div class="upload-zone" id="drop-zone"
             onclick="document.getElementById('photo-input').click()">
            <i class="bi bi-cloud-arrow-up"></i>
            <p>Haz clic o arrastra fotos aquí</p>
            <span>JPG, PNG, WEBP · Máx. 5MB por foto · Puedes subir varias a la vez</span>
        </div>

        <!-- Input real — múltiple, siempre sincronizado -->
        <input type="file" id="photo-input" name="photos[]"
               multiple accept="image/*" style="display:none"
               onchange="handleFileSelect(this.files)">

        <!-- Preview grid -->
        <div class="photo-grid" id="photo-grid"></div>
        <p id="no-photos-hint" style="font-size:12px;color:#94a3b8;margin-top:10px;display:none;">
            No hay fotos seleccionadas. El formulario guardará sin fotos.
        </p>

        <!-- Fotos ya subidas (si hay) -->
        <?php if (!empty($existingPhotos)): ?>
            <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--iw-border);">
                <div class="iw-label" style="margin-bottom:10px;">Fotos ya subidas</div>
                <div class="photo-grid">
                    <?php foreach ($existingPhotos as $p): ?>
                        <div class="photo-item">
                            <img src="<?= base_url($p['file_path']) ?>" alt="">
                            <a href="<?= base_url('/inventory/delete-unit-media/' . $p['id']) ?>"
                               class="photo-remove"
                               onclick="return confirm('¿Eliminar esta foto?')">×</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="iw-footer">
        <a href="<?= base_url('/inventory/wizard/step/2') ?>" class="btn-iw-back">
            <i class="bi bi-arrow-left"></i> Atrás
        </a>
        <div style="display:flex;gap:10px;">
            <a href="<?= base_url('/inventory') ?>" class="btn-iw-skip">
                Ir al inventario sin fotos
            </a>
            <button type="submit" class="btn-iw-next" id="btn-upload">
                <i class="bi bi-cloud-upload"></i> Subir y finalizar
            </button>
        </div>
    </div>
</form>

<script>
    const dropZone   = document.getElementById('drop-zone');
    const photoInput = document.getElementById('photo-input');
    const photoGrid  = document.getElementById('photo-grid');

    // Drag & drop
    dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        // Combinar con archivos ya seleccionados
        const existing = Array.from(photoInput.files);
        const added    = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
        setFiles([...existing, ...added]);
    });

    function handleFileSelect(fileList) {
        const existing = Array.from(photoInput.files);
        // Evitar duplicados por nombre
        const newFiles = Array.from(fileList).filter(
            f => !existing.some(e => e.name === f.name && e.size === f.size)
        );
        setFiles([...existing, ...newFiles]);
    }

    function setFiles(filesArray) {
        // Reconstruir el FileList — usa DataTransfer donde esté disponible
        // En Safari se hace submit normal con los archivos seleccionados manualmente
        try {
            const dt = new DataTransfer();
            filesArray.forEach(f => dt.items.add(f));
            photoInput.files = dt.files;
        } catch(e) {
            // Safari fallback: no podemos reconstruir el FileList,
            // así que simplemente dejamos el input tal como está
        }
        renderPreviews(filesArray);
    }

    function renderPreviews(filesArray) {
        photoGrid.innerHTML = '';
        filesArray.forEach((file, idx) => {
            const reader = new FileReader();
            reader.onload = e => {
                photoGrid.insertAdjacentHTML('beforeend', `
                <div class="photo-item" id="preview-${idx}">
                    <img src="${e.target.result}" alt="${file.name}">
                    <button type="button" class="photo-remove"
                            onclick="removeFile(${idx})">×</button>
                </div>
            `);
            };
            reader.readAsDataURL(file);
        });
    }

    function removeFile(idx) {
        const current = Array.from(photoInput.files);
        current.splice(idx, 1);
        setFiles(current);
    }
</script>