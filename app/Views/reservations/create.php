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
                            <span class="badge bg-secondary" id="guests-counter-badge">0 Acompañantes</span>
                        </div>
                        <div class="card-body">
                            <div id="guests-container">
                                <div class="text-center py-3 text-muted border border-dashed rounded" id="no-guests-msg">
                                    <i class="bi bi-info-circle"></i> Cambia el número de personas en los detalles para agregar acompañantes.
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
                                <select name="unit_id" id="unit_id" class="form-select trigger-calc" required>
                                    <option value="">Seleccione una unidad...</option>
                                    <?php foreach ($units as $u): ?>
                                        <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?> (<?= esc($u['type_name']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold">Check-In</label>
                                    <input type="date" name="check_in" id="check_in" class="form-control trigger-calc" required value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold">Check-Out</label>
                                    <input type="date" name="check_out" id="check_out" class="form-control trigger-calc" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold">Total Personas</label>
                                    <input type="number" name="num_guests" id="num_guests" class="form-control trigger-calc" value="1" min="1" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold">Plan Tarifario</label>
                                    <select name="rate_plan_id" id="rate_plan_id" class="form-select trigger-calc" required>
                                        <?php foreach ($rate_plans as $rp): ?>
                                            <option value="<?= $rp['id'] ?>" <?= $rp['is_default'] ? 'selected' : '' ?>>
                                                <?= esc($rp['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-success">Código de Cupón (Opcional)</label>
                                <input type="text" name="promo_code" id="promo_code" class="form-control border-success text-uppercase" placeholder="Ej. VERANO2026">
                            </div>

                            <div class="mb-3 bg-light p-3 rounded border">
                                <label class="form-label fw-bold text-primary mb-1">Precio Total Estimado</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white border-primary">$</span>
                                    <input type="number" step="0.01" name="total_price" id="total_price" class="form-control form-control-lg fw-bold text-end" required>
                                </div>
                                <small class="text-muted d-block mt-1" id="price-details-helper">Calculado dinámicamente. Permite ajuste manual.</small>
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
            // Referencias al DOM
            const numGuestsInput = document.getElementById('num_guests');
            const guestsContainer = document.getElementById('guests-container');
            const noGuestsMsg = document.getElementById('no-guests-msg');
            const guestsCounterBadge = document.getElementById('guests-counter-badge');
            const totalPriceInput = document.getElementById('total_price');
            const priceHelper = document.getElementById('price-details-helper');
            const promoCodeInput = document.getElementById('promo_code');

            // Todos los inputs que disparan el recálculo tienen la clase 'trigger-calc'
            const triggerInputs = document.querySelectorAll('.trigger-calc');

            // 1. FUNCIÓN: Generar campos de acompañantes basados en 'num_guests'
            function generateGuestFields() {
                const totalPersonas = parseInt(numGuestsInput.value) || 1;
                const acompañantesExtra = totalPersonas - 1; // Restamos al titular

                guestsCounterBadge.textContent = acompañantesExtra + (acompañantesExtra === 1 ? ' Acompañante' : ' Acompañantes');

                // Limpiamos contenedor
                guestsContainer.innerHTML = '';

                if (acompañantesExtra <= 0) {
                    guestsContainer.appendChild(noGuestsMsg);
                    noGuestsMsg.style.display = 'block';
                    logDebug("Sin acompañantes. Mostrando mensaje por defecto.");
                    return;
                }

                logDebug(`Generando campos para ${acompañantesExtra} acompañantes.`);
                noGuestsMsg.style.display = 'none';

                for (let i = 1; i <= acompañantesExtra; i++) {
                    const guestHtml = `
                    <div class="card border-light bg-light mb-3 guest-row shadow-sm animate__animated animate__fadeIn">
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-md-12 mb-1"><strong class="text-secondary">Acompañante ${i}</strong></div>
                                <div class="col-md-3">
                                    <label class="small text-muted fw-bold">Nombres</label>
                                    <input type="text" name="extra_guest_name[]" class="form-control form-control-sm" placeholder="Nombre">
                                </div>
                                <div class="col-md-3">
                                    <label class="small text-muted fw-bold">Apellidos</label>
                                    <input type="text" name="extra_guest_lastname[]" class="form-control form-control-sm" placeholder="Apellido">
                                </div>
                                <div class="col-md-3">
                                    <label class="small text-muted fw-bold">Tipo Doc.</label>
                                    <select name="extra_guest_doc_type[]" class="form-select form-select-sm">
                                        <option value="">Seleccionar...</option>
                                        <option value="CC">Cédula</option>
                                        <option value="TI">T. Identidad</option>
                                        <option value="CE">Cédula Ext.</option>
                                        <option value="PA">Pasaporte</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="small text-muted fw-bold">Documento</label>
                                    <input type="text" name="extra_guest_doc_number[]" class="form-control form-control-sm" placeholder="No. Documento">
                                </div>
                            </div>
                        </div>
                    </div>`;
                    guestsContainer.insertAdjacentHTML('beforeend', guestHtml);
                }
            }

            // 2. FUNCIÓN: Calcular Precio vía AJAX
            async function calculatePrice() {
                const unitId = document.getElementById('unit_id').value;
                const checkIn = document.getElementById('check_in').value;
                const checkOut = document.getElementById('check_out').value;
                const numGuests = numGuestsInput.value;
                const ratePlanId = document.getElementById('rate_plan_id').value;
                const promoCode = promoCodeInput.value.trim();

                // Validamos que tengamos lo mínimo indispensable
                if (!unitId || !checkIn || !checkOut || numGuests < 1) {
                    return;
                }

                logDebug(`Iniciando cálculo: Unidad ${unitId}, Fechas: ${checkIn} a ${checkOut}, Personas: ${numGuests}`);

                // Efecto visual de carga
                totalPriceInput.classList.add('bg-warning', 'text-dark');
                priceHelper.innerHTML = '<span class="text-warning"><i class="bi bi-hourglass-split"></i> Calculando...</span>';

                try {
                    const formData = new FormData();
                    formData.append('accommodation_unit_id', unitId);
                    formData.append('check_in_date', checkIn);
                    formData.append('check_out_date', checkOut);
                    formData.append('num_guests', numGuests);
                    formData.append('rate_plan_id', ratePlanId);
                    formData.append('promo_code', promoCode);

                    // Token CSRF de CodeIgniter 4
                    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                    const response = await fetch('<?= base_url('reservations/calculate-price') ?>', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        totalPriceInput.value = data.total_price;

                        let helperText = `Noches: ${data.nights} | Precio Base: $${data.original_price}`;
                        if (data.promo_applied) {
                            helperText += ` <br><span class="text-success fw-bold"><i class="bi bi-tag-fill"></i> ¡Cupón aplicado! Ahorro: $${data.discount_amount}</span>`;
                        }
                        priceHelper.innerHTML = helperText;
                        logDebug("Cálculo exitoso: $" + data.total_price);
                    } else {
                        priceHelper.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> ${data.message}</span>`;
                        logDebug("Cálculo fallido: " + data.message);
                    }
                } catch (error) {
                    console.error('Error calculando precio:', error);
                    priceHelper.innerHTML = '<span class="text-danger">Error de conexión al calcular.</span>';
                } finally {
                    totalPriceInput.classList.remove('bg-warning', 'text-dark');
                }
            }

            // 3. LISTENERS DE EVENTOS

            // Para el input de 'Total Personas': Genera campos HTML y recalcula precio
            numGuestsInput.addEventListener('change', function() {
                generateGuestFields();
                calculatePrice();
            });
            numGuestsInput.addEventListener('blur', generateGuestFields);

            // Para el cupón de descuento: Calcular solo al salir del campo (blur) para evitar llamados en cada tecla
            promoCodeInput.addEventListener('blur', calculatePrice);

            // Para el resto de inputs (unidad, fechas, plan tarifario): Solo calculan precio
            triggerInputs.forEach(input => {
                if(input.id !== 'num_guests') { // Ya le asignamos su listener arriba
                    input.addEventListener('change', calculatePrice);
                }
            });

            // 4. VALIDACIÓN DE BOOTSTRAP
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

            // Utilidad de Log
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