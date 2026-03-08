<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800">Panel de Control</h2>
            <p class="text-muted small mb-0">Resumen operativo para hoy: <?= date('d/m/Y') ?></p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-success border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Ingresos (Hoy)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= session('currency_symbol') ?: '$' ?><?= number_format($metrics['income_today'], 2) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cash-stack fa-2x text-muted opacity-50 fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-primary border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Llegadas Pendientes</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= $metrics['expected_checkins'] ?> Huéspedes</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-box-arrow-in-right text-muted opacity-50 fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-warning border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Salidas (Check-outs)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= $metrics['expected_checkouts'] ?> Habitaciones</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-box-arrow-right text-muted opacity-50 fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-info border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Ocupación Actual</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 fw-bold text-gray-800"><?= $metrics['occupancy_rate'] ?>%</div>
                                </div>
                                <div class="col px-3">
                                    <div class="progress progress-sm mr-2" style="height: 8px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $metrics['occupancy_rate'] ?>%" aria-valuenow="<?= $metrics['occupancy_rate'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted"><?= $metrics['occupied_units'] ?> de <?= $metrics['total_units'] ?> unidades ocupadas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-primary">Línea de Tiempo de Ocupación</h6>
        </div>
        <div class="card-body">
            <div id='calendar'></div>
        </div>
    </div>

    <style>
        #calendar { max-width: 100%; margin: 0 auto; }
        .fc-timeline-slot-label { font-size: 0.85em; font-weight: bold; }
        .fc-event { cursor: pointer; transition: transform 0.1s; }
        .fc-event:hover { transform: scale(1.01); z-index: 5; }
    </style>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.11/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'resourceTimelineMonth',
                locale: 'es',
                height: 'auto',
                headerToolbar: {
                    left: 'today prev,next',
                    center: 'title',
                    right: 'resourceTimelineMonth,resourceTimelineWeek'
                },
                resourceAreaWidth: '200px',
                resourceAreaHeaderContent: 'Habitaciones',
                resources: '<?= base_url('/api/resources') ?>',
                events: '<?= base_url('/api/events') ?>',
                nowIndicator: true,
                slotMinWidth: 40,
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    if (info.event.url) window.location.href = info.event.url;
                }
            });
            calendar.render();
        });
    </script>

<?= $this->endSection() ?>


