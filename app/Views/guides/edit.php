<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

<?php
// Este partial sirve tanto para create como para edit
// En edit, $guide existe. En create, no.
$isEdit     = isset($guide);
$formAction = $isEdit
    ? base_url("/guides/{$guide['id']}/update")
    : base_url('/guides/store');
$title = $isEdit ? 'Editar Guía' : 'Nuevo Guía';
$g     = $guide ?? [];
?>

    <div class="d-flex align-items-center mb-4">
        <a href="<?= base_url('/guides') ?>" class="btn btn-sm btn-outline-secondary me-3">&larr; Volver</a>
        <h2 class="mb-0"><?= $title ?></h2>
    </div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

    <form action="<?= $formAction ?>" method="post" id="guideForm">
        <?= csrf_field() ?>

        <div class="row g-4">

            <!-- Datos personales -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header fw-bold">Datos del Guía</div>
                    <div class="card-body row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="<?= esc($g['name'] ?? old('name')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="phone" class="form-control"
                                   value="<?= esc($g['phone'] ?? old('phone')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Documento</label>
                            <input type="text" name="document" class="form-control"
                                   value="<?= esc($g['document'] ?? old('document')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Especialidad</label>
                            <input type="text" name="specialty" class="form-control"
                                   placeholder="Ej: Senderismo, Buceo"
                                   value="<?= esc($g['specialty'] ?? old('specialty')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Idiomas</label>
                            <input type="text" name="languages" class="form-control"
                                   placeholder="Ej: ES, EN, FR"
                                   value="<?= esc($g['languages'] ?? old('languages')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas internas</label>
                            <textarea name="notes" class="form-control" rows="2"><?= esc($g['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modelo de pago -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header fw-bold">Modelo de Pago</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select name="payment_model" id="payment_model" class="form-select"
                                    onchange="showPaymentFields(this.value)" required>
                                <option value="fixed_per_tour" <?= ($g['payment_model'] ?? '') === 'fixed_per_tour' ? 'selected' : '' ?>>
                                    Fijo por salida
                                </option>
                                <option value="per_pax" <?= ($g['payment_model'] ?? '') === 'per_pax' ? 'selected' : '' ?>>
                                    Por pasajero
                                </option>
                                <option value="commission_pct" <?= ($g['payment_model'] ?? '') === 'commission_pct' ? 'selected' : '' ?>>
                                    Comisión sobre venta (%)
                                </option>
                                <option value="mixed" <?= ($g['payment_model'] ?? '') === 'mixed' ? 'selected' : '' ?>>
                                    Mixto (base + por pax extra)
                                </option>
                                <option value="salary" <?= ($g['payment_model'] ?? '') === 'salary' ? 'selected' : '' ?>>
                                    Salario fijo mensual
                                </option>
                            </select>
                            <small class="text-muted">El cálculo del pago se genera automáticamente al completar cada salida.</small>
                        </div>

                        <!-- fixed_per_tour -->
                        <div id="fields_fixed" class="payment-fields">
                            <label class="form-label">Tarifa fija por salida</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="rate_fixed" class="form-control"
                                       value="<?= $g['rate_fixed'] ?? '' ?>">
                            </div>
                        </div>

                        <!-- per_pax -->
                        <div id="fields_per_pax" class="payment-fields d-none">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label">Por adulto</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" name="rate_per_adult" class="form-control"
                                               value="<?= $g['rate_per_adult'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Por niño</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" name="rate_per_child" class="form-control"
                                               value="<?= $g['rate_per_child'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- commission_pct -->
                        <div id="fields_commission" class="payment-fields d-none">
                            <label class="form-label">Porcentaje sobre total vendido</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="commission_pct" class="form-control"
                                       value="<?= $g['commission_pct'] ?? '' ?>">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        <!-- mixed -->
                        <div id="fields_mixed" class="payment-fields d-none">
                            <div class="row g-2 mb-2">
                                <div class="col-12">
                                    <label class="form-label">Tarifa base por salida</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" name="rate_fixed" class="form-control"
                                               value="<?= $g['rate_fixed'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Mínimo de pax incluido en la base</label>
                                    <input type="number" name="min_pax_for_bonus" class="form-control"
                                           placeholder="Ej: 5"
                                           value="<?= $g['min_pax_for_bonus'] ?? '' ?>">
                                    <small class="text-muted">Desde el pax N+1 en adelante se cobra por persona.</small>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Por adulto extra</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" name="rate_per_adult" class="form-control"
                                               value="<?= $g['rate_per_adult'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Por niño extra</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" name="rate_per_child" class="form-control"
                                               value="<?= $g['rate_per_child'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- salary: no requiere campos numéricos -->
                        <div id="fields_salary" class="payment-fields d-none">
                            <div class="alert alert-info small mb-0">
                                <i class="bi bi-info-circle"></i>
                                El guía es empleado con salario fijo. Los tours que realice
                                se registrarán en su historial pero no generarán pagos automáticos por salida.
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <div class="mt-3 text-end">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-circle"></i> <?= $isEdit ? 'Actualizar' : 'Guardar' ?> Guía
            </button>
        </div>
    </form>

    <script>
        const MODEL_FIELDS = {
            'fixed_per_tour' : ['fields_fixed'],
            'per_pax'        : ['fields_per_pax'],
            'commission_pct' : ['fields_commission'],
            'mixed'          : ['fields_mixed'],
            'salary'         : ['fields_salary'],
        };

        function showPaymentFields(model) {
            // Ocultar todos
            document.querySelectorAll('.payment-fields').forEach(el => el.classList.add('d-none'));
            // Mostrar los del modelo seleccionado
            (MODEL_FIELDS[model] || []).forEach(id => {
                document.getElementById(id)?.classList.remove('d-none');
            });
        }

        // Ejecutar al cargar para mostrar los campos correctos en edición
        document.addEventListener('DOMContentLoaded', () => {
            showPaymentFields(document.getElementById('payment_model').value);
        });
    </script>

<?= $this->endSection() ?>