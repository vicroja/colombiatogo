<?= $this->extend('layouts/pms') ?>

<?= $this->section('styles') ?>
<style>
    /* ─── Dashboard styles ─────────────────────────────────────────── */
    .dash-header { margin-bottom: 1.5rem; }
    .dash-header h1 { font-size: 1.75rem; font-weight: 600; margin-bottom: .25rem; }
    .dash-header .text-muted { font-size: .9rem; text-transform: capitalize; }

    /* KPI cards */
    .kpi-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: .75rem;
        padding: 1.1rem 1.25rem;
        transition: transform .15s, box-shadow .15s;
        height: 100%;
    }
    .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.06); }
    .kpi-card .kpi-label  { font-size: .78rem; color: #6c757d; text-transform: uppercase; letter-spacing: .03em; }
    .kpi-card .kpi-value  { font-size: 1.85rem; font-weight: 700; line-height: 1.1; margin-top: .25rem; }
    .kpi-card .kpi-delta  { font-size: .8rem; margin-top: .25rem; }
    .kpi-card .kpi-icon {
        width: 38px; height: 38px; border-radius: .5rem;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; float: right;
    }

    /* Alerts strip */
    .alert-strip {
        background: linear-gradient(135deg, #fff5f5 0%, #fff8e1 100%);
        border: 1px solid #ffd6d6;
        border-radius: .75rem;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }
    .alert-strip .alert-item {
        display: flex; align-items: center; gap: .75rem;
        padding: .5rem 0;
        border-bottom: 1px solid rgba(0,0,0,.04);
    }
    .alert-strip .alert-item:last-child { border-bottom: 0; }
    .alert-strip .alert-icon {
        width: 32px; height: 32px; border-radius: .4rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .alert-icon.critical { background: #fee; color: #c33; }
    .alert-icon.warning  { background: #fff4e0; color: #c77a00; }
    .alert-icon.info     { background: #e7f3ff; color: #0d6efd; }
    .alert-strip .alert-message { flex: 1; font-size: .9rem; }
    .alert-strip .alert-message .alert-detail { font-size: .8rem; color: #6c757d; }

    /* Departure cards */
    .departure-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-left: 4px solid #ced4da;
        border-radius: .65rem;
        padding: 1rem;
        margin-bottom: .75rem;
        transition: box-shadow .15s;
    }
    .departure-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.06); }
    .departure-card.health-success { border-left-color: #198754; }
    .departure-card.health-warning { border-left-color: #ffc107; }
    .departure-card.health-danger  { border-left-color: #dc3545; }
    .departure-card.health-primary { border-left-color: #0d6efd; }
    .departure-card.health-secondary { border-left-color: #6c757d; opacity: .7; }

    .departure-card .dep-time {
        font-size: 1.4rem; font-weight: 700; color: #212529;
        line-height: 1; margin-bottom: .15rem;
    }
    .departure-card .dep-tour  { font-weight: 600; font-size: 1rem; margin-bottom: .25rem; }
    .departure-card .dep-meta  { font-size: .82rem; color: #6c757d; }

    .occupancy-bar {
        height: 6px; background: #f1f3f5; border-radius: 3px; overflow: hidden;
        margin-top: .5rem;
    }
    .occupancy-bar .fill {
        height: 100%; background: linear-gradient(90deg, #0d6efd, #6610f2);
        transition: width .3s;
    }
    .occupancy-bar .fill.warn { background: linear-gradient(90deg, #ffc107, #fd7e14); }
    .occupancy-bar .fill.full { background: linear-gradient(90deg, #198754, #20c997); }

    .health-badge {
        display: inline-flex; align-items: center; gap: .25rem;
        font-size: .75rem; padding: .15rem .5rem; border-radius: .25rem;
        font-weight: 500;
    }

    /* Section headers */
    .section-title {
        display: flex; align-items: center; justify-content: space-between;
        margin: 2rem 0 1rem; padding-bottom: .5rem;
        border-bottom: 1px solid #e9ecef;
    }
    .section-title h3 { font-size: 1.1rem; font-weight: 600; margin: 0; }
    .section-title .badge-count {
        background: #e7f3ff; color: #0d6efd; font-weight: 600;
        padding: .2rem .55rem; border-radius: 1rem; font-size: .75rem;
        margin-left: .5rem;
    }

    /* Upcoming day group */
    .day-group { margin-bottom: 1.25rem; }
    .day-group .day-header {
        font-size: .8rem; font-weight: 600; color: #6c757d;
        text-transform: uppercase; letter-spacing: .03em;
        padding: .25rem .5rem; background: #f8f9fa; border-radius: .35rem;
        margin-bottom: .5rem;
    }
    .day-group .day-row {
        display: flex; align-items: center; gap: .75rem;
        padding: .5rem .25rem; font-size: .88rem;
        border-bottom: 1px solid #f1f3f5;
    }
    .day-group .day-row:last-child { border-bottom: 0; }
    .day-group .day-time { font-weight: 600; min-width: 50px; }
    .day-group .day-tour { flex: 1; }
    .day-group .day-pax  { font-size: .8rem; color: #6c757d; }

    /* Simple cards */
    .panel {
        background: #fff; border: 1px solid #e9ecef;
        border-radius: .75rem; padding: 1.25rem;
        height: 100%;
    }
    .panel h4 { font-size: 1rem; font-weight: 600; margin: 0 0 1rem; }

    .top-tour-row {
        display: flex; align-items: center; gap: .75rem;
        padding: .55rem 0; border-bottom: 1px solid #f1f3f5;
    }
    .top-tour-row:last-child { border-bottom: 0; }
    .top-tour-row .rank {
        width: 24px; height: 24px; border-radius: 50%;
        background: #f8f9fa; color: #6c757d;
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem; font-weight: 600;
    }
    .top-tour-row .rank.gold   { background: #fff3cd; color: #997404; }
    .top-tour-row .rank.silver { background: #e9ecef; color: #495057; }
    .top-tour-row .rank.bronze { background: #f8d7da; color: #842029; }

    .recent-res-row {
        display: flex; align-items: center; gap: .75rem;
        padding: .65rem 0; border-bottom: 1px solid #f1f3f5;
        font-size: .88rem;
    }
    .recent-res-row:last-child { border-bottom: 0; }

    .empty-state { text-align: center; padding: 2.5rem 1rem; color: #adb5bd; }
    .empty-state i { font-size: 2.5rem; margin-bottom: .5rem; display: block; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ─── Encabezado ─────────────────────────────────────────────── -->
<div class="dash-header">
    <h1>Hola, <?= esc($userName) ?> 👋</h1>
    <p class="text-muted mb-0"><?= $today ?> · <?= esc($hotelName) ?></p>
</div>

<!-- ─── ALERTAS OPERATIVAS ─────────────────────────────────────── -->
<?php if (!empty($widgets['tour_alerts'])): ?>
    <?= $this->include('dashboard/_widgets/alerts_strip') ?>
<?php endif; ?>

<!-- ─── KPIs ───────────────────────────────────────────────────── -->
<div class="row g-3 mb-2">
    <?php if ($features['has_tours']): ?>
        <?= $this->include('dashboard/_widgets/kpis_tours') ?>
    <?php endif; ?>

    <?php if ($features['has_accommodation']): ?>
        <?= $this->include('dashboard/_widgets/kpis_hotel') ?>
    <?php endif; ?>
</div>

<!-- ─── BLOQUE TOURS ───────────────────────────────────────────── -->
<?php if ($features['has_tours']): ?>

    <!-- Salidas de hoy -->
    <div class="section-title">
        <h3>
            <i class="bi bi-sun"></i> Salidas de hoy
            <?php if (!empty($widgets['todays_departures'])): ?>
                <span class="badge-count"><?= count($widgets['todays_departures']) ?></span>
            <?php endif; ?>
        </h3>
        <a href="<?= base_url('/tours') ?>" class="btn btn-sm btn-outline-secondary">
            Ver todos los tours <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <?= $this->include('dashboard/_widgets/todays_departures') ?>

    <!-- Próximas salidas y panel lateral -->
    <div class="row g-3 mt-1">
        <div class="col-lg-7">
            <div class="panel">
                <h4><i class="bi bi-calendar-week"></i> Próximos 7 días</h4>
                <?= $this->include('dashboard/_widgets/upcoming_departures') ?>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel mb-3">
                <h4><i class="bi bi-trophy"></i> Top tours del mes</h4>
                <?= $this->include('dashboard/_widgets/top_tours') ?>
            </div>
            <div class="panel">
                <h4><i class="bi bi-person-badge"></i> Guías</h4>
                <?= $this->include('dashboard/_widgets/guides_summary') ?>
            </div>
        </div>
    </div>

    <!-- Reservas recientes -->
    <div class="section-title">
        <h3><i class="bi bi-bookmark-star"></i> Reservas recientes</h3>
        <a href="<?= base_url('/tours/reservations') ?>" class="btn btn-sm btn-outline-secondary">
            Ver todas <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="panel">
        <?= $this->include('dashboard/_widgets/recent_reservations') ?>
    </div>

<?php endif; ?>

<!-- ─── BLOQUE HOTEL ───────────────────────────────────────────── -->
<?php if ($features['has_accommodation']): ?>

    <div class="section-title">
        <h3><i class="bi bi-building"></i> Alojamiento</h3>
        <a href="<?= base_url('/reservations') ?>" class="btn btn-sm btn-outline-secondary">
            Ver reservas <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="panel">
                <h4><i class="bi bi-box-arrow-in-right"></i> Llegadas hoy</h4>
                <?= $this->include('dashboard/_widgets/arrivals_today') ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel">
                <h4><i class="bi bi-box-arrow-right"></i> Salidas hoy</h4>
                <?= $this->include('dashboard/_widgets/departures_today') ?>
            </div>
        </div>
    </div>

<?php endif; ?>

<?= $this->endSection() ?>
