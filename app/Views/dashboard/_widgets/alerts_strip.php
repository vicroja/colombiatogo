<?php
/**
 * Widget: franja de alertas operativas.
 * Recibe: $widgets['tour_alerts']
 */
$alerts = $widgets['tour_alerts'] ?? [];
if (empty($alerts)) return;

// Mostramos máximo 5 alertas; las demás van a "ver más"
$visible = array_slice($alerts, 0, 5);
$extra   = count($alerts) - count($visible);
?>
<div class="alert-strip">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <strong><i class="bi bi-bell-fill text-warning"></i> Requiere atención</strong>
        <small class="text-muted"><?= count($alerts) ?> alerta<?= count($alerts) !== 1 ? 's' : '' ?></small>
    </div>

    <?php foreach ($visible as $alert): ?>
        <div class="alert-item">
            <div class="alert-icon <?= $alert['severity'] ?>">
                <i class="bi <?= $alert['icon'] ?>"></i>
            </div>
            <div class="alert-message">
                <div><?= $alert['message'] ?></div>
                <div class="alert-detail"><?= $alert['detail'] ?></div>
            </div>
            <a href="<?= base_url($alert['action_url']) ?>"
               class="btn btn-sm btn-outline-dark">
                <?= $alert['action_label'] ?> <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    <?php endforeach; ?>

    <?php if ($extra > 0): ?>
        <div class="text-end mt-2">
            <small class="text-muted">y <?= $extra ?> alerta<?= $extra !== 1 ? 's' : '' ?> más...</small>
        </div>
    <?php endif; ?>
</div>
