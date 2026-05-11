<?= $this->extend('super/layout/main') ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Nuevo vendedor</h4>
        <a href="/super/sales-users" class="btn btn-sm btn-outline-secondary">← Volver</a>
    </div>

<?php if (session('error')): ?>
    <div class="alert alert-danger"><?= esc(session('error')) ?></div>
<?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" action="/super/sales-users/store">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre completo *</label>
                        <input type="text" name="name" class="form-control"
                               value="<?= old('name') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= old('email') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="phone" class="form-control"
                               value="<?= old('phone') ?>" placeholder="+57 300 1234567">
                        <small class="text-muted">Se usa para enviar recordatorios por WhatsApp.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Contraseña inicial *</label>
                        <input type="text" name="password" class="form-control" required
                               minlength="6" placeholder="Mínimo 6 caracteres">
                        <small class="text-muted">Compártesela al vendedor; podrá cambiarla después.</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Rol *</label>
                        <select name="role" class="form-select" required>
                            <option value="seller" <?= old('role') === 'seller' ? 'selected' : '' ?>>Vendedor</option>
                            <option value="manager" <?= old('role') === 'manager' ? 'selected' : '' ?>>Gerente</option>
                        </select>
                        <small class="text-muted">El gerente ve todos los leads del equipo.</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Comisión %</label>
                        <input type="number" name="commission_rate" class="form-control"
                               step="0.01" min="0" max="100"
                               value="<?= old('commission_rate', '0') ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tope de leads activos</label>
                        <input type="number" name="max_active_leads" class="form-control"
                               min="1" max="500"
                               value="<?= old('max_active_leads', '50') ?>">
                        <small class="text-muted">Anti-acaparamiento.</small>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="accepts_inbound" id="accepts_inbound"
                                   class="form-check-input" value="1"
                                <?= old('accepts_inbound', '1') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="accepts_inbound">
                                <strong>Entra en la rotación de leads inbound</strong>
                            </label>
                            <div class="form-text">
                                Si está activo, este vendedor recibirá automáticamente leads que lleguen por el formulario web (round-robin).
                                Desactívalo si solo prospecta en frío (outbound).
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Crear vendedor</button>
                    <a href="/super/sales-users" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
<?= $this->endSection() ?>