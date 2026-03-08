<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Centro de Reportes y Exportación</h2>
        <p class="text-muted mb-0">Descarga tu información en formato CSV (Excel)</p>
    </div>

    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-spreadsheet"></i> Generador de Reportes</h5>
                </div>
                <div class="card-body bg-light">
                    <form action="<?= base_url('/reports/export') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Tipo de Reporte</label>
                            <select name="report_type" class="form-select border-primary" required>
                                <option value="">Selecciona un reporte...</option>
                                <option value="reservations">1. Historial de Reservas (Ocupación)</option>
                                <option value="income">2. Ingresos de Caja (Pagos recibidos)</option>
                                <option value="open_accounts">3. Cuentas Abiertas (Huéspedes que deben dinero)</option>
                            </select>
                            <small class="text-muted mt-1 d-block">Las Cuentas Abiertas ignoran las fechas y muestran el saldo actual.</small>
                        </div>

                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="form-label fw-bold">Fecha Inicio</label>
                                <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-01') ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold">Fecha Fin</label>
                                <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-t') ?>" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100 fw-bold shadow-sm">
                            <i class="bi bi-download"></i> Descargar Reporte (CSV)
                        </button>
                    </form>
                </div>
            </div>

            <div class="alert alert-info mt-4 d-flex">
                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                <div>
                    <strong>Tip para Excel:</strong> Si al abrir el archivo descargado ves los datos en una sola columna, usa la función <em>"Datos > Texto a Columnas"</em> y elige la coma (,) como separador.
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>