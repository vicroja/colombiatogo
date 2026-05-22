<?= $this->extend('super/layouts/main') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <a href="<?= base_url('/super/tenants') ?>" class="btn btn-sm btn-outline-secondary me-3">&larr; Volver</a>
        <h2 class="mb-0"><?= esc($tenant['name']) ?></h2>
        <span class="ms-3">
        <?php if ($tenant['is_suspended']): ?>
            <span class="badge bg-danger">Suspendido</span>
        <?php else: ?>
            <span class="badge bg-success">Activo</span>
        <?php endif; ?>
    </span>
    </div>

    <div class="row g-4">

        <!-- ===== DATOS DEL TENANT ===== -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Datos de la Propiedad</h5>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('/super/tenants/update/' . $tenant['id']) ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" name="name" required
                                       value="<?= esc($tenant['name']) ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email"
                                       value="<?= esc($tenant['email']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control" name="phone"
                                       value="<?= esc($tenant['phone']) ?>">
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">Dirección</label>
                                <input type="text" class="form-control" name="address"
                                       value="<?= esc($tenant['address']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ciudad</label>
                                <input type="text" class="form-control" name="city"
                                       value="<?= esc($tenant['city']) ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">País</label>
                                <input type="text" class="form-control" name="country"
                                       value="<?= esc($tenant['country']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sitio web</label>
                                <input type="url" class="form-control" name="website"
                                       value="<?= esc($tenant['website']) ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Estado de onboarding</label>
                                <select class="form-select" name="onboarding_status">
                                    <?php foreach (['pending' => 'Pendiente', 'in_progress' => 'En progreso', 'complete' => 'Completo'] as $k => $v): ?>
                                        <option value="<?= $k ?>" <?= $tenant['onboarding_status'] === $k ? 'selected' : '' ?>>
                                            <?= $v ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        <?= $tenant['is_active'] ? 'checked' : '' ?>>
                                    <label class="form-check-label">Tenant activo</label>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ===== SUSCRIPCIÓN + CAMBIO DE PLAN ===== -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Suscripción Actual</h5>
                </div>
                <div class="card-body">
                    <?php if ($subscription && $currentPlan): ?>
                        <p class="mb-1">
                            <strong>Plan:</strong>
                            <span class="badge" style="background-color: <?= esc($currentPlan['color']) ?>">
                            <?= esc($currentPlan['name']) ?>
                        </span>
                        </p>
                        <p class="mb-1"><strong>Precio:</strong> $<?= number_format($currentPlan['price'], 2) ?> <?= $currentPlan['currency'] ?> / <?= $currentPlan['billing_cycle'] ?></p>
                        <p class="mb-1"><strong>Estado:</strong> <code><?= esc($subscription['status']) ?></code></p>
                        <p class="mb-1"><strong>Inicio periodo:</strong> <?= $subscription['current_period_start'] ?></p>
                        <p class="mb-1"><strong>Fin periodo:</strong> <?= $subscription['current_period_end'] ?></p>
                        <p class="mb-0"><strong>Días de gracia:</strong> <?= $subscription['grace_period_days'] ?></p>
                    <?php else: ?>
                        <p class="text-muted">Este tenant no tiene suscripción registrada.</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($subscription): ?>
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Cambiar de Plan</h5>
                    </div>
                    <div class="card-body">
                        <form action="<?= base_url('/super/tenants/change-plan/' . $tenant['id']) ?>" method="post"
                              onsubmit="return confirm('¿Confirmas el cambio de plan?');">
                            <?= csrf_field() ?>

                            <div class="mb-3">
                                <label class="form-label">Nuevo plan</label>
                                <select class="form-select" name="new_plan_id" required>
                                    <?php foreach ($allPlans as $p): ?>
                                        <option value="<?= $p['id'] ?>" <?= $p['id'] == $subscription['plan_id'] ? 'disabled' : '' ?>>
                                            <?= esc($p['name']) ?> ($<?= $p['price'] ?> <?= $p['currency'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Cuándo aplicar</label>
                                <select class="form-select" name="apply_mode">
                                    <option value="next_period">En la próxima renovación (recomendado)</option>
                                    <option value="immediate">Inmediatamente (reinicia el periodo)</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-warning w-100">Aplicar cambio de plan</button>
                        </form>
                    </div>
                </div>

                <!-- ===== SUSPENDER / REACTIVAR ===== -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Acciones</h5>
                    </div>
                    <div class="card-body">
                        <form action="<?= base_url('/super/tenants/toggle-suspend/' . $tenant['id']) ?>" method="post"
                              onsubmit="return confirm('¿Confirmas esta acción?');">
                            <?= csrf_field() ?>
                            <?php if (!$tenant['is_suspended']): ?>
                                <input type="text" class="form-control mb-2" name="reason"
                                       placeholder="Motivo de suspensión (opcional)">
                                <button class="btn btn-outline-danger w-100">Suspender Tenant</button>
                            <?php else: ?>
                                <p class="text-muted small mb-2">
                                    <strong>Motivo de suspensión:</strong><br>
                                    <?= esc($tenant['suspended_reason'] ?: 'N/A') ?>
                                </p>
                                <button class="btn btn-outline-success w-100">Reactivar Tenant</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?= $this->endSection() ?>