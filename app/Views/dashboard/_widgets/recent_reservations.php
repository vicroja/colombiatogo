<?php
/**
 * Widget: lista de reservas recientes.
 * Recibe: $widgets['recent_reservations']
 */
$reservations = $widgets['recent_reservations'] ?? [];
$cur = $currency ?? '$';

$badges = [
    'pending'   => ['class' => 'warning', 'label' => 'Pendiente'],
    'confirmed' => ['class' => 'info',    'label' => 'Confirmada'],
    'completed' => ['class' => 'success', 'label' => 'Completada'],
    'no_show'   => ['class' => 'dark',    'label' => 'No Show'],
    'cancelled' => ['class' => 'danger',  'label' => 'Cancelada'],
    'refunded'  => ['class' => 'secondary','label' => 'Reembolsada'],
];
?>

<?php if (empty($reservations)): ?>
    <div class="empty-state">
        <i class="bi bi-bookmark"></i>
        <p class="mb-0">Aún no hay reservas registradas.</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead style="font-size:.75rem;text-transform:uppercase;color:#6c757d;">
                <tr>
                    <th>Cliente</th>
                    <th>Tour</th>
                    <th>Salida</th>
                    <th class="text-center">Pax</th>
                    <th class="text-end">Total</th>
                    <th>Estado</th>
                    <th class="text-end">Creada</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $r): ?>
                    <?php $b = $badges[$r['status']] ?? ['class' => 'secondary', 'label' => $r['status']]; ?>
                    <tr>
                        <td>
                            <strong><?= esc($r['guest_name']) ?></strong>
                        </td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width:200px;">
                                <?= esc($r['tour_name']) ?>
                            </span>
                        </td>
                        <td>
                            <small><?= $r['departure_at'] ?></small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark"><?= $r['pax_total'] ?></span>
                        </td>
                        <td class="text-end">
                            <strong><?= $cur ?><?= number_format($r['total_price'], 0, ',', '.') ?></strong>
                        </td>
                        <td>
                            <span class="badge bg-<?= $b['class'] ?>"><?= $b['label'] ?></span>
                        </td>
                        <td class="text-end">
                            <small class="text-muted"><?= $r['time_ago'] ?></small>
                        </td>
                        <td class="text-end">
                            <a href="<?= base_url("/tours/reservation/{$r['id']}") ?>"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
