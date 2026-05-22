<?php
/**
 * onboarding/import/extract_form.php
 * Formulario inicial: 3 modos de entrada (URL / PDF·Imagen / Texto pegado).
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Importar negocio — Onboarding</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f8fafc; font-family: 'Inter', system-ui, sans-serif; }
        .nav-tabs .nav-link { color: #64748b; border: none; padding: .75rem 1.25rem; }
        .nav-tabs .nav-link.active { color: #6366f1; border-bottom: 2px solid #6366f1; background: transparent; }
        .drop-zone {
            border: 2px dashed #c7d2fe; border-radius: 12px;
            padding: 2rem; text-align: center; background: #f8faff;
            transition: all .2s; cursor: pointer;
        }
        .drop-zone:hover, .drop-zone.dragover { border-color: #6366f1; background: #eef2ff; }
        .btn-primary-wiz {
            background: #6366f1; color: #fff; border: none;
            padding: .7rem 1.75rem; border-radius: 10px; font-weight: 600;
        }
        .btn-primary-wiz:hover { background: #4f46e5; color: #fff; }
    </style>
</head>
<body>

<div class="container py-4" style="max-width: 820px;">

    <div class="d-flex align-items-center mb-3">
        <a href="/onboarding" class="text-decoration-none text-muted me-3">
            <i class="bi bi-arrow-left"></i> Volver al wizard
        </a>
    </div>

    <h2 class="mb-1">🪄 Importa tu negocio automáticamente</h2>
    <p class="text-muted">
        Pega la URL de tu sitio web, sube un PDF o imagen de tu brochure, o pega texto.
        Extraeremos
        <?php if ($profile['has_accommodation'] ?? false): ?>habitaciones, tarifas, <?php endif ?>
        <?php if ($profile['has_tours'] ?? false): ?>tours, <?php endif ?>
        precios y fotos automáticamente.
    </p>

    <?php if (session('error')): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle me-1"></i> <?= esc(session('error')) ?>
        </div>
    <?php endif ?>

    <?php if (!empty($latest)):
        $status = $latest['status'] ?? '';
        ?>
        <?php if ($status === 'extracted' || $status === 'reviewed'): ?>
            <div class="alert alert-info d-flex align-items-center justify-content-between">
                <span>Tienes una extracción reciente lista para revisar.</span>
                <a href="/onboarding/import/review/<?= $latest['id'] ?>" class="btn btn-sm btn-primary">
                    Revisar ahora →
                </a>
            </div>
        <?php elseif ($status === 'imported'): ?>
            <div class="alert alert-success">
                ✅ Ya importaste contenido anteriormente. Puedes importar más abajo.
            </div>
        <?php endif ?>
    <?php endif ?>

    <ul class="nav nav-tabs mt-4" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-urls">
                <i class="bi bi-globe me-1"></i> Desde URL
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-file">
                <i class="bi bi-file-earmark-pdf me-1"></i> Desde PDF o imagen
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-text">
                <i class="bi bi-card-text me-1"></i> Pegar texto
            </button>
        </li>
    </ul>

    <div class="tab-content bg-white border border-top-0 rounded-bottom p-4 mb-4">

        <!-- ══════════════════════════════════════════════════════════════
             TAB: URLs
        ══════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade show active" id="tab-urls">
            <form action="/onboarding/import/extract" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="mode" value="urls">

                <label class="form-label fw-semibold">
                    URL(s) de tu sitio web — una por línea, máximo 5
                </label>
                <textarea name="urls" class="form-control" rows="4"
                          placeholder="https://mihotel.com&#10;https://mihotel.com/habitaciones&#10;https://mihotel.com/tours"
                          required></textarea>
                <div class="form-text">
                    <i class="bi bi-info-circle me-1"></i>
                    Pega la página de inicio y, opcionalmente, páginas internas con detalle (habitaciones, tarifas, tours).
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn-primary-wiz">
                        <i class="bi bi-magic me-1"></i> Extraer información
                    </button>
                </div>
            </form>
        </div>

        <!-- ══════════════════════════════════════════════════════════════
             TAB: archivo
        ══════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-file">
            <form action="/onboarding/import/extract" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="mode" value="file">

                <div class="drop-zone" id="dropZone"
                     onclick="document.getElementById('fileInput').click()">
                    <i class="bi bi-cloud-upload" style="font-size:2.2rem;color:#6366f1"></i>
                    <p class="mb-1 mt-2 fw-semibold">Haz clic o arrastra tu archivo aquí</p>
                    <p class="text-muted small mb-0">
                        PDF, JPG, PNG o WEBP · Máx. 8 MB
                    </p>
                    <input type="file" id="fileInput" name="file"
                           accept="application/pdf,image/jpeg,image/png,image/webp"
                           class="d-none" required>
                </div>

                <div id="fileSelected" class="mt-3 d-none alert alert-success">
                    <i class="bi bi-file-check me-1"></i>
                    <span id="fileSelectedName"></span>
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn-primary-wiz">
                        <i class="bi bi-magic me-1"></i> Extraer del archivo
                    </button>
                </div>
            </form>
        </div>

        <!-- ══════════════════════════════════════════════════════════════
             TAB: texto pegado
        ══════════════════════════════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-text">
            <form action="/onboarding/import/extract" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="mode" value="text">

                <label class="form-label fw-semibold">
                    Pega aquí la descripción de tu negocio
                </label>
                <textarea name="text" class="form-control" rows="10"
                          placeholder="Pega aquí: descripción del hotel, lista de habitaciones, precios, tours, lo que sea. Mientras más detalle, mejor."
                          required minlength="30"></textarea>
                <div class="form-text">
                    <i class="bi bi-lightbulb me-1"></i>
                    Tip: incluye nombres de habitaciones, capacidades, precios, tipos de cama, y cualquier servicio adicional.
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn-primary-wiz">
                        <i class="bi bi-magic me-1"></i> Extraer del texto
                    </button>
                </div>
            </form>
        </div>

    </div>

    <div class="small text-muted">
        💡 Después de extraer podrás revisar y editar todo antes de aplicar a tu PMS.
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Drag & drop básico
    const dropZone  = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const selected  = document.getElementById('fileSelected');
    const selName   = document.getElementById('fileSelectedName');

    ['dragover','dragenter'].forEach(ev => dropZone.addEventListener(ev, e => {
        e.preventDefault(); dropZone.classList.add('dragover');
    }));
    ['dragleave','drop'].forEach(ev => dropZone.addEventListener(ev, e => {
        e.preventDefault(); dropZone.classList.remove('dragover');
    }));
    dropZone.addEventListener('drop', e => {
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            showFileName();
        }
    });
    fileInput.addEventListener('change', showFileName);

    function showFileName() {
        if (!fileInput.files[0]) return;
        selName.textContent = fileInput.files[0].name;
        selected.classList.remove('d-none');
    }
</script>

</body>
</html>
