<?php
/**
 * Widget: Salidas de hoy.
 * Recibe: $widgets['todays_departures']
 */
$departures = $widgets['todays_departures'] ?? [];
?>

<?php if (empty($departures)): ?>
    <div class="panel">
        <div class="empty-state">
            <i class="bi bi-calendar-x"></i>
            <p class="mb-2"><strong>No hay salidas programadas para hoy.</strong></p>
            <small>Día tranquilo. Aprovecha para revisar la planificación de los próximos días.</small>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($departures as $d): ?>
            <?php
                $health = $d['health'];
                $occClass = '';
                if ($d['occupancy_pct'] >= 100) $occClass = 'full';
                elseif ($d['occupancy_pct'] >= 60) $occClass = '';
                elseif ($d['occupancy_pct'] >= 30) $occClass = 'warn';
                else $occClass = 'warn';
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="departure-card health-<?= $health['color'] ?>">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="dep-time"><?= $d['time_label'] ?></div>
                            <small class="text-muted"><?= $d['time_relative'] ?></small>
                        </div>
                        <span class="health-badge bg-<?= $health['color'] ?>-subtle text-<?= $health['color'] ?>">
                            <i class="bi <?= $health['icon'] ?>"></i> <?= $health['label'] ?>
                        </span>
                    </div>

                    <div class="dep-tour"><?= esc($d['tour_name']) ?></div>

                    <div class="dep-meta mb-2">
                        <i class="bi bi-geo-alt"></i> <?= esc($d['meeting_point'] ?? 'Sin punto de encuentro') ?>
                    </div>

                    <div class="dep-meta">
                        <?php if ($d['guide_name']): ?>
                            <i class="bi bi-person-badge"></i> <?= esc($d['guide_name']) ?>
                            <?php if ($d['guide_phone']): ?>
                                <a href="https://wa.me/<?= preg_replace('/\D/', '', $d['guide_phone']) ?>"
                                   target="_blank" class="text-success ms-1" title="WhatsApp al guía">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-danger">
                                <i class="bi bi-person-x"></i> Sin guía asignado
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Ocupación -->
                    <div class="mt-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Ocupación: <strong><?= $d['current_pax'] ?>/<?= $d['max_pax'] ?></strong>
                                <?php if ($d['pending_reservations'] > 0): ?>
                                    <span class="text-warning ms-1" title="Reservas pendientes de confirmar">
                                        +<?= $d['pending_reservations'] ?> <i class="bi bi-hourglass"></i>
                                    </span>
                                <?php endif; ?>
                            </small>
                            <small class="text-muted"><?= $d['occupancy_pct'] ?>%</small>
                        </div>
                        <div class="occupancy-bar">
                            <div class="fill <?= $occClass ?>" style="width: <?= min($d['occupancy_pct'], 100) ?>%"></div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="d-flex gap-2 mt-3">
                        <a href="<?= base_url("/tours/manifest/{$d['id']}") ?>"
                           class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="bi bi-list-check"></i> Manifiesto
                        </a>
                        <?php if (!$d['guide_id']): ?>
                            <a href="<?= base_url("/tours/{$d['tour_id']}/schedules") ?>"
                               class="btn btn-sm btn-warning flex-fill">
                                <i class="bi bi-person-plus"></i> Asignar guía
                            </a>
                        <?php else: ?>
                            <a href="<?= base_url("/tours/{$d['tour_id']}/reserve?schedule_id={$d['id']}") ?>"
                               class="btn btn-sm btn-outline-success flex-fill"
                               <?= $d['current_pax'] >= $d['max_pax'] ? 'aria-disabled="true" style="pointer-events:none;opacity:.5"' : '' ?>>
                                <i class="bi bi-person-plus"></i> Reservar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
