<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Promociones y Cupones</h2>
        <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#promoModal">
            <i class="bi bi-tag-fill"></i> Nuevo Cupón
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Código</th>
                    <th>Campaña</th>
                    <th>Descuento</th>
                    <th>Validez</th>
                    <th>Usos</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if(empty($promotions)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No tienes promociones activas.</td></tr>
                <?php else: ?>
                    <?php foreach($promotions as $promo): ?>
                        <tr>
                            <td><span class="badge bg-dark fs-6 font-monospace"><?= esc($promo['code']) ?></span></td>
                            <td><?= esc($promo['name']) ?></td>
                            <td class="fw-bold text-success">
                                <?= $promo['discount_type'] == 'percentage' ? $promo['discount_value'].'%' : session('currency_symbol').number_format($promo['discount_value'], 2) ?>
                            </td>
                            <td class="small text-muted">
                                Del: <?= date('d/m/Y', strtotime($promo['valid_from'])) ?><br>
                                Al: <?= date('d/m/Y', strtotime($promo['valid_until'])) ?>
                            </td>
                            <td class="small">
                                <?= $promo['current_uses'] ?> / <?= $promo['max_uses'] > 0 ? $promo['max_uses'] : '&infin;' ?>
                            </td>
                            <td>
                                <?php
                                $today = date('Y-m-d');
                                $isActive = $promo['is_active'] && $today >= $promo['valid_from'] && $today <= $promo['valid_until'];
                                ?>
                                <?= $isActive ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo/Vencido</span>' ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= base_url('/promotions/delete/'.$promo['id']) ?>" class="text-danger" onclick="return confirm('¿Eliminar cupón?');"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="promoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form action="<?= base_url('/promotions/store') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-ticket-perforated"></i> Crear Cupón de Descuento</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Código del Cupón (Sin espacios)</label>
                            <input type="text" name="code" class="form-control font-monospace text-uppercase" placeholder="Ej. VERANO2026" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nombre de la Campaña</label>
                            <input type="text" name="name" class="form-control" placeholder="Ej. Promo Vacaciones Mitad de Año" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Tipo de Descuento</label>
                                <select name="discount_type" class="form-select" required>
                                    <option value="percentage">Porcentaje (%)</option>
                                    <option value="fixed">Monto Fijo ($)</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Valor</label>
                                <input type="number" step="0.01" name="discount_value" class="form-control" placeholder="Ej. 15" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Válido Desde</label>
                                <input type="date" name="valid_from" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Válido Hasta</label>
                                <input type="date" name="valid_until" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Límite de Usos (0 = Ilimitado)</label>
                            <input type="number" name="max_uses" class="form-control" value="0" required>
                            <small class="text-muted">¿Cuántas veces en total se puede usar este código?</small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary fw-bold">Guardar Cupón</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>