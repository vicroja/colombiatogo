<?php
/**
 * onboarding/import/imported.php
 * Pantalla final después de aplicar la importación.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>¡Importación completada!</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f8fafc; font-family: 'Inter', system-ui, sans-serif; }
        .success-icon {
            width: 90px; height: 90px; border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff; font-size: 2.5rem;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem;
        }
        .summary-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: .75rem 1rem; border-bottom: 1px solid #f1f5f9;
        }
        .summary-row:last-child { border-bottom: none; }
        .summary-row .count {
            font-weight: 700; color: #0f172a; font-size: 1.1rem;
        }
        .btn-primary-wiz {
            background: #6366f1; color: #fff; border: none;
            padding: .75rem 1.75rem; border-radius: 10px; font-weight: 600;
        }
        .btn-primary-wiz:hover { background: #4f46e5; color: #fff; }
    </style>
</head>
<body>

<div class="container py-5 text-center" style="max-width: 720px;">

    <div class="success-icon">
        <i class="bi bi-check-lg"></i>
    </div>

    <h2 class="mb-1">¡Importación completada!</h2>
    <p class="lead text-muted">Tu negocio quedó configurado en el PMS.</p>

    <div class="card mt-4 text-start shadow-sm">
        <div class="card-body p-0">

            <?php if (!empty($summary['business_summary_updated'])): ?>
                <div class="summary-row">
                    <span><i class="bi bi-building me-2 text-primary"></i> Datos del negocio actualizados</span>
                    <span class="count">✓</span>
                </div>
            <?php endif ?>

            <?php if (!empty($summary['logo_downloaded'])): ?>
                <div class="summary-row">
                    <span><i class="bi bi-palette me-2 text-primary"></i> Logo descargado</span>
                    <span class="count">✓</span>
                </div>
            <?php endif ?>

            <?php if (!empty($summary['bed_types_created'])): ?>
                <div class="summary-row">
                    <span><i class="bi bi-moon me-2 text-primary"></i> Tipos de cama nuevos creados</span>
                    <span class="count"><?= (int)$summary['bed_types_created'] ?></span>
                </div>
            <?php endif ?>

            <?php if (!empty($summary['amenities_created'])): ?>
                <div class="summary-row">
                    <span><i class="bi bi-star me-2 text-primary"></i> Amenidades nuevas creadas</span>
                    <span class="count"><?= (int)$summary['amenities_created'] ?></span>
                </div>
            <?php endif ?>

            <?php if (!empty($summary['accommodation_types_created'])): ?>
                <div class="summary-row">
                    <span><i class="bi bi-tag me-2 text-primary"></i> Tipos de alojamiento</span>
                    <span class="count"><?= (int)$summary['accommodation_types_created'] ?></span>
                </div>
            <?php endif ?>

            <?php if (!empty($summary['units_created'])): ?>
                <div class="summary-row">
                    <span>
                        <i class="bi bi-door-open me-2 text-primary"></i>
                        Habitaciones / unidades creadas
                        <?php if (!empty($summary['unit_images_downloaded'])): ?>
                            <small class="text-success">
                                (<?= (int)$summary['unit_images_downloaded'] ?> con foto)
                            </small>
                        <?php endif ?>
                    </span>
                    <span class="count"><?= (int)$summary['units_created'] ?></span>
                </div>
            <?php endif ?>

            <?php if (!empty($summary['unit_beds_created'])): ?>
                <div class="summary-row">
                    <span><i class="bi bi-bookmark me-2 text-primary"></i> Camas asignadas</span>
                    <span class="count"><?= (int)$summary['unit_beds_created'] ?></span>
                </div>
            <?php endif ?>

            <?php if (!empty($summary['unit_amenities_created'])): ?>
                <div class="summary-row">
                    <span><i class="bi bi-link me-2 text-primary"></i> Amenidades asociadas</span>
                    <span class="count"><?= (int)$summary['unit_amenities_created'] ?></span>
                </div>
            <?php endif ?>

            <?php if (!empty($summary['rate_plans_created'])): ?>
                <div class="summary-row">
                    <span><i class="bi bi-card-list me-2 text-primary"></i> Planes tarifarios</span>
                    <span class="count"><?= (int)$summary['rate_plans_created'] ?></span>
                </div>
            <?php endif ?>

            <?php if (!empty($summary['unit_rates_created'])): ?>
                <div class="summary-row">
                    <span><i class="bi bi-currency-dollar me-2 text-primary"></i> Precios por unidad</span>
                    <span class="count"><?= (int)$summary['unit_rates_created'] ?></span>
                </div>
            <?php endif ?>

            <?php if (!empty($summary['tours_created'])): ?>
                <div class="summary-row">
                    <span>
                        <i class="bi bi-compass me-2 text-primary"></i>
                        Tours creados
                        <?php if (!empty($summary['tour_images_downloaded'])): ?>
                            <small class="text-success">
                                (<?= (int)$summary['tour_images_downloaded'] ?> con foto)
                            </small>
                        <?php endif ?>
                    </span>
                    <span class="count"><?= (int)$summary['tours_created'] ?></span>
                </div>
            <?php endif ?>

            <?php if (!empty($summary['tour_schedules_created'])): ?>
                <div class="summary-row">
                    <span><i class="bi bi-calendar-event me-2 text-primary"></i> Salidas programadas</span>
                    <span class="count"><?= (int)$summary['tour_schedules_created'] ?></span>
                </div>
            <?php endif ?>

            <?php if (!empty($summary['products_created'])): ?>
                <div class="summary-row">
                    <span>
                        <i class="bi bi-box-seam me-2 text-primary"></i>
                        Productos creados
                        <?php if (!empty($summary['product_images_downloaded'])): ?>
                            <small class="text-success">
                                (<?= (int)$summary['product_images_downloaded'] ?> con foto)
                            </small>
                        <?php endif ?>
                    </span>
                    <span class="count"><?= (int)$summary['products_created'] ?></span>
                </div>
            <?php endif ?>

        </div>
    </div>

    <div class="mt-4 d-flex justify-content-center gap-2 flex-wrap">
        <?php if (!empty($back_to_wizard)): ?>
            <a href="/onboarding" class="btn-primary-wiz">
                ✓ Continuar el wizard <i class="bi bi-arrow-right ms-1"></i>
            </a>
        <?php else: ?>
            <a href="/dashboard" class="btn-primary-wiz">Ir al dashboard</a>
        <?php endif ?>
        <a href="/onboarding/import" class="btn btn-outline-secondary">
            <i class="bi bi-plus me-1"></i> Importar más
        </a>
    </div>

    <p class="mt-4 small text-muted">
        💡 Puedes revisar y editar todo lo importado desde el panel principal.
    </p>

</div>

</body>
</html>
