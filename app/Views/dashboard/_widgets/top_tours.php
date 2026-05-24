<?php
/**
 * Widget: ranking de tours del mes.
 * Recibe: $widgets['top_tours_month']
 */
$tours = $widgets['top_tours_month'] ?? [];
$cur   = $currency ?? '$';
?>

<?php if (empty($tours)): ?>
    <div class="empty-state">
        <i class="bi bi-bar-chart"></i>
        <p class="mb-0">Aún no hay reservas este mes.</p>
    </div>
<?php else: ?>
    <?php foreach ($tours as $i => $tour): ?>
        <?php
            $rank = $i + 1;
            $rankClass = $rank === 1 ? 'gold' : ($rank === 2 ? 'silver' : ($rank === 3 ? 'bronze' : ''));
        ?>
        <div class="top-tour-row">
            <div class="rank <?= $rankClass ?>"><?= $rank ?></div>
            <div class="flex-fill">
                <div style="font-weight:500;font-size:.9rem;">
                    <?= esc($tour['name']) ?>
                </div>
                <small class="text-muted">
                    <?= $tour['reservation_count'] ?> reserva<?= $tour['reservation_count'] !== '1' ? 's' : '' ?>
                    · <?= $tour['pax_total'] ?> pax
                </small>
            </div>
            <div class="text-end">
                <strong style="font-size:.9rem;">
                    <?= $cur ?><?= number_format($tour['revenue'], 0, ',', '.') ?>
                </strong>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
