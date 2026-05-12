<?= $this->extend('sales/layout/main') ?>

<?= $this->section('content') ?>
<h4 class="mb-3">Mis comisiones</h4>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body">
                <small class="text-muted text-uppercase">Pendientes</small>
                <h4 class="text-warning mb-0">$<?= number_format($earnings['pending'], 0, ',', '.') ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body">
                <small class="text-muted text-uppercase">Aprobadas</small>
                <h4 class="text-info mb-0">$<?= number_format($earnings['approved'], 0, ',', '.') ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body">
                <small class="text-muted text-uppercase">Pagadas</small>
                <h4 class="text-success mb-0">$<?= number_format($earnings['paid'], 0, ',', '.') ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-secondary">
            <div class="card-body">
                <small class="text-muted text-uppercase">Total histórico</small>
                <h4 class="text-secondary mb-0">$<?= number_format($earnings['total'], 0, ',', '.') ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white"><strong>Historial</strong></div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Lead</th>
                    <th class="text-end">Base venta</th>
                    <th class="text-center">%</th>
                    <th class="text-end">Comisión</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($commissions)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">
                        Aún no tienes comisiones generadas. Ganas tu primera al cerrar un lead.
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($commissions as $c): ?>
                        <tr>
                            <td><small><?= date('d/m/Y', strtotime($c['earned_at'])) ?></small></td>
                            <td>
                                <?php if ($c['type'] === 'direct'): ?>
                                    <span class="badge bg-primary">Directa</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"
                                          title="Override por venta de <?= esc($c['source_name']) ?>">
                                        Override
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= esc($c['property_name']) ?></strong>
                                <?php if ($c['type'] === 'override' && !empty($c['source_name'])): ?>
                                    <br><small class="text-muted">Por venta de <?= esc($c['source_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-end"><small>$<?= number_format($c['base_amount'], 0, ',', '.') ?></small></td>
                            <td class="text-center"><small><?= number_format($c['rate'], 1) ?>%</small></td>
                            <td class="text-end"><strong>$<?= number_format($c['amount'], 0, ',', '.') ?></strong></td>
                            <td class="text-center">
                                <?php
                                    $badges = [
                                        'pending'   => '<span class="badge bg-warning text-dark">Pendiente</span>',
                                        'approved'  => '<span class="badge bg-info">Aprobada</span>',
                                        'paid'      => '<span class="badge bg-success">Pagada</span>',
                                        'cancelled' => '<span class="badge bg-secondary">Cancelada</span>',
                                    ];
                                    echo $badges[$c['status']];
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
