<?php
/**
 * onboarding/steps/step4_rates.php
 *
 * Paso 4: Crear el plan tarifario base y asignarlo a la unidad del paso 3.
 * Muestra el nombre de la unidad creada para dar contexto al usuario.
 */

$unitName = $unit_name ?? 'tu unidad';
?>

<!-- ── Card principal ────────────────────────────────────────────────────── -->
<div class="wizard-card">
    <div class="card-eyebrow">Paso 4 · Requerido</div>
    <h5>Plan tarifario para <em><?= esc($unitName) ?></em></h5>
    <p class="card-hint">
        Define cuánto cuesta por noche. Podrás crear tarifas de temporada,
        descuentos y planes adicionales desde el módulo de tarifas una vez
        completes el onboarding.
    </p>

    <form action="/onboarding/step/4" method="POST" id="formStep4">
        <?= csrf_field() ?>

        <!-- ── Nombre del plan ───────────────────────────────────────────── -->
        <div class="mb-4">
            <label class="form-label fw-semibold" for="plan_name">
                Nombre del plan <span class="text-danger">*</span>
            </label>
            <input
                type="text"
                class="form-control form-control-lg"
                id="plan_name"
                name="plan_name"
                value="Tarifa Rack"
                placeholder="Ej: Tarifa Rack, Tarifa Directa, Plan Estándar"
                required
                maxlength="100"
            >
            <div class="form-text">
                La "Tarifa Rack" es el precio base oficial. Puedes llamarlo como prefieras.
            </div>
        </div>

        <!-- ── Precio por noche ──────────────────────────────────────────── -->
        <div class="mb-4">
            <label class="form-label fw-semibold" for="price_per_night">
                Precio por noche <span class="text-danger">*</span>
            </label>

            <!-- Card visual de precio -->
            <div class="price-input-wrap"
                 style="display:flex;align-items:center;gap:0;
                        border:2px solid #c7d2fe;border-radius:12px;
                        overflow:hidden;background:#fff;
                        transition:border-color .2s">
                <div style="padding:.75rem 1rem;background:#f0f4ff;
                            font-weight:700;color:#4338ca;font-size:1.1rem;
                            border-right:2px solid #c7d2fe;white-space:nowrap">
                    <?= esc($tenant['currency_symbol'] ?? '$') ?>
                    <span style="font-size:.72rem;color:#6366f1;
                                 font-weight:600;margin-left:.2rem">
                        <?= esc($tenant['currency_code'] ?? 'COP') ?>
                    </span>
                </div>
                <input
                    type="number"
                    class="form-control border-0 shadow-none"
                    id="price_per_night"
                    name="price_per_night"
                    placeholder="0.00"
                    min="0"
                    step="0.01"
                    required
                    style="font-size:1.4rem;font-weight:700;
                           color:#0f172a;padding:.75rem 1rem"
                    oninput="updatePriceSummary()"
                >
                <div style="padding:.75rem 1rem;color:#94a3b8;
                            font-size:.82rem;white-space:nowrap">
                    / noche
                </div>
            </div>
        </div>

        <!-- ── Cargos adicionales ────────────────────────────────────────── -->
        <div class="mb-4">
            <p class="fw-semibold mb-1">
                <i class="bi bi-person-plus me-1 text-primary"></i>
                Cargos por persona adicional
                <span class="text-muted fw-normal">(opcional)</span>
            </p>
            <p class="text-muted small mb-3">
                Se cobra cuando el número de huéspedes supera la ocupación base.
            </p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold" for="extra_person_price">
                        Adulto adicional
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <?= esc($tenant['currency_symbol'] ?? '$') ?>
                        </span>
                        <input
                            type="number"
                            class="form-control"
                            id="extra_person_price"
                            name="extra_person_price"
                            placeholder="0.00"
                            min="0"
                            step="0.01"
                            value="0"
                            oninput="updatePriceSummary()"
                        >
                        <span class="input-group-text text-muted"
                              style="font-size:.8rem">/ noche</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold" for="extra_child_price">
                        Niño adicional
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <?= esc($tenant['currency_symbol'] ?? '$') ?>
                        </span>
                        <input
                            type="number"
                            class="form-control"
                            id="extra_child_price"
                            name="extra_child_price"
                            placeholder="0.00"
                            min="0"
                            step="0.01"
                            value="0"
                            oninput="updatePriceSummary()"
                        >
                        <span class="input-group-text text-muted"
                              style="font-size:.8rem">/ noche</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Opciones del plan ─────────────────────────────────────────── -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-semibold" for="min_nights">
                    Mínimo de noches
                </label>
                <div class="input-group">
                    <input
                        type="number"
                        class="form-control text-center"
                        id="min_nights"
                        name="min_nights"
                        value="1"
                        min="1"
                        max="30"
                    >
                    <span class="input-group-text">noche(s)</span>
                </div>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <div class="form-check form-switch pb-2">
                    <input class="form-check-input" type="checkbox"
                           id="includes_breakfast" name="includes_breakfast"
                           value="1">
                    <label class="form-check-label fw-semibold"
                           for="includes_breakfast">
                        <i class="bi bi-cup-hot me-1 text-warning"></i>
                        Incluye desayuno
                    </label>
                    <div class="form-text">
                        Se mostrará como beneficio en el sitio web.
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Descripción del plan ──────────────────────────────────────── -->
        <div class="mb-4">
            <label class="form-label fw-semibold" for="plan_description">
                Descripción del plan
                <span class="text-muted fw-normal">(opcional)</span>
            </label>
            <input
                type="text"
                class="form-control"
                id="plan_description"
                name="plan_description"
                placeholder="Ej: Incluye wifi, parqueadero y acceso a zonas comunes"
                maxlength="255"
            >
        </div>

        <hr style="border-color:#f1f5f9">

        <!-- ── Resumen de precio ─────────────────────────────────────────── -->
        <div id="priceSummary"
             style="background:#f8faff;border:1px solid #e0e7ff;
                    border-radius:12px;padding:1.25rem;display:none">
            <p class="fw-semibold mb-3" style="color:#3730a3;font-size:.9rem">
                <i class="bi bi-calculator me-2"></i>
                Vista previa de precios
            </p>
            <div class="row g-2 text-center">
                <!-- 2 personas, 1 noche -->
                <div class="col-4">
                    <div style="background:#fff;border-radius:10px;
                                padding:.75rem;border:1px solid #e0e7ff">
                        <div style="font-size:.7rem;color:#6366f1;
                                    font-weight:700;text-transform:uppercase;
                                    letter-spacing:.05em">
                            1 noche · base
                        </div>
                        <div id="price1n2p"
                             style="font-size:1.2rem;font-weight:800;
                                    color:#0f172a;margin:.25rem 0">
                            —
                        </div>
                        <div style="font-size:.7rem;color:#94a3b8">
                            Ocupación base
                        </div>
                    </div>
                </div>
                <!-- 2 noches -->
                <div class="col-4">
                    <div style="background:#fff;border-radius:10px;
                                padding:.75rem;border:1px solid #e0e7ff">
                        <div style="font-size:.7rem;color:#6366f1;
                                    font-weight:700;text-transform:uppercase;
                                    letter-spacing:.05em">
                            2 noches · base
                        </div>
                        <div id="price2n2p"
                             style="font-size:1.2rem;font-weight:800;
                                    color:#0f172a;margin:.25rem 0">
                            —
                        </div>
                        <div style="font-size:.7rem;color:#94a3b8">
                            Ocupación base
                        </div>
                    </div>
                </div>
                <!-- 1 noche + 1 extra -->
                <div class="col-4">
                    <div style="background:#fff;border-radius:10px;
                                padding:.75rem;border:1px solid #e0e7ff">
                        <div style="font-size:.7rem;color:#6366f1;
                                    font-weight:700;text-transform:uppercase;
                                    letter-spacing:.05em">
                            1 noche · +1 adulto
                        </div>
                        <div id="price1n3p"
                             style="font-size:1.2rem;font-weight:800;
                                    color:#0f172a;margin:.25rem 0">
                            —
                        </div>
                        <div style="font-size:.7rem;color:#94a3b8">
                            Con persona extra
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Navegación ────────────────────────────────────────────────── -->
        <div class="d-flex justify-content-between align-items-center pt-4 mt-2
                    border-top" style="border-color:#f1f5f9!important">
            <a href="/onboarding/step/3" class="btn-wiz-secondary">
                <i class="bi bi-arrow-left me-1"></i> Anterior
            </a>
            <button type="submit" class="btn-wiz-primary" id="btnSubmit4">
                Guardar y continuar
                <i class="bi bi-arrow-right ms-2"></i>
            </button>
        </div>

    </form>
</div>

<!-- ── Tip ──────────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-start gap-3 p-3 rounded-3"
     style="background:#f0fdf4;border:1px solid #bbf7d0">
    <i class="bi bi-graph-up-arrow mt-1"
       style="color:#16a34a;font-size:1.1rem"></i>
    <div>
        <strong style="font-size:.85rem;color:#15803d">
            Tarifas de temporada
        </strong>
        <p class="mb-0 text-muted" style="font-size:.82rem">
            Una vez completes el onboarding podrás crear tarifas especiales para
            temporada alta, fines de semana o fechas especiales desde
            <strong>Tarifas → Temporadas</strong>.
        </p>
    </div>
</div>

<script>
    /**
     * Formatea un número como moneda local
     * @param {number} amount
     * @returns {string}
     */
    function formatCurrency(amount) {
        if (isNaN(amount) || amount <= 0) return '—';
        return new Intl.NumberFormat('es-CO', {
            minimumFractionDigits : 0,
            maximumFractionDigits : 2,
        }).format(amount);
    }

    /**
     * Actualiza el resumen de precios en tiempo real
     * mientras el usuario escribe en los campos de tarifa
     */
    function updatePriceSummary() {
        const base  = parseFloat(document.getElementById('price_per_night').value)  || 0;
        const extra = parseFloat(document.getElementById('extra_person_price').value)|| 0;
        const sym   = '<?= esc($tenant['currency_symbol'] ?? '$') ?>';

        const summary = document.getElementById('priceSummary');

        // Mostrar resumen solo si hay precio base
        if (base <= 0) {
            summary.style.display = 'none';
            return;
        }

        summary.style.display = 'block';

        document.getElementById('price1n2p').textContent = sym + formatCurrency(base);
        document.getElementById('price2n2p').textContent = sym + formatCurrency(base * 2);
        document.getElementById('price1n3p').textContent = sym + formatCurrency(base + extra);
    }

    /**
     * Resalta el input de precio al hacer foco
     */
    document.getElementById('price_per_night').addEventListener('focus', function () {
        this.closest('.price-input-wrap').style.borderColor = '#6366f1';
    });
    document.getElementById('price_per_night').addEventListener('blur', function () {
        this.closest('.price-input-wrap').style.borderColor = '#c7d2fe';
    });

    /**
     * Submit con loader
     */
    document.getElementById('formStep4').addEventListener('submit', function () {
        const btn     = document.getElementById('btnSubmit4');
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    });
</script>