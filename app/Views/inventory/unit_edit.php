<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0"><?= esc($title) ?></h3>
            <a href="<?= base_url('inventory') ?>" class="btn btn-outline-secondary">Volver al Inventario</a>
        </div>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <form action="<?= base_url("inventory/unit/update/{$unit['id']}") ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Información General</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Nombre / Número de Unidad</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= esc($unit['name']) ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="type_id" class="form-label">Tipo de Alojamiento</label>
                                    <select class="form-select" id="type_id" name="type_id" required>
                                        <?php foreach ($types as $type): ?>
                                            <option value="<?= $type['id'] ?>" <?= ($type['id'] == $unit['type_id']) ? 'selected' : '' ?>>
                                                <?= esc($type['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="status" class="form-label">Estado</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="available" <?= ($unit['status'] == 'available') ? 'selected' : '' ?>>Disponible</option>
                                        <option value="maintenance" <?= ($unit['status'] == 'maintenance') ? 'selected' : '' ?>>Mantenimiento</option>
                                        <option value="blocked" <?= ($unit['status'] == 'blocked') ? 'selected' : '' ?>>Bloqueado</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Descripción Detallada</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?= esc($unit['description'] ?? '') ?></textarea>
                                <small class="text-muted">Esta descripción puede mostrarse en tu motor de reservas o página web.</small>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Características y Comodidades (Amenities)</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="bathrooms" class="form-label">Número de Baños</label>
                                    <input type="number" class="form-control" id="bathrooms" name="bathrooms" min="0" value="<?= esc($features['bathrooms'] ?? 1) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="beds" class="form-label">Número de Camas</label>
                                    <input type="number" class="form-control" id="beds" name="beds" min="1" value="<?= esc($features['beds'] ?? 1) ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="has_ac" name="has_ac" value="1" <?= !empty($features['has_ac']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="has_ac">Aire Acondicionado</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="has_wifi" name="has_wifi" value="1" <?= !empty($features['has_wifi']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="has_wifi">Wi-Fi</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="has_tv" name="has_tv" value="1" <?= !empty($features['has_tv']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="has_tv">Televisión</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="has_kitchen" name="has_kitchen" value="1" <?= !empty($features['has_kitchen']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="has_kitchen">Cocina Equipada</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="pet_friendly" name="pet_friendly" value="1" <?= !empty($features['pet_friendly']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="pet_friendly">Pet Friendly</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_private" name="is_private" value="1" <?= !empty($features['is_private']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_private">Habitación Privada</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-md-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Fotos y Videos</h5>
                        </div>
                        <div class="card-body">

                            <div class="mb-3">
                                <label for="media" class="form-label fw-bold">Subir nuevos archivos</label>
                                <input class="form-control" type="file" id="media_upload" name="media[]" multiple accept="image/*,video/mp4,video/webm">
                                <small class="text-muted">Selecciona varios archivos. Podrás agregarles una descripción abajo.</small>
                            </div>

                            <div id="media_preview_container" class="mb-4"></div>

                            <hr>

                            <h6 class="fw-bold mb-3">Archivos Actuales</h6>
                            <div class="row g-3 mt-2">
                                <?php if (empty($media)): ?>
                                    <p class="text-muted small">No hay fotos ni videos subidos para esta unidad.</p>
                                <?php else: ?>
                                    <?php foreach ($media as $item): ?>
                                        <div class="col-12 position-relative border rounded p-2 bg-light">
                                            <div class="d-flex align-items-center mb-2">
                                                <div style="width: 80px; height: 60px; flex-shrink: 0;" class="me-3">
                                                    <?php if ($item['file_type'] == 'image'): ?>
                                                        <img src="<?= base_url($item['file_path']) ?>" class="img-fluid rounded border h-100 w-100" style="object-fit: cover;">
                                                    <?php elseif ($item['file_type'] == 'video'): ?>
                                                        <video src="<?= base_url($item['file_path']) ?>" class="img-fluid rounded border h-100 w-100" style="object-fit: cover;"></video>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <input type="text" name="existing_media_descriptions[<?= $item['id'] ?>]" value="<?= esc($item['description'] ?? '') ?>" class="form-control form-control-sm" placeholder="Descripción (ej. Vista al mar)">
                                                </div>
                                            </div>

                                            <a href="<?= base_url("inventory/unit/media/delete/{$item['id']}") ?>"
                                               class="btn btn-outline-danger btn-sm w-100"
                                               onclick="return confirm('¿Seguro que deseas eliminar este archivo?');">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 sticky-top" style="top: 20px;">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">Guardar Cambios</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mediaInput = document.getElementById('media_upload');
            const previewContainer = document.getElementById('media_preview_container');

            mediaInput.addEventListener('change', function(event) {
                previewContainer.innerHTML = ''; // Limpiar previas anteriores
                const files = event.target.files;

                if (files.length > 0) {
                    const header = document.createElement('h6');
                    header.className = 'text-primary mt-3 mb-2 small fw-bold';
                    header.innerText = 'Archivos listos para subir:';
                    previewContainer.appendChild(header);

                    Array.from(files).forEach((file, index) => {
                        const fileUrl = URL.createObjectURL(file);
                        const isVideo = file.type.startsWith('video/');

                        // Generar miniatura
                        let mediaElement = isVideo
                            ? `<video src="${fileUrl}" class="img-fluid rounded border h-100 w-100" style="object-fit: cover;"></video>`
                            : `<img src="${fileUrl}" class="img-fluid rounded border h-100 w-100" style="object-fit: cover;">`;

                        // Construir la fila para cada archivo
                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'd-flex align-items-center mb-2 p-2 border border-primary rounded bg-white shadow-sm';
                        itemDiv.innerHTML = `
                    <div style="width: 60px; height: 60px; flex-shrink: 0;" class="me-3">
                        ${mediaElement}
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="mb-1 small fw-bold text-truncate" title="${file.name}">${file.name}</p>
                        <input type="text" name="new_media_descriptions[]" class="form-control form-control-sm" placeholder="Escribe una descripción...">
                    </div>
                `;
                        previewContainer.appendChild(itemDiv);
                    });
                }
            });
        });
    </script>
<?= $this->endSection() ?>