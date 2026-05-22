<?php
/**
 * onboarding/steps/step_import.php
 *
 * Paso 3 del wizard PMS: ofrece importar automáticamente desde web/PDF/texto,
 * o saltar y continuar con configuración manual.
 *
 * Variables disponibles (de WizardController::getStepData('import')):
 *   $latestStaging  — último import_staging del tenant (puede ser null)
 *   $profile        — ['has_accommodation' => bool, 'has_tours' => bool]
 *   $currentStep    — número del paso actual
 *   $settings       — settings_json del tenant
 *
 * NOTA: Esta vista se inserta en el flujo del WizardController; el layout
 *       (sidebar + content-header) viene del onboarding/layout.php padre.
 */

$latest    = $latestStaging   ?? null;
$has_acc   = (bool)($profile['has_accommodation'] ?? true);
$has_tours = (bool)($profile['has_tours']         ?? false);

$hasImported  = !empty($latest) && ($latest['status'] ?? '') === 'imported';
$hasExtracted = !empty($latest) && in_array($latest['status'] ?? '', ['extracted', 'reviewed'], true);
?>

<div class="wizard-card">
    <div class="card-eyebrow">Paso <?= esc($currentStep) ?> · Opcional</div>
    <h5>🪄 ¿Quieres importar tu negocio automáticamente?</h5>
    <p class="card-hint">
        Si ya tienes información en línea (sitio web, brochure PDF, lista de habitaciones),
        podemos extraerla con IA y configurar
        <?php if ($has_acc): ?>habitaciones, tarifas<?php endif ?>
        <?php if ($has_acc && $has_tours): ?>, <?php endif ?>
        <?php if ($has_tours): ?>tours y salidas<?php endif ?>
        en segundos. Después podrás revisar y editar todo antes de aplicar.
    </p>

    <?php if ($hasImported): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill me-1"></i>
            Ya importaste tu negocio anteriormente.
            <a href="/onboarding/import" class="alert-link">Importar de nuevo o agregar más</a>.
        </div>
    <?php elseif ($hasExtracted): ?>
        <div class="alert alert-info d-flex align-items-center justify-content-between">
            <span>
                <i class="bi bi-hourglass-split me-1"></i>
                Tienes una extracción reciente lista para revisar.
            </span>
            <a href="/onboarding/import/review/<?= (int)$latest['id'] ?>" class="btn btn-sm btn-primary">
                Revisarla →
            </a>
        </div>
    <?php endif ?>

    <div class="row g-3 mt-2">

        <!-- ── Opción 1: Importador ──────────────────────────────────── -->
        <div class="col-md-7">
            <div class="card h-100 border-primary shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">🪄 Usar el importador mágico</h5>
                    <p class="card-text text-muted small">
                        Pega la URL de tu sitio, sube un PDF/imagen de tu brochure,
                        o pega un texto descriptivo. La IA extrae todo automáticamente.
                    </p>
                    <ul class="small text-muted ps-3 mb-3">
                        <li>✓ Funciona con sitios web, Shopify, PDFs, capturas</li>
                        <li>✓ Detecta habitaciones, tarifas, tours, productos, fotos</li>
                        <li>✓ Te muestra todo para revisar y editar antes de aplicar</li>
                    </ul>
                    <a href="/onboarding/import" class="btn btn-primary">
                        <i class="bi bi-magic me-1"></i> Empezar a importar
                    </a>
                </div>
            </div>
        </div>

        <!-- ── Opción 2: Saltar ──────────────────────────────────────── -->
        <div class="col-md-5">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">⏭️ Saltar por ahora</h5>
                    <p class="card-text text-muted small">
                        Si prefieres cargar todo manualmente, no hay problema.
                        Lo puedes hacer en los siguientes pasos del wizard, o desde
                        el panel principal después.
                    </p>
                    <form method="post" action="/onboarding/step/<?= (int)$currentStep ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="decision" value="skip">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="bi bi-skip-forward me-1"></i> Saltar este paso
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <div class="mt-4 small text-muted">
        💡 También puedes usar el importador después desde el panel principal,
        no es ahora o nunca.
    </div>
</div>
