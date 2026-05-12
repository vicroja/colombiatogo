<?= $this->extend('super/layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Liquidación de comisiones</h4>
        <small class="text-muted">Resumen consolidado por vendedor</small>
    </div>
    <a href="/super/leads/commissions/detail" class="btn btn-sm btn-outline-primary">
        Ver detalle completo →
    </a>
</div>

<?php if (session('success')): ?>
    <div class="alert alert-success"><?= esc(session('success')) ?></div>
<?php endif; ?>
<?php if (session('error')): ?>
    <div class="alert alert-danger"><?= esc(session('error')) ?></div>
<?php endif; ?>

<?php
    $totalPending = array_sum(array_column($summary, 'total_pending'));
    $totalApproved = array_sum(array_column($summary, 'total_approved'));
    $totalPaid = array_sum(array_column($summary, 'total_paid'));
?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-warning">
            <div class="card-body">
                <small class="text-muted text-uppercase">Pendientes de aprobar</small>
                <h3 class="text-warning mb-0">$<?= number_format($totalPending, 0, ',', '.') ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-info">
            <div class="card-body">
                <small class="text-muted text-uppercase">Aprobadas (por pagar)</small>
                <h3 class="text-info mb-0">$<?= number_format($totalApproved, 0, ',', '.') ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body">
                <small class="text-muted text-uppercase">Pagadas históricas</small>
                <h3 class="text-success mb-0">$<?= number_format($totalPaid, 0, ',', '.') ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white"><strong>Resumen por persona</strong></div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Vendedor / Gerente</th>
                    <th class="text-end">Pendientes</th>
                    <th class="text-end">Aprobadas</th>
                    <th class="text-end">Pagadas</th>
                    <th class="text-center">Registros</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($summary)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">
                        Aún no hay comisiones generadas. Se crean automáticamente al ganar un lead.
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($summary as $row): ?>
                        <tr>
                            <td>
                                <strong><?= esc($row['name']) ?></strong><br>
                                <small class="text-muted"><?= esc($row['email']) ?></small>
                            </td>
                            <td class="text-end">
                                <?php if ($row['total_pending'] > 0): ?>
                                    <strong class="text-warning">$<?= number_format($row['total_pending'], 0, ',', '.') ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($row['total_approved'] > 0): ?>
                                    <strong class="text-info">$<?= number_format($row['total_approved'], 0, ',', '.') ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($row['total_paid'] > 0): ?>
                                    <span class="text-success">$<?= number_format($row['total_paid'], 0, ',', '.') ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= $row['total_records'] ?></td>
                            <td class="text-center">
                                <a href="/super/leads/commissions/detail?user_id=<?= $row['id'] ?>"
                                   class="btn btn-sm btn-outline-secondary">Ver</a>

                                <?php if ($row['total_pending'] > 0): ?>
                                    <form method="post" action="/super/leads/commissions/approve-all"
                                          style="display:inline" onsubmit="return confirm('¿Aprobar TODAS las pendientes de <?= esc($row['name']) ?>?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                        <button class="btn btn-sm btn-warning">Aprobar todo</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
