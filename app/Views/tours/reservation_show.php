<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= base_url('/tours') ?>" class="btn btn-sm btn-outline-secondary me-2">&larr; Volver</a>
            <h2 class="d-inline-block mb-0">Reserva Tour #<?= $reservation['id'] ?></h2>
        </div>
        <?php
        $badges = [
            'pending'   => 'bg-warning text-dark',
            'confirmed' => 'bg-info text-dark',
            'completed' => 'bg-success',
            'no_show'   => 'bg-dark',
            'cancelled' => 'bg-danger',
            'refunded'  => 'bg-secondary',
        ];
        ?>
        <span class="badge <?= $badges[$reservation['status']] ?> fs-5">
        <?= strtoupper($reservation['status']) ?>
    </span>
    </div>

    <div class="row">

        <!-- Columna izquierda -->
        <div class="col-md-5 mb-4">

            <!-- Detalles -->
            <div class="card shadow-sm mb-3">
                <div class="card-header fw-bold">Detalles de la Reserva</div>
                <div class="card-body">
                    <p class="mb-1"><strong>Huésped:</strong> <?= esc($reservation['full_name']) ?></p>
                    <p class="mb-1"><strong>Documento:</strong> <?= esc($reservation['guest_document'] ?? '—') ?></p>
                    <p class="mb-1"><strong>Teléfono:</strong> <?= esc($reservation['guest_phone'] ?? '—') ?></p>
                    <p class="mb-1"><strong>Tour:</strong> <?= esc($reservation['tour_name']) ?></p>
                    <p class="mb-1"><strong>Salida:</strong> <?= date('d/m/Y H:i', strtotime($reservation['start_datetime'])) ?></p>
                    <p class="mb-1"><strong>Punto de encuentro:</strong> <?= esc($reservation['meeting_point'] ?? '—') ?></p>
                    <p class="mb-1"><strong>Recogida:</strong> <?= esc($reservation['pickup_location'] ?? 'Igual al punto de encuentro') ?></p>
                    <p class="mb-1"><strong>Adultos:</strong> <?= $reservation['num_adults'] ?></p>
                    <p class="mb-1"><strong>Niños:</strong> <?= $reservation['num_children'] ?></p>
                    <p class="mb-0"><strong>Guía:</strong> <?= esc($reservation['guide_name'] ?? 'Sin asignar') ?>
                        <?php if ($reservation['guide_phone']): ?>
                            <small class="text-muted">(<?= esc($reservation['guide_phone']) ?>)</small>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Desglose de precio (del snapshot) -->
            <?php if (!empty($priceSnapshot)): ?>
                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-bold">Desglose de Precio</div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td class="text-muted">Adultos</td>
                                <td class="text-end">
                                    <?= $priceSnapshot['num_adults'] ?> × $<?= number_format($priceSnapshot['price_adult'], 2) ?>
                                    = <strong>$<?= number_format($priceSnapshot['total_adults'], 2) ?></strong>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Niños</td>
                                <td class="text-end">
                                    <?= $priceSnapshot['num_children'] ?> × $<?= number_format($priceSnapshot['price_child'], 2) ?>
                                    = <strong>$<?= number_format($priceSnapshot['total_children'], 2) ?></strong>
                                </td>
                            </tr>
                            <?php if ($priceSnapshot['applied_season']): ?>
                                <tr>
                                    <td colspan="2">
                                        <small class="text-info">
                                            <i class="bi bi-calendar-event"></i>
                                            Temporada aplicada: <?= esc($priceSnapshot['applied_season']) ?>
                                        </small>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <tr class="fw-bold border-top">
                                <td>Total</td>
                                <td class="text-end fs-5">$<?= number_format($priceSnapshot['total_price'], 2) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Máquina de estados -->
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white fw-bold">Cambiar Estado</div>
                <div class="card-body">
                    <form action="<?= base_url('/tours/reservation/'.$reservation['id'].'/status') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="input-group">
                            <select name="new_status" class="form-select" required>
                                <option value="">Cambiar estado a...</option>
                                <?php if ($reservation['status'] === 'pending'): ?>
                                    <option value="confirmed">Confirmar</option>
                                    <option value="cancelled">Cancelar</option>
                                <?php elseif ($reservation['status'] === 'confirmed'): ?>
                                    <option value="completed">Completado</option>
                                    <option value="no_show">No Show</option>
                                    <option value="cancelled">Cancelar</option>
                                <?php endif; ?>
                            </select>
                            <button type="submit" class="btn btn-primary"
                                <?= in_array($reservation['status'], ['completed', 'no_show', 'cancelled', 'refunded']) ? 'disabled' : '' ?>>
                                Aplicar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        <!-- Columna derecha: Pagos -->
        <div class="col-md-7">

            <!-- Resumen financiero -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center d-flex justify-content-around align-items-center">
                    <div>
                        <h6 class="text-muted small mb-0">Total Tour</h6>
                        <h5 class="mb-0">$<?= number_format($reservation['total_price'], 2) ?></h5>
                    </div>
                    <div>
                        <h6 class="text-muted small mb-0">Total Pagado</h6>
                        <h5 class="mb-0 text-success">$<?= number_format($totalPaid, 2) ?></h5>
                    </div>
                    <div class="bg-light p-2 rounded border">
                        <h6 class="text-dark fw-bold mb-0">Saldo Pendiente</h6>
                        <h4 class="mb-0 <?= $balance > 0 ? 'text-danger' : 'text-secondary' ?> fw-bold">
                            $<?= number_format($balance, 2) ?>
                        </h4>
                    </div>
                </div>
            </div>

            <!-- Registrar abono -->
            <?php if ($balance > 0 && !in_array($reservation['status'], ['cancelled', 'refunded', 'completed'])): ?>
                <div class="card shadow-sm mb-4 border-success">
                    <div class="card-header bg-success text-white fw-bold">Registrar Abono</div>
                    <div class="card-body">
                        <form action="<?= base_url('/tours/reservation/'.$reservation['id'].'/payment') ?>" method="post" class="row g-2">
                            <?= csrf_field() ?>
                            <div class="col-md-4">
                                <label class="form-label small">Monto</label>
                                <input type="number" step="0.01" name="amount" class="form-control" value="<?= $balance ?>" max="<?= $balance ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Método</label>
                                <select name="payment_method" class="form-select">
                                    <option value="cash">Efectivo</option>
                                    <option value="bank_transfer">Transferencia</option>
                                    <option value="credit_card">Tarjeta</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Referencia</label>
                                <input type="text" name="reference" class="form-control" placeholder="Opcional">
                            </div>
                            <div class="col-12 text-end mt-2">
                                <button type="submit" class="btn btn-success">Registrar Pago</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Historial de pagos -->
            <div class="card shadow-sm">
                <div class="card-header fw-bold">Historial de Pagos</div>
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
                        <?php if (empty($payments)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">Sin pagos registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                                    <td><?= strtoupper($p['payment_method']) ?></td>
                                    <td><?= esc($p['reference']) ?: '—' ?></td>
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