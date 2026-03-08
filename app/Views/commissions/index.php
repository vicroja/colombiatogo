<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-cash-coin text-success"></i> Liquidación de Comisiones</h2>
            <p class="text-muted small">Controla y paga las comisiones de tus promotores y agencias.</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10 border-start border-warning border-4">
                <div class="card-body">
                    <h6 class="text-warning fw-bold mb-1">Total Pendiente por Pagar</h6>
                    <h3 class="mb-0"><?= session('currency_symbol') . number_format($totalPending, 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10 border-start border-success border-4">
                <div class="card-body">
                    <h6 class="text-success fw-bold mb-1">Total Pagado a Agencias</h6>
                    <h3 class="mb-0"><?= session('currency_symbol') . number_format($totalPaid, 2) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>Agencia / Promotor</th>
                        <th>Huésped y Fechas</th>
                        <th>Alojamiento</th>
                        <th>Comisión a Pagar</th>
                        <th>Estado Pago</th>
                        <th class="text-end">Acción</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(empty($commissions)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No hay comisiones registradas en el sistema.</td></tr>
                    <?php else: ?>
                        <?php foreach($commissions as $c): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold text-primary"><?= esc($c['agent_name']) ?></span><br>
                                    <small class="text-muted" title="<?= esc($c['bank_details']) ?>"><i class="bi bi-bank"></i> Ver cuenta bancaria</small>
                                </td>
                                <td>
                                    <strong><?= esc($c['guest_name']) ?></strong><br>
                                    <small class="text-muted"><?= date('d/m', strtotime($c['check_in_date'])) ?> al <?= date('d/m/Y', strtotime($c['check_out_date'])) ?></small><br>
                                    <?php if($c['reservation_status'] == 'cancelled'): ?>
                                        <span class="badge bg-danger" style="font-size: 0.65rem;">Reserva Cancelada</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted">
                                    <?= session('currency_symbol') . number_format($c['total_price'], 2) ?>
                                </td>
                                <td class="fw-bold fs-5 text-success">
                                    <?= session('currency_symbol') . number_format($c['amount'], 2) ?>
                                </td>
                                <td>
                                    <?php if($c['status'] == 'paid'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check2-all"></i> Pagada</span><br>
                                        <small class="text-muted" style="font-size: 0.7rem;"><?= date('d/m/y H:i', strtotime($c['paid_at'])) ?></small>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if($c['status'] != 'paid'): ?>
                                        <a href="<?= base_url('/commissions/pay/'.$c['id']) ?>" class="btn btn-sm btn-success fw-bold shadow-sm" onclick="return confirm('¿Confirmas que ya transferiste este dinero a la agencia?');">
                                            <i class="bi bi-currency-dollar"></i> Pagar
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-light text-muted" disabled><i class="bi bi-lock-fill"></i> Liquidada</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>