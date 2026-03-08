<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-briefcase"></i> Comisionistas y Agencias</h2>
            <p class="text-muted small">Registra a los aliados que traen reservas a tu propiedad.</p>
        </div>
        <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#agentModal">
            <i class="bi bi-person-plus-fill"></i> Nuevo Aliado
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Nombre / Agencia</th>
                    <th>Código de Rastreo</th>
                    <th>Comisión</th>
                    <th>Contacto</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if(empty($agents)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted">Aún no tienes aliados o comisionistas registrados.</td></tr>
                <?php else: ?>
                    <?php foreach($agents as $agent): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= esc($agent['name']) ?></td>
                            <td><span class="badge bg-dark fs-6 font-monospace"><?= esc($agent['tracking_code']) ?></span></td>
                            <td class="fw-bold text-success">
                                <?= $agent['commission_type'] == 'percentage' ? $agent['commission_value'].'%' : session('currency_symbol').number_format($agent['commission_value'], 2) ?>
                            </td>
                            <td class="small">
                                <?= esc($agent['contact_info']) ?><br>
                                <span class="text-muted" title="<?= esc($agent['bank_details']) ?>"><i class="bi bi-bank"></i> Datos bancarios guardados</span>
                            </td>
                            <td>
                                <?= $agent['is_active'] ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>' ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= base_url('/agents/delete/'.$agent['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este comisionista?');"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="agentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <form action="<?= base_url('/agents/store') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-person-badge"></i> Registrar Nuevo Aliado</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">

                        <div class="row mb-3">
                            <div class="col-md-7">
                                <label class="form-label small fw-bold">Nombre del Promotor o Agencia</label>
                                <input type="text" name="name" class="form-control" placeholder="Ej. Agencia Tours Anzá o Walter" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold">Código de Rastreo</label>
                                <input type="text" name="tracking_code" class="form-control font-monospace text-uppercase" placeholder="Ej. WALTER26" required>
                                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">El huésped o el recepcionista usará este código.</small>
                            </div>
                        </div>

                        <h6 class="fw-bold mt-4 border-bottom pb-2">Regla de Comisión</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Tipo de Pago</label>
                                <select name="commission_type" class="form-select" required>
                                    <option value="percentage">Porcentaje sobre Alojamiento (%)</option>
                                    <option value="fixed">Monto Fijo por Reserva ($)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Valor</label>
                                <input type="number" step="0.01" name="commission_value" class="form-control" placeholder="Ej. 10 o 50000" required>
                            </div>
                        </div>

                        <h6 class="fw-bold mt-4 border-bottom pb-2">Datos de Contacto y Pago</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Teléfono / Email</label>
                                <input type="text" name="contact_info" class="form-control" placeholder="Para notificarle sus ventas">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Datos Bancarios (Para transferirle)</label>
                                <textarea name="bank_details" class="form-control" rows="2" placeholder="Ej. Nequi: 3001234567"></textarea>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4">Guardar Comisionista</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>