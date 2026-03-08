<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="row justify-content-center">
        <div class="col-md-9">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="<?= base_url('/reservations/show/'.$reservation['id']) ?>" class="btn btn-outline-secondary">&larr; Volver al Folio</a>

                <a href="<?= base_url('/reservations/invoice/'.$reservation['id']) ?>" target="_blank" class="btn btn-danger shadow-sm fw-bold">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Generar PDF
                </a>
            </div>

            <div class="card shadow border-0 mb-4">
                <div class="card-body p-5">
                    <div class="row mb-4 border-bottom pb-4">
                        <div class="col-sm-6">
                            <h2 class="text-primary fw-bold mb-1"><?= session('tenant_name') ?: 'Hotel' ?></h2>
                            <div class="text-muted small">Liquidación de Estadía</div>
                        </div>
                        <div class="col-sm-6 text-end">
                            <h5 class="fw-bold mb-1">Reserva #<?= str_pad($reservation['id'], 5, '0', STR_PAD_LEFT) ?></h5>
                            <div class="text-muted small">Fecha: <?= date('d/m/Y') ?></div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <div class="text-muted small mb-1">Cobrar a:</div>
                            <h6 class="fw-bold mb-0"><?= esc($reservation['full_name']) ?></h6>
                            <div class="small text-muted">Doc: <?= esc($reservation['document']) ?></div>
                        </div>
                        <div class="col-sm-6 text-end">
                            <div class="text-muted small mb-1">Detalles de Alojamiento:</div>
                            <h6 class="fw-bold mb-0"><?= esc($reservation['unit_name']) ?></h6>
                            <div class="small text-muted">
                                Llegada: <?= date('d/m/Y', strtotime($reservation['check_in_date'])) ?><br>
                                Salida: <?= date('d/m/Y', strtotime($reservation['check_out_date'])) ?>
                            </div>
                        </div>
                    </div>

                    <table class="table table-borderless mb-4">
                        <thead class="border-bottom border-top bg-light">
                        <tr>
                            <th class="py-2">Descripción</th>
                            <th class="text-center py-2">Cant.</th>
                            <th class="text-end py-2">Precio Unit.</th>
                            <th class="text-end py-2">Subtotal</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><strong>Tarifa de Alojamiento</strong></td>
                            <td class="text-center">1</td>
                            <td class="text-end">$<?= number_format($reservation['total_price'], 2) ?></td>
                            <td class="text-end fw-bold">$<?= number_format($reservation['total_price'], 2) ?></td>
                        </tr>
                        <?php foreach($consumptions as $c): ?>
                            <tr>
                                <td class="text-muted ps-4">&#8627; <?= esc($c['description']) ?></td>
                                <td class="text-center text-muted"><?= $c['quantity'] ?></td>
                                <td class="text-end text-muted">$<?= number_format($c['unit_price'], 2) ?></td>
                                <td class="text-end">$<?= number_format($c['subtotal'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot class="border-top">
                        <tr>
                            <td colspan="3" class="text-end text-muted pt-3">Gran Total:</td>
                            <td class="text-end pt-3 fw-bold fs-5">$<?= number_format($grandTotal, 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end text-muted pb-3 border-bottom">Pagos Realizados (Abonos):</td>
                            <td class="text-end pb-3 text-success border-bottom">-$<?= number_format($totalPaid, 2) ?></td>
                        </tr>
                        <tr class="bg-light">
                            <td colspan="3" class="text-end fw-bold py-3">SALDO A PAGAR:</td>
                            <td class="text-end fw-bold py-3 fs-4 <?= $balance > 0 ? 'text-danger' : 'text-success' ?>">
                                $<?= number_format($balance, 2) ?>
                            </td>
                        </tr>
                        </tfoot>
                    </table>

                    <?php if($balance > 0): ?>
                        <div class="alert alert-danger d-flex align-items-center mb-0">
                            <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
                            <div>
                                <h5 class="alert-heading mb-1 fw-bold">Saldo Pendiente</h5>
                                <p class="mb-0 small">El huésped debe pagar <strong>$<?= number_format($balance, 2) ?></strong> antes de poder realizar el Check-out. Registra el pago en la pantalla del Folio o aquí abajo.</p>
                            </div>
                        </div>

                        <div class="card border-danger mt-4 shadow-sm">
                            <div class="card-body bg-white">
                                <form action="<?= base_url('/reservations/add-payment/'.$reservation['id']) ?>" method="post" class="row gx-2 align-items-end">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="redirect_to" value="closure">
                                    <div class="col-md-3">
                                        <label class="form-label small">Monto Restante</label>
                                        <input type="number" step="0.01" name="amount" class="form-control" value="<?= $balance ?>" max="<?= $balance ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Método</label>
                                        <select name="payment_method" class="form-select" required>
                                            <option value="cash">Efectivo</option>
                                            <option value="credit_card">Tarjeta</option>
                                            <option value="bank_transfer">Transferencia</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Referencia</label>
                                        <input type="text" name="reference" class="form-control" placeholder="Opcional">
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <button type="submit" class="btn btn-success w-100 fw-bold">Pagar Saldo</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center mt-4">
                            <div class="mb-4">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                                <h4 class="text-success fw-bold mt-2">Cuenta en Cero</h4>
                                <p class="text-muted">La liquidación está completa. Ya puedes liberar la habitación.</p>
                            </div>
                            <form action="<?= base_url('/reservations/process-checkout/'.$reservation['id']) ?>" method="post">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm" onclick="return confirm('¿Confirmas que el huésped entregó la habitación?');">
                                    <i class="bi bi-door-open"></i> Procesar Check-Out Definitivo
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>
