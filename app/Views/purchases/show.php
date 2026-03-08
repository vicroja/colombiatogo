<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

<?php
// Colores para el estado de la compra
$badge = 'bg-secondary';
if($purchase['status'] == 'paid') $badge = 'bg-success';
if($purchase['status'] == 'partial') $badge = 'bg-warning text-dark';
if($purchase['status'] == 'pending') $badge = 'bg-danger';

// Cálculo de saldo pendiente
$balance = $purchase['total'] - $purchase['amount_paid'];
?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= base_url('/purchases') ?>" class="btn btn-sm btn-outline-secondary me-2">&larr; Volver</a>
            <h2 class="d-inline-block mb-0">Detalle de Compra #<?= str_pad($purchase['id'], 5, '0', STR_PAD_LEFT) ?></h2>
        </div>
        <span class="badge <?= $badge ?> fs-6 text-uppercase px-3 py-2"><?= esc($purchase['status']) ?></span>
    </div>

    <div class="row">
        <div class="col-md-8">

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <small class="text-muted fw-bold d-block">Proveedor</small>
                            <span class="fs-5 fw-bold text-dark"><?= esc($purchase['supplier_name'] ?? 'No asignado') ?></span>
                            <br><small class="text-muted">NIT: <?= esc($purchase['tax_id'] ?? '-') ?></small>
                        </div>
                        <div class="col-sm-4 border-start">
                            <small class="text-muted fw-bold d-block">Factura / Referencia</small>
                            <span class="fs-5"><?= esc($purchase['reference_number'] ?: 'Sin Referencia') ?></span>
                        </div>
                        <div class="col-sm-4 border-start">
                            <small class="text-muted fw-bold d-block">Fecha de Compra</small>
                            <span class="fs-5"><?= date('d/m/Y', strtotime($purchase['purchase_date'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4 border-top border-primary border-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-box-seam"></i> Detalle de Productos/Servicios</h5>
                </div>

                <?php if($purchase['status'] != 'paid'): ?>
                    <div class="card-body bg-light border-bottom">
                        <form action="<?= base_url('/purchases/add-item/'.$purchase['id']) ?>" method="post" class="row gx-2 align-items-end">
                            <?= csrf_field() ?>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Producto del Catálogo</label>
                                <select name="product_id" class="form-select form-select-sm">
                                    <option value="">(Ítem Manual / Gasto libre)</option>
                                    <?php foreach($products as $prod): ?>
                                        <option value="<?= $prod['id'] ?>"><?= esc($prod['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Descripción (Si es manual)</label>
                                <input type="text" name="description" class="form-control form-control-sm" placeholder="Ej. Reparación tubo">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Cantidad</label>
                                <input type="number" step="0.01" name="quantity" class="form-control form-control-sm" value="1" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Costo Unit.</label>
                                <input type="number" step="0.01" name="unit_cost" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-1 text-end">
                                <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold">+</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light small">
                        <tr>
                            <th>Descripción</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-end">Costo Unit.</th>
                            <th class="text-end">Impuesto</th>
                            <th class="text-end">Subtotal</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(empty($items)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No has agregado ítems a esta factura.</td></tr>
                        <?php else: ?>
                            <?php foreach($items as $item): ?>
                                <tr>
                                    <td><?= esc($item['description']) ?></td>
                                    <td class="text-center"><?= floatval($item['quantity']) ?></td>
                                    <td class="text-end">$<?= number_format($item['unit_cost'], 2) ?></td>
                                    <td class="text-end text-muted small">$<?= number_format($item['tax_amount'], 2) ?></td>
                                    <td class="text-end fw-bold">$<?= number_format($item['subtotal'], 2) ?></td>
                                    <td class="text-end">
                                        <?php if($purchase['status'] != 'paid'): ?>
                                            <a href="<?= base_url('/purchases/delete-item/'.$purchase['id'].'/'.$item['id']) ?>" class="text-danger" onclick="return confirm('¿Eliminar ítem?');"><i class="bi bi-trash"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">

            <div class="card shadow-sm border-0 mb-4 bg-dark text-white">
                <div class="card-body">
                    <h5 class="fw-bold mb-3 border-bottom border-secondary pb-2">Resumen de Compra</h5>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-light">Subtotal:</span>
                        <span>$<?= number_format($purchase['subtotal'], 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small border-bottom border-secondary pb-2">
                        <span class="text-light">Impuestos:</span>
                        <span>$<?= number_format($purchase['tax_amount'], 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 mt-2">
                        <span class="fs-5 text-light">Gran Total:</span>
                        <span class="fs-5 fw-bold">$<?= number_format($purchase['total'], 2) ?></span>
                    </div>

                    <div class="d-flex justify-content-between mb-2 small text-success">
                        <span>Pagado (Abonos):</span>
                        <span>-$<?= number_format($purchase['amount_paid'], 2) ?></span>
                    </div>

                    <div class="d-flex justify-content-between mt-3 pt-3 border-top border-secondary">
                        <span class="fs-5 text-warning fw-bold">Saldo Pendiente:</span>
                        <span class="fs-4 fw-bold text-warning">$<?= number_format($balance, 2) ?></span>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 border-top border-success border-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-success"><i class="bi bi-wallet2"></i> Registro de Pagos</h6>
                </div>

                <?php if($balance > 0 && !empty($items)): ?>
                    <div class="card-body bg-light border-bottom p-2">
                        <form action="<?= base_url('/purchases/add-payment/'.$purchase['id']) ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="row gx-1 mb-2">
                                <div class="col-6">
                                    <label class="form-label small mb-0 text-muted">Monto</label>
                                    <input type="number" step="0.01" name="amount" class="form-control form-control-sm" value="<?= $balance ?>" max="<?= $balance ?>" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small mb-0 text-muted">Método</label>
                                    <select name="payment_method" class="form-select form-select-sm" required>
                                        <option value="cash">Efectivo (Caja)</option>
                                        <option value="transfer">Transferencia</option>
                                        <option value="card">Tarjeta</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row gx-1 mb-2">
                                <div class="col-6">
                                    <label class="form-label small mb-0 text-muted">Fecha</label>
                                    <input type="date" name="payment_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small mb-0 text-muted">Referencia</label>
                                    <input type="text" name="reference" class="form-control form-control-sm" placeholder="Opcional">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-sm btn-success w-100 fw-bold mt-1">Registrar Egreso</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        <?php if(empty($payments)): ?>
                            <li class="list-group-item text-center text-muted py-3">Sin pagos registrados.</li>
                        <?php else: ?>
                            <?php foreach($payments as $pay): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="text-success">$<?= number_format($pay['amount'], 2) ?></strong><br>
                                        <span class="text-muted" style="font-size: 0.75rem;">
                                        <?= date('d/m/y', strtotime($pay['payment_date'])) ?> | <?= strtoupper($pay['payment_method']) ?>
                                    </span>
                                    </div>
                                    <?php if($pay['reference']): ?>
                                        <span class="badge bg-light text-dark border">Ref: <?= esc($pay['reference']) ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

        </div>
    </div>

<?= $this->endSection() ?>