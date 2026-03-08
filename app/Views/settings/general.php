<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Configuración de la Propiedad</h2>
    </div>

    <form action="<?= base_url('/settings/update') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="row">
            <div class="col-md-8">

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-building"></i> Información General</h5>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Nombre Comercial</label>
                                <input type="text" name="name" class="form-control" value="<?= esc($tenant['name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Correo de Contacto</label>
                                <input type="email" name="email" class="form-control" value="<?= esc($tenant['email']) ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Teléfono</label>
                                <input type="text" name="phone" class="form-control" value="<?= esc($tenant['phone']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Ciudad</label>
                                <input type="text" name="city" class="form-control" value="<?= esc($tenant['city']) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">País</label>
                                <input type="text" name="country" class="form-control" value="<?= esc($tenant['country']) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Dirección Completa</label>
                            <input type="text" name="address" class="form-control" value="<?= esc($tenant['address']) ?>">
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-info"><i class="bi bi-clock-history"></i> Políticas Operativas</h5>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Hora Estándar de Check-in</label>
                                <input type="time" name="checkin_time" class="form-control" value="<?= esc($tenant['checkin_time']) ?>" required>
                                <small class="text-muted">Usada para prerrellenar nuevas reservas.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Hora Estándar de Check-out</label>
                                <input type="time" name="checkout_time" class="form-control" value="<?= esc($tenant['checkout_time']) ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-4">

                <div class="card shadow-sm border-0 mb-4 text-center">
                    <div class="card-header bg-white py-3 text-start">
                        <h5 class="mb-0 fw-bold text-secondary"><i class="bi bi-image"></i> Logotipo</h5>
                    </div>
                    <div class="card-body bg-light">
                        <?php if($tenant['logo_path']): ?>
                            <img src="<?= base_url($tenant['logo_path']) ?>" alt="Logo" class="img-fluid mb-3 rounded shadow-sm" style="max-height: 150px;">
                        <?php else: ?>
                            <div class="bg-white border rounded py-4 mb-3 text-muted">
                                <i class="bi bi-camera fs-1"></i><br>Sin logo
                            </div>
                        <?php endif; ?>
                        <input type="file" name="logo" class="form-control form-control-sm" accept="image/*">
                        <small class="text-muted d-block mt-2">Formatos: JPG, PNG, WEBP. Max 2MB.</small>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-success"><i class="bi bi-globe-americas"></i> Localización</h5>
                    </div>
                    <div class="card-body bg-light">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Símbolo de Moneda</label>
                            <input type="text" name="currency_symbol" class="form-control fw-bold text-success" value="<?= esc($tenant['currency_symbol']) ?>" placeholder="Ej. $ o €" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Código de Moneda (ISO)</label>
                            <input type="text" name="currency_code" class="form-control" value="<?= esc($tenant['currency_code']) ?>" placeholder="Ej. COP, USD">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Zona Horaria</label>
                            <select name="timezone" class="form-select" required>
                                <option value="America/Bogota" <?= $tenant['timezone'] == 'America/Bogota' ? 'selected' : '' ?>>America/Bogotá (Colombia)</option>
                                <option value="America/Mexico_City" <?= $tenant['timezone'] == 'America/Mexico_City' ? 'selected' : '' ?>>America/Mexico City</option>
                                <option value="America/Lima" <?= $tenant['timezone'] == 'America/Lima' ? 'selected' : '' ?>>America/Lima</option>
                                <option value="America/New_York" <?= $tenant['timezone'] == 'America/New_York' ? 'selected' : '' ?>>America/New York</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="text-end mb-5">
            <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm">
                <i class="bi bi-save"></i> Guardar Configuración
            </button>
        </div>
    </form>

<?= $this->endSection() ?>


