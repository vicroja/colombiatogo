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
                    <th>Teléfono</th>
                    <th>Rol</th>
                    <th class="text-center">Comisión %</th>
                    <th class="text-center">Tope leads</th>
                    <th class="text-center">Inbound</th>
                    <th class="text-center">Estado</th>
                    <th>Último login</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            No hay vendedores creados aún.
                            <a href="/super/sales-users/create">Crea el primero</a>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr class="<?= $u['is_active'] ? '' : 'text-muted' ?>">
                            <td>
                                <strong><?= esc($u['name']) ?></strong>
                            </td>
                            <td><?= esc($u['email']) ?></td>
                            <td><?= esc($u['phone'] ?: '—') ?></td>
                            <td>
                                <?php if ($u['role'] === 'manager'): ?>
                                    <span class="badge bg-primary">Gerente</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Vendedor</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= number_format($u['commission_rate'], 2) ?>%</td>
                            <td class="text-center"><?= $u['max_active_leads'] ?></td>
                            <td class="text-center">
                                <?php if ($u['accepts_inbound']): ?>
                                    <span class="badge bg-success">Sí</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark">No</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($u['is_active']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small>
                                    <?= $u['last_login_at']
                                        ? date('d/m/Y H:i', strtotime($u['last_login_at']))
                                        : '<span class="text-muted">Nunca</span>' ?>
                                </small>
                            </td>
                            <td>
                                <a href="/super/sales-users/toggle/<?= $u['id'] ?>"
                                   class="btn btn-sm <?= $u['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                   onclick="return confirm('<?= $u['is_active'] ? '¿Desactivar este vendedor?' : '¿Reactivar este vendedor?' ?>')">
                                    <?= $u['is_active'] ? 'Desactivar' : 'Activar' ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 text-muted small">
        <strong>Tope leads:</strong> máximo de leads activos por vendedor antes de saturarse (anti-acaparamiento).<br>
        <strong>Inbound:</strong> si está activo, entra en la rotación automática (round-robin) de leads que llegan por web.
    </div>
<?= $this->endSection() ?>