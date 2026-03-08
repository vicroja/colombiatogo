<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Empleados</h2>
        <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#userModal">
            <i class="bi bi-person-plus-fill"></i> Nuevo Empleado
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Correo (Usuario)</th>
                    <th>Rol / Permisos</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($users as $user): ?>
                    <tr>
                        <td class="fw-bold">
                            <i class="bi bi-person-circle text-secondary me-2 fs-5"></i>
                            <?= esc($user['name']) ?>
                            <?php if($user['id'] == session('user_id')): ?>
                                <span class="badge bg-info text-dark ms-2">Tú</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($user['email']) ?></td>
                        <td>
                            <span class="badge bg-secondary"><?= esc($user['role_name'] ?? 'Sin Rol') ?></span>
                        </td>
                        <td>
                            <?= $user['is_active'] ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>' ?>
                        </td>
                        <td class="text-end">
                            <?php if($user['id'] != session('user_id')): ?>
                                <a href="<?= base_url('/users/delete/'.$user['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Estás seguro de eliminar el acceso a este empleado?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form action="<?= base_url('/users/store') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-person-badge"></i> Crear Cuenta de Acceso</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Nombre Completo del Empleado</label>
                            <input type="text" name="name" class="form-control" required placeholder="Ej. María Gómez">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Correo Electrónico (Para Iniciar Sesión)</label>
                            <input type="email" name="email" class="form-control" required placeholder="recepcion@casalucerito.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Contraseña Temporal</label>
                            <input type="text" name="password" class="form-control" required placeholder="Asigna una clave segura">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Rol en el Sistema</label>
                            <select name="role_id" class="form-select" required>
                                <option value="">Seleccione un rol...</option>
                                <?php foreach($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>"><?= esc($role['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted d-block mt-1">El rol limitará lo que el usuario puede ver y hacer en el PMS.</small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary fw-bold">Guardar Empleado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>


