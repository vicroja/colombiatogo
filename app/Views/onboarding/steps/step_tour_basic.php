<?php
/**
 * onboarding/steps/step_tour_basic.php
 * Configura el primer tour del operador.
 */
?>

<div class="wizard-card">
    <div class="card-eyebrow">Tours</div>
    <h5>Crea tu primer tour</h5>
    <p class="card-hint">
        Este será el tour base de tu catálogo. Podrás agregar más y programar
        salidas desde el panel principal.
    </p>

    <form action="/onboarding/step/6" method="POST" id="formTourBasic">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label fw-semibold">Nombre del tour <span class="text-danger">*</span></label>
            <input type="text" name="tour_name" class="form-control form-control-lg"
                   placeholder="Ej: Cañón del Chicamocha al Amanecer"
                   value="<?= old('tour_name') ?>" required maxlength="150">
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Descripción</label>
            <textarea name="tour_description" class="form-control" rows="3"
                      placeholder="Describe la experiencia, qué verán, qué harán..."><?= old('tour_description') ?></textarea>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Duración (minutos) <span class="text-danger">*</span></label>
                <input type="number" name="duration_minutes" class="form-control"
                       value="<?= old('duration_minutes', 120) ?>" min="15" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Mínimo de personas</label>
                <input type="number" name="min_pax" class="form-control"
                       value="<?= old('min_pax', 2) ?>" min="1">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Dificultad</label>
                <select name="difficulty_level" class="form-select">
                    <option value="easy">Fácil</option>
                    <option value="moderate">Moderado</option>
                    <option value="hard">Difícil</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Punto de encuentro</label>
            <input type="text" name="meeting_point" class="form-control"
                   placeholder="Ej: Parque Principal, frente a la Alcaldía"
                   value="<?= old('meeting_point') ?>">
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Precio adulto <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" name="price_adult" class="form-control"
                           value="<?= old('price_adult', '0.00') ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Precio niño</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" name="price_child" class="form-control"
                           value="<?= old('price_child', '0.00') ?>">
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn-wiz-primary" id="btnTourBasic">
                Guardar y continuar <i class="bi bi-arrow-right ms-2"></i>
            </button>
        </div>
    </form>
</div>