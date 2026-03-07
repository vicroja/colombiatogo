<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white pb-0 border-bottom-0">
                    <h4 class="mb-0 text-primary mt-2"><i class="bi bi-calendar-plus"></i> Registrar Nueva Reserva</h4>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('/reservations/store') ?>" method="post">
                        <?= csrf_field() ?>

                        <h5 class="text-secondary mt-2 border-bottom pb-2">Datos del Huésped Principal</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Nombre Completo</label>
                                <input type="text" name="guest_name" class="form-control" required placeholder="Ej. Juan Pérez">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Documento / Pasaporte</label>
                                <input type="text" name="guest_document" class="form-control" required placeholder="Ej. 1020304050">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Teléfono</label>
                                <input type="text" name="guest_phone" class="form-control" placeholder="Ej. +57 300 000 0000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted fw-bold">Correo Electrónico</label>
                                <input type="email" name="guest_email" class="form-control" placeholder="Ej. juan@correo.com">
                            </div>
                        </div>

                        <h5 class="text-secondary mt-4 border-bottom pb-2">Datos de la Estancia</h5>

                        <div class="row mb-3">
                            <div class="col-md-4 mb-3">
                                <label class="form-label small text-muted fw-bold">Canal / Fuente</label>
                                <select name="source_id" id="source_id" class="form-select border-primary" required>
                                    <option value="">Seleccione origen...</option>
                                    <?php if(!empty($sources)): ?>
                                        <?php foreach($sources as $s): ?>
                                            <option value="<?= $s['id'] ?>"><?= esc($s['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small text-muted fw-bold">Habitación Asignada</label>
                                <select name="unit_id" id="unit_id" class="form-select border-primary" required>
                                    <option value="">Seleccione habitación...</option>
                                    <?php if(!empty($units)): ?>
                                        <?php foreach($units as $u): ?>
                                            <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small text-muted fw-bold">Plan Tarifario</label>
                                <select name="rate_plan_id" id="rate_plan_id" class="form-select border-primary" required>
                                    <option value="">Seleccione plan...</option>
                                    <?php if(!empty($plans)): ?>
                                        <?php foreach($plans as $p): ?>
                                            <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3 align-items-end">
                            <div class="col-md-4 mb-3">
                                <label class="form-label small text-muted fw-bold">Fecha Llegada (Check-in)</label>
                                <input type="date" name="check_in" id="check_in" class="form-control bg-light" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small text-muted fw-bold">Fecha Salida (Check-out)</label>
                                <input type="date" name="check_out" id="check_out" class="form-control bg-light" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold text-success">Precio Total (Automático)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white border-success"><?= session('currency_symbol') ?: '$' ?></span>
                                    <input type="text" name="total_price" id="total_price" class="form-control fw-bold text-success border-success bg-white" required readonly placeholder="0.00">
                                </div>
                                <small id="nights_info" class="text-muted d-block mt-1"></small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-5 pt-3 border-top">
                            <a href="<?= base_url('/reservations') ?>" class="btn btn-outline-secondary px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-5 fw-bold text-white shadow-sm">Crear Reserva (Pendiente)</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Seleccionamos los elementos del DOM que afectan el precio
            const unitSelect = document.getElementById('unit_id');
            const planSelect = document.getElementById('rate_plan_id');
            const checkInInput = document.getElementById('check_in');
            const checkOutInput = document.getElementById('check_out');
            const priceInput = document.getElementById('total_price');
            const nightsInfo = document.getElementById('nights_info');

            // Función que llama al servidor para cotizar la estadía
            function fetchCalculatedPrice() {
                const unit_id = unitSelect.value;
                const rate_plan_id = planSelect.value;
                const check_in = checkInInput.value;
                const check_out = checkOutInput.value;

                // Solo calculamos si los 4 datos requeridos están llenos
                if(unit_id && rate_plan_id && check_in && check_out) {

                    // Validación básica frontend de fechas lógicas
                    if (new Date(check_in) >= new Date(check_out)) {
                        priceInput.value = '0.00';
                        nightsInfo.innerHTML = "<span class='text-danger fw-bold'>Fechas inválidas</span>";
                        return;
                    }

                    // Efecto visual de carga
                    priceInput.value = 'Calculando...';
                    nightsInfo.innerHTML = "<span class='text-info'>Consultando tarifas y temporadas...</span>";

                    // Construimos la URL con los parámetros GET hacia el endpoint
                    const url = `<?= base_url('/reservations/calculate-price') ?>?unit_id=${unit_id}&rate_plan_id=${rate_plan_id}&check_in=${check_in}&check_out=${check_out}`;

                    // Llamada invisible al backend (AJAX con Fetch API)
                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if(data.success) {
                                priceInput.value = data.total_price;
                                nightsInfo.innerHTML = `<span class='text-success fw-bold'>Por ${data.nights} noche(s)</span>`;
                            } else {
                                priceInput.value = '0.00';
                                nightsInfo.innerHTML = `<span class='text-danger'>${data.message}</span>`;
                            }
                        })
                        .catch(error => {
                            console.error('Error calculando el precio:', error);
                            priceInput.value = 'Error';
                            nightsInfo.innerHTML = "<span class='text-danger'>Fallo de conexión</span>";
                        });
                } else {
                    // Si falta algún dato, limpiamos el campo
                    priceInput.value = '';
                    nightsInfo.innerText = "";
                }
            }

            // Escuchamos cualquier cambio en los 4 campos y disparamos la función AJAX
            unitSelect.addEventListener('change', fetchCalculatedPrice);
            planSelect.addEventListener('change', fetchCalculatedPrice);
            checkInInput.addEventListener('change', fetchCalculatedPrice);
            checkOutInput.addEventListener('change', fetchCalculatedPrice);
        });
    </script>

<?= $this->endSection() ?>