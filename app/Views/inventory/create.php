<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Añadir Habitación</h4>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('/inventory/store') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label">Tipo de Alojamiento</label>
                            <select name="type_id" class="form-select" required>
                                <?php foreach($types as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?> (Max: <?= $t['max_capacity'] ?> pax)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Nombre o Número de la Unidad</label>
                            <input type="text" name="name" class="form-control" required placeholder="Ej. Cabaña 1, Habitación 101">
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('/inventory') ?>" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar Habitación</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>