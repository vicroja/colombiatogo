<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= base_url('/inventory') ?>" class="text-decoration-none text-muted mb-2 d-inline-block">&larr; Volver al Inventario</a>
                <h2 class="mb-0">Editar Unidad: <span class="text-primary"><?= esc($unit['name']) ?></span></h2>
            </div>
            <button type="submit" form="editUnitForm" class="btn btn-primary btn-lg shadow-sm">
                <i class="bi bi-save"></i> Guardar Cambios
            </button>
        </div>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm"><i class="bi bi-check-circle-fill"></i> <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm"><i class="bi bi-exclamation-triangle-fill"></i> <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <ul class="nav nav-tabs mb-4" id="unitTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab"><i class="bi bi-info-circle"></i> 1. Datos Generales</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="rooms-tab" data-bs-toggle="tab" data-bs-target="#rooms" type="button" role="tab"><i class="bi bi-door-open"></i> 2. Distribución (Habitaciones)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="gallery-tab" data-bs-toggle="tab" data-bs-target="#gallery" type="button" role="tab"><i class="bi bi-images"></i> 3. Galería Multimedia</button>
            </li>
        </ul>

        <form action="<?= base_url("inventory/unit/update/{$unit['id']}") ?>" method="POST" enctype="multipart/form-data" id="editUnitForm">
            <?= csrf_field() ?>

            <div class="tab-content" id="unitTabsContent">

                <div class="tab-pane fade show active" id="basic" role="tabpanel">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold">Nombre / Número de la Unidad</label>
                                    <input type="text" class="form-control" name="parent_name" value="<?= esc($unit['name']) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Tipo de Alojamiento Principal</label>
                                    <select name="type_id" class="form-select" required>
                                        <?php foreach($types as $t): ?>
                                            <option value="<?= $t['id'] ?>" <?= ($t['id'] == $unit['type_id']) ? 'selected' : '' ?>>
                                                <?= esc($t['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Estado</label>
                                    <select name="status" class="form-select">
                                        <option value="available" <?= ($unit['status'] == 'available') ? 'selected' : '' ?>>Disponible</option>
                                        <option value="maintenance" <?= ($unit['status'] == 'maintenance') ? 'selected' : '' ?>>Mantenimiento</option>
                                        <option value="blocked" <?= ($unit['status'] == 'blocked') ? 'selected' : '' ?>>Bloqueada</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Descripción General</label>
                                    <textarea name="parent_description" class="form-control" rows="3"><?= esc($unit['description']) ?></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <label class="form-label fw-bold text-primary border-bottom w-100 pb-2 mb-3">Amenidades Compartidas (Aplica a toda la unidad)</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        <?php
                                        $parentAmenityIds = $unit['amenities_list'] ?? [];
                                        foreach($amenities as $amenity):
                                            $isChecked = in_array($amenity['id'], $parentAmenityIds) ? 'checked' : '';
                                            ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="parent_amenities[]" value="<?= $amenity['id'] ?>" id="parent_amenity_<?= $amenity['id'] ?>" <?= $isChecked ?>>
                                                <label class="form-check-label" for="parent_amenity_<?= $amenity['id'] ?>">
                                                    <?= esc($amenity['name']) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="rooms" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 text-secondary">Configuración de Habitaciones</h5>
                        <button type="button" class="btn btn-sm btn-success fw-bold shadow-sm" onclick="addRoom()">
                            <i class="bi bi-plus-circle"></i> Agregar Habitación
                        </button>
                    </div>

                    <div id="rooms-container">
                        <?php
                        $roomIndex = 0;
                        $rooms = $unit['rooms'] ?? [];
                        foreach ($rooms as $room):
                            ?>
                            <div class="card shadow-sm mb-3 border-primary room-card" id="room-card-<?= $roomIndex ?>">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                    <h6 class="mb-0 fw-bold text-primary">Habitación (Edición)</h6>
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0" onclick="removeElement('room-card-<?= $roomIndex ?>')"><i class="bi bi-trash"></i> Eliminar</button>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" name="rooms[<?= $roomIndex ?>][id]" value="<?= $room['id'] ?>">

                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Nombre del Cuarto</label>
                                            <input type="text" name="rooms[<?= $roomIndex ?>][name]" class="form-control form-control-sm" value="<?= esc($room['name']) ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Tipo</label>
                                            <select name="rooms[<?= $roomIndex ?>][type_id]" class="form-select form-select-sm" required>
                                                <?php foreach($types as $t): ?>
                                                    <option value="<?= $t['id'] ?>" <?= ($t['id'] == $room['type_id']) ? 'selected' : '' ?>><?= esc($t['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Cant. Baños</label>
                                            <input type="number" name="rooms[<?= $roomIndex ?>][bathrooms]" class="form-control form-control-sm" value="<?= esc($room['bathrooms']) ?>" min="0" step="0.5">
                                        </div>
                                    </div>

                                    <div class="p-3 bg-light rounded border mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold small text-muted">Camas en esta habitación</span>
                                            <button type="button" class="btn btn-sm btn-outline-primary py-0" onclick="addBed(<?= $roomIndex ?>)">+ Añadir Cama</button>
                                        </div>
                                        <div id="beds-container-<?= $roomIndex ?>" class="row g-2">
                                            <?php
                                            $bedIndex = 0;
                                            foreach ($room['beds'] ?? [] as $bed):
                                                ?>
                                                <div class="col-md-6 bed-item" id="bed-<?= $roomIndex ?>-<?= $bedIndex ?>">
                                                    <div class="input-group input-group-sm shadow-sm">
                                                        <select name="rooms[<?= $roomIndex ?>][beds][<?= $bedIndex ?>][bed_type_id]" class="form-select" required>
                                                            <?php foreach($bedTypes as $bt): ?>
                                                                <option value="<?= $bt['id'] ?>" <?= ($bt['id'] == $bed['bed_type_id']) ? 'selected' : '' ?>><?= esc($bt['name']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <span class="input-group-text">Cant.</span>
                                                        <input type="number" name="rooms[<?= $roomIndex ?>][beds][<?= $bedIndex ?>][quantity]" class="form-control" value="<?= esc($bed['quantity']) ?>" min="1" required style="max-width: 60px;">
                                                        <button class="btn btn-outline-danger" type="button" onclick="removeElement('bed-<?= $roomIndex ?>-<?= $bedIndex ?>')">X</button>
                                                    </div>
                                                </div>
                                                <?php
                                                $bedIndex++;
                                            endforeach;
                                            ?>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="form-label small fw-bold d-block">Características Específicas del Cuarto</label>
                                        <?php
                                        $roomAmenityIds = $room['amenities_list'] ?? [];
                                        foreach($amenities as $amenity):
                                            $isChecked = in_array($amenity['id'], $roomAmenityIds) ? 'checked' : '';
                                            ?>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="rooms[<?= $roomIndex ?>][amenities][]" value="<?= $amenity['id'] ?>" id="room_<?= $roomIndex ?>_amenity_<?= $amenity['id'] ?>" <?= $isChecked ?>>
                                                <label class="form-check-label small" for="room_<?= $roomIndex ?>_amenity_<?= $amenity['id'] ?>"><?= esc($amenity['name']) ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $roomIndex++;
                        endforeach;
                        ?>
                    </div>
                </div>

                <div class="tab-pane fade" id="gallery" role="tabpanel">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">

                            <div class="mb-4 p-4 border rounded bg-light">
                                <h6 class="fw-bold mb-3"><i class="bi bi-cloud-arrow-up"></i> Agregar Nuevos Archivos</h6>
                                <div class="mb-3">
                                    <label for="media" class="form-label">Seleccionar Imágenes o Videos</label>
                                    <input class="form-control" type="file" id="media" name="media[]" multiple accept="image/*,video/*">
                                    <div class="form-text">Puedes seleccionar múltiples archivos a la vez.</div>
                                </div>
                                <div id="preview-container" class="mt-3">
                                </div>
                            </div>

                            <div class="mt-4">
                                <h6 class="fw-bold mb-3 border-bottom pb-2">Archivos Existentes</h6>
                                <div class="row g-3">
                                    <?php if(empty($media)): ?>
                                        <p class="text-muted small">No hay archivos multimedia para esta unidad.</p>
                                    <?php else: ?>
                                        <?php foreach($media as $m): ?>
                                            <div class="col-md-3 col-sm-6 mb-3">
                                                <div class="card h-100 shadow-sm border-0">
                                                    <div class="position-relative overflow-hidden rounded-top" style="height: 140px; background: #000;">
                                                        <?php if($m['file_type'] == 'video'): ?>
                                                            <video src="<?= base_url($m['file_path']) ?>" class="w-100 h-100" style="object-fit: cover;" muted></video>
                                                            <div class="position-absolute bottom-0 start-0 bg-dark text-white small px-2 py-1"><i class="bi bi-camera-video"></i></div>
                                                        <?php else: ?>
                                                            <img src="<?= base_url($m['file_path']) ?>" class="w-100 h-100" style="object-fit: cover;">
                                                        <?php endif; ?>

                                                        <a href="<?= base_url('/inventory/delete-unit-media/'.$m['id']) ?>" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 shadow" onclick="return confirm('¿Borrar este archivo permanentemente?');">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </div>
                                                    <div class="card-body p-2">
                                                        <input type="text" name="existing_media_descriptions[<?= $m['id'] ?>]" class="form-control form-control-sm" placeholder="Descripción breve..." value="<?= esc($m['description'] ?? '') ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div> </form>
    </div>

    <script>
        // Variables dinámicas desde PHP para el clonador
        let roomIndex = <?= count($unit['rooms'] ?? []) ?>;
        const bedTypes = <?= json_encode($bedTypes) ?>;
        const amenities = <?= json_encode($amenities) ?>;
        const accommodationTypes = <?= json_encode($types) ?>;

        /* ---------------------------------------------------
           Lógica de Habitaciones y Camas (Misma que en Create)
           --------------------------------------------------- */
        function addRoom() {
            const container = document.getElementById('rooms-container');

            let typeOptions = '<option value="">Seleccione...</option>';
            accommodationTypes.forEach(t => { typeOptions += `<option value="${t.id}">${t.name}</option>`; });

            let amenitiesHtml = '';
            amenities.forEach(a => {
                amenitiesHtml += `
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="rooms[${roomIndex}][amenities][]" value="${a.id}" id="new_room_${roomIndex}_amenity_${a.id}">
                    <label class="form-check-label small" for="new_room_${roomIndex}_amenity_${a.id}">${a.name}</label>
                </div>`;
            });

            const roomHtml = `
            <div class="card shadow-sm mb-3 border-secondary room-card" id="room-card-${roomIndex}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                    <h6 class="mb-0 fw-bold">Nueva Habitación</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0" onclick="removeElement('room-card-${roomIndex}')"><i class="bi bi-trash"></i> Eliminar</button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Nombre del Cuarto</label>
                            <input type="text" name="rooms[${roomIndex}][name]" class="form-control form-control-sm" required placeholder="Ej. Alcoba">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Tipo</label>
                            <select name="rooms[${roomIndex}][type_id]" class="form-select form-select-sm" required>${typeOptions}</select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Cant. Baños</label>
                            <input type="number" name="rooms[${roomIndex}][bathrooms]" class="form-control form-control-sm" value="1" min="0" step="0.5">
                        </div>
                    </div>
                    <div class="p-3 bg-light rounded border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold small text-muted">Camas</span>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0" onclick="addBed(${roomIndex})">+ Añadir Cama</button>
                        </div>
                        <div id="beds-container-${roomIndex}" class="row g-2"></div>
                    </div>
                    <div>
                        <label class="form-label small fw-bold d-block">Características Específicas del Cuarto</label>
                        ${amenitiesHtml}
                    </div>
                </div>
            </div>
        `;

            container.insertAdjacentHTML('beforeend', roomHtml);
            addBed(roomIndex); // Agregar una cama por defecto a la nueva habitación
            roomIndex++;
        }

        function addBed(rIndex) {
            const container = document.getElementById(`beds-container-${rIndex}`);
            const bedIndex = container.children.length;

            let bedOptions = '<option value="">Tipo Cama...</option>';
            bedTypes.forEach(b => { bedOptions += `<option value="${b.id}">${b.name}</option>`; });

            const bedHtml = `
            <div class="col-md-6 bed-item" id="bed-${rIndex}-${bedIndex}">
                <div class="input-group input-group-sm shadow-sm">
                    <select name="rooms[${rIndex}][beds][${bedIndex}][bed_type_id]" class="form-select" required>${bedOptions}</select>
                    <span class="input-group-text">Cant.</span>
                    <input type="number" name="rooms[${rIndex}][beds][${bedIndex}][quantity]" class="form-control" value="1" min="1" required style="max-width: 60px;">
                    <button class="btn btn-outline-danger" type="button" onclick="removeElement('bed-${rIndex}-${bedIndex}')">X</button>
                </div>
            </div>
        `;
            container.insertAdjacentHTML('beforeend', bedHtml);
        }

        function removeElement(elementId) {
            const el = document.getElementById(elementId);
            if (el) el.remove();
        }

        /* ---------------------------------------------------
           Lógica de Galería Multimedia (Tu código original)
           --------------------------------------------------- */
        document.addEventListener("DOMContentLoaded", function () {
            const fileInput = document.getElementById('media');
            const previewContainer = document.getElementById('preview-container');

            fileInput.addEventListener('change', function () {
                previewContainer.innerHTML = '';
                const files = Array.from(fileInput.files);

                files.forEach((file, index) => {
                    if (file.type.startsWith('image/') || file.type.startsWith('video/')) {
                        const fileUrl = URL.createObjectURL(file);
                        const isVideo = file.type.startsWith('video/');

                        let mediaElement = isVideo
                            ? `<video src="${fileUrl}" class="img-fluid rounded border h-100 w-100" style="object-fit: cover;"></video>`
                            : `<img src="${fileUrl}" class="img-fluid rounded border h-100 w-100" style="object-fit: cover;">`;

                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'd-flex align-items-center mb-2 p-2 border border-primary rounded bg-white shadow-sm';
                        itemDiv.innerHTML = `
                        <div style="width: 60px; height: 60px; flex-shrink: 0;" class="me-3">
                            ${mediaElement}
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="mb-1 small fw-bold text-truncate" title="${file.name}">${file.name}</p>
                            <input type="text" name="new_media_descriptions[]" class="form-control form-control-sm" placeholder="Escribe una descripción (opcional)...">
                        </div>
                    `;
                        previewContainer.appendChild(itemDiv);
                    }
                });
            });
        });
    </script>

<?= $this->endSection() ?>