<?php
/**
 * Widget: resumen del estado de guías.
 * Recibe: $widgets['guides_summary']
 */
$g   = $widgets['guides_summary'];
$cur = $currency ?? '$';
?>

<div class="row g-2">
    <div class="col-6">
        <div style="background:#f8f9fa;border-radius:.5rem;padding:.75rem;text-align:center;">
            <div style="font-size:1.5rem;font-weight:700;color:#0d6efd;">
                <?= $g['active_guides'] ?>
            </div>
            <small class="text-muted">Activos</small>
        </div>
    </div>
    <div class="col-6">
        <div style="background:#f8f9fa;border-radius:.5rem;padding:.75rem;text-align:center;">
            <div style="font-size:1.5rem;font-weight:700;color:#198754;">
                <?= $g['guides_working_week'] ?>
            </div>
            <small class="text-muted">Trabajan esta semana</small>
        </div>
    </div>
</div>

<?php if ((int)$g['pending_payments_count'] > 0): ?>
    <div class="mt-3 p-2 rounded" style="background:#fff3cd;border:1px solid #ffe69c;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted">Pagos pendientes</small>
                <div style="font-weight:600;">
                    <?= $cur ?><?= number_format($g['total_pending_payments'], 0, ',', '.') ?>
                </div>
                <small class="text-muted"><?= $g['pending_payments_count'] ?> pago<?= $g['pending_payments_count'] !== '1' ? 's' : '' ?></small>
            </div>
            <a href="<?= base_url('/guides/payments/pending') ?>" class="btn btn-sm btn-warning">
                Gestionar <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="mt-3 p-2 rounded text-center" style="background:#d1f2eb;color:#0a6e4f;">
        <small><i class="bi bi-check-circle-fill"></i> Todos los pagos al día</small>
    </div>
<?php endif; ?>

<div class="mt-3 text-end">
    <a href="<?= base_url('/guides') ?>" class="btn btn-sm btn-outline-secondary">
        Gestionar guías <i class="bi bi-arrow-right"></i>
    </a>
</div>
