<?php /** inventory/wizard/step1_base.php */ ?>

<div class="iw-step-header">
    <div class="iw-step-eyebrow">Paso 1 de 3 · Requerido</div>
    <h1 class="iw-step-title-main">Información base</h1>
    <p class="iw-step-hint">Define el nombre, tipo y capacidad. Con esto la unidad ya estará disponible para reservar.</p>
</div>

<form action="<?= base_url('/inventory/wizard/save/1') ?>" method="post" id="step1-form" novalidate>
    <?= csrf_field() ?>

    <!-- Modo: simple vs compuesta -->
    <div class="iw-card">
        <div class="iw-card-title"><i class="bi bi-grid-1x2"></i> ¿Cómo es esta unidad?</div>
        <div class="mode-grid">
            <label class="mode-card active" id="mode-simple-card">
                <input type="radio" name="unit_mode" value="simple" checked id="mode-simple">
                <div class="mode-check"><i class="bi bi-check"></i></div>
                <span class="mode-icon">🛏️</span>
                <div class="mode-title">Unidad simple</div>
                <div class="mode-desc">Habitación, suite, apartamento, glamping — una sola unidad reservable</div>
            </label>
            <label class="mode-card" id="mode-compound-card">
                <input type="radio" name="unit_mode" value="compound" id="mode-compound">
                <div class="mode-check"><i class="bi bi-check"></i></div>
                <span class="mode-icon">🏡</span>
                <div class="mode-title">Cabaña / Villa</div>
                <div class="mode-desc">Un espacio principal con varias habitaciones hijas (alcobas, estudios...)</div>
            </label>
        </div>
    </div>

    <!-- Datos generales -->
    <div class="iw-card">
        <div class="iw-card-title"><i class="bi bi-info-circle"></i> Datos generales</div>

        <div class="two-col" style="margin-bottom:14px;">
            <div class="field">
                <label class="iw-label" for="parent_name">Nombre de la unidad <span class="req">*</span></label>
                <input type="text" name="parent_name" id="parent_name" class="iw-input"
                       required placeholder="Ej. Cabaña Los Pinos, Hab. 101, Suite Presidencial"
                       value="<?= esc(old('parent_name')) ?>">
            </div>
            <div class="field">
                <label class="iw-label" for="type_id">Tipo <span class="req">*</span></label>
                <select name="type_id" id="type_id" class="iw-select" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= old('type_id') == $t['id'] ? 'selected' : '' ?>>
                            <?= esc($t['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="field">
            <label class="iw-label" for="parent_description">Descripción</label>
            <textarea name="parent_description" id="parent_description" class="iw-textarea" rows="2"
                      placeholder="Breve descripción del espacio, vistas, atmósfera..."><?= esc(old('parent_description')) ?></textarea>
        </div>
    </div>

    <!-- Capacidad -->
    <div class="iw-card">
        <div class="iw-card-title"><i class="bi bi-people"></i> Capacidad</div>

        <div class="occ-grid" style="margin-bottom:14px;">
            <div class="occ-box">
                <div>
                    <div class="occ-label">Capacidad base</div>
                    <div class="occ-sub">Incluida en la tarifa base</div>
                </div>
                <div class="occ-stepper">
                    <button type="button" class="occ-btn" id="base-minus" disabled>−</button>
                    <span class="occ-val" id="base-val">2</span>
                    <button type="button" class="occ-btn" id="base-plus">+</button>
                    <input type="hidden" name="parent_base_occupancy" id="base-hidden" value="2">
                </div>
            </div>
            <div class="occ-box">
                <div>
                    <div class="occ-label">Capacidad máxima</div>
                    <div class="occ-sub">Personas extras cobran adicional</div>
                </div>
                <div class="occ-stepper">
                    <button type="button" class="occ-btn" id="max-minus" disabled>−</button>
                    <span class="occ-val" id="max-val">4</span>
                    <button type="button" class="occ-btn" id="max-plus">+</button>
                    <input type="hidden" name="max_occupancy" id="max-hidden" value="4">
                </div>
            </div>
        </div>

        <div class="two-col">
            <div class="field">
                <label class="iw-label" for="bathrooms">Baños</label>
                <input type="number" name="bathrooms" id="bathrooms" class="iw-input"
                       step="0.5" min="0" value="1" placeholder="1">
                <div class="iw-hint">Usa 0.5 para baños compartidos</div>
            </div>
            <div class="field">
                <label class="iw-label" for="beds_info">Resumen de camas</label>
                <input type="text" name="beds_info" id="beds_info" class="iw-input"
                       placeholder="Se genera automáticamente" readonly
                       value="<?= esc(old('beds_info')) ?>"
                       style="background:#f8fafc;color:#64748b;">
            </div>
        </div>
    </div>

    <!-- Camas de la unidad (solo modo simple) -->
    <div id="simple-beds-section">
        <div class="iw-card">
            <div class="iw-card-title"><i class="bi bi-moon-stars-fill"></i> Camas</div>
            <div class="iw-hint" style="margin-bottom:14px;">
                Registra los tipos de cama que tiene esta unidad.
            </div>
            <div id="simple-beds-container"></div>
            <button type="button" class="btn-add-bed" onclick="addSimpleBed()" style="margin-top:6px;">
                <i class="bi bi-plus"></i> Añadir cama
            </button>
        </div>
    </div>

    <!-- Sub-habitaciones (solo modo compuesto) -->
    <div id="rooms-section" style="display:none;">
        <div class="iw-card">
            <div class="iw-card-title"><i class="bi bi-door-open"></i> Habitaciones internas</div>
            <div class="iw-hint" style="margin-bottom:14px;">
                Cada habitación puede tener su propio tipo de cama. Las amenidades se configuran en el paso 2.
            </div>
            <div id="rooms-container"></div>
            <button type="button" class="btn-add-room" onclick="addRoom()">
                <i class="bi bi-plus-circle"></i> Agregar habitación
            </button>
        </div>
    </div>

    <div class="iw-footer">
        <a href="<?= base_url('/inventory') ?>" class="btn-iw-back">
            <i class="bi bi-arrow-left"></i> Cancelar
        </a>
        <button type="button" class="btn-iw-next" onclick="submitStep1()">
            Continuar <i class="bi bi-arrow-right"></i>
        </button>
    </div>
</form>

<script>
    // ── Catálogos desde PHP ──────────────────────────────────────────
    const bedTypes = <?= json_encode($bedTypes) ?>;
    const unitTypes = <?= json_encode($types) ?>;

    // ── Steppers de capacidad ────────────────────────────────────────
    let baseVal = 2, maxVal = 4;

    function syncOcc() {
        document.getElementById('base-val').textContent = baseVal;
        document.getElementById('max-val').textContent  = maxVal;
        document.getElementById('base-hidden').value    = baseVal;
        document.getElementById('max-hidden').value     = maxVal;
        document.getElementById('base-minus').disabled  = baseVal <= 1;
        document.getElementById('max-minus').disabled   = maxVal <= baseVal;
    }

    document.getElementById('base-plus') .addEventListener('click', () => { baseVal++; if(maxVal < baseVal) maxVal = baseVal; syncOcc(); });
    document.getElementById('base-minus').addEventListener('click', () => { if(baseVal > 1) { baseVal--; syncOcc(); } });
    document.getElementById('max-plus') .addEventListener('click', () => { maxVal++; syncOcc(); });
    document.getElementById('max-minus').addEventListener('click', () => { if(maxVal > baseVal) { maxVal--; syncOcc(); } });
    syncOcc();

    // ── Modo simple / compuesto ──────────────────────────────────────
    const modeCards    = { simple: document.getElementById('mode-simple-card'), compound: document.getElementById('mode-compound-card') };
    const simpleBeds   = document.getElementById('simple-beds-section');
    const roomsSection = document.getElementById('rooms-section');

    function syncMode() {
        const mode = document.querySelector('input[name="unit_mode"]:checked').value;
        Object.values(modeCards).forEach(c => c.classList.remove('active'));
        modeCards[mode].classList.add('active');

        if (mode === 'compound') {
            simpleBeds.style.display   = 'none';
            roomsSection.style.display = 'block';
            if (document.getElementById('rooms-container').children.length === 0) addRoom();
        } else {
            simpleBeds.style.display   = 'block';
            roomsSection.style.display = 'none';
            if (document.getElementById('simple-beds-container').children.length === 0) addSimpleBed();
        }
    }

    document.querySelectorAll('input[name="unit_mode"]').forEach(r => r.addEventListener('change', syncMode));
    Object.values(modeCards).forEach(card => card.addEventListener('click', function() {
        this.querySelector('input').checked = true;
        syncMode();
    }));

    // ── CAMAS unidad simple ──────────────────────────────────────────
    let simpleBedCounter = 0;

    function addSimpleBed() {
        const container = document.getElementById('simple-beds-container');
        const n = simpleBedCounter++;
        let opts = bedTypes.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
        container.insertAdjacentHTML('beforeend', `
        <div class="bed-row" id="sbed-${n}">
            <select name="simple_beds[${n}][bed_type_id]" class="iw-select"
                    style="font-size:13px;padding:7px 10px;"
                    onchange="updateBedsInfo()">
                <option value="">Tipo de cama...</option>${opts}
            </select>
            <input type="number" name="simple_beds[${n}][quantity]"
                   class="iw-input" style="font-size:13px;padding:7px 10px;width:70px;"
                   value="1" min="1" onchange="updateBedsInfo()">
            <button type="button" class="btn-rm-bed"
                    onclick="document.getElementById('sbed-${n}').remove(); updateBedsInfo()">×</button>
        </div>
    `);
        updateBedsInfo();
    }

    function updateBedsInfo() {
        // Genera el resumen legible automáticamente
        const rows = document.querySelectorAll('#simple-beds-container .bed-row');
        const parts = [];
        rows.forEach(row => {
            const sel = row.querySelector('select');
            const qty = row.querySelector('input[type=number]');
            if (sel && sel.value && sel.selectedIndex > 0) {
                const name = sel.options[sel.selectedIndex].text;
                const q = parseInt(qty?.value || 1);
                parts.push(`${q} ${name}`);
            }
        });
        document.getElementById('beds_info').value = parts.join(', ');
    }

    // ── HABITACIONES hijas (modo compuesto) ─────────────────────────
    let roomCounter = 0;

    function addRoom() {
        const container = document.getElementById('rooms-container');
        const n = roomCounter++;
        let typeOpts = unitTypes.map(t => `<option value="${t.id}">${t.name}</option>`).join('');

        container.insertAdjacentHTML('beforeend', `
        <div class="room-card" id="room-${n}">
            <div class="room-card-header">
                <span class="room-num">Habitación ${n + 1}</span>
                <button type="button" class="btn-remove"
                        onclick="document.getElementById('room-${n}').remove()">
                    <i class="bi bi-x-circle"></i> Quitar
                </button>
            </div>
            <div class="room-grid">
                <div class="field">
                    <label class="iw-label">Nombre</label>
                    <input type="text" name="rooms[${n}][name]" class="iw-input"
                           style="font-size:13px;padding:8px 10px;"
                           placeholder="Ej. Alcoba Principal">
                </div>
                <div class="field">
                    <label class="iw-label">Tipo</label>
                    <select name="rooms[${n}][type_id]" class="iw-select"
                            style="font-size:13px;padding:8px 10px;">
                        <option value="">Tipo...</option>${typeOpts}
                    </select>
                </div>
                <div class="field">
                    <label class="iw-label">Baños</label>
                    <input type="number" name="rooms[${n}][bathrooms]" class="iw-input"
                           style="font-size:13px;padding:8px 10px;"
                           value="1" min="0" step="0.5">
                </div>
            </div>
            <div style="margin-top:8px;">
                <label class="iw-label">Camas</label>
                <div id="rbeds-${n}"></div>
                <button type="button" class="btn-add-bed" onclick="addRoomBed(${n})" style="margin-top:4px;">
                    <i class="bi bi-plus"></i> Añadir cama
                </button>
            </div>
        </div>
    `);
        addRoomBed(n);
    }

    // Contador global de camas para evitar colisiones de IDs
    let globalBedCounter = 0;

    function addRoomBed(roomN) {
        const container = document.getElementById(`rbeds-${roomN}`);
        const n = globalBedCounter++;
        let opts = bedTypes.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
        container.insertAdjacentHTML('beforeend', `
        <div class="bed-row" id="rbed-${n}">
            <select name="rooms[${roomN}][beds][${n}][bed_type_id]" class="iw-select"
                    style="font-size:13px;padding:7px 10px;">
                <option value="">Tipo de cama...</option>${opts}
            </select>
            <input type="number" name="rooms[${roomN}][beds][${n}][quantity]"
                   class="iw-input" style="font-size:13px;padding:7px 10px;width:70px;"
                   value="1" min="1">
            <button type="button" class="btn-rm-bed"
                    onclick="document.getElementById('rbed-${n}').remove()">×</button>
        </div>
    `);
    }

    // ── Validación y submit manual ───────────────────────────────────
    function submitStep1() {
        const name   = document.getElementById('parent_name').value.trim();
        const typeId = document.getElementById('type_id').value;

        if (!name) {
            document.getElementById('parent_name').focus();
            document.getElementById('parent_name').style.borderColor = '#ef4444';
            return;
        }
        if (!typeId) {
            document.getElementById('type_id').focus();
            document.getElementById('type_id').style.borderColor = '#ef4444';
            return;
        }
        document.getElementById('step1-form').submit();
    }

    // Limpiar borde rojo al escribir
    ['parent_name','type_id'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', function() {
            this.style.borderColor = '';
        });
    });

    // ── Init ─────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        addSimpleBed(); // Empezar con una cama por defecto en modo simple
    });
</script>