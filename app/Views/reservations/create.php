<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Registrar Nueva Reserva</h4>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('/reservations/store') ?>" method="post">
                        <?= csrf_field() ?>

                        <h5 class="text-primary mt-2">Datos del Huésped</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre Completo</label>
                                <input type="text" name="guest_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Documento / Pasaporte</label>
                                <input type="text" name="guest_document" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="guest_phone" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" name="guest_email" class="form-control">
                            </div>
                        </div>

                        <h5 class="text-primary border-top pt-3">Datos de la Estancia</h5>
                        <div class="row mb-3">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Habitación Asignada</label>
                                <select name="unit_id" class="form-select" required>
                                    <?php foreach($units as $u): ?>
                                        <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha Llegada</label>
                                <input type="date" name="check_in" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha Salida</label>
                                <input type="date" name="check_out" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Precio Total</label>
                                <input type="number" step="0.01" name="total_price" class="form-control" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= base_url('/reservations') ?>" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Crear Reserva (Pendiente)</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>