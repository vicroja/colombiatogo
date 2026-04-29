<?php
/**
 * onboarding/steps/step_tour_schedule.php
 * Programa la primera salida del tour recién creado.
 */
$tourName = $tour['name'] ?? 'tu tour';
?>

<div class="wizard-card">
    <div class="card-eyebrow">Tours</div>
    <h5>Programa la primera salida</h5>
    <p class="card-hint">
        Define cuándo saldrá por primera vez <strong><?= esc($tourName) ?></strong>.
        Podrás agregar más salidas desde el panel de tours.
    </p>

    <form action="/onboarding/step/7" method="POST" id="formTourSchedule">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label fw-semibold">
                Fecha y hora de salida <span class="text-danger">*</span>
            </label>
            <input type="datetime-local" name="start_datetime" class="form-control form-control-lg"
                   min="<?= date('Y-m-d\TH:i') ?>" required>
            <small class="text-muted">Selecciona la fecha y hora exacta de salida del grupo.</small>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">
                Cupo máximo <span class="text-danger">*</span>
            </label>
            <input type="number" name="max_pax" class="form-control"
                   value="10" min="1" required>
            <small class="text-muted">Número máximo de personas que pueden unirse a esta salida.</small>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Notas internas</label>
            <textarea name="notes" class="form-control" rows="2"
                      placeholder="Equipo requerido, condiciones especiales, etc."></textarea>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <button type="button" class="btn-wiz-skip" onclick="skipStep(7)">
                Configurar esto después
            </button>
            <button type="submit" class="btn-wiz-primary" id="btnTourSchedule">
                Guardar y continuar <i class="bi bi-arrow-right ms-2"></i>
            </button>
        </div>
    </form>
</div>