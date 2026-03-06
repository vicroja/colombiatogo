<?= $this->extend('super/layouts/main') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Propiedades Registradas</h2>
        <a href="<?= base_url('/super/tenants/create') ?>" class="btn btn-primary">
            + Crear Nueva Propiedad
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Identificador (Slug)</th>
                    <th>Plan Actual</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($tenants)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No hay propiedades registradas aún.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tenants as $t): ?>
                        <tr>
                            <td><?= $t['id'] ?></td>
                            <td><strong><?= esc($t['name']) ?></strong></td>
                            <td><span class="badge bg-secondary"><?= esc($t['slug']) ?></span></td>
                            <td><?= strtoupper($t['current_plan_slug']) ?></td>
                            <td>
                                <?php if ($t['is_suspended']): ?>
                                    <span class="badge bg-danger">Suspendido</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary">Editar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?= $this->endSection() ?>