<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= base_url('/guides') ?>" class="btn btn-sm btn-outline-secondary me-2">&larr; Volver</a>
            <h2 class="d-inline-block mb-0">Pagos Pendientes a Guías</h2>
        </div>
        <span class="badge bg-warning text-dark fs-6"><?= count($payments) ?> pendientes</span>
    </div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<?php if (empty($payments)): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle"></i> No hay pagos pendientes. ¡Todo al día!
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                <tr>
                    <th>Guía</th>
                    <th>Tour / Salida</th>
                    <th>Modelo</th>
                    <th class="text-end">Monto</th>
                    <th>Generado</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($payments as $p): ?>
                    <tr>
                        <td>
                            <strong><?= esc($p['guide_name']) ?></strong><br>
                            <small class="text-muted"><?= esc($p['guide_phone'] ?? '') ?></small>
                        </td>
                        <td>
                            <?= esc($p['tour_name'] ?? '—') ?><br>
                            <small class="text-muted">
                                <?= $p['start_datetime']
                                    ? date('d/m/Y H:i', strtotime($p['start_datetime']))
                                    : '—' ?>
                            </small>
                        </td>
                        <td>
                                <span class="badge bg-secondary">
                                    <?= esc($p['payment_model_snapshot']) ?>
                                </span>
                        </td>
                        <td class="text-end fw-bold text-warning">
                            $<?= number_format($p['amount'], 2) ?>
                        </td>
                        <td>
                            <small><?= date('d/m/Y', strtotime($p['created_at'])) ?></small>
                        </td>
                        <td>
                            <!-- Modal trigger -->
                            <button class="btn btn-sm btn-success"
                                    data-bs-toggle="modal"
                                    data-bs-target="#payModal"
                                    data-payment-id="<?= $p['id'] ?>"
                                    data-guide="<?= esc($p['guide_name']) ?>"
                                    data-amount="<?= $p['amount'] ?>">
                                <i class="bi bi-cash"></i> Pagar
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="3" class="text-end">Total pendiente:</td>
                    <td class="text-end text-warning">
                        $<?= number_format(array_sum(array_column($payments, 'amount')), 2) ?>
                    </td>
                    <td colspan="2"></td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php endif; ?>

    <!-- Modal para registrar pago -->
    <div class="modal fade" id="payModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="payForm" method="post">
                    <?= csrf_field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Registrar Pago</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <p id="payModalDesc" class="text-muted small mb-0"></p>
                        <div class="col-md-6">
                            <label class="form-label">Monto a pagar</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="amount_display"
                                       id="payModalAmount" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha de pago</label>
                            <input type="date" name="payment_date" class="form-control"
                                   value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Método</label>
                            <select name="payment_method" class="form-select">
                                <option value="cash">Efectivo</option>
                                <option value="bank_transfer">Transferencia</option>
                                <option value="other">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Referencia</label>
                            <input type="text" name="reference" class="form-control" placeholder="Opcional">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Confirmar Pago</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Al abrir el modal, configurar el action del form con el ID del pago
        document.getElementById('payModal').addEventListener('show.bs.modal', function(e) {
            const btn      = e.relatedTarget;
            const id       = btn.dataset.paymentId;
            const guide    = btn.dataset.guide;
            const amount   = btn.dataset.amount;

            document.getElementById('payForm').action = `/guides/payment/${id}/mark-paid`;
            document.getElementById('payModalAmount').value = parseFloat(amount).toFixed(2);
            document.getElementById('payModalDesc').textContent = `Pago a: ${guide}`;
        });
    </script>

<?= $this->endSection() ?>