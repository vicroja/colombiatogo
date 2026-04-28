<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Tours y Actividades</h2>
        <a href="<?= base_url('/tours/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Tour
        </a>
    </div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php if (empty($tours)): ?>
            <div class="col-12">
                <div class="alert alert-info">No hay tours registrados aún.</div>
            </div>
        <?php else: ?>
            <?php foreach ($tours as $tour): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?= esc($tour['name']) ?></h5>
                            <p class="text-muted small mb-1">
                                <i class="bi bi-tag"></i> <?= esc($tour['category_name'] ?? 'Sin categoría') ?>
                                &nbsp;|&nbsp;
                                <i class="bi bi-clock"></i> <?= $tour['duration_minutes'] ?> min
                            </p>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-geo-alt"></i> <?= esc($tour['meeting_point'] ?? '—') ?>
                            </p>
                            <p class="mb-1">
                                <strong>Adulto:</strong> $<?= number_format($tour['price_adult'], 2) ?>
                                &nbsp;|&nbsp;
                                <strong>Niño:</strong> $<?= number_format($tour['price_child'], 2) ?>
                            </p>

                            <?php
                            $diffColors = ['easy' => 'success', 'moderate' => 'warning', 'hard' => 'danger'];
                            $diffLabels = ['easy' => 'Fácil', 'moderate' => 'Moderado', 'hard' => 'Difícil'];
                            ?>
                            <span class="badge bg-<?= $diffColors[$tour['difficulty_level']] ?>">
                            <?= $diffLabels[$tour['difficulty_level']] ?>
                        </span>
                        </div>
                        <div class="card-footer bg-white border-top-0 d-flex gap-2">
                            <a href="<?= base_url("/tours/{$tour['id']}/schedules") ?>" class="btn btn-sm btn-outline-primary w-100">
                                <i class="bi bi-calendar3"></i> Salidas
                            </a>
                            <a href="<?= base_url("/tours/{$tour['id']}/edit") ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<?= $this->endSection() ?>