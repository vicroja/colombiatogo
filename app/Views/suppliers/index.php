<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Directorio de Proveedores</h2>
        <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#supplierModal">
            <i class="bi bi-building-add"></i> Nuevo Proveedor
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Empresa / Proveedor</th>
                    <th>NIT / Identificación</th>
                    <th>Contacto</th>
                    <th>Teléfono / Email</th>
                    <th>Estado</th>
                </tr>
                </thead>
                <tbody>
                <?php if(empty($suppliers)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No tienes proveedores registrados.</td></tr>
                <?php else: ?>
                    <?php foreach($suppliers as $sup): ?>
                        <tr>
                            <td class="fw-bold">
                                <i class="bi bi-building text-secondary me-2"></i><?= esc($sup['name']) ?>
                            </td>
                            <td><?= esc($sup['tax_id'] ?? '-') ?></td>
                            <td><?= esc($sup['contact_name'] ?? '-') ?></td>
                            <td>
                                <?= esc($sup['phone']) ?><br>
                                <small class="text-muted"><?= esc($sup['email']) ?></small>
                            </td>
                            <td>
                                <?= $sup['is_active'] ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="supplierModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form action="<?= base_url('/suppliers/store') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-truck"></i> Registrar Proveedor</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Razón Social o Nombre</label>
                            <input type="text" name="name" class="form-control" required placeholder="Ej. Lavandería La Blanca SAS">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">NIT / Documento</label>
                            <input type="text" name="tax_id" class="form-control" placeholder="Ej. 900.123.456-7">
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Nombre del Contacto</label>
                                <input type="text" name="contact_name" class="form-control" placeholder="Ej. Carlos Ruiz">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Teléfono</label>
                                <input type="text" name="phone" class="form-control" placeholder="Ej. 300 123 4567">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" placeholder="contacto@empresa.com">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary fw-bold">Guardar Proveedor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>


