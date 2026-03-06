<?= $this->extend('super/layouts/main') ?>
<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Control de Suscripciones</h2>
        <p class="text-muted mb-0">Gestiona los pagos de tus clientes (Hoteles)</p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                <tr>
                    <th>Establecimiento</th>
                    <th>Plan Activo</th>
                    <th>Cuota Mensual</th>
                    <th>Fecha de Corte</th>
                    <th>Estado SaaS</th>
                    <th>Acción</th>
                </tr>
                </thead>
                <tbody>
                <?php if(empty($tenants)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No hay hoteles registrados aún.</td></tr>
                <?php else: ?>
                    <?php foreach($tenants as $t): ?>
                        <?php
                        // Lógica de semaforización
                        $today = strtotime(date('Y-m-d'));
                        $endDate = strtotime($t['current_period_end']);
                        $daysLeft = round(($endDate - $today) / (60 * 60 * 24));

                        $rowClass = '';
                        $statusBadge = '<span class="badge bg-success">Al Día</span>';

                        if ($t['is_suspended']) {
                            $rowClass = 'table-danger';
                            $statusBadge = '<span class="badge bg-danger"><i class="bi bi-lock-fill"></i> Suspendido</span>';
                        } elseif ($daysLeft < 0) {
                            $rowClass = 'table-warning';
                            $statusBadge = '<span class="badge bg-warning text-dark">Vencido (Periodo de Gracia)</span>';
                        } elseif ($daysLeft <= 5) {
                            $statusBadge = '<span class="badge bg-info text-dark">Vence en '.$daysLeft.' días</span>';
                        }
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td><strong><?= esc($t['name']) ?></strong></td>
                            <td><?= esc($t['plan_name']) ?></td>
                            <td>$<?= number_format($t['price'], 2) ?> USD</td>
                            <td class="fw-bold <?= $daysLeft < 0 ? 'text-danger' : '' ?>">
                                <?= date('d/m/Y', strtotime($t['current_period_end'])) ?>
                            </td>
                            <td><?= $statusBadge ?></td>
                            <td>
                                <form action="<?= base_url('/super/billing/renew/'.$t['id']) ?>" method="post" onsubmit="return confirm('¿Confirmas que MAVILUSA recibió el pago de este cliente?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-success">
                                        Registrar Pago (+1 Mes)
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?= $this->endSection() ?>