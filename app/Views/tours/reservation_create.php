<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex align-items-center mb-4">
        <a href="<?= base_url("/tours/{$tour['id']}/schedules") ?>" class="btn btn-sm btn-outline-secondary me-3">&larr; Volver</a>
        <div>
            <h2 class="mb-0">Reservar Tour</h2>
            <small class="text-muted"><?= esc($tour['name']) ?></small>
        </div>
    </div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

    <form action="<?= base_url('/tours/reservation/store') ?>" method="post" id="tourResForm">
        <?= csrf_field() ?>
        <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">

        <div class="row g-4">

            <!-- Columna izquierda: Datos de la reserva -->
            <div class="col-md-7">

                <!-- Selección de salida -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-bold">1. Seleccionar Salida</div>
                    <div class="card-body">
                        <select name="schedule_id" id="schedule_id" class="form-select" required>
                            <option value="">Seleccione una salida...</option>
                            <?php
                            $preselected = $this->request->getGet('schedule_id');
                            foreach ($schedules as $s):
                                $available = (int)$s['max_pax'] - (int)$s['current_pax'];
                                $priceAd   = $s['price_adult_override'] ?? $tour['price_adult'];
                                $priceNi   = $s['price_child_override'] ?? $tour['price_child'];
                                $disabled  = $available === 0 ? 'disabled' : '';
                                $label     = date('d/m/Y H:i', strtotime($s['start_datetime']))
                                    . " — {$available} cupos — Ad $" . number_format($priceAd, 2)
                                    . " / Ni $" . number_format($priceNi, 2);
                                ?>
                                <option value="<?= $s['id'] ?>"
                                        data-price-adult="<?= $priceAd ?>"
                                        data-price-child="<?= $priceNi ?>"
                                        data-available="<?= $available ?>"
                                    <?= $disabled ?>
                                    <?= $preselected == $s['id'] ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Datos del huésped -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-bold">2. Huésped</div>
                    <div class="card-body row g-3">
                        <div class="col-12">
                            <label class="form-label">Huésped principal <span class="text-danger">*</span></label>
                            <select name="guest_id" class="form-select" required>
                                <option value="">Seleccionar huésped...</option>
                                <?php foreach ($guests as $g): ?>
                                    <option value="<?= $g['id'] ?>"><?= esc($g['full_name']) ?> — <?= esc($g['document'] ?? 'Sin doc') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Adultos <span class="text-danger">*</span></label>
                            <input type="number" name="num_adults" id="num_adults" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Niños</label>
                            <input type="number" name="num_children" id="num_children" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Punto de recogida <small class="text-muted">(si difiere del punto de encuentro)</small></label>
                            <input type="text" name="pickup_location" class="form-control" placeholder="<?= esc($tour['meeting_point'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Opcionales -->
                <div class="card shadow-sm">
                    <div class="card-header fw-bold">3. Opcionales</div>
                    <div class="card-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Reserva de hotel vinculada</label>
                            <input type="number" name="parent_reservation_id" class="form-control" placeholder="ID Reserva Hotel">
                            <small class="text-muted">El tour se cargará al folio del huésped.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Agente comisionista</label>
                            <select name="agent_id" class="form-select">
                                <option value="">Sin agente</option>
                                <?php
                                foreach ($agents as $agent):?>
                                    <option value="<?= $agent['id'] ?>">
                                        <?= esc($agent['name']) ?>
                                        (<?= $agent['commission_type'] === 'percentage' ? $agent['commission_value'].'%' : '$'.$agent['commission_value'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas internas</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Columna derecha: Panel de precio y pago -->
            <div class="col-md-5">
                <div class="card shadow-sm border-primary sticky-top" style="top: 80px">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-calculator"></i> Resumen de Precio
                    </div>
                    <div class="card-body">
                        <table class="table table-sm mb-3">
                            <tbody>
                            <tr>
                                <td class="text-muted">Adultos</td>
                                <td class="text-end" id="summary-adults">—</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Niños</td>
                                <td class="text-end" id="summary-children">—</td>
                            </tr>
                            <tr class="fw-bold border-top">
                                <td>Total</td>
                                <td class="text-end fs-5" id="summary-total">$0.00</td>
                            </tr>
                            </tbody>
                        </table>

                        <hr>
                        <h6 class="fw-bold">Abono Inicial</h6>
                        <div class="row g-2">
                            <div class="col-7">
                                <input type="number" step="0.01" name="initial_payment" id="initial_payment"
                                       class="form-control" placeholder="0.00" min="0">
                            </div>
                            <div class="col-5">
                                <select name="payment_method" class="form-select">
                                    <option value="cash">Efectivo</option>
                                    <option value="bank_transfer">Transferencia</option>
                                    <option value="credit_card">Tarjeta</option>
                                </select>
                            </div>
                        </div>
                        <small class="text-muted">Dejar en 0 para registrar sin pago inicial.</small>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100 fw-bold">
                            <i class="bi bi-check-circle"></i> Confirmar Reserva
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>

    <script>
        // Actualiza el panel de precio en tiempo real al cambiar adultos, niños o salida
        function updateSummary() {
            const scheduleSelect = document.getElementById('schedule_id');
            const selected = scheduleSelect.options[scheduleSelect.selectedIndex];

            const priceAdult  = parseFloat(selected.dataset.priceAdult  || 0);
            const priceChild  = parseFloat(selected.dataset.priceChild  || 0);
            const numAdults   = parseInt(document.getElementById('num_adults').value   || 0);
            const numChildren = parseInt(document.getElementById('num_children').value || 0);

            const totalAdults   = priceAdult  * numAdults;
            const totalChildren = priceChild  * numChildren;
            const total         = totalAdults + totalChildren;

            document.getElementById('summary-adults').textContent   = numAdults   + ' × $' + priceAdult.toFixed(2)  + ' = $' + totalAdults.toFixed(2);
            document.getElementById('summary-children').textContent = numChildren + ' × $' + priceChild.toFixed(2)  + ' = $' + totalChildren.toFixed(2);
            document.getElementById('summary-total').textContent    = '$' + total.toFixed(2);

            // Pre-llenar el campo de abono con el total
            document.getElementById('initial_payment').placeholder = total.toFixed(2);
        }

        document.getElementById('schedule_id').addEventListener('change', updateSummary);
        document.getElementById('num_adults').addEventListener('input',   updateSummary);
        document.getElementById('num_children').addEventListener('input', updateSummary);

        // Ejecutar al cargar si hay salida preseleccionada
        document.addEventListener('DOMContentLoaded', updateSummary);
    </script>

<?= $this->endSection() ?>