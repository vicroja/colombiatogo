<?php
/**
 * onboarding/steps/step6_product.php
 *
 * Paso 6: Crear el primer producto o servicio del hotel.
 * Paso opcional. Categoría + producto en un solo formulario.
 */
?>

<!-- ── Card principal ────────────────────────────────────────────────────── -->
<div class="wizard-card">
    <div class="card-eyebrow">Paso 6 · Opcional</div>
    <h5>Primer producto o servicio</h5>
    <p class="card-hint">
        Los productos y servicios se pueden agregar al folio de una reservación.
        Agrega aquí el más común: desayuno, tour, lavandería, transporte, etc.
    </p>

    <form action="/onboarding/step/6" method="POST" id="formStep6">
        <?= csrf_field() ?>

        <!-- ── Sugerencias rápidas ───────────────────────────────────────── -->
        <div class="mb-4">
            <p class="fw-semibold small mb-2">
                <i class="bi bi-lightning-charge me-1 text-warning"></i>
                Sugerencias rápidas — haz clic para pre-llenar
            </p>
            <div class="d-flex flex-wrap gap-2" id="quickSuggestions">
                <?php
                $suggestions = [
                    ['name' => 'Desayuno',          'cat' => 'Alimentos y Bebidas',
                        'type' => 'service', 'price' => '15000', 'desc' => 'Desayuno incluido por persona'],
                    ['name' => 'Tour local',         'cat' => 'Actividades',
                        'type' => 'service', 'price' => '50000', 'desc' => 'Tour guiado por la zona'],
                    ['name' => 'Servicio de lavandería','cat' => 'Servicios',
                        'type' => 'service', 'price' => '20000', 'desc' => 'Lavado y planchado de ropa'],
                    ['name' => 'Transporte aeropuerto','cat' => 'Transporte',
                        'type' => 'service', 'price' => '80000', 'desc' => 'Traslado al aeropuerto'],
                    ['name' => 'Botella de vino',    'cat' => 'Alimentos y Bebidas',
                        'type' => 'product', 'price' => '45000', 'desc' => 'Vino de bienvenida en habitación'],
                    ['name' => 'Alquiler de bicicleta','cat' => 'Actividades',
                        'type' => 'product', 'price' => '25000', 'desc' => 'Bicicleta por día'],
                ];
                foreach ($suggestions as $s):
                    ?>
                    <button type="button"
                            class="btn btn-outline-secondary btn-sm quick-chip"
                            onclick='fillSuggestion(<?= json_encode($s) ?>)'>
                        <?= esc($s['name']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <hr style="border-color:#f1f5f9">

        <!-- ── Tipo de item ──────────────────────────────────────────────── -->
        <div class="mb-4">
            <p class="fw-semibold small mb-2">Tipo</p>
            <div class="d-flex gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio"
                           name="category_type" id="typeService"
                           value="service" checked>
                    <label class="form-check-label" for="typeService">
                        <i class="bi bi-person-workspace me-1 text-primary"></i>
                        Servicio
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio"
                           name="category_type" id="typeProduct"
                           value="product">
                    <label class="form-check-label" for="typeProduct">
                        <i class="bi bi-box-seam me-1 text-primary"></i>
                        Producto físico
                    </label>
                </div>
            </div>
        </div>

        <!-- ── Categoría ────────────────────────────────────────────────── -->
        <div class="mb-4">
            <label class="form-label fw-semibold" for="category_name">
                Categoría <span class="text-danger">*</span>
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-tag"></i>
                </span>
                <input
                    type="text"
                    class="form-control"
                    id="category_name"
                    name="category_name"
                    placeholder="Ej: Alimentos y Bebidas, Actividades, Servicios"
                    required
                    maxlength="80"
                    list="catSuggestions"
                >
                <datalist id="catSuggestions">
                    <option value="Alimentos y Bebidas">
                    <option value="Actividades">
                    <option value="Servicios">
                    <option value="Transporte">
                    <option value="Spa y Bienestar">
                    <option value="Otros">
                </datalist>
            </div>
            <div class="form-text">
                La categoría agrupa productos relacionados en el punto de venta.
            </div>
        </div>

        <!-- ── Nombre del producto ───────────────────────────────────────── -->
        <div class="mb-4">
            <label class="form-label fw-semibold" for="product_name">
                Nombre <span class="text-danger">*</span>
            </label>
            <input
                type="text"
                class="form-control form-control-lg"
                id="product_name"
                name="product_name"
                placeholder="Ej: Desayuno continental, Tour al volcán"
                required
                maxlength="150"
            >
        </div>

        <!-- ── Descripción ───────────────────────────────────────────────── -->
        <div class="mb-4">
            <label class="form-label fw-semibold" for="product_description">
                Descripción
                <span class="text-muted fw-normal">(opcional)</span>
            </label>
            <textarea
                class="form-control"
                id="product_description"
                name="product_description"
                rows="2"
                placeholder="Breve descripción que verán los recepcionistas al agregar el cargo..."
                maxlength="300"
            ></textarea>
        </div>

        <!-- ── Precio ────────────────────────────────────────────────────── -->
        <div class="mb-4">
            <label class="form-label fw-semibold" for="unit_price">
                Precio unitario <span class="text-danger">*</span>
            </label>
            <div class="price-input-wrap"
                 style="display:flex;align-items:center;
                        border:2px solid #c7d2fe;border-radius:12px;
                        overflow:hidden;background:#fff;
                        transition:border-color .2s">
                <div style="padding:.65rem 1rem;background:#f0f4ff;
                            font-weight:700;color:#4338ca;
                            border-right:2px solid #c7d2fe;
                            white-space:nowrap">
                    <?= esc($tenant['currency_symbol'] ?? '$') ?>
                </div>
                <input
                    type="number"
                    class="form-control border-0 shadow-none"
                    id="unit_price"
                    name="unit_price"
                    placeholder="0.00"
                    min="0"
                    step="0.01"
                    required
                    style="font-size:1.2rem;font-weight:700;
                           color:#0f172a;padding:.65rem 1rem"
                >
            </div>
        </div>

        <!-- ── Toggle disponible para huéspedes ─────────────────────────── -->
        <div class="mb-4 p-3 rounded-3"
             style="background:#f8faff;border:1px solid #e0e7ff">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox"
                       id="is_available_for_guests"
                       name="is_available_for_guests"
                       value="1" checked>
                <label class="form-check-label fw-semibold"
                       for="is_available_for_guests">
                    <i class="bi bi-phone me-1 text-primary"></i>
                    Visible para motor de reservas
                </label>
            </div>
            <p class="mb-0 text-muted ms-4 ps-2" style="font-size:.78rem">
                Si está activo, los huéspedes podrán agregar este item al hacer
                su reserva online.
            </p>
        </div>

        <!-- ── Preview del item ──────────────────────────────────────────── -->
        <div id="itemPreview"
             style="display:none;background:#fff;border:1px solid #e2e8f0;
                    border-radius:12px;padding:1rem;margin-bottom:1rem">
            <p class="text-muted mb-2" style="font-size:.72rem;
               text-transform:uppercase;font-weight:700;letter-spacing:.06em">
                Vista previa en punto de venta
            </p>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div id="previewName"
                         class="fw-semibold" style="font-size:.95rem">—</div>
                    <div id="previewCat"
                         class="text-muted" style="font-size:.78rem">—</div>
                </div>
                <div id="previewPrice"
                     class="fw-bold"
                     style="font-size:1.1rem;color:#4338ca">—</div>
            </div>
        </div>

        <!-- ── Navegación ────────────────────────────────────────────────── -->
        <div class="d-flex justify-content-between align-items-center pt-3
                    border-top" style="border-color:#f1f5f9!important">
            <a href="/onboarding/step/5" class="btn-wiz-secondary">
                <i class="bi bi-arrow-left me-1"></i> Anterior
            </a>
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn-wiz-skip"
                        onclick="skipStep(<?= $currentStep ?>)">
                    Omitir por ahora
                </button>
                <button type="submit" class="btn-wiz-primary"
                        id="btnSubmit6">
                    Guardar y continuar
                    <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>

    </form>
</div>

<!-- ── Tip ──────────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-start gap-3 p-3 rounded-3"
     style="background:#f0f9ff;border:1px solid #bae6fd">
    <i class="bi bi-cart-check-fill mt-1"
       style="color:#0284c7;font-size:1.1rem"></i>
    <div>
        <strong style="font-size:.85rem;color:#0c4a6e">
            Punto de venta integrado
        </strong>
        <p class="mb-0 text-muted" style="font-size:.82rem">
            Desde el folio de cada reservación podrás agregar cargos de
            productos y servicios. El total se suma automáticamente a la
            cuenta del huésped.
        </p>
    </div>
</div>

<script>
    // ── Preview en tiempo real ────────────────────────────────────────────────

    const sym = '<?= esc($tenant['currency_symbol'] ?? '$') ?>';

    /**
     * Actualiza la vista previa del producto mientras el usuario escribe
     */
    function updatePreview() {
        const name  = document.getElementById('product_name').value.trim();
        const cat   = document.getElementById('category_name').value.trim();
        const price = parseFloat(document.getElementById('unit_price').value) || 0;

        const preview = document.getElementById('itemPreview');

        if (!name && !price) {
            preview.style.display = 'none';
            return;
        }

        preview.style.display = 'block';

        document.getElementById('previewName').textContent  = name  || '—';
        document.getElementById('previewCat').textContent   = cat   || 'Sin categoría';
        document.getElementById('previewPrice').textContent =
            price > 0
                ? sym + new Intl.NumberFormat('es-CO').format(price)
                : '—';
    }

    // Escuchar cambios en los campos relevantes
    ['product_name', 'category_name', 'unit_price'].forEach(id => {
        document.getElementById(id).addEventListener('input', updatePreview);
    });

    // ── Sugerencias rápidas ───────────────────────────────────────────────────

    /**
     * Pre-llena el formulario con una sugerencia predefinida
     * @param {object} s - objeto con name, cat, type, price, desc
     */
    function fillSuggestion(s) {
        document.getElementById('product_name').value        = s.name;
        document.getElementById('category_name').value       = s.cat;
        document.getElementById('product_description').value = s.desc;
        document.getElementById('unit_price').value          = s.price;

        // Seleccionar el tipo correcto
        document.getElementById(
            s.type === 'product' ? 'typeProduct' : 'typeService'
        ).checked = true;

        // Resaltar el chip seleccionado
        document.querySelectorAll('.quick-chip').forEach(c =>
            c.classList.remove('active', 'btn-primary'));
        event.target.classList.add('active', 'btn-primary');
        event.target.classList.remove('btn-outline-secondary');

        updatePreview();

        // Scroll suave al formulario
        document.getElementById('product_name').scrollIntoView({
            behavior: 'smooth', block: 'center'
        });
    }

    // ── Foco en input de precio ───────────────────────────────────────────────

    document.getElementById('unit_price').addEventListener('focus', function () {
        this.closest('.price-input-wrap').style.borderColor = '#6366f1';
    });

    document.getElementById('unit_price').addEventListener('blur', function () {
        this.closest('.price-input-wrap').style.borderColor = '#c7d2fe';
    });

    // ── Submit con loader ─────────────────────────────────────────────────────

    document.getElementById('formStep6').addEventListener('submit', function () {
        const btn     = document.getElementById('btnSubmit6');
        btn.disabled  = true;
        btn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    });
</script>