<?php
/**
 * Widget: KPIs del módulo tours.
 * Recibe: $widgets['tour_kpis']
 */
$kpi = $widgets['tour_kpis'];
$cur = $currency ?? '$';

$deltaClass = '';
$deltaIcon  = '';
if ($kpi['revenue_delta'] !== null) {
    if ($kpi['revenue_delta'] > 0) {
        $deltaClass = 'text-success';
        $deltaIcon  = 'bi-arrow-up-short';
    } elseif ($kpi['revenue_delta'] < 0) {
        $deltaClass = 'text-danger';
        $deltaIcon  = 'bi-arrow-down-short';
    } else {
        $deltaClass = 'text-muted';
        $deltaIcon  = 'bi-dash';
    }
}
?>

<div class="col-md-6 col-lg-3">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#e7f3ff;color:#0d6efd;">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="kpi-label">Pax que salen hoy</div>
        <div class="kpi-value"><?= $kpi['pax_today'] ?></div>
        <div class="kpi-delta text-muted">
            en <?= $kpi['departures_today'] ?> salida<?= $kpi['departures_today'] !== 1 ? 's' : '' ?>
        </div>
    </div>
</div>

<div class="col-md-6 col-lg-3">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#d1f2eb;color:#198754;">
            <i class="bi bi-cash-coin"></i>
        </div>
        <div class="kpi-label">Ingresos del mes</div>
        <div class="kpi-value">
            <?= $cur ?><?= number_format($kpi['revenue_month'], 0, ',', '.') ?>
        </div>
        <?php if ($kpi['revenue_delta'] !== null): ?>
            <div class="kpi-delta <?= $deltaClass ?>">
                <i class="bi <?= $deltaIcon ?>"></i>
                <?= abs($kpi['revenue_delta']) ?>% vs mes anterior
            </div>
        <?php else: ?>
            <div class="kpi-delta text-muted">sin datos previos</div>
        <?php endif; ?>
    </div>
</div>

<div class="col-md-6 col-lg-3">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fff3cd;color:#997404;">
            <i class="bi bi-bookmark-plus"></i>
        </div>
        <div class="kpi-label">Reservas nuevas hoy</div>
        <div class="kpi-value"><?= $kpi['new_reservations'] ?></div>
        <div class="kpi-delta text-muted">
            <i class="bi bi-clock"></i> en las últimas 24h
        </div>
    </div>
</div>

<div class="col-md-6 col-lg-3">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#f3e7ff;color:#6f42c1;">
            <i class="bi bi-graph-up"></i>
        </div>
        <div class="kpi-label">Ocupación 7 días</div>
        <div class="kpi-value"><?= $kpi['avg_occupancy_7d'] ?>%</div>
        <div class="kpi-delta text-muted">promedio próximas salidas</div>
    </div>
</div>
