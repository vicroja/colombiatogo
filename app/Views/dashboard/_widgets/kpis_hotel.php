<?php
/**
 * Widget: KPIs del módulo hotel.
 * Recibe: $widgets['hotel_kpis']
 */
$kpi = $widgets['hotel_kpis'];
$cur = $currency ?? '$';
?>

<div class="col-md-6 col-lg-3">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#e7f3ff;color:#0d6efd;">
            <i class="bi bi-box-arrow-in-right"></i>
        </div>
        <div class="kpi-label">Llegadas hoy</div>
        <div class="kpi-value"><?= $kpi['arrivals_today'] ?></div>
    </div>
</div>

<div class="col-md-6 col-lg-3">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fff3cd;color:#997404;">
            <i class="bi bi-box-arrow-right"></i>
        </div>
        <div class="kpi-label">Salidas hoy</div>
        <div class="kpi-value"><?= $kpi['departures_today'] ?></div>
    </div>
</div>

<div class="col-md-6 col-lg-3">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#f3e7ff;color:#6f42c1;">
            <i class="bi bi-building-fill-check"></i>
        </div>
        <div class="kpi-label">Ocupación</div>
        <div class="kpi-value"><?= $kpi['occupancy_pct'] ?>%</div>
        <div class="kpi-delta text-muted">
            <?= $kpi['units_occupied'] ?> / <?= $kpi['units_total'] ?> unidades
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
    </div>
</div>
