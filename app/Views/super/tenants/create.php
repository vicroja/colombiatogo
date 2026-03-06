<?= $this->extend('super/layouts/main') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="row">
        <div class="col-md-8 offset-md-2">

            <div class="d-flex align-items-center mb-4">
                <a href="<?= base_url('/super/tenants') ?>" class="btn btn-sm btn-outline-secondary me-3">&larr; Volver</a>
                <h2 class="mb-0">Registrar Propiedad</h2>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="<?= base_url('/super/tenants/store') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label">Nombre del Alojamiento</label>
                            <input type="text" class="form-control" name="name" required placeholder="Ej. Casa Lucerito - Laboratorio Creativo">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Identificador Único (Slug)</label>
                            <input type="text" class="form-control" name="slug" required placeholder="Ej. casa-lucerito">
                            <small class="text-muted">Se usará para la URL o base de datos. Solo letras minúsculas, números y guiones.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico de Contacto</label>
                            <input type="email" class="form-control" name="email" required placeholder="contacto@alojamiento.com">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Plan de Suscripción Inicial</label>
                            <select class="form-select" name="plan_id" required>
                                <option value="">Seleccione un plan...</option>
                                <?php foreach ($plans as $plan): ?>
                                    <option value="<?= $plan['id'] ?>">
                                        <?= esc($plan['name']) ?> ($<?= $plan['price'] ?>)
                                        <?= $plan['trial_days'] > 0 ? " - {$plan['trial_days']} días de prueba" : "" ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4">Guardar y Asignar Suscripción</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
<?= $this->endSection() ?>