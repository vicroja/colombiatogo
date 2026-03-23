<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <!-- ── Encabezado de página ─────────────────────────────────── -->
    <div class="page-header">
        <div>
            <h1 class="page-header-title">Panel de Control</h1>
            <p class="page-header-sub">
                <i class="bi bi-calendar3 me-1"></i>
                Resumen operativo para hoy: <?= date('l, d \d\e F \d\e Y') ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('/reservations/create') ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Nueva reserva
            </a>
        </div>
    </div>

    <!-- ── Tarjetas KPI ──────────────────────────────────────────── -->
    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="kpi-card-icon success">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="kpi-card-value">
                    <?= session('currency_symbol') ?: '$' ?><?= number_format($metrics['income_today'], 2) ?>
                </div>
                <div class="kpi-card-label">Ingresos de hoy</div>
                <?php
                $diff = ($metrics['income_today'] ?? 0) - ($metrics['income_yesterday'] ?? 0);
                $pct  = ($metrics['income_yesterday'] ?? 0) > 0
                    ? round(($diff / $metrics['income_yesterday']) * 100, 1)
                    : null;
                ?>
                <?php if ($pct !== null): ?>
                    <span class="kpi-card-badge <?= $pct >= 0 ? 'up' : 'down' ?>">
                    <i class="bi bi-arrow-<?= $pct >= 0 ? 'up' : 'down' ?>-short"></i>
                    <?= abs($pct) ?>% vs ayer
                </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="kpi-card-icon info">
                    <i class="bi bi-box-arrow-in-right"></i>
                </div>
                <div class="kpi-card-value"><?= $metrics['expected_checkins'] ?></div>
                <div class="kpi-card-label">Llegadas pendientes</div>
                <span class="kpi-card-badge <?= $metrics['expected_checkins'] > 0 ? 'warn' : 'info' ?>">
                <i class="bi bi-people"></i>
                <?= $metrics['expected_checkins'] > 0 ? 'Por confirmar' : 'Sin llegadas' ?>
            </span>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="kpi-card-icon warning">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
                <div class="kpi-card-value"><?= $metrics['expected_checkouts'] ?></div>
                <div class="kpi-card-label">Salidas de hoy</div>
                <span class="kpi-card-badge <?= $metrics['expected_checkouts'] > 0 ? 'warn' : 'info' ?>">
                <i class="bi bi-door-open"></i>
                <?= $metrics['expected_checkouts'] > 0 ? 'Check-outs pendientes' : 'Sin salidas' ?>
            </span>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="kpi-card">
                <div class="kpi-card-icon accent">
                    <i class="bi bi-building"></i>
                </div>
                <div class="kpi-card-value"><?= $metrics['occupancy_rate'] ?>%</div>
                <div class="kpi-card-label">Ocupación actual</div>
                <div class="mt-2" style="margin-bottom: 6px;">
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-info"
                             role="progressbar"
                             style="width: <?= $metrics['occupancy_rate'] ?>%"
                             aria-valuenow="<?= $metrics['occupancy_rate'] ?>"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>
                <span class="kpi-card-badge info">
                <?= $metrics['occupied_units'] ?> de <?= $metrics['total_units'] ?> unidades
            </span>
            </div>
        </div>

    </div>

    <!-- ── Estado de habitaciones (mapa rápido) ─────────────────── -->
<?php if (!empty($metrics['units_status'])): ?>
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h6 class="m-0"><i class="bi bi-grid-3x3-gap me-2 text-muted"></i>Estado de habitaciones</h6>
            <a href="<?= base_url('/inventory') ?>" class="btn btn-sm btn-outline-secondary">
                Ver inventario <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($metrics['units_status'] as $unit): ?>
                    <?php
                    $statusMap = [
                        'available'   => ['class' => 'badge-available',   'icon' => 'bi-check-circle',     'label' => 'Disponible'],
                        'occupied'    => ['class' => 'badge-occupied',    'icon' => 'bi-person-fill',      'label' => 'Ocupado'],
                        'maintenance' => ['class' => 'badge-maintenance', 'icon' => 'bi-tools',            'label' => 'Mantenimiento'],
                        'blocked'     => ['class' => 'badge-blocked',     'icon' => 'bi-slash-circle',     'label' => 'Bloqueado'],
                    ];
                    $s = $statusMap[$unit['status']] ?? ['class' => 'bg-secondary', 'icon' => 'bi-question', 'label' => $unit['status']];
                    ?>
                    <a href="<?= base_url('/reservations?unit=' . $unit['id']) ?>"
                       class="badge <?= $s['class'] ?> text-decoration-none d-flex align-items-center gap-1"
                       title="<?= esc($unit['name']) ?> — <?= $s['label'] ?>"
                       style="font-size: 12px; padding: 6px 10px; border-radius: 8px;">
                        <i class="bi <?= $s['icon'] ?>"></i>
                        <?= esc($unit['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Leyenda -->
            <div class="d-flex flex-wrap gap-3 mt-3 pt-3" style="border-top: 1px solid var(--pms-border);">
            <span class="d-flex align-items-center gap-1 text-xs text-muted">
                <span class="badge badge-available" style="width:10px;height:10px;padding:0;border-radius:50%;"></span> Disponible
            </span>
                <span class="d-flex align-items-center gap-1 text-xs text-muted">
                <span class="badge badge-occupied" style="width:10px;height:10px;padding:0;border-radius:50%;"></span> Ocupado
            </span>
                <span class="d-flex align-items-center gap-1 text-xs text-muted">
                <span class="badge badge-maintenance" style="width:10px;height:10px;padding:0;border-radius:50%;"></span> Mantenimiento
            </span>
                <span class="d-flex align-items-center gap-1 text-xs text-muted">
                <span class="badge badge-blocked" style="width:10px;height:10px;padding:0;border-radius:50%;"></span> Bloqueado
            </span>
            </div>
        </div>
    </div>
<?php endif; ?>

    <!-- ── Línea de tiempo de ocupación ─────────────────────────── -->
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h6 class="m-0">
                <i class="bi bi-calendar-week me-2 text-muted"></i>Línea de tiempo de ocupación
            </h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" id="btn-week" onclick="switchView('resourceTimelineWeek')">
                    Semana
                </button>
                <button class="btn btn-sm btn-primary" id="btn-month" onclick="switchView('resourceTimelineMonth')">
                    Mes
                </button>
            </div>
        </div>
        <div class="card-body" style="padding: 12px 16px;">
            <div id="calendar"></div>
        </div>
    </div>

    <!-- ── Estilos del calendar ──────────────────────────────────── -->
    <style>
        #calendar { max-width: 100%; }

        /* Cabeceras */
        .fc .fc-resource-area-header .fc-cell-shaded,
        .fc .fc-col-header-cell { background: var(--pms-surface-2) !important; }

        .fc .fc-col-header-cell-cushion,
        .fc .fc-datagrid-cell-main {
            font-size: 12px;
            font-weight: 500;
            color: var(--pms-text-secondary);
        }

        /* Eventos */
        .fc-event {
            border-radius: 6px !important;
            border: none !important;
            font-size: 11.5px !important;
            font-weight: 500 !important;
            cursor: pointer;
            transition: filter 0.15s, transform 0.1s;
        }

        .fc-event:hover {
            filter: brightness(0.92);
            transform: scaleY(1.04);
            z-index: 5;
        }

        /* Slot hoy */
        .fc .fc-timeline-now-indicator-line {
            border-color: var(--pms-accent);
            border-width: 2px;
        }

        .fc .fc-bg-event { opacity: 0.08; }

        /* Scrollbar dentro del calendar */
        .fc-scroller::-webkit-scrollbar { height: 5px; }
        .fc-scroller::-webkit-scrollbar-thumb {
            background: var(--pms-border-strong);
            border-radius: 4px;
        }
    </style>

    <!-- ── Scripts del calendar ─────────────────────────────────── -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.11/index.global.min.js'></script>
    <script>
        var calendar;

        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');

            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'resourceTimelineMonth',
                locale: 'es',
                height: 'auto',
                headerToolbar: {
                    left: 'today prev,next',
                    center: 'title',
                    right: false   // los botones Semana/Mes los manejamos nosotros
                },
                resourceAreaWidth: '160px',
                resourceAreaHeaderContent: 'Habitación',
                resources: '<?= base_url('/api/resources') ?>',
                events:    '<?= base_url('/api/events') ?>',
                nowIndicator: true,
                slotMinWidth: 38,
                eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                eventClick: function (info) {
                    info.jsEvent.preventDefault();
                    if (info.event.url) window.location.href = info.event.url;
                },
                // Color por defecto de eventos sin color definido
                eventColor: '#5e78ff',
            });

            calendar.render();
        });

        function switchView(view) {
            calendar.changeView(view);
            document.getElementById('btn-week').classList.toggle('btn-primary',  view === 'resourceTimelineWeek');
            document.getElementById('btn-week').classList.toggle('btn-outline-secondary', view !== 'resourceTimelineWeek');
            document.getElementById('btn-month').classList.toggle('btn-primary', view === 'resourceTimelineMonth');
            document.getElementById('btn-month').classList.toggle('btn-outline-secondary', view !== 'resourceTimelineMonth');
        }
    </script>

<?= $this->endSection() ?>