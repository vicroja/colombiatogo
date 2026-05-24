<?php
/**
 * Widget: salidas de hoy (hotel - check-outs).
 * Recibe: $widgets['departures_today']
 */
$departures = $widgets['departures_today'] ?? [];
?>

<?php if (empty($departures)): ?>
    <div class="empty-state">
        <i class="bi bi-house-check"></i>
        <p class="mb-0">No hay check-outs programados para hoy.</p>
    </div>
<?php else: ?>
    <div class="list-group list-group-flush">
        <?php foreach ($departures as $r): ?>
            <a href="<?= base_url("/reservations/{$r['id']}") ?>"
               class="list-group-item list-group-item-action px-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= esc($r['full_name']) ?></strong>
                        <div class="small text-muted">
                            <i class="bi bi-door-open"></i> <?= esc($r['unit_name'] ?? '—') ?>
                        </div>
                    </div>
                    <span class="badge bg-info text-dark">Check-out</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
