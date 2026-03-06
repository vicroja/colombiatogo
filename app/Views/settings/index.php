<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Configuración del Establecimiento</h2>
    </div>

    <form action="<?= base_url('/settings/update') ?>" method="post">
        <?= csrf_field() ?>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-bold">
                        <i class="bi bi-building"></i> Información Comercial
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Nombre del Alojamiento</label>
                            <input type="text" name="name" class="form-control" value="<?= esc($tenant['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Correo de Recepción / Contacto</label>
                            <input type="email" name="email" class="form-control" value="<?= esc($tenant['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Teléfono Principal</label>
                            <input type="text" name="phone" class="form-control" value="<?= esc($tenant['phone']) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-bold">
                        <i class="bi bi-globe"></i> Localización y Finanzas
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Moneda (Código)</label>
                                <input type="text" name="currency_code" class="form-control" value="<?= esc($tenant['currency_code']) ?>" placeholder="Ej. COP, USD, EUR" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small">Símbolo</label>
                                <input type="text" name="currency_symbol" class="form-control" value="<?= esc($tenant['currency_symbol']) ?>" placeholder="Ej. $, €" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Zona Horaria</label>
                            <select name="timezone" class="form-select" required>
                                <option value="America/Bogota" <?= $tenant['timezone'] == 'America/Bogota' ? 'selected' : '' ?>>América / Bogotá (UTC-5)</option>
                                <option value="America/Mexico_City" <?= $tenant['timezone'] == 'America/Mexico_City' ? 'selected' : '' ?>>América / Ciudad de México</option>
                                <option value="America/Lima" <?= $tenant['timezone'] == 'America/Lima' ? 'selected' : '' ?>>América / Lima</option>
                                <option value="America/Argentina/Buenos_Aires" <?= $tenant['timezone'] == 'America/Argentina/Buenos_Aires' ? 'selected' : '' ?>>América / Buenos Aires</option>
                                <option value="Europe/Madrid" <?= $tenant['timezone'] == 'Europe/Madrid' ? 'selected' : '' ?>>Europa / Madrid</option>
                            </select>
                            <small class="text-muted">Vital para el cálculo automático de Check-in y Check-out.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mb-5">
            <button type="submit" class="btn btn-primary px-5">Guardar Cambios</button>
        </div>
    </form>

<?= $this->endSection() ?>


