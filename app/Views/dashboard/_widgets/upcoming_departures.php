<?php
/**
 * Widget: próximos 7 días, agrupados por fecha.
 * Recibe: $widgets['upcoming_departures'] (array agrupado por fecha YYYY-MM-DD)
 */
$grouped = $widgets['upcoming_departures'] ?? [];
$dayNames = [
    'Mon' => 'Lun', 'Tue' => 'Mar', 'Wed' => 'Mié', 'Thu' => 'Jue',
    'Fri' => 'Vie', 'Sat' => 'Sáb', 'Sun' => 'Dom',
];
?>

<?php if (empty($grouped)): ?>
    <div class="empty-state">
        <i class="bi bi-calendar-x"></i>
        <p class="mb-0">No hay salidas programadas en los próximos 7 días.</p>
    </div>
<?php else: ?>
    <?php foreach ($grouped as $date => $items): ?>
        <?php
            $ts        = strtotime($date);
            $dayShort  = $dayNames[date('D', $ts)] ?? date('D', $ts);
            $dayLabel  = $dayShort . ' ' . date('d/m', $ts);
            $isTomorrow = ($date === date('Y-m-d', strtotime('+1 day')));
        ?>
        <div class="day-group">
            <div class="day-header">
                <?= $dayLabel ?>
                <?php if ($isTomorrow): ?> · Mañana <?php endif; ?>
                <span class="text-muted ms-1">(<?= count($items) ?>)</span>
            </div>

            <?php foreach ($items as $item): ?>
                <div class="day-row">
                    <span class="day-time"><?= $item['time'] ?></span>
                    <span class="day-tour"><?= esc($item['tour_name']) ?></span>
                    <span class="day-pax">
                        <?php if (!$item['guide_name']): ?>
                            <span class="text-danger" title="Sin guía">
                                <i class="bi bi-person-x"></i>
                            </span>
                        <?php else: ?>
                            <i class="bi bi-person-badge text-muted"></i>
                            <?= esc($item['guide_name']) ?>
                        <?php endif; ?>
                    </span>
                    <span class="day-pax">
                        <i class="bi bi-people"></i>
                        <?= $item['current_pax'] ?>/<?= $item['max_pax'] ?>
                        <span class="text-muted">(<?= $item['occupancy_pct'] ?>%)</span>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
