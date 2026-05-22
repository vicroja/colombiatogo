<?= $this->extend('super/layouts/main') ?>

<?= $this->section('title') ?>Dashboard SuperAdmin<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Panel de Control Maestro</h2>
            <p class="text-muted mb-0">Hola <?= esc($adminName) ?>, bienvenido a MAVILUSA</p>
        </div>
    </div>

    <!-- KPIs principales -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-success border-4">
                <div class="card-body">
                    <small class="text-muted">Ingresos del mes</small>
                    <h3 class="text-success mb-0">$<?= number_format($revenueThisMonth, 2) ?></h3>
                    <small class="text-muted">MRR: $<?= number_format($mrr, 2) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-primary border-4">
                <div class="card-body">
                    <small class="text-muted">Tenants activos</small>
                    <h3 class="mb-0"><?= $activeTenants ?> / <?= $totalTenants ?></h3>
                    <small class="text-muted"><?= $trialSubs ?> en trial</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-warning border-4">
                <div class="card-body">
                    <small class="text-muted">En mora</small>
                    <h3 class="text-warning mb-0"><?= $pastDueSubs ?></h3>
                    <small class="text-muted">Periodo de gracia</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-danger border-4">
                <div class="card-body">
                    <small class="text-muted">Suspendidos</small>
                    <h3 class="text-danger mb-0"><?= $suspendedTenants ?></h3>
                    <small class="text-muted">Sin acceso</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Accesos rápidos -->
        <div class="col-md-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Accesos rápidos</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?= base_url('/super/tenants') ?>" class="list-group-item list-group-item-action">
                        <strong>Propiedades</strong><br>
                        <small class="text-muted">Crear, editar y suspender hoteles</small>
                    </a>
                    <a href="<?= base_url('/super/plans') ?>" class="list-group-item list-group-item-action">
                        <strong>Planes</strong><br>
                        <small class="text-muted">Precios, límites y módulos</small>
                    </a>
                    <a href="<?= base_url('/super/billing') ?>" class="list-group-item list-group-item-action">
                        <strong>Facturación</strong><br>
                        <small class="text-muted">Cobros mensuales y vencimientos</small>
                    </a>
                </div>
            </div>
        </div>

        <!-- Vencimientos próximos -->
        <div class="col-md-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Vencimientos próximos (7 días)</h5>
                    <a href="<?= base_url('/super/billing') ?>" class="btn btn-sm btn-outline-primary">Ver todos</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($upcoming)): ?>
                        <p class="p-4 text-muted mb-0">No hay vencimientos próximos.</p>
                    <?php else: ?>
                        <table class="table mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>Tenant</th>
                                <th>Vence</th>
                                <th>Estado</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($upcoming as $u): ?>
                                <?php
                                $daysLeft = round((strtotime($u['current_period_end']) - time()) / 86400);
                                ?>
                                <tr>
                                    <td><?= esc($u['tenant_name']) ?></td>
                                    <td>
                                        <strong><?= date('d/m/Y', strtotime($u['current_period_end'])) ?></strong>
                                        <small class="text-muted">(en <?= $daysLeft ?>d)</small>
                                    </td>
                                    <td><code><?= esc($u['status']) ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>