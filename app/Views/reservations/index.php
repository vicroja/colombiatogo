<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Reservas Activas</h2>
        <a href="<?= base_url('/reservations/create') ?>" class="btn btn-success">+ Nueva Reserva</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th>Huésped</th>
                    <th>Habitación</th>
                    <th>Llegada</th>
                    <th>Salida</th>
                    <th>Estado</th>
                    <th>Acción (FSM)</th>
                </tr>
                </thead>


                <tbody>
                <?php if(empty($reservations)): ?>
                    <tr><td colspan="6" class="text-center py-4">No hay reservas registradas.</td></tr>
                <?php else: ?>
                    <?php foreach($reservations as $r): ?>
                        <tr>
                            <td>
                                <a href="<?= base_url('/reservations/show/'.$r['id']) ?>" class="text-decoration-none fw-bold text-primary">
                                    <?= esc($r['full_name']) ?> &rarr;
                                </a>
                            </td>
                            <td><?= esc($r['unit_name']) ?></td>
                            <td><?= date('d/m/Y', strtotime($r['check_in_date'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($r['check_out_date'])) ?></td>
                            <td>
                                <?php
                                $badges = [
                                    'pending' => 'bg-warning text-dark',
                                    'confirmed' => 'bg-info text-dark',
                                    'checked_in' => 'bg-success',
                                    'checked_out' => 'bg-secondary',
                                    'cancelled' => 'bg-danger'
                                ];
                                ?>
                                <span class="badge <?= $badges[$r['status']] ?>"><?= strtoupper($r['status']) ?></span>
                            </td>
                            <td>
                                <form action="<?= base_url('/reservations/update-status/'.$r['id']) ?>" method="post" class="d-flex gap-2">
                                    <?= csrf_field() ?>
                                    <select name="new_status" class="form-select form-select-sm" required>
                                        <option value="">Cambiar a...</option>
                                        <?php if($r['status'] == 'pending'): ?>
                                            <option value="confirmed">Confirmar</option>
                                            <option value="cancelled">Cancelar</option>
                                        <?php elseif($r['status'] == 'confirmed'): ?>
                                            <option value="checked_in">Check-in (Entró)</option>
                                            <option value="cancelled">Cancelar</option>
                                        <?php elseif($r['status'] == 'checked_in'): ?>
                                            <option value="checked_out">Check-out (Salió)</option>
                                        <?php endif; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary" <?= in_array($r['status'], ['checked_out', 'cancelled']) ? 'disabled' : '' ?>>Ok</button>
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


