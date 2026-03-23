<?= $this->extend('layouts/pms') ?>

<?= $this->section('title') ?>Nueva Reserva - <?= session('tenant_name') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="<?= base_url('/reservations') ?>" class="btn btn-sm btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <h2 class="d-inline-block mb-0">Crear Nueva Reserva</h2>
            </div>
        </div>

        <form action="<?= base_url('/reservations/store') ?>" method="post" id="reservation-form" class="needs-validation" novalidate>
            <?= csrf_field() ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 text-primary"><i class="bi bi-person-badge"></i> Información del Titular</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Nombre Completo del Cliente</label>
                                    <input type="text" name="full_name" class="form-control" required placeholder="Ej. Juan Pérez">
                                    <div class="invalid-feedback">El nombre es obligatorio.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Documento de Identidad</label>
                                    <input type="text" name="document" class="form-control" placeholder="Cédula, Pasaporte, etc.">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Teléfono / WhatsApp</label>
                                    <input type="text" name="phone" class="form-control" placeholder="Ej. +57 300 000 0000">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Correo Electrónico</label>
                                    <input type="email" name="email" class="form-control" placeholder="cliente@correo.com">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-primary"><i class="bi bi-people"></i> Acompañantes / Manifiesto</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-guest-btn">
                                <i class="bi bi-plus-circle"></i> Agregar Acompañante
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="guests-container">
                                <div class="text-center py-3 text-muted border border-dashed rounded" id="no-guests-msg">
                                    <i class="bi bi-info-circle"></i> No se han registrado acompañantes aún.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm border-primary mb-4">
                        <div class="card-header bg-primary text-white py-3">
                            <h5 class="mb-0">Detalles de la Estancia</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Habitación / Unidad</label>
                                <select name="unit_id" class="form-select" required>
                                    <option value="">Seleccione una unidad...</option>
                                    <?php foreach ($units as $u): ?>
                                        <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?> (<?= esc($u['type_name']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Check-In (Entrada)</label>
                                <input type="date" name="check_in" id="check_in" class="form-control" required value="<?= date('Y-m-d') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Check-Out (Salida)</label>
                                <input type="date" name="check_out" id="check_out" class="form-control" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Plan Tarifario</label>
                                <select name="rate_plan_id" class="form-select" required>
                                    <?php foreach ($rate_plans as $rp): ?>
                                        <option value="<?= $rp['id'] ?>" <?= $rp['is_default'] ? 'selected' : '' ?>>
                                            <?= esc($rp['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Notas Especiales</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Observaciones, alergias, peticiones..."></textarea>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-success btn-lg shadow">
                                    <i class="bi bi-calendar-check"></i> Confirmar Reserva
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const guestsContainer = document.getElementById('guests-container');
            const addGuestBtn = document.getElementById('add-guest-btn');
            const noGuestsMsg = document.getElementById('no-guests-msg');
            let guestCount = 0;

            // Función para agregar fila de acompañante
            addGuestBtn.addEventListener('click', function() {
                if (noGuestsMsg) noGuestsMsg.style.display = 'none';

                guestCount++;
                const guestHtml = `
            <div class="card border-light bg-light mb-3 guest-row shadow-sm animate__animated animate__fadeIn">
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="small text-muted fw-bold">Nombre</label>
                            <input type="text" name="additional_guests[${guestCount}][first_name]" class="form-control form-control-sm" required placeholder="Nombre">
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted fw-bold">Apellido</label>
                            <input type="text" name="additional_guests[${guestCount}][last_name]" class="form-control form-control-sm" required placeholder="Apellido">
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted fw-bold">Documento</label>
                            <input type="text" name="additional_guests[${guestCount}][doc_number]" class="form-control form-control-sm" placeholder="ID/Cédula">
                        </div>
                        <div class="col-md-1 d-flex align-items-end justify-content-end">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-guest-btn" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;

                guestsContainer.insertAdjacentHTML('beforeend', guestHtml);
                logDebug("Agregado acompañante #" + guestCount);
            });

            // Delegación de eventos para eliminar acompañantes
            guestsContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-guest-btn')) {
                    const row = e.target.closest('.guest-row');
                    row.classList.remove('animate__fadeIn');
                    row.classList.add('animate__fadeOut');

                    setTimeout(() => {
                        row.remove();
                        if (guestsContainer.querySelectorAll('.guest-row').length === 0) {
                            noGuestsMsg.style.display = 'block';
                        }
                    }, 300);
                }
            });

            // Validación de formularios de Bootstrap
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });

            function logDebug(msg) {
                console.log("[MAVILUSA DEBUG] " + new Date().toLocaleTimeString() + ": " + msg);
            }
        });
    </script>

    <style>
        .border-dashed { border-style: dashed !important; border-width: 2px !important; }
        .animate__animated { animation-duration: 0.4s; }
    </style>

<?= $this->endSection() ?>