<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <style>
        #calendar {
            max-width: 100%;
            margin: 0 auto;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .fc-timeline-slot-label { font-size: 0.85em; font-weight: bold; }
        .fc-event { cursor: pointer; transition: transform 0.1s; }
        .fc-event:hover { transform: scale(1.02); z-index: 5; }

        /* Leyenda de colores */
        .legend-dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-right: 5px; }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">Recepción: Ocupación Actual</h2>
        <div class="d-flex align-items-center small text-muted bg-white px-3 py-2 rounded shadow-sm">
            <span class="me-3"><span class="legend-dot" style="background:#ffc107"></span>Pendiente</span>
            <span class="me-3"><span class="legend-dot" style="background:#0dcaf0"></span>Confirmada</span>
            <span class="me-3"><span class="legend-dot" style="background:#198754"></span>In-House</span>
            <span><span class="legend-dot" style="background:#dc3545"></span>Cancelada</span>
        </div>
    </div>

    <div id='calendar'></div>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.11/index.global.min.js'></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                // Usamos la vista de línea de tiempo por recursos
                initialView: 'resourceTimelineMonth',
                locale: 'es', // Español
                height: 'auto', // Se ajusta al contenido

                headerToolbar: {
                    left: 'today prev,next',
                    center: 'title',
                    right: 'resourceTimelineMonth,resourceTimelineWeek'
                },

                // Configuración de la columna izquierda (Habitaciones)
                resourceAreaWidth: '200px',
                resourceAreaHeaderContent: 'Habitaciones',

                // 2. Conectar FullCalendar con nuestro CodeIgniter
                resources: '<?= base_url('/api/resources') ?>',
                events: '<?= base_url('/api/events') ?>',

                // 3. Pequeños detalles visuales
                nowIndicator: true, // Línea roja que marca "Hoy"
                slotMinWidth: 40,   // Ancho de cada día en el mes

                // Evento al hacer clic en una reserva (por ahora los manda a la lista)
                eventClick: function(info) {
                    // Prevenimos la navegación automática por si queremos hacer un modal futuro
                    info.jsEvent.preventDefault();
                    if (info.event.url) {
                        window.location.href = info.event.url;
                    }
                }
            });

            calendar.render();
        });
    </script>

<?= $this->endSection() ?>


