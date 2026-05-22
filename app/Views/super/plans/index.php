<?= $this->extend('super/layouts/main') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Planes de Suscripción</h2>
            <p class="text-muted mb-0">Catálogo de planes que ofreces a tus clientes</p>
        </div>
        <a href="<?= base_url('/super/plans/create') ?>" class="btn btn-primary">
            + Crear Nuevo Plan
        </a>
    </div>

    <div class="row">
        <?php if (empty($plans)): ?>
            <div class="col-12">
                <div class="alert alert-info">No hay planes registrados todavía.</div>
            </div>
        <?php else: ?>
            <?php foreach ($plans as $p): ?>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card shadow-sm border-0 h-100" style="border-top: 4px solid <?= esc($p['color']) ?> !important;">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h4 class="card-title mb-0"><?= esc($p['name']) ?></h4>
                                <?php if ($p['is_active']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </div>

                            <p class="text-muted small mb-3"><?= esc($p['description']) ?></p>

                            <div class="mb-3">
                                <span class="h3">$<?= number_format($p['price'], 2) ?></span>
                                <small class="text-muted">/ <?= esc($p['billing_cycle']) ?> <?= esc($p['currency']) ?></small>
                            </div>

                            <?php if ($p['trial_days'] > 0): ?>
                                <div class="mb-2">
                                    <span class="badge bg-info text-dark"><?= $p['trial_days'] ?> días de prueba</span>
                                </div>
                            <?php endif; ?>

                            <hr>

                            <ul class="list-unstyled small mb-3 flex-grow-1">
                                <li>
                                    <strong>Unidades:</strong>
                                    <?= ($p['limits']['max_units'] ?? 0) == -1 ? '∞ Ilimitadas' : ($p['limits']['max_units'] ?? 0) ?>
                                </li>
                                <li>
                                    <strong>Usuarios:</strong>
                                    <?= ($p['limits']['max_users'] ?? 0) == -1 ? '∞ Ilimitados' : ($p['limits']['max_users'] ?? 0) ?>
                                </li>
                                <li>
                                    <strong>Reservas/mes:</strong>
                                    <?= ($p['limits']['max_reservations_per_month'] ?? 0) == -1 ? '∞' : ($p['limits']['max_reservations_per_month'] ?? 0) ?>
                                </li>
                                <li>
                                    <strong>Soporte:</strong> <?= esc($p['limits']['support_level'] ?? 'basic') ?>
                                </li>
                            </ul>

                            <div class="alert alert-light p-2 small mb-3 mb-0 text-center">
                                <i class="bi bi-people"></i>
                                <strong><?= $p['tenants_count'] ?></strong> tenant(s) suscrito(s)
                            </div>

                            <div class="d-grid gap-2">
                                <a href="<?= base_url('/super/plans/edit/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    Editar
                                </a>
                                <form action="<?= base_url('/super/plans/toggle/' . $p['id']) ?>" method="post"
                                      onsubmit="return confirm('¿Cambiar el estado de este plan?');">
                                    <?= csrf_field() ?>
                                    <?php if ($p['is_active']): ?>
                                        <button class="btn btn-sm btn-outline-warning w-100">Desactivar</button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-success w-100">Activar</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?= $this->endSection() ?>