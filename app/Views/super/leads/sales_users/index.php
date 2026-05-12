<?= $this->extend('super/layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Equipo comercial</h4>
        <small class="text-muted"><?= count($users) ?> vendedores registrados</small>
    </div>
    <a href="/super/sales-users/create" class="btn btn-primary">+ Nuevo vendedor</a>
</div>

<?php if (session('success')): ?>
    <div class="alert alert-success"><?= esc(session('success')) ?></div>
<?php endif; ?>
<?php if (session('error')): ?>
    <div class="alert alert-danger"><?= esc(session('error')) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Reporta a</th>
                    <th class="text-center">Comisión</th>
                    <th class="text-center">Override</th>
                    <th class="text-center">Tope</th>
                    <th class="text-center">Inbound</th>
                    <th class="text-center">Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="10" class="text-center text-muted py-4">
                        No hay vendedores. <a href="/super/sales-users/create">Crea el primero</a>.
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr class="<?= $u['is_active'] ? '' : 'text-muted' ?>">
                            <td><strong><?= esc($u['name']) ?></strong></td>
                            <td><small><?= esc($u['email']) ?></small></td>
                            <td>
                                <?php if ($u['role'] === 'manager'): ?>
                                    <span class="badge bg-primary">Gerente</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Vendedor</span>
                                <?php endif; ?>
                            </td>
                            <td><small><?= esc($u['manager_name'] ?? '—') ?></small></td>
                            <td class="text-center"><?= number_format($u['commission_rate'], 1) ?>%</td>
                            <td class="text-center">
                                <?php if ($u['role'] === 'manager' && $u['override_rate'] > 0): ?>
                                    <strong class="text-success"><?= number_format($u['override_rate'], 1) ?>%</strong>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= $u['max_active_leads'] ?></td>
                            <td class="text-center">
                                <?= $u['accepts_inbound']
                                    ? '<span class="badge bg-success">Sí</span>'
                                    : '<span class="badge bg-light text-dark">No</span>' ?>
                            </td>
                            <td class="text-center">
                                <?= $u['is_active']
                                    ? '<span class="badge bg-success">Activo</span>'
                                    : '<span class="badge bg-secondary">Inactivo</span>' ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/super/sales-users/edit/<?= $u['id'] ?>"
                                       class="btn btn-outline-primary">Editar</a>
                                    <a href="/super/sales-users/toggle/<?= $u['id'] ?>"
                                       class="btn <?= $u['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                       onclick="return confirm('¿Confirmar?')">
                                        <?= $u['is_active'] ? 'Desactivar' : 'Activar' ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3 text-muted small">
    <strong>Comisión:</strong> % que gana por venta propia.
    <strong>Override:</strong> % que un gerente recibe por cada venta de su equipo.
    <strong>Tope:</strong> máximo de leads activos simultáneos.
    <strong>Inbound:</strong> si entra en round-robin automático.
</div>
<?= $this->endSection() ?>
