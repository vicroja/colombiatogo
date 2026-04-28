<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary me-2">&larr; Volver</a>
            <h2 class="d-inline-block mb-0">Manifiesto de Pasajeros</h2>
        </div>
        <button onclick="window.print()" class="btn btn-outline-dark">
            <i class="bi bi-printer"></i> Imprimir
        </button>
    </div>

    <!-- Encabezado del manifiesto -->
    <div class="card shadow-sm mb-4">
        <div class="card-body row">
            <div class="col-md-6">
                <h5 class="fw-bold mb-1"><?= esc($schedule['tour_name']) ?></h5>
                <p class="mb-1 text-muted">
                    <i class="bi bi-calendar-event"></i>
                    <?= date('d/m/Y H:i', strtotime($schedule['start_datetime'])) ?>
                </p>
                <p class="mb-0 text-muted">
                    <i class="bi bi-geo-alt"></i> <?= esc($schedule['meeting_point'] ?? 'No especificado') ?>
                </p>
            </div>
            <div class="col-md-3">
                <p class="mb-1"><strong>Guía:</strong> <?= esc($schedule['guide_name'] ?? 'Sin asignar') ?></p>
                <?php if ($schedule['guide_phone']): ?>
                    <p class="mb-0 text-muted"><i class="bi bi-telephone"></i> <?= esc($schedule['guide_phone']) ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-3 text-end">
                <div class="bg-primary text-white rounded p-2 d-inline-block">
                    <div class="small">Total Pasajeros</div>
                    <div class="fs-3 fw-bold"><?= $totalPax ?></div>
                    <div class="small"><?= $totalAdults ?> Ad / <?= $totalChildren ?> Ni</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de pasajeros -->
    <div class="card shadow-sm">
        <div class="card-header fw-bold">Lista de Pasajeros</div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-dark">
                <tr>
                    <th width="40">#</th>
                    <th>Nombre Completo</th>
                    <th>Documento</th>
                    <th>Teléfono</th>
                    <th class="text-center">Ad</th>
                    <th class="text-center">Ni</th>
                    <th>Recogida</th>
                    <th>Estado</th>
                    <th class="text-center d-print-none">✓</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($passengers)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No hay pasajeros confirmados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($passengers as $i => $p): ?>
                        <?php
                        $statusBadge = [
                            'confirmed' => 'bg-success',
                            'pending'   => 'bg-warning text-dark',
                            'no_show'   => 'bg-dark',
                        ];
                        ?>
                        <tr>
                            <td class="text-muted"><?= $i + 1 ?></td>
                            <td class="fw-bold"><?= esc($p['full_name']) ?></td>
                            <td><?= esc($p['guest_document'] ?? '—') ?></td>
                            <td><?= esc($p['guest_phone'] ?? '—') ?></td>
                            <td class="text-center"><?= $p['num_adults'] ?></td>
                            <td class="text-center"><?= $p['num_children'] ?></td>
                            <td><?= esc($p['pickup_location'] ?? '—') ?></td>
                            <td>
                                <span class="badge <?= $statusBadge[$p['status']] ?? 'bg-secondary' ?>">
                                    <?= strtoupper($p['status']) ?>
                                </span>
                            </td>
                            <!-- Checkbox de asistencia para el guía (solo visual, sin lógica aún) -->
                            <td class="text-center d-print-none">
                                <input type="checkbox" class="form-check-input">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="4" class="text-end">Totales:</td>
                    <td class="text-center"><?= $totalAdults ?></td>
                    <td class="text-center"><?= $totalChildren ?></td>
                    <td colspan="3"></td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Estilos solo para impresión -->
    <style>
        @media print {
            .btn, nav, aside { display: none !important; }
            .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; }
        }
    </style>

<?= $this->endSection() ?>