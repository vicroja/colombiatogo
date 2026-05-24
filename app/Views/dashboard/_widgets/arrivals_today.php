<?php
/**
 * Widget: llegadas de hoy (hotel).
 * Recibe: $widgets['arrivals_today']
 */
$arrivals = $widgets['arrivals_today'] ?? [];
?>

<?php if (empty($arrivals)): ?>
    <div class="empty-state">
        <i class="bi bi-house"></i>
        <p class="mb-0">No hay llegadas programadas para hoy.</p>
    </div>
<?php else: ?>
    <div class="list-group list-group-flush">
        <?php foreach ($arrivals as $r): ?>
            <a href="<?= base_url("/reservations/{$r['id']}") ?>"
               class="list-group-item list-group-item-action px-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= esc($r['full_name']) ?></strong>
                        <div class="small text-muted">
                            <i class="bi bi-door-open"></i> <?= esc($r['unit_name'] ?? '—') ?>
                            <?php if ($r['phone']): ?>
                                · <i class="bi bi-telephone"></i> <?= esc($r['phone']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="badge bg-<?= $r['status'] === 'confirmed' ? 'success' : 'warning text-dark' ?>">
                        <?= ucfirst($r['status']) ?>
                    </span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
