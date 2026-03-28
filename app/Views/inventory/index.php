<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Inventario de Alojamientos</h2>

        <?php if ($limitInfo['unlimited'] || $limitInfo['used'] < $limitInfo['limit']): ?>
            <a href="<?= base_url('/inventory/create') ?>" class="btn btn-success fw-bold shadow-sm">
                <i class="bi bi-plus-circle"></i> Nueva Unidad / Cabaña
            </a>
        <?php else: ?>
            <button class="btn btn-secondary" disabled>Límite de Plan Alcanzado</button>
        <?php endif; ?>
    </div>

    <div class="card mb-4 shadow-sm border-info">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between text-muted small mb-1">
                <span>Uso del Plan Actual</span>
                <span class="fw-bold">
                    <?= $limitInfo['used'] ?> / <?= $limitInfo['unlimited'] ? 'Ilimitadas' : $limitInfo['limit'] ?> unidades
                </span>
            </div>
            <?php if (!$limitInfo['unlimited']): ?>
                <?php $percent = ($limitInfo['limit'] > 0) ? ($limitInfo['used'] / $limitInfo['limit']) * 100 : 0; ?>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar <?= $percent >= 100 ? 'bg-danger' : 'bg-info' ?>" role="progressbar" style="width: <?= $percent ?>%" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th class="ps-4">Nombre / Número</th>
                    <th>Tipo</th>
                    <th>Estado Actual</th>
                    <th class="text-end pe-4">Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php if(empty($units)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Aún no hay alojamientos configurados en el inventario.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($units as $unit): ?>
                        <tr class="<?= !empty($unit['parent_id']) ? 'bg-light' : '' ?>">

                            <td class="ps-4">
                                <?php if(!empty($unit['parent_id'])): ?>
                                    <div class="ps-3 border-start border-2 border-secondary">
                                        <strong class="text-dark"><?= esc($unit['name']) ?></strong>
                                        <br>
                                        <span class="badge bg-white text-dark border border-secondary" style="font-size: 0.7em;">
                                            <i class="bi bi-arrow-return-right"></i> Sub-unidad
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <strong class="text-primary fs-6"><?= esc($unit['name']) ?></strong>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= esc($unit['type_name'] ?? 'N/A') ?>
                                <?php if(empty($unit['parent_id'])): ?>
                                    <span class="badge bg-primary ms-1" style="font-size: 0.7em;">Principal</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php
                                // Determinar color del badge según el estado
                                $badgeColor = 'secondary';
                                if ($unit['status'] === 'available') $badgeColor = 'success';
                                if ($unit['status'] === 'occupied') $badgeColor = 'danger';
                                if ($unit['status'] === 'maintenance') $badgeColor = 'warning text-dark';
                                if ($unit['status'] === 'blocked') $badgeColor = 'dark';
                                ?>
                                <span class="badge bg-<?= $badgeColor ?>">
                                    <?= str_replace(
                                        ['available', 'occupied', 'maintenance', 'blocked'],
                                        ['Disponible', 'Ocupada', 'Mantenimiento', 'Bloqueada'],
                                        esc($unit['status'])
                                    ) ?>
                                </span>
                            </td>

                            <td class="text-end pe-4">
                                <a href="<?= base_url('inventory/unit/edit/' . $unit['id']) ?>" class="btn btn-sm btn-outline-primary shadow-sm" title="Ver y Editar Detalles">
                                    <i class="bi bi-pencil-square"></i> Detalles
                                </a>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?= $this->endSection() ?>