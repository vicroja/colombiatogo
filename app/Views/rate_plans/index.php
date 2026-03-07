<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Planes Tarifarios</h2>
        <a href="<?= base_url('/rate-plans/matrix') ?>" class="btn btn-warning fw-bold">
            <i class="bi bi-grid-3x3"></i> Ir a la Matriz de Precios
        </a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-primary mb-4">
                <div class="card-header bg-primary text-white fw-bold">Nuevo Plan</div>
                <div class="card-body">
                    <form action="<?= base_url('/rate-plans/store') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label small">Nombre del Plan</label>
                            <input type="text" name="name" class="form-control" required placeholder="Ej. Tarifa Premium">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Descripción</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Detalles de lo que incluye..."></textarea>
                        </div>
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="includes_breakfast" value="1" id="breakfastCheck">
                            <label class="form-check-label small" for="breakfastCheck">
                                Incluye Desayuno
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Crear Plan</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Nombre del Plan</th>
                            <th class="text-center">Desayuno</th>
                            <th class="text-center">Estado</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($plans as $p): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($p['name']) ?></strong>
                                    <?php if($p['is_default']): ?>
                                        <span class="badge bg-secondary ms-2">Por defecto</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?= $p['includes_breakfast'] ? '<span class="badge bg-success">Sí</span>' : '<span class="text-muted">No</span>' ?>
                                </td>
                                <td class="text-center">
                                    <?= $p['is_active'] ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>