<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= base_url('/reservations') ?>" class="btn btn-sm btn-outline-secondary me-2">&larr; Volver</a>
            <h2 class="d-inline-block mb-0">Folio de Reserva #<?= $reservation['id'] ?></h2>
        </div>
        <?php
        $badges = [
            'pending' => 'bg-warning text-dark', 'confirmed' => 'bg-info text-dark',
            'checked_in' => 'bg-success', 'checked_out' => 'bg-secondary', 'cancelled' => 'bg-danger'
        ];
        ?>
        <span class="badge <?= $badges[$reservation['status']] ?> fs-5"><?= strtoupper($reservation['status']) ?></span>
    </div>

    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Detalles de la Estancia</div>
                <div class="card-body">
                    <p class="mb-1"><strong>Huésped:</strong> <?= esc($reservation['full_name']) ?> (Doc: <?= esc($reservation['document']) ?>)</p>
                    <p class="mb-1"><strong>Habitación:</strong> <?= esc($reservation['unit_name']) ?></p>
                    <p class="mb-1"><strong>Check-in:</strong> <?= date('d/m/Y', strtotime($reservation['check_in_date'])) ?></p>
                    <p class="mb-0"><strong>Check-out:</strong> <?= date('d/m/Y', strtotime($reservation['check_out_date'])) ?></p>
                </div>
            </div>

            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white fw-bold">Acciones (Máquina de Estados)</div>
                <div class="card-body">
                    <form action="<?= base_url('/reservations/update-status/'.$reservation['id']) ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="input-group">
                            <select name="new_status" class="form-select" required>
                                <option value="">Cambiar estado a...</option>
                                <?php if($reservation['status'] == 'pending'): ?>
                                    <option value="confirmed">Confirmar Reserva</option>
                                    <option value="cancelled">Cancelar</option>
                                <?php elseif($reservation['status'] == 'confirmed'): ?>
                                    <option value="checked_in">Realizar Check-in (Entró)</option>
                                    <option value="cancelled">Cancelar</option>
                                <?php elseif($reservation['status'] == 'checked_in'): ?>
                                    <option value="checked_out">Realizar Check-out (Salió)</option>
                                <?php endif; ?>
                            </select>
                            <button type="submit" class="btn btn-primary" <?= in_array($reservation['status'], ['checked_out', 'cancelled']) ? 'disabled' : '' ?>>Aplicar</button>
                        </div>
                        <?php if($reservation['status'] == 'checked_in' && $balance > 0): ?>
                            <small class="text-danger mt-2 d-block fw-bold"><i class="bi bi-exclamation-triangle"></i> Cuidado: El huésped tiene saldo pendiente antes del Check-out.</small>
                        <?php endif; ?>
                    </form>
                </div>
            </div>


            <?php if(!in_array($reservation['status'], ['cancelled', 'checked_out'])): ?>
                <div class="card shadow-sm border-warning mt-4">
                    <div class="card-header bg-warning text-dark fw-bold">
                        <i class="bi bi-cart-plus"></i> Cargar Consumo a la Habitación
                    </div>
                    <div class="card-body bg-light">
                        <form action="<?= base_url('/reservations/add-consumption/'.$reservation['id']) ?>" method="post" class="row gx-2 align-items-end">
                            <?= csrf_field() ?>
                            <div class="col-md-7">
                                <?php if(!in_array($reservation['status'], ['cancelled', 'checked_out'])): ?>
                                    <div class="d-grid mb-4">
                                        <a href="<?= base_url('/reservations/closure/'.$reservation['id']) ?>" class="btn btn-dark btn-lg fw-bold shadow-sm">
                                            <i class="bi bi-receipt"></i> Cerrar Cuenta y Check-out &rarr;
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <label class="form-label small">Producto / Servicio</label>
                                <select name="product_id" class="form-select form-select-sm" required>
                                    <option value="">Seleccionar...</option>
                                    <?php foreach($products as $prod): ?>
                                        <option value="<?= $prod['id'] ?>"><?= esc($prod['name']) ?> ($<?= number_format($prod['unit_price'], 2) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Cant.</label>
                                <input type="number" name="quantity" class="form-control form-control-sm" value="1" min="1" required>
                            </div>
                            <div class="col-md-2 text-end">
                                <button type="submit" class="btn btn-sm btn-warning w-100 fw-bold">+</button>
                            </div>
                        </form>
                    </div>

                    <div class="card-body p-0 border-top bg-white">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                            <tr>
                                <th class="small">Ítem</th>
                                <th class="small text-center">Cant</th>
                                <th class="small text-end">Subtotal</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(empty($consumptions)): ?>
                                <tr><td colspan="4" class="text-center py-2 text-muted small">Sin consumos extra.</td></tr>
                            <?php else: ?>
                                <?php foreach($consumptions as $c): ?>
                                    <tr>
                                        <td class="small"><?= esc($c['description']) ?></td>
                                        <td class="small text-center"><?= $c['quantity'] ?></td>
                                        <td class="small text-end">$<?= number_format($c['subtotal'], 2) ?></td>
                                        <td class="text-end">
                                            <a href="<?= base_url('/reservations/delete-consumption/'.$reservation['id'].'/'.$c['id']) ?>" class="text-danger text-decoration-none" onclick="return confirm('¿Borrar consumo?');">&times;</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>


        </div>

        <div class="col-md-7">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center d-flex justify-content-around align-items-center">
                    <div>
                        <h6 class="text-muted mb-0 small">Alojamiento</h6>
                        <h5 class="mb-0">$<?= number_format($reservation['total_price'], 2) ?></h5>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0 small">Extras (POS)</h6>
                        <h5 class="mb-0 text-warning">$<?= number_format($totalConsumptions, 2) ?></h5>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0 small">Total Pagado</h6>
                        <h5 class="mb-0 text-success">$<?= number_format($totalPaid, 2) ?></h5>
                    </div>
                    <div class="bg-light p-2 rounded border">
                        <h6 class="text-dark fw-bold mb-0">Saldo Pendiente</h6>
                        <h4 class="mb-0 <?= $balance > 0 ? 'text-danger' : 'text-secondary' ?> fw-bold">$<?= number_format($balance, 2) ?></h4>
                    </div>
                </div>
            </div>

            <?php if ($balance > 0 && !in_array($reservation['status'], ['cancelled', 'checked_out'])): ?>
                <div class="card shadow-sm mb-4 border-success">
                    <div class="card-header bg-success text-white fw-bold">Registrar Nuevo Abono</div>
                    <div class="card-body">
                        <form action="<?= base_url('/reservations/add-payment/'.$reservation['id']) ?>" method="post" class="row gx-2">
                            <?= csrf_field() ?>
                            <div class="col-md-4">
                                <label class="form-label small">Monto</label>
                                <input type="number" step="0.01" max="<?= $balance ?>" name="amount" class="form-control" value="<?= $balance ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Método</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="cash">Efectivo</option>
                                    <option value="credit_card">Tarjeta</option>
                                    <option value="bank_transfer">Transferencia</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Referencia (Opcional)</label>
                                <input type="text" name="reference" class="form-control" placeholder="# Transacción">
                            </div>
                            <div class="col-12 mt-3 text-end">
                                <button type="submit" class="btn btn-success">Procesar Pago</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">Historial de Pagos</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Método</th>
                            <th>Referencia</th>
                            <th class="text-end">Monto</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(empty($payments)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">Aún no hay pagos registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach($payments as $p): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                                    <td><?= strtoupper($p['payment_method']) ?></td>
                                    <td><?= esc($p['reference']) ?: '-' ?></td>
                                    <td class="text-end text-success fw-bold">$<?= number_format($p['amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>


