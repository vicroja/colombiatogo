<?= $this->extend('super/layouts/main') ?>
<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <a href="<?= base_url('/super/billing') ?>" class="btn btn-sm btn-outline-secondary me-3">&larr; Volver</a>
        <div>
            <h2 class="mb-0"><?= esc($tenant['name']) ?></h2>
            <p class="text-muted mb-0">Historial de pagos SaaS</p>
        </div>
    </div>

    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">Total pagado</small>
                    <h3 class="mb-0 text-success">$<?= number_format($totalPaid, 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">Número de pagos</small>
                    <h3 class="mb-0"><?= count($payments) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">Cliente desde</small>
                    <h3 class="mb-0"><?= date('M Y', strtotime($tenant['created_at'])) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Plan</th>
                    <th>Monto</th>
                    <th>Método</th>
                    <th>Referencia</th>
                    <th>Periodo cubierto</th>
                    <th>Registrado por</th>
                    <th>Notas</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($payments)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">Sin pagos registrados.</td></tr>
                <?php else: ?>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                            <td>
                            <span class="badge" style="background-color: <?= esc($p['plan_color'] ?? '#666') ?>">
                                <?= esc($p['plan_name']) ?>
                            </span>
                            </td>
                            <td><strong>$<?= number_format($p['amount'], 2) ?></strong> <?= esc($p['currency']) ?></td>
                            <td><code><?= esc($p['payment_method']) ?></code></td>
                            <td><?= esc($p['reference'] ?: '—') ?></td>
                            <td>
                                <small><?= date('d/m/Y', strtotime($p['period_start'])) ?> →
                                    <?= date('d/m/Y', strtotime($p['period_end'])) ?></small>
                            </td>
                            <td><small><?= esc($p['admin_name'] ?: 'Sistema') ?></small></td>
                            <td><small class="text-muted"><?= esc($p['notes'] ?: '—') ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?= $this->endSection() ?>