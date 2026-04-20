<?php
/**
 * onboarding/steps/step3_unit.php
 *
 * Paso 3: Crear la primera unidad de alojamiento.
 * Soporta dos modos:
 *   A) Unidad simple (habitación, suite, apartamento)
 *   B) Cabaña/Villa con cuartos hijos (parent → children)
 */

$bedTypes  = $bedTypes  ?? [];
$amenities = $amenities ?? [];

$unitTypes = [
    'simple' => [
        ['value' => 'Habitación Estándar', 'label' => 'Habitación Estándar'],
        ['value' => 'Habitación Doble',    'label' => 'Habitación Doble'],
        ['value' => 'Suite',               'label' => 'Suite'],
        ['value' => 'Apartamento',         'label' => 'Apartamento'],
        ['value' => 'Glamping',            'label' => 'Glamping'],
        ['value' => 'Otro',                'label' => 'Otro'],
    ],
    'compound' => [
        ['value' => 'Cabaña',  'label' => 'Cabaña'],
        ['value' => 'Villa',   'label' => 'Villa'],
        ['value' => 'Casa',    'label' => 'Casa campestre'],
        ['value' => 'Bungalow','label' => 'Bungalow'],
        ['value' => 'Otro',    'label' => 'Otro'],
    ],
];
?>

<style>
    .mode-selector{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:2rem}
    .mode-btn{border:2px solid #e2e8f0;border-radius:14px;padding:1.25rem;cursor:pointer;
        text-align:center;transition:all .2s;background:#fff;position:relative}
    .mode-btn:hover{border-color:#a5b4fc}
    .mode-btn.active{border-color:#6366f1;background:#f0f4ff}
    .mode-btn .mode-icon{font-size:1.8rem;margin-bottom:.5rem;display:block}
    .mode-btn .mode-title{font-weight:700;font-size:.9rem;color:#0f172a}
    .mode-btn .mode-desc{font-size:.75rem;color:#64748b;margin-top:.25rem;line-height:1.4}
    .mode-check{position:absolute;top:10px;right:10px;width:20px;height:20px;
        border-radius:50%;background:#6366f1;display:none;
        align-items:center;justify-content:center;color:#fff;font-size:.7rem}
    .mode-btn.active .mode-check{display:flex}

    .room-row{background:#f8faff;border:1px solid #e0e7ff;border-radius:12px;
        padding:1.25rem;margin-bottom:1rem;position:relative}
    .room-row-header{display:flex;justify-content:space-between;align-items:center;
        margin-bottom:1rem}
    .room-num{font-size:.75rem;font-weight:700;color:#6366f1;text-transform:uppercase;
        letter-spacing:.06em}
    .btn-remove-room{background:none;border:none;color:#e11d48;cursor:pointer;
        font-size:.8rem;padding:.25rem .5rem;border-radius:6px}
    .btn-remove-room:hover{background:#fff1f2}

    .bed-subrow{display:grid;grid-template-columns:1fr auto auto;gap:.5rem;
        align-items:center;margin-bottom:.5rem}
    .btn-add-bed{background:none;border:1px dashed #a5b4fc;color:#6366f1;
        border-radius:8px;padding:.35rem .75rem;font-size:.78rem;
        cursor:pointer;width:100%;margin-top:.5rem;transition:all .2s}
    .btn-add-bed:hover{background:#f0f4ff}
    .btn-remove-bed{background:none;border:none;color:#94a3b8;cursor:pointer;
        font-size:.9rem;padding:.2rem .4rem}
    .btn-remove-bed:hover{color:#e11d48}

    .section-divider{font-size:.72rem;font-weight:700;color:#6366f1;
        text-transform:uppercase;letter-spacing:.08em;
        margin:1.25rem 0 .75rem;display:flex;align-items:center;gap:.5rem}
    .section-divider::after{content:'';flex:1;height:1px;background:#e0e7ff}
</style>

<!-- ── Card principal ────────────────────────────────────────────────────── -->
<div class="wizard-card">
    <div class="card-eyebrow">Paso 3 · Requerido</div>
    <h5>Tu primera unidad de alojamiento</h5>
    <p class="card-hint">
        ¿Es una habitación individual o una cabaña/villa con varios cuartos?
    </p>

    <form action="/onboarding/step/3" method="POST"
          enctype="multipart/form-data" id="formStep3">
        <?= csrf_field() ?>

        <!-- ── Selector de modo ──────────────────────────────────────────── -->
        <div class="mode-selector">
            <div class="mode-btn active" id="btnModeSimple"
                 onclick="setMode('simple')">
                <div class="mode-check"><i class="bi bi-check"></i></div>
                <span class="mode-icon">🛏️</span>
                <div class="mode-title">Habitación / Suite</div>
                <div class="mode-desc">
                    Una sola unidad reservable con sus camas
                </div>
            </div>
            <div class="mode-btn" id="btnModeCompound"
                 onclick="setMode('compound')">
                <div class="mode-check"><i class="bi bi-check"></i></div>
                <span class="mode-icon">🏡</span>
                <div class="mode-title">Cabaña / Villa</div>
                <div class="mode-desc">
                    Se reserva completa, tiene varios cuartos internos
                </div>
            </div>
        </div>

        <!-- Campo oculto que guarda el modo -->
        <input type="hidden" name="unit_mode" id="unit_mode" value="simple">

        <!-- ══════════════════════════════════════════════════════════════
             SECCIÓN COMÚN — nombre, tipo, descripción
        ══════════════════════════════════════════════════════════════ -->
        <div class="row g-3 mb-4">
            <div class="col-md-7">
                <label class="form-label fw-semibold" for="unit_name">
                    Nombre <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control form-control-lg"
                       id="unit_name" name="unit_name"
                       placeholder="Ej: Cabaña Río Verde, Suite 101"
                       required maxlength="50">
                <div class="form-text">
                    Este nombre aparecerá en reservaciones y en el sitio web.
                </div>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-semibold" for="type_name">
                    Tipo <span class="text-danger">*</span>
                </label>
                <select class="form-select form-select-lg"
                        id="type_name" name="type_name" required>
                    <option value="">Selecciona...</option>
                    <optgroup label="— Unidad simple —" id="optSimple">
                        <?php foreach ($unitTypes['simple'] as $t): ?>
                            <option value="<?= esc($t['value']) ?>">
                                <?= esc($t['label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="— Cabaña / Villa —"
                              id="optCompound" style="display:none">
                        <?php foreach ($unitTypes['compound'] as $t): ?>
                            <option value="<?= esc($t['value']) ?>">
                                <?= esc($t['label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>
        </div>

        <!-- ── Descripción con IA ────────────────────────────────────────── -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-semibold mb-0" for="description">
                    Descripción
                </label>
                <div class="d-flex align-items-center gap-2">
                    <div class="ai-loading" id="aiDescLoading">
                        <span class="spinner-border spinner-border-sm"
                              style="color:#6366f1"></span>
                        <span style="font-size:.8rem;color:#6366f1">
                            Generando...
                        </span>
                    </div>
                    <button type="button" class="btn-ai"
                            id="btnAiDesc" onclick="generateDescription()">
                        <i class="bi bi-stars"></i> Generar con IA
                    </button>
                </div>
            </div>
            <div id="aiContextWrap" class="mb-2">
                <input type="text" class="form-control" id="aiContextInput"
                       placeholder="Describe brevemente: 'cabaña con vista al río, jacuzzi, tres cuartos'"
                       style="border-style:dashed;background:#fafbff;font-size:.85rem">
                <div class="form-text">
                    <i class="bi bi-magic me-1" style="color:#6366f1"></i>
                    La IA generará una descripción atractiva para tus huéspedes.
                </div>
            </div>
            <textarea class="form-control" id="description"
                      name="description" rows="3"
                      placeholder="Descripción para el sitio web y reservaciones..."
                      maxlength="1000"></textarea>
            <div class="d-flex justify-content-between mt-1">
                <div id="aiSuggestionBadge" style="display:none">
                    <span class="badge"
                          style="background:#f0f4ff;color:#4338ca;font-size:.72rem">
                        <i class="bi bi-stars me-1"></i>Generado por IA
                    </span>
                </div>
                <small class="text-muted ms-auto" id="descCharCount">
                    0 / 1000
                </small>
            </div>
        </div>

        <!-- ── Capacidad total ───────────────────────────────────────────── -->
        <div class="mb-4">
            <p class="fw-semibold mb-1">
                <i class="bi bi-people me-1 text-primary"></i>
                Capacidad total
            </p>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold"
                           for="base_occupancy">
                        Base <span class="text-danger">*</span>
                    </label>
                    <input type="number" class="form-control text-center"
                           id="base_occupancy" name="base_occupancy"
                           value="2" min="1" max="30" required>
                    <div class="form-text text-center">Incluidos</div>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold"
                           for="max_occupancy">
                        Máximo <span class="text-danger">*</span>
                    </label>
                    <input type="number" class="form-control text-center"
                           id="max_occupancy" name="max_occupancy"
                           value="6" min="1" max="30" required>
                    <div class="form-text text-center">Permitido</div>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold"
                           for="bathrooms">
                        Baños
                    </label>
                    <select class="form-select text-center"
                            id="bathrooms" name="bathrooms">
                        <option value="1.0">1</option>
                        <option value="1.5">1½</option>
                        <option value="2.0" selected>2</option>
                        <option value="2.5">2½</option>
                        <option value="3.0">3+</option>
                    </select>
                </div>
            </div>
        </div>

        <hr style="border-color:#f1f5f9">

        <!-- ══════════════════════════════════════════════════════════════
             MODO SIMPLE — camas directas en la unidad
        ══════════════════════════════════════════════════════════════ -->
        <div id="sectionSimple">
            <div class="section-divider">
                <i class="bi bi-moon"></i> Camas
            </div>

            <div id="bedsContainerSimple">
                <div class="bed-subrow">
                    <select class="form-select" name="beds[0][type_id]">
                        <option value="">Tipo de cama...</option>
                        <?php foreach ($bedTypes as $bt): ?>
                            <option value="<?= $bt['id'] ?>">
                                <?= esc($bt['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" class="form-control text-center"
                           name="beds[0][qty]"
                           placeholder="Cant." min="1" max="10" value="1"
                           style="width:80px">
                    <button type="button" class="btn-remove-bed"
                            onclick="removeBed(this)">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <button type="button" class="btn-add-bed"
                    onclick="addBedSimple()">
                <i class="bi bi-plus me-1"></i> Agregar tipo de cama
            </button>
        </div>

        <!-- ══════════════════════════════════════════════════════════════
             MODO COMPOUND — cuartos con camas cada uno
        ══════════════════════════════════════════════════════════════ -->
        <div id="sectionCompound" style="display:none">
            <div class="section-divider">
                <i class="bi bi-door-open"></i> Cuartos de la cabaña
            </div>

            <div id="roomsContainer">
                <!-- Los cuartos se agregan dinámicamente -->
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm"
                    onclick="addRoom()">
                <i class="bi bi-plus me-1"></i> Agregar cuarto
            </button>
        </div>

        <hr style="border-color:#f1f5f9">

        <!-- ── Amenidades ────────────────────────────────────────────────── -->
        <?php if (!empty($amenities)): ?>
            <div class="mb-4">
                <p class="fw-semibold mb-2">
                    <i class="bi bi-star me-1 text-primary"></i>
                    Amenidades incluidas
                </p>
                <div class="row g-2">
                    <?php foreach ($amenities as $amenity): ?>
                        <div class="col-6 col-md-4">
                            <label class="d-flex align-items-center gap-2 p-2
                                      rounded-2 border amenity-check"
                                   style="cursor:pointer;font-size:.85rem;
                                      transition:background .15s">
                                <input type="checkbox" name="amenity_ids[]"
                                       value="<?= $amenity['id'] ?>"
                                       class="form-check-input mb-0"
                                       style="flex-shrink:0">
                                <?php if (!empty($amenity['icon'])): ?>
                                    <i class="bi bi-<?= esc($amenity['icon']) ?>"></i>
                                <?php endif; ?>
                                <?= esc($amenity['name']) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <hr style="border-color:#f1f5f9">
        <?php endif; ?>

        <!-- ── Foto principal ────────────────────────────────────────────── -->
        <div class="mb-4">
            <p class="fw-semibold mb-1">
                <i class="bi bi-camera me-1 text-primary"></i>
                Foto principal
                <span class="text-muted fw-normal">(opcional)</span>
            </p>
            <div class="d-flex align-items-center gap-3">
                <div id="unitPhotoWrap"
                     style="width:110px;height:80px;border-radius:10px;
                            border:2px dashed #c7d2fe;background:#f8faff;
                            display:flex;align-items:center;
                            justify-content:center;overflow:hidden;
                            flex-shrink:0;cursor:pointer"
                     onclick="document.getElementById('unitPhotoInput').click()">
                    <div id="unitPhotoPlaceholder"
                         style="text-align:center;color:#a5b4fc">
                        <i class="bi bi-image" style="font-size:1.5rem"></i>
                        <div style="font-size:.65rem;margin-top:.2rem">
                            Subir foto
                        </div>
                    </div>
                    <img id="unitPhotoPreview" src=""
                         style="width:100%;height:100%;
                                object-fit:cover;display:none">
                </div>
                <div>
                    <input type="file" id="unitPhotoInput" name="unit_photo"
                           accept="image/jpeg,image/png,image/webp"
                           class="d-none">
                    <button type="button"
                            class="btn btn-outline-primary btn-sm mb-1"
                            onclick="document.getElementById('unitPhotoInput').click()">
                        <i class="bi bi-upload me-1"></i> Seleccionar foto
                    </button>
                    <p class="text-muted mb-0" style="font-size:.75rem">
                        JPG, PNG o WEBP · Máx. 5MB
                    </p>
                </div>
            </div>
        </div>

        <!-- ── Navegación ────────────────────────────────────────────────── -->
        <div class="d-flex justify-content-between align-items-center pt-3
                    border-top" style="border-color:#f1f5f9!important">
            <a href="/onboarding/step/2" class="btn-wiz-secondary">
                <i class="bi bi-arrow-left me-1"></i> Anterior
            </a>
            <button type="submit" class="btn-wiz-primary" id="btnSubmit3">
                Guardar y continuar
                <i class="bi bi-arrow-right ms-2"></i>
            </button>
        </div>

    </form>
</div>

<!-- ── Tip ──────────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-start gap-3 p-3 rounded-3"
     style="background:#fefce8;border:1px solid #fde68a">
    <i class="bi bi-info-circle-fill mt-1"
       style="color:#d97706;font-size:1.1rem"></i>
    <div>
        <strong style="font-size:.85rem;color:#92400e">
            Puedes agregar más después
        </strong>
        <p class="mb-0 text-muted" style="font-size:.82rem">
            Desde <strong>Inventario</strong> podrás agregar todas las
            unidades que necesites, con fotos adicionales y configuración
            avanzada.
        </p>
    </div>
</div>

<!-- ── Template para fila de cama ───────────────────────────────────────── -->
<template id="bedRowTpl">
    <div class="bed-subrow">
        <select class="form-select" name="BEDS_NAME">
            <option value="">Tipo de cama...</option>
            <?php foreach ($bedTypes as $bt): ?>
                <option value="<?= $bt['id'] ?>">
                    <?= esc($bt['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="number" class="form-control text-center"
               name="QTY_NAME"
               placeholder="Cant." min="1" max="10" value="1"
               style="width:80px">
        <button type="button" class="btn-remove-bed"
                onclick="removeBed(this)">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</template>

<!-- ── Template para cuarto ─────────────────────────────────────────────── -->
<template id="roomTpl">
    <div class="room-row" data-room="ROOM_IDX">
        <div class="room-row-header">
            <span class="room-num">Cuarto ROOM_NUM</span>
            <button type="button" class="btn-remove-room"
                    onclick="removeRoom(this)">
                <i class="bi bi-trash me-1"></i> Eliminar cuarto
            </button>
        </div>
        <div class="row g-2 mb-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">
                    Nombre del cuarto
                </label>
                <input type="text" class="form-control"
                       name="rooms[ROOM_IDX][name]"
                       placeholder="Ej: Cuarto principal, Cuarto 1"
                       maxlength="50">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Capacidad</label>
                <input type="number" class="form-control text-center"
                       name="rooms[ROOM_IDX][capacity]"
                       value="2" min="1" max="10">
            </div>
        </div>
        <div class="section-divider" style="font-size:.68rem">
            <i class="bi bi-moon"></i> Camas
        </div>
        <div class="room-beds" data-room="ROOM_IDX">
            <div class="bed-subrow">
                <select class="form-select"
                        name="rooms[ROOM_IDX][beds][0][type_id]">
                    <option value="">Tipo de cama...</option>
                    <?php foreach ($bedTypes as $bt): ?>
                        <option value="<?= $bt['id'] ?>">
                            <?= esc($bt['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" class="form-control text-center"
                       name="rooms[ROOM_IDX][beds][0][qty]"
                       placeholder="Cant." min="1" max="10" value="1"
                       style="width:80px">
                <button type="button" class="btn-remove-bed"
                        onclick="removeBed(this)">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
        <button type="button" class="btn-add-bed"
                onclick="addBedToRoom(this)">
            <i class="bi bi-plus me-1"></i> Agregar tipo de cama
        </button>
    </div>
</template>

<script>
    // ── Estado ────────────────────────────────────────────────────────────────
    let currentMode  = 'simple';
    let roomCount    = 0;
    let simpleBedIdx = 1; // índice para camas en modo simple (ya hay 1)

    // ── Inicializar con un cuarto si es compound ──────────────────────────────
    // (vacío al inicio, se agrega cuando el user elige ese modo)

    // ── Cambio de modo ────────────────────────────────────────────────────────
    function setMode(mode) {
        currentMode = mode;
        document.getElementById('unit_mode').value = mode;

        document.getElementById('btnModeSimple')
            .classList.toggle('active', mode === 'simple');
        document.getElementById('btnModeCompound')
            .classList.toggle('active', mode === 'compound');

        document.getElementById('sectionSimple').style.display =
            mode === 'simple' ? 'block' : 'none';
        document.getElementById('sectionCompound').style.display =
            mode === 'compound' ? 'block' : 'none';

        // Actualizar optgroups del select de tipo
        document.getElementById('optSimple').style.display =
            mode === 'simple' ? '' : 'none';
        document.getElementById('optCompound').style.display =
            mode === 'compound' ? '' : 'none';
        document.getElementById('type_name').value = '';

        // Ajustar placeholder del nombre
        document.getElementById('unit_name').placeholder =
            mode === 'simple'
                ? 'Ej: Suite 101, Habitación Doble'
                : 'Ej: Cabaña Río Verde, Villa El Manglar';

        // Ajustar capacidad por defecto
        document.getElementById('base_occupancy').value =
            mode === 'simple' ? 2 : 6;
        document.getElementById('max_occupancy').value =
            mode === 'simple' ? 4 : 10;

        // Si es compound y no hay cuartos, agregar el primero
        if (mode === 'compound' && roomCount === 0) {
            addRoom();
            addRoom();
        }
    }

    // ── MODO SIMPLE — agregar cama ────────────────────────────────────────────
    function addBedSimple() {
        const tpl       = document.getElementById('bedRowTpl');
        const container = document.getElementById('bedsContainerSimple');
        const clone     = tpl.content.cloneNode(true);

        // Reemplazar placeholders de nombre
        clone.querySelectorAll('[name="BEDS_NAME"]').forEach(el => {
            el.name = `beds[${simpleBedIdx}][type_id]`;
        });
        clone.querySelectorAll('[name="QTY_NAME"]').forEach(el => {
            el.name = `beds[${simpleBedIdx}][qty]`;
        });

        container.appendChild(clone);
        simpleBedIdx++;
    }

    // ── MODO COMPOUND — agregar cuarto ────────────────────────────────────────
    function addRoom() {
        const tpl       = document.getElementById('roomTpl');
        const container = document.getElementById('roomsContainer');
        let   html      = tpl.innerHTML;

        // Reemplazar índice y número
        html = html.replaceAll('ROOM_IDX', roomCount);
        html = html.replaceAll('ROOM_NUM', roomCount + 1);

        const div       = document.createElement('div');
        div.innerHTML   = html;
        container.appendChild(div.firstElementChild);
        roomCount++;
    }

    // ── MODO COMPOUND — agregar cama a un cuarto ──────────────────────────────
    function addBedToRoom(btn) {
        const roomEl  = btn.closest('.room-row');
        const roomIdx = roomEl.dataset.room;
        const bedsDiv = roomEl.querySelector('.room-beds');
        const bedCount= bedsDiv.querySelectorAll('.bed-subrow').length;

        const tpl     = document.getElementById('bedRowTpl');
        const clone   = tpl.content.cloneNode(true);

        clone.querySelectorAll('[name="BEDS_NAME"]').forEach(el => {
            el.name = `rooms[${roomIdx}][beds][${bedCount}][type_id]`;
        });
        clone.querySelectorAll('[name="QTY_NAME"]').forEach(el => {
            el.name = `rooms[${roomIdx}][beds][${bedCount}][qty]`;
        });

        bedsDiv.appendChild(clone);
    }

    // ── Eliminar cama ─────────────────────────────────────────────────────────
    function removeBed(btn) {
        const row = btn.closest('.bed-subrow');
        // No eliminar si es la única cama del contenedor
        const container = row.parentElement;
        if (container.querySelectorAll('.bed-subrow').length <= 1) {
            return;
        }
        row.remove();
    }

    // ── Eliminar cuarto ───────────────────────────────────────────────────────
    function removeRoom(btn) {
        const rooms = document.querySelectorAll('#roomsContainer .room-row');
        if (rooms.length <= 1) return; // mínimo un cuarto
        btn.closest('.room-row').remove();
    }

    // ── Descripción con IA ────────────────────────────────────────────────────
    document.getElementById('description').addEventListener('input', function () {
        document.getElementById('descCharCount').textContent =
            `${this.value.length} / 1000`;
    });

    document.getElementById('unitPhotoInput').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) {
            showFlash('danger', 'La foto no debe superar 5MB.');
            this.value = '';
            return;
        }
        const reader  = new FileReader();
        reader.onload = e => {
            const img         = document.getElementById('unitPhotoPreview');
            const placeholder = document.getElementById('unitPhotoPlaceholder');
            img.src           = e.target.result;
            img.style.display = 'block';
            placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });

    document.getElementById('base_occupancy').addEventListener('change', function () {
        const maxInput = document.getElementById('max_occupancy');
        if (parseInt(maxInput.value) < parseInt(this.value)) {
            maxInput.value = this.value;
        }
    });

    // Amenidades hover
    document.querySelectorAll('.amenity-check').forEach(label => {
        const cb     = label.querySelector('input');
        const update = () => {
            label.style.background  = cb.checked ? '#eef2ff' : '';
            label.style.borderColor = cb.checked ? '#a5b4fc' : '';
        };
        cb.addEventListener('change', update);
        update();
    });

    async function generateDescription() {
        const contextInput = document.getElementById('aiContextInput');
        const textarea     = document.getElementById('description');
        const btn          = document.getElementById('btnAiDesc');
        const unitName     = document.getElementById('unit_name').value.trim();
        const unitType     = document.getElementById('type_name').value;
        const contextText  = contextInput.value.trim() || textarea.value.trim();
        const modeLabel    = currentMode === 'compound'
            ? 'cabaña con varios cuartos' : '';

        if (!contextText && !unitName) {
            showFlash('warning', 'Escribe el nombre o algunas palabras clave primero.');
            contextInput.focus();
            return;
        }

        const fullText = [unitType, modeLabel, unitName, contextText]
            .filter(Boolean).join(' · ');

        btn.disabled = true;
        setAiLoading('aiDescLoading', true);

        try {
            const result = await wizardAI('generate_description', { text: fullText });

            if (result.success && result.text) {
                textarea.value = result.text;
                document.getElementById('descCharCount').textContent =
                    `${result.text.length} / 1000`;
                document.getElementById('aiSuggestionBadge').style.display = 'block';
                document.getElementById('aiContextWrap').style.display     = 'none';
                showFlash('success', 'Descripción generada. Puedes editarla.');
                textarea.focus();
            } else {
                showFlash('danger', result.message || 'No se pudo generar.');
            }
        } catch (err) {
            console.error('[AI/Descripción]', err);
            showFlash('danger', 'Error de conexión con IA.');
        } finally {
            btn.disabled = false;
            setAiLoading('aiDescLoading', false);
        }
    }

    document.getElementById('formStep3').addEventListener('submit', function (e) {
        const base = parseInt(document.getElementById('base_occupancy').value);
        const max  = parseInt(document.getElementById('max_occupancy').value);

        if (max < base) {
            e.preventDefault();
            showFlash('warning', 'La capacidad máxima no puede ser menor que la base.');
            return;
        }

        // Validar que en modo compound haya al menos un cuarto
        if (currentMode === 'compound') {
            const rooms = document.querySelectorAll('#roomsContainer .room-row');
            if (rooms.length === 0) {
                e.preventDefault();
                showFlash('warning', 'Agrega al menos un cuarto a la cabaña.');
                return;
            }
        }

        const btn     = document.getElementById('btnSubmit3');
        btn.disabled  = true;
        btn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    });
</script>