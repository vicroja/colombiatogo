<?= $this->extend('super/layouts/main') ?>

<?= $this->section('title') ?>Dashboard SuperAdmin<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Panel de Control Maestro</h2>
        <p class="text-muted mb-0">Bienvenido al corazón de MAVILUSA</p>
    </div>

    <div class="alert alert-success shadow-sm border-0 border-start border-success border-4 mb-5">
        <h4 class="alert-heading"><i class="bi bi-shield-check"></i> ¡Autenticación Aislada Exitosa!</h4>
        <p class="mb-0">Has ingresado al panel seguro del SuperAdmin. Esta área está completamente separada de los usuarios de los hoteles.</p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center py-5">
                    <h4 class="card-title">Propiedades (Hoteles)</h4>
                    <p class="card-text text-muted mb-4">Crea, edita y suspende los hoteles que utilizan tu PMS.</p>
                    <a href="<?= base_url('/super/tenants') ?>" class="btn btn-primary px-4">
                        Gestionar Propiedades
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center py-5">
                    <h4 class="card-title">Facturación SaaS</h4>
                    <p class="card-text text-muted mb-4">Controla los pagos mensuales y las fechas de corte de tus clientes.</p>
                    <a href="<?= base_url('/super/billing') ?>" class="btn btn-dark px-4">
                        Ver Suscripciones
                    </a>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>