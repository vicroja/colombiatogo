<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Inventario de Habitaciones</h2>
        <?php if ($limitInfo['unlimited'] || $limitInfo['used'] < $limitInfo['limit']): ?>
            <a href="<?= base_url('/inventory/create') ?>" class="btn btn-success">+ Nueva Habitación</a>
        <?php else: ?>
            <button class="btn btn-secondary" disabled>Límite Alcanzado</button>
        <?php endif; ?>
    </div>

    <div class="card mb-4 shadow-sm border-info">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between text-muted small mb-1">
                <span>Uso del Plan Actual</span>
                <span>
                <?= $limitInfo['used'] ?> / <?= $limitInfo['unlimited'] ? 'Ilimitadas' : $limitInfo['limit'] ?> unidades
            </span>
            </div>
            <?php if (!$limitInfo['unlimited']): ?>
                <?php $percent = ($limitInfo['used'] / $limitInfo['limit']) * 100; ?>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar <?= $percent >= 100 ? 'bg-danger' : 'bg-info' ?>" style="width: <?= $percent ?>%"></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                <tr>
                    <th>Nombre / Número</th>
                    <th>Tipo</th>
                    <th>Estado Actual</th>
                </tr>
                </thead>
                <tbody>
                <?php if(empty($units)): ?>
                    <tr><td colspan="3" class="text-center py-4 text-muted">Aún no hay habitaciones configuradas.</td></tr>
                <?php else: ?>
                    <?php foreach($units as $u): ?>
                        <tr>
                            <td><strong><?= esc($u['name']) ?></strong></td>
                            <td><?= esc($u['type_name']) ?></td>
                            <td>
                                <span class="badge bg-success">Disponible</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?= $this->endSection() ?>