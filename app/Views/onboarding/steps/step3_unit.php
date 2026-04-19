<?php
/**
 * onboarding/steps/step3_unit.php
 *
 * Paso 3: Crear la primera unidad de alojamiento.
 * Incluye botón de IA para generar descripción automática.
 */

$bedTypes  = $bedTypes  ?? [];
$amenities = $amenities ?? [];

// Tipos de habitación sugeridos para el select
$suggestedTypes = [
    'Habitación Estándar',
    'Habitación Doble',
    'Habitación Triple',
    'Suite',
    'Cabaña',
    'Apartamento',
    'Villa',
    'Bungalow',
    'Dormitorio Compartido',
    'Otro',
];
?>

<!-- ── Card principal ────────────────────────────────────────────────────── -->
<div class="wizard-card">
    <div class="card-eyebrow">Paso 3 · Requerido</div>
    <h5>Tu primera unidad de alojamiento</h5>
    <p class="card-hint">
        Puedes agregar más habitaciones después. Por ahora configuremos al menos una
        para poder asignarle tarifas en el siguiente paso.
    </p>

    <form action="/onboarding/step/3" method="POST"
          enctype="multipart/form-data" id="formStep3">
        <?= csrf_field() ?>

        <!-- ── Nombre y tipo ─────────────────────────────────────────────── -->
        <div class="row g-3 mb-4">
            <div class="col-md-7">
                <label class="form-label fw-semibold" for="unit_name">
                    Nombre de la unidad <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    class="form-control form-control-lg"
                    id="unit_name"
                    name="unit_name"
                    placeholder="Ej: Cabaña Río Verde, Suite 101"
                    required
                    maxlength="50"
                >
                <div class="form-text">Este nombre aparecerá en reservaciones y en el sitio web.</div>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-semibold" for="type_name">
                    Tipo de alojamiento <span class="text-danger">*</span>
                </label>
                <select class="form-select form-select-lg" id="type_name" name="type_name" required>
                    <option value="">Selecciona...</option>
                    <?php foreach ($suggestedTypes as $type): ?>
                        <option value="<?= esc($type) ?>"><?= esc($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- ── Descripción con botón IA ──────────────────────────────────── -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-semibold mb-0" for="description">
                    Descripción
                </label>
                <div class="d-flex align-items-center gap-2">
                    <!-- Spinner visible solo durante carga IA -->
                    <div class="ai-loading" id="aiDescLoading">
                        <span class="spinner-border spinner-border-sm"
                              style="color:#6366f1"></span>
                        <span style="font-size:.8rem;color:#6366f1">Generando...</span>
                    </div>
                    <button type="button" class="btn-ai" id="btnAiDesc"
                            onclick="generateDescription()">
                        <i class="bi bi-stars"></i>
                        Generar con IA
                    </button>
                </div>
            </div>

            <!-- Input rápido para contexto de la IA (se oculta tras generar) -->
            <div id="aiContextWrap" class="mb-2">
                <input
                    type="text"
                    class="form-control"
                    id="aiContextInput"
                    placeholder="Describe brevemente tu unidad para la IA: 'cabaña con vista al río, jacuzzi, dos camas'"
                    style="border-style:dashed;background:#fafbff;font-size:.85rem"
                >
                <div class="form-text">
                    <i class="bi bi-magic me-1" style="color:#6366f1"></i>
                    Escribe palabras clave y la IA redactará una descripción atractiva para tus huéspedes.
                </div>
            </div>

            <textarea
                class="form-control"
                id="description"
                name="description"
                rows="4"
                placeholder="La descripción aparecerá en tu sitio web y en las reservaciones..."
                maxlength="1000"
            ></textarea>

            <!-- Contador de caracteres -->
            <div class="d-flex justify-content-between mt-1">
                <div id="aiSuggestionBadge" style="display:none">
                    <span class="badge"
                          style="background:#f0f4ff;color:#4338ca;
                                 font-size:.72rem;font-weight:600">
                        <i class="bi bi-stars me-1"></i>Generado por IA · Puedes editarlo
                    </span>
                </div>
                <small class="text-muted ms-auto" id="descCharCount">0 / 1000</small>
            </div>
        </div>

        <hr style="border-color:#f1f5f9">

        <!-- ── Capacidad ─────────────────────────────────────────────────── -->
        <div class="mb-4">
            <p class="fw-semibold mb-1">
                <i class="bi bi-people me-1 text-primary"></i>
                Capacidad
            </p>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold" for="base_occupancy">
                        Ocupación base <span class="text-danger">*</span>
                    </label>
                    <input type="number" class="form-control text-center"
                           id="base_occupancy" name="base_occupancy"
                           value="2" min="1" max="20" required>
                    <div class="form-text text-center">Huéspedes incluidos</div>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold" for="max_occupancy">
                        Máximo <span class="text-danger">*</span>
                    </label>
                    <input type="number" class="form-control text-center"
                           id="max_occupancy" name="max_occupancy"
                           value="4" min="1" max="20" required>
                    <div class="form-text text-center">Máximo permitido</div>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold" for="bathrooms">
                        Baños
                    </label>
                    <select class="form-select text-center" id="bathrooms" name="bathrooms">
                        <option value="1.0">1</option>
                        <option value="1.5">1½</option>
                        <option value="2.0">2</option>
                        <option value="2.5">2½</option>
                        <option value="3.0">3+</option>
                    </select>
                </div>
            </div>
        </div>

        <hr style="border-color:#f1f5f9">

        <!-- ── Camas ─────────────────────────────────────────────────────── -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <p class="fw-semibold mb-0">
                    <i class="bi bi-moon me-1 text-primary"></i>
                    Configuración de camas
                </p>
                <button type="button" class="btn btn-outline-secondary btn-sm"
                        onclick="addBedRow()">
                    <i class="bi bi-plus me-1"></i>Agregar tipo de cama
                </button>
            </div>

            <div id="bedsContainer">
                <?php if (!empty($bedTypes)): ?>
                    <!-- Fila inicial con los tipos disponibles -->
                    <div class="bed-row row g-2 align-items-center mb-2">
                        <div class="col-7">
                            <select class="form-select" name="bed_type_id[]">
                                <option value="">Selecciona tipo...</option>
                                <?php foreach ($bedTypes as $bt): ?>
                                    <option value="<?= $bt['id'] ?>">
                                        <?= esc($bt['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-3">
                            <input type="number" class="form-control text-center"
                                   name="bed_quantity[]"
                                   placeholder="Cant." min="1" max="10" value="1">
                        </div>
                        <div class="col-2">
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm w-100"
                                    onclick="removeBedRow(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Si no hay bed_types configurados aún -->
                    <div class="alert alert-light border d-flex align-items-center gap-2"
                         style="font-size:.83rem">
                        <i class="bi bi-info-circle text-primary"></i>
                        No hay tipos de cama configurados aún. Puedes agregarlos
                        después desde <strong>Inventario → Tipos de cama</strong>.
                    </div>
                <?php endif; ?>
            </div>
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
                            <label class="d-flex align-items-center gap-2 p-2 rounded-2
                                      border amenity-check"
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

        <!-- ── Foto de la unidad ─────────────────────────────────────────── -->
        <div class="mb-4">
            <p class="fw-semibold mb-1">
                <i class="bi bi-camera me-1 text-primary"></i>
                Foto principal <span class="text-muted fw-normal">(opcional)</span>
            </p>
            <div class="d-flex align-items-center gap-3">

                <!-- Preview -->
                <div id="unitPhotoWrap"
                     style="width:110px;height:80px;border-radius:10px;
                            border:2px dashed #c7d2fe;background:#f8faff;
                            display:flex;align-items:center;justify-content:center;
                            overflow:hidden;flex-shrink:0;cursor:pointer"
                     onclick="document.getElementById('unitPhotoInput').click()">
                    <div id="unitPhotoPlaceholder"
                         style="text-align:center;color:#a5b4fc">
                        <i class="bi bi-image" style="font-size:1.5rem"></i>
                        <div style="font-size:.65rem;margin-top:.2rem">Subir foto</div>
                    </div>
                    <img id="unitPhotoPreview" src=""
                         style="width:100%;height:100%;object-fit:cover;display:none">
                </div>

                <div>
                    <input type="file" id="unitPhotoInput" name="unit_photo"
                           accept="image/jpeg,image/png,image/webp"
                           class="d-none">
                    <button type="button"
                            class="btn btn-outline-primary btn-sm mb-1"
                            onclick="document.getElementById('unitPhotoInput').click()">
                        <i class="bi bi-upload me-1"></i>Seleccionar foto
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
        <strong style="font-size:.85rem;color:#92400e">Puedes agregar más después</strong>
        <p class="mb-0 text-muted" style="font-size:.82rem">
            Una vez completes el onboarding, podrás agregar todas las unidades
            que necesites desde <strong>Inventario</strong>. Aquí solo configuramos
            la primera para dejar el sistema listo.
        </p>
    </div>
</div>

<!-- Template para filas de camas (clonado por JS) -->
<template id="bedRowTemplate">
    <div class="bed-row row g-2 align-items-center mb-2">
        <div class="col-7">
            <select class="form-select" name="bed_type_id[]">
                <option value="">Selecciona tipo...</option>
                <?php foreach ($bedTypes as $bt): ?>
                    <option value="<?= $bt['id'] ?>">
                        <?= esc($bt['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-3">
            <input type="number" class="form-control text-center"
                   name="bed_quantity[]"
                   placeholder="Cant." min="1" max="10" value="1">
        </div>
        <div class="col-2">
            <button type="button"
                    class="btn btn-outline-danger btn-sm w-100"
                    onclick="removeBedRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
</template>

<script>
    // ── Contador de caracteres en descripción ─────────────────────────────────
    document.getElementById('description').addEventListener('input', function () {
        document.getElementById('descCharCount').textContent =
            `${this.value.length} / 1000`;
    });

    // ── Preview foto de unidad ────────────────────────────────────────────────
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

    // ── Hover en amenidades ───────────────────────────────────────────────────
    document.querySelectorAll('.amenity-check').forEach(label => {
        const checkbox = label.querySelector('input');
        const update   = () => {
            label.style.background = checkbox.checked ? '#eef2ff' : '';
            label.style.borderColor= checkbox.checked ? '#a5b4fc' : '';
        };
        checkbox.addEventListener('change', update);
        update();
    });

    // ── Filas de camas ────────────────────────────────────────────────────────

    /**
     * Agrega una nueva fila de tipo de cama clonando el template
     */
    function addBedRow() {
        const template  = document.getElementById('bedRowTemplate');
        const container = document.getElementById('bedsContainer');

        // Remover el alert informativo si existe
        const alert = container.querySelector('.alert');
        if (alert) alert.remove();

        const clone = template.content.cloneNode(true);
        container.appendChild(clone);
    }

    /**
     * Elimina una fila de cama
     */
    function removeBedRow(btn) {
        btn.closest('.bed-row').remove();
    }

    // ── Validación de capacidad ───────────────────────────────────────────────
    document.getElementById('base_occupancy').addEventListener('change', function () {
        const maxInput = document.getElementById('max_occupancy');
        if (parseInt(maxInput.value) < parseInt(this.value)) {
            maxInput.value = this.value;
        }
    });

    // ── Generación de descripción con IA ─────────────────────────────────────

    /**
     * Llama al endpoint de IA para generar una descripción
     * basada en el texto de contexto ingresado por el usuario
     */
    async function generateDescription() {
        const contextInput = document.getElementById('aiContextInput');
        const textarea     = document.getElementById('description');
        const btn          = document.getElementById('btnAiDesc');
        const text         = contextInput.value.trim();

        // Usar la descripción actual como contexto si el campo está vacío
        const contextText  = text || textarea.value.trim();

        if (!contextText) {
            showFlash('warning', 'Escribe algunas palabras clave antes de usar la IA.');
            contextInput.focus();
            return;
        }

        // Enriquecer contexto con nombre de unidad y tipo si ya están llenos
        const unitName  = document.getElementById('unit_name').value.trim();
        const unitType  = document.getElementById('type_name').value;
        const fullText  = [unitType, unitName, contextText].filter(Boolean).join(' · ');

        btn.disabled = true;
        setAiLoading('aiDescLoading', true);

        try {
            const result = await wizardAI('generate_description', { text: fullText });

            if (result.success && result.text) {
                textarea.value = result.text;

                // Actualizar contador
                document.getElementById('descCharCount').textContent =
                    `${result.text.length} / 1000`;

                // Mostrar badge de "generado por IA"
                document.getElementById('aiSuggestionBadge').style.display = 'block';

                // Ocultar el campo de contexto tras generar
                document.getElementById('aiContextWrap').style.display = 'none';

                showFlash('success', 'Descripción generada. Puedes editarla libremente.');
                textarea.focus();
            } else {
                showFlash('danger', result.message || 'No se pudo generar la descripción.');
            }
        } catch (err) {
            console.error('[AI/Descripción] Error:', err);
            showFlash('danger', 'Error de conexión con el servicio de IA.');
        } finally {
            btn.disabled = false;
            setAiLoading('aiDescLoading', false);
        }
    }

    // ── Submit con loader ─────────────────────────────────────────────────────
    document.getElementById('formStep3').addEventListener('submit', function (e) {
        // Validación rápida: max >= base
        const base = parseInt(document.getElementById('base_occupancy').value);
        const max  = parseInt(document.getElementById('max_occupancy').value);

        if (max < base) {
            e.preventDefault();
            showFlash('warning', 'La capacidad máxima no puede ser menor que la base.');
            return;
        }

        const btn     = document.getElementById('btnSubmit3');
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    });
</script>