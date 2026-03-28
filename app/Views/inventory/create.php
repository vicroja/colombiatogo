<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Configurar Nueva Cabaña / Alojamiento</h3>
            <a href="<?= base_url('/inventory') ?>" class="btn btn-outline-secondary">Cancelar y Volver</a>
        </div>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <form action="<?= base_url('/inventory/store') ?>" method="post" id="inventoryForm">
            <?= csrf_field() ?>

            <div class="card shadow-sm mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-house-door"></i> 1. Datos Generales del Alojamiento</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo de Alojamiento Principal</label>
                            <select name="type_id" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach($types as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre de la Cabaña/Unidad</label>
                            <input type="text" name="parent_name" class="form-control" required placeholder="Ej. Cabaña Los Pinos, Villa 1">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Descripción General</label>
                            <textarea name="parent_description" class="form-control" rows="2" placeholder="Breve descripción del espacio..."></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Amenidades Compartidas (De toda la cabaña)</label>
                            <div class="d-flex flex-wrap gap-3">
                                <?php foreach($amenities as $amenity): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="parent_amenities[]" value="<?= $amenity['id'] ?>" id="parent_amenity_<?= $amenity['id'] ?>">
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

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0 text-secondary">2. Distribución (Habitaciones)</h4>
                <button type="button" class="btn btn-sm btn-success fw-bold" onclick="addRoom()">
                    <i class="bi bi-plus-circle"></i> Agregar Habitación
                </button>
            </div>

            <div id="rooms-container"></div>

            <div class="card shadow-sm mt-4">
                <div class="card-body text-end">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="bi bi-save"></i> Guardar Inventario Completo
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        let roomIndex = 0;

        // Pasamos los catálogos de PHP a JS de forma segura para usarlos al clonar
        const bedTypes = <?= json_encode($bedTypes) ?>;
        const amenities = <?= json_encode($amenities) ?>;
        const accommodationTypes = <?= json_encode($types) ?>;

        function addRoom() {
            console.log(`[InventoryBuilder] Agregando habitación index: ${roomIndex}`);
            const container = document.getElementById('rooms-container');

            // Generar options para tipos de alojamiento
            let typeOptions = '<option value="">Seleccione...</option>';
            accommodationTypes.forEach(t => {
                typeOptions += `<option value="${t.id}">${t.name}</option>`;
            });

            // Generar checkboxes de amenidades
            let amenitiesHtml = '';
            amenities.forEach(a => {
                amenitiesHtml += `
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="rooms[${roomIndex}][amenities][]" value="${a.id}" id="room_${roomIndex}_amenity_${a.id}">
                    <label class="form-check-label small" for="room_${roomIndex}_amenity_${a.id}">${a.name}</label>
                </div>`;
            });

            const roomHtml = `
            <div class="card shadow-sm mb-3 border-secondary room-card" id="room-card-${roomIndex}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                    <h6 class="mb-0 fw-bold">Habitación ${roomIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0" onclick="removeElement('room-card-${roomIndex}')">X Quitar</button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Nombre del Cuarto</label>
                            <input type="text" name="rooms[${roomIndex}][name]" class="form-control form-control-sm" required placeholder="Ej. Alcoba Principal">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Tipo</label>
                            <select name="rooms[${roomIndex}][type_id]" class="form-select form-select-sm" required>
                                ${typeOptions}
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Cant. Baños</label>
                            <input type="number" name="rooms[${roomIndex}][bathrooms]" class="form-control form-control-sm" value="1" min="0" step="0.5">
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold small text-muted">Camas en esta habitación</span>
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

            // Agregar al menos una cama por defecto
            addBed(roomIndex);

            roomIndex++;
        }

        function addBed(rIndex) {
            const container = document.getElementById(`beds-container-${rIndex}`);
            const bedIndex = container.children.length; // Contamos cuántas camas tiene esta habitación

            let bedOptions = '<option value="">Tipo Cama...</option>';
            bedTypes.forEach(b => {
                bedOptions += `<option value="${b.id}">${b.name}</option>`;
            });

            const bedHtml = `
            <div class="col-md-6 bed-item" id="bed-${rIndex}-${bedIndex}">
                <div class="input-group input-group-sm">
                    <select name="rooms[${rIndex}][beds][${bedIndex}][bed_type_id]" class="form-select" required>
                        ${bedOptions}
                    </select>
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

        // Inicializar con una habitación vacía al cargar
        document.addEventListener("DOMContentLoaded", () => {
            addRoom();
        });
    </script>

<?= $this->endSection() ?>