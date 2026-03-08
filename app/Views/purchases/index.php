<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Registro de Compras y Gastos</h2>
        <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#purchaseModal">
            <i class="bi bi-cart-plus"></i> Nueva Compra
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th>Factura Ref.</th>
                    <th class="text-end">Total</th>
                    <th class="text-center">Estado</th>
                    <th class="text-end">Acción</th>
                </tr>
                </thead>
                <tbody>
                <?php if(empty($purchases)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No hay compras registradas.</td></tr>
                <?php else: ?>
                    <?php foreach($purchases as $p): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($p['purchase_date'])) ?></td>
                            <td class="fw-bold"><?= esc($p['supplier_name'] ?? 'Proveedor no asignado') ?></td>
                            <td><?= esc($p['reference_number'] ?: '-') ?></td>
                            <td class="text-end fw-bold"><?= session('currency_symbol') ?: '$' ?><?= number_format($p['total'], 2) ?></td>
                            <td class="text-center">
                                <?php
                                $badge = 'bg-secondary';
                                if($p['status'] == 'paid') $badge = 'bg-success';
                                if($p['status'] == 'partial') $badge = 'bg-warning text-dark';
                                if($p['status'] == 'pending') $badge = 'bg-danger';
                                ?>
                                <span class="badge <?= $badge ?> text-uppercase"><?= esc($p['status']) ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= base_url('/purchases/show/'.$p['id']) ?>" class="btn btn-sm btn-outline-primary">Ver Detalles</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="purchaseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form action="<?= base_url('/purchases/store') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-receipt"></i> Crear Factura de Compra</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Proveedor</label>
                            <select name="supplier_id" class="form-select" required>
                                <option value="">Seleccione proveedor...</option>
                                <?php foreach($suppliers as $sup): ?>
                                    <option value="<?= $sup['id'] ?>"><?= esc($sup['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Número de Factura</label>
                                <input type="text" name="reference_number" class="form-control" placeholder="Ej. FAC-1020">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Fecha de Compra</label>
                                <input type="date" name="purchase_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary fw-bold">Continuar &rarr;</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>