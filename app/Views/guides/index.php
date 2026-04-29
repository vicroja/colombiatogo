<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Guías Turísticos</h2>
        <div class="d-flex gap-2">
            <a href="<?= base_url('/guides/payments') ?>" class="btn btn-outline-warning">
                <i class="bi bi-cash-coin"></i> Pagos Pendientes
            </a>
            <a href="<?= base_url('/guides/create') ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nuevo Guía
            </a>
        </div>
    </div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<?php
$modelLabels = [
    'fixed_per_tour' => ['label' => 'Fijo por salida',   'color' => 'primary'],
    'per_pax'        => ['label' => 'Por pasajero',       'color' => 'info'],
    'commission_pct' => ['label' => 'Comisión %',         'color' => 'success'],
    'mixed'          => ['label' => 'Mixto',              'color' => 'warning'],
    'salary'         => ['label' => 'Salario fijo',       'color' => 'secondary'],
];
?>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Contacto</th>
                    <th>Especialidad</th>
                    <th>Modelo de Pago</th>
                    <th class="text-center">Tours</th>
                    <th class="text-end">Pendiente</th>
                    <th class="text-center">Estado</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($guides)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No hay guías registrados.</td></tr>
                <?php else: ?>
                    <?php foreach ($guides as $g): ?>
                        <?php $model = $modelLabels[$g['payment_model']] ?? ['label' => $g['payment_model'], 'color' => 'secondary']; ?>
                        <tr>
                            <td class="fw-bold"><?= esc($g['name']) ?></td>
                            <td>
                                <?php if ($g['phone']): ?>
                                    <a href="tel:<?= esc($g['phone']) ?>" class="text-decoration-none">
                                        <i class="bi bi-telephone text-muted"></i> <?= esc($g['phone']) ?>
                                    </a>
                                <?php else: ?> — <?php endif; ?>
                            </td>
                            <td><?= esc($g['specialty'] ?? '—') ?></td>
                            <td>
                                <span class="badge bg-<?= $model['color'] ?>">
                                    <?= $model['label'] ?>
                                </span>
                            </td>
                            <td class="text-center"><?= $g['total_tours'] ?></td>
                            <td class="text-end <?= $g['pending_payment'] > 0 ? 'text-warning fw-bold' : 'text-muted' ?>">
                                $<?= number_format($g['pending_payment'], 2) ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $g['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $g['is_active'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="<?= base_url("/guides/{$g['id']}/history") ?>"
                                       class="btn btn-sm btn-outline-secondary" title="Historial">
                                        <i class="bi bi-clock-history"></i>
                                    </a>
                                    <a href="<?= base_url("/guides/{$g['id']}/payments") ?>"
                                       class="btn btn-sm btn-outline-warning" title="Pagos">
                                        <i class="bi bi-cash-coin"></i>
                                    </a>
                                    <a href="<?= base_url("/guides/{$g['id']}/edit") ?>"
                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="<?= base_url("/guides/{$g['id']}/toggle-status") ?>"
                                          method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit"
                                                class="btn btn-sm <?= $g['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                                                title="<?= $g['is_active'] ? 'Desactivar' : 'Activar' ?>"
                                                onclick="return confirm('¿Confirmas el cambio de estado?')">
                                            <i class="bi bi-<?= $g['is_active'] ? 'pause' : 'play' ?>-circle"></i>
                                        </button>
                                    </form>
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