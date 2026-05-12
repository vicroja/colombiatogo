<?= $this->extend('super/layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Detalle de comisiones</h4>
    <a href="/super/leads/commissions" class="btn btn-sm btn-outline-secondary">← Resumen</a>
</div>

<?php if (session('success')): ?>
    <div class="alert alert-success"><?= esc(session('success')) ?></div>
<?php endif; ?>

<!-- Filtros -->
<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-2">
            <div class="col-md-3">
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">— Todos los vendedores —</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= (($filters['user_id'] ?? '') == $u['id']) ? 'selected' : '' ?>>
                            <?= esc($u['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">— Estado —</option>
                    <option value="pending"   <?= (($filters['status'] ?? '') === 'pending')   ? 'selected' : '' ?>>Pendiente</option>
                    <option value="approved"  <?= (($filters['status'] ?? '') === 'approved')  ? 'selected' : '' ?>>Aprobada</option>
                    <option value="paid"      <?= (($filters['status'] ?? '') === 'paid')      ? 'selected' : '' ?>>Pagada</option>
                    <option value="cancelled" <?= (($filters['status'] ?? '') === 'cancelled') ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">— Tipo —</option>
                    <option value="direct"   <?= (($filters['type'] ?? '') === 'direct')   ? 'selected' : '' ?>>Directa</option>
                    <option value="override" <?= (($filters['type'] ?? '') === 'override') ? 'selected' : '' ?>>Override</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from" class="form-control form-control-sm" value="<?= esc($filters['from'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="to" class="form-control form-control-sm" value="<?= esc($filters['to'] ?? '') ?>">
            </div>
            <div class="col-md-1">
                <button class="btn btn-sm btn-primary w-100">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla -->
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Beneficiario</th>
                    <th>Tipo</th>
                    <th>Lead</th>
                    <th>Origen</th>
                    <th class="text-end">Base</th>
                    <th class="text-center">%</th>
                    <th class="text-end">Comisión</th>
                    <th class="text-center">Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($commissions)): ?>
                    <tr><td colspan="10" class="text-center text-muted py-4">Sin resultados con esos filtros.</td></tr>
                <?php else: ?>
                    <?php foreach ($commissions as $c): ?>
                        <tr>
                            <td><small><?= date('d/m/Y', strtotime($c['earned_at'])) ?></small></td>
                            <td><strong><?= esc($c['user_name']) ?></strong></td>
                            <td>
                                <?php if ($c['type'] === 'direct'): ?>
                                    <span class="badge bg-primary">Directa</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark" title="Por venta de <?= esc($c['source_name']) ?>">
                                        Override
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small>
                                    <strong><?= esc($c['property_name']) ?></strong><br>
                                    <?= esc($c['contact_name']) ?>
                                </small>
                            </td>
                            <td><small><?= esc($c['source_name'] ?? '—') ?></small></td>
                            <td class="text-end"><small>$<?= number_format($c['base_amount'], 0, ',', '.') ?></small></td>
                            <td class="text-center"><small><?= number_format($c['rate'], 1) ?>%</small></td>
                            <td class="text-end"><strong>$<?= number_format($c['amount'], 0, ',', '.') ?></strong></td>
                            <td class="text-center">
                                <?php
                                    $badges = [
                                        'pending'   => 'bg-warning text-dark',
                                        'approved'  => 'bg-info',
                                        'paid'      => 'bg-success',
                                        'cancelled' => 'bg-secondary',
                                    ];
                                    $labels = [
                                        'pending'   => 'Pendiente',
                                        'approved'  => 'Aprobada',
                                        'paid'      => 'Pagada',
                                        'cancelled' => 'Cancelada',
                                    ];
                                ?>
                                <span class="badge <?= $badges[$c['status']] ?>"><?= $labels[$c['status']] ?></span>
                            </td>
                            <td>
                                <?php if ($c['status'] === 'pending'): ?>
                                    <a href="/super/leads/commissions/approve/<?= $c['id'] ?>"
                                       class="btn btn-sm btn-outline-info"
                                       onclick="return confirm('¿Aprobar?')">Aprobar</a>
                                <?php elseif ($c['status'] === 'approved'): ?>
                                    <button class="btn btn-sm btn-outline-success"
                                            onclick="payModal(<?= $c['id'] ?>, '<?= esc($c['amount']) ?>')">
                                        Marcar pagada
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de pago simple -->
<script>
function payModal(id, amount) {
    const method = prompt('Método de pago (transferencia, nómina, efectivo):');
    if (!method) return;
    const ref = prompt('Referencia (número de transferencia, etc):') || '';
    const notes = prompt('Notas (opcional):') || '';

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/super/leads/commissions/pay/' + id;
    form.innerHTML = `
        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
        <input type="hidden" name="payment_method" value="${method}">
        <input type="hidden" name="payment_reference" value="${ref}">
        <input type="hidden" name="notes" value="${notes}">
    `;
    document.body.appendChild(form);
    form.submit();
}
</script>
<?= $this->endSection() ?>
