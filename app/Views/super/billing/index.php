<?= $this->extend('super/layouts/main') ?>
<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Control de Suscripciones</h2>
            <p class="text-muted mb-0">Gestiona los pagos de tus clientes</p>
        </div>
    </div>

    <!-- ===== KPIs ===== -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">MRR (últimos 30 días)</small>
                    <h3 class="mb-0 text-success">$<?= number_format($kpis['mrr'], 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">Total tenants</small>
                    <h3 class="mb-0"><?= $kpis['total_tenants'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">Vencen esta semana</small>
                    <h3 class="mb-0 text-warning"><?= $kpis['expiring_this_week'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">Suspendidos</small>
                    <h3 class="mb-0 text-danger"><?= $kpis['suspended'] ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                <tr>
                    <th>Establecimiento</th>
                    <th>Plan</th>
                    <th>Cuota</th>
                    <th>Fecha de Corte</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php if(empty($tenants)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No hay hoteles registrados aún.</td></tr>
                <?php else: ?>
                    <?php foreach($tenants as $t): ?>
                        <?php
                        $today    = strtotime(date('Y-m-d'));
                        $endDate  = strtotime($t['current_period_end']);
                        $daysLeft = round(($endDate - $today) / 86400);
                        $grace    = (int)$t['grace_period_days'];

                        $rowClass = '';
                        $statusBadge = '<span class="badge bg-success">Al Día</span>';

                        if ($t['is_suspended'] || $t['sub_status'] === 'suspended') {
                            $rowClass = 'table-danger';
                            $statusBadge = '<span class="badge bg-danger">Suspendido</span>';
                        } elseif ($t['sub_status'] === 'past_due' || $daysLeft < 0) {
                            $rowClass = 'table-warning';
                            $statusBadge = '<span class="badge bg-warning text-dark">En mora (gracia '.$grace.'d)</span>';
                        } elseif ($daysLeft <= 5) {
                            $statusBadge = '<span class="badge bg-info text-dark">Vence en '.$daysLeft.'d</span>';
                        } elseif ($t['sub_status'] === 'trial') {
                            $statusBadge = '<span class="badge bg-primary">Trial</span>';
                        }
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td><strong><?= esc($t['name']) ?></strong></td>
                            <td>
                            <span class="badge" style="background-color: <?= esc($t['color']) ?>">
                                <?= esc($t['plan_name']) ?>
                            </span>
                            </td>
                            <td>$<?= number_format($t['price'], 2) ?> <?= esc($t['currency']) ?></td>
                            <td class="fw-bold <?= $daysLeft < 0 ? 'text-danger' : '' ?>">
                                <?= date('d/m/Y', strtotime($t['current_period_end'])) ?>
                            </td>
                            <td><?= $statusBadge ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                            data-bs-target="#payModal-<?= $t['id'] ?>">
                                        Registrar Pago
                                    </button>
                                    <a href="<?= base_url('/super/billing/history/' . $t['id']) ?>"
                                       class="btn btn-outline-secondary">Historial</a>
                                </div>

                                <!-- Modal de registro de pago -->
                                <div class="modal fade" id="payModal-<?= $t['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="<?= base_url('/super/billing/renew/' . $t['id']) ?>" method="post">
                                                <?= csrf_field() ?>
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Pago de <?= esc($t['name']) ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <div class="mb-3">
                                                        <label class="form-label">Monto</label>
                                                        <input type="number" step="0.01" name="amount" class="form-control"
                                                               value="<?= $t['price'] ?>" required>
                                                        <small class="text-muted">Default: cuota del plan</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Método de pago</label>
                                                        <select name="payment_method" class="form-select">
                                                            <option value="transfer">Transferencia</option>
                                                            <option value="cash">Efectivo</option>
                                                            <option value="card">Tarjeta</option>
                                                            <option value="pse">PSE</option>
                                                            <option value="other">Otro</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Referencia</label>
                                                        <input type="text" name="reference" class="form-control"
                                                               placeholder="Nº comprobante, transacción...">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Notas</label>
                                                        <textarea name="notes" class="form-control" rows="2"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-success">Confirmar y Renovar (+1 mes)</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?= $this->endSection() ?>