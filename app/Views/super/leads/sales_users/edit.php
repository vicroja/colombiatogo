<?= $this->extend('super/layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Editar: <?= esc($user['name']) ?></h4>
    <a href="/super/sales-users" class="btn btn-sm btn-outline-secondary">← Volver</a>
</div>

<?php if (session('error')): ?>
    <div class="alert alert-danger"><?= esc(session('error')) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="post" action="/super/sales-users/update/<?= $user['id'] ?>">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" name="name" class="form-control" value="<?= esc($user['name']) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?= esc($user['email']) ?>" disabled>
                    <small class="text-muted">El email no se puede cambiar.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="phone" class="form-control" value="<?= esc($user['phone']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nueva contraseña (opcional)</label>
                    <input type="text" name="password" class="form-control" placeholder="Dejar vacío para mantener la actual">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Rol</label>
                    <select name="role" id="role" class="form-select" onchange="toggleFields()" required>
                        <option value="seller"  <?= $user['role']==='seller'  ? 'selected' : '' ?>>Vendedor</option>
                        <option value="manager" <?= $user['role']==='manager' ? 'selected' : '' ?>>Gerente</option>
                    </select>
                </div>

                <div class="col-md-4" id="manager-field">
                    <label class="form-label">Gerente al que reporta</label>
                    <select name="manager_id" class="form-select">
                        <option value="">— Sin gerente —</option>
                        <?php foreach ($managers as $m): ?>
                            <?php if ($m['id'] == $user['id']) continue; // no auto-referencia ?>
                            <option value="<?= $m['id'] ?>" <?= $user['manager_id'] == $m['id'] ? 'selected' : '' ?>>
                                <?= esc($m['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Comisión directa %</label>
                    <input type="number" name="commission_rate" class="form-control"
                           step="0.01" min="0" max="100" value="<?= $user['commission_rate'] ?>">
                </div>

                <div class="col-md-4" id="override-field" style="display:none;">
                    <label class="form-label">Override %</label>
                    <input type="number" name="override_rate" class="form-control"
                           step="0.01" min="0" max="100" value="<?= $user['override_rate'] ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tope leads activos</label>
                    <input type="number" name="max_active_leads" class="form-control"
                           min="1" max="500" value="<?= $user['max_active_leads'] ?>">
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" name="accepts_inbound" id="accepts_inbound"
                               class="form-check-input" value="1"
                               <?= $user['accepts_inbound'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="accepts_inbound">
                            Entra en rotación inbound
                        </label>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="/super/sales-users" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>

<script>
function toggleFields() {
    const role = document.getElementById('role').value;
    document.getElementById('manager-field').style.display  = (role === 'seller')  ? '' : 'none';
    document.getElementById('override-field').style.display = (role === 'manager') ? '' : 'none';
}
toggleFields();
</script>
<?= $this->endSection() ?>
