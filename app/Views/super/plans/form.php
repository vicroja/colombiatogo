<?= $this->extend('super/layouts/main') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$isEdit  = !empty($plan);
$action  = $isEdit
    ? base_url('/super/plans/update/' . $plan['id'])
    : base_url('/super/plans/store');

// Helpers para valores actuales (en edición) o por defecto (en creación)
$val = function ($key, $default = '') use ($plan) {
    return old($key, $plan[$key] ?? $default);
};
?>

    <div class="row">
        <div class="col-lg-10 offset-lg-1">

            <div class="d-flex align-items-center mb-4">
                <a href="<?= base_url('/super/plans') ?>" class="btn btn-sm btn-outline-secondary me-3">&larr; Volver</a>
                <h2 class="mb-0"><?= $isEdit ? 'Editar Plan' : 'Crear Plan' ?></h2>
            </div>

            <form action="<?= $action ?>" method="post">
                <?= csrf_field() ?>

                <!-- ===== DATOS BÁSICOS ===== -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Información Básica</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nombre del Plan *</label>
                                <input type="text" class="form-control" name="name" required
                                       value="<?= esc($val('name')) ?>" placeholder="Ej. Professional">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Color identificador</label>
                                <input type="color" class="form-control form-control-color w-100"
                                       name="color" value="<?= esc($val('color', '#2563EB')) ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Slug (identificador URL) *</label>
                                <input type="text" class="form-control" name="slug" required
                                       value="<?= esc($val('slug')) ?>" placeholder="ej. professional">
                                <small class="text-muted">Solo minúsculas, números y guiones</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Orden de aparición</label>
                                <input type="number" class="form-control" name="sort_order"
                                       value="<?= esc($val('sort_order', 0)) ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="description" rows="2"><?= esc($val('description')) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===== PRECIO Y CICLO ===== -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Precio y Facturación</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Precio *</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="price" required
                                       value="<?= esc($val('price', '0.00')) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Moneda *</label>
                                <select class="form-select" name="currency" required>
                                    <?php foreach (['USD', 'COP', 'EUR', 'MXN'] as $cur): ?>
                                        <option value="<?= $cur ?>" <?= $val('currency', 'USD') === $cur ? 'selected' : '' ?>>
                                            <?= $cur ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ciclo *</label>
                                <select class="form-select" name="billing_cycle" required>
                                    <?php
                                    $cycles = ['monthly' => 'Mensual', 'annual' => 'Anual', 'one_time' => 'Pago único'];
                                    foreach ($cycles as $k => $lbl):
                                        ?>
                                        <option value="<?= $k ?>" <?= $val('billing_cycle', 'monthly') === $k ? 'selected' : '' ?>>
                                            <?= $lbl ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Días de prueba</label>
                                <input type="number" min="0" class="form-control" name="trial_days"
                                       value="<?= esc($val('trial_days', 0)) ?>">
                                <small class="text-muted">0 = sin prueba gratis</small>
                            </div>

                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_public" value="1"
                                        <?= $val('is_public', 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label">Plan público (visible en landing)</label>
                                </div>
                            </div>

                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        <?= $val('is_active', 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label">Plan activo (disponible para nuevos clientes)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===== LÍMITES (limits_json) ===== -->
                <?php
                // Agrupar el catálogo por 'group' para renderizar por secciones
                $groups = [];
                foreach ($catalog as $key => $meta) {
                    $groups[$meta['group']][$key] = $meta;
                }
                ?>

                <?php foreach ($groups as $groupName => $fields): ?>
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><?= esc($groupName) ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <?php foreach ($fields as $key => $meta): ?>
                                    <?php $current = $limits[$key] ?? null; ?>

                                    <?php if ($meta['type'] === 'int'): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label"><?= esc($meta['label']) ?></label>
                                            <input type="number" class="form-control"
                                                   name="limits[<?= $key ?>]"
                                                   value="<?= esc(old("limits.$key", $current ?? 0)) ?>">
                                            <?php if (!empty($meta['help'])): ?>
                                                <small class="text-muted"><?= esc($meta['help']) ?></small>
                                            <?php endif; ?>
                                        </div>

                                    <?php elseif ($meta['type'] === 'bool'): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="form-check form-switch mt-md-4">
                                                <input class="form-check-input" type="checkbox"
                                                       name="limits[<?= $key ?>]" value="1"
                                                    <?= !empty(old("limits.$key", $current)) ? 'checked' : '' ?>>
                                                <label class="form-check-label"><?= esc($meta['label']) ?></label>
                                            </div>
                                        </div>

                                    <?php elseif ($meta['type'] === 'select'): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label"><?= esc($meta['label']) ?></label>
                                            <select class="form-select" name="limits[<?= $key ?>]">
                                                <?php foreach ($meta['options'] as $optVal => $optLbl): ?>
                                                    <option value="<?= $optVal ?>"
                                                        <?= (old("limits.$key", $current) === $optVal) ? 'selected' : '' ?>>
                                                        <?= esc($optLbl) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- ===== BOTONES ===== -->
                <div class="d-flex justify-content-end gap-2 mb-5">
                    <a href="<?= base_url('/super/plans') ?>" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <?= $isEdit ? 'Actualizar Plan' : 'Crear Plan' ?>
                    </button>
                </div>

            </form>

        </div>
    </div>
<?= $this->endSection() ?>