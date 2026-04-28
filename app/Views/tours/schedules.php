<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex align-items-center mb-4">
        <a href="<?= base_url('/tours') ?>" class="btn btn-sm btn-outline-secondary me-3">&larr; Volver</a>
        <div>
            <h2 class="mb-0"><?= esc($tour['name']) ?></h2>
            <small class="text-muted">Próximas salidas programadas</small>
        </div>
    </div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

    <div class="row">

        <!-- Formulario nueva salida -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="bi bi-plus-circle"></i> Programar Salida
                </div>
                <div class="card-body">
                    <form action="<?= base_url("/tours/{$tour['id']}/schedules/store") ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label">Fecha y hora de salida <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="start_datetime" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cupo máximo <span class="text-danger">*</span></label>
                            <input type="number" name="max_pax" class="form-control" value="<?= $tour['min_pax'] ?>" min="1" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Guía asignado</label>
                            <select name="guide_id" class="form-select">
                                <option value="">Sin asignar</option>
                                <?php foreach ($guides as $guide): ?>
                                    <option value="<?= $guide['id'] ?>"><?= esc($guide['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Precio override: vacío = usa precio base del tour -->
                        <div class="mb-3">
                            <label class="form-label">
                                Precio adulto
                                <small class="text-muted">(vacío = $<?= number_format($tour['price_adult'], 2) ?> base)</small>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="price_adult_override" class="form-control" placeholder="<?= $tour['price_adult'] ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Precio niño
                                <small class="text-muted">(vacío = $<?= number_format($tour['price_child'], 2) ?> base)</small>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="price_child_override" class="form-control" placeholder="<?= $tour['price_child'] ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notas internas</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Guardar Salida</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Listado de salidas -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">Salidas Programadas</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Fecha / Hora</th>
                            <th>Guía</th>
                            <th class="text-center">Cupos</th>
                            <th>Precio Ad. / Ni.</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($schedules)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No hay salidas programadas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($schedules as $s): ?>
                                <?php
                                $available = (int)$s['max_pax'] - (int)$s['current_pax'];
                                $paxClass  = $available === 0 ? 'text-danger fw-bold' : ($available <= 3 ? 'text-warning fw-bold' : 'text-success');
                                $priceAd   = $s['price_adult_override'] ?? $tour['price_adult'];
                                $priceNi   = $s['price_child_override'] ?? $tour['price_child'];
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= date('d/m/Y', strtotime($s['start_datetime'])) ?></strong><br>
                                        <small class="text-muted"><?= date('H:i', strtotime($s['start_datetime'])) ?></small>
                                    </td>
                                    <td><?= esc($s['guide_name'] ?? '—') ?></td>
                                    <td class="text-center <?= $paxClass ?>">
                                        <?= $s['current_pax'] ?> / <?= $s['max_pax'] ?>
                                    </td>
                                    <td>
                                        $<?= number_format($priceAd, 2) ?> /
                                        $<?= number_format($priceNi, 2) ?>
                                        <?php if ($s['price_adult_override'] !== null): ?>
                                            <span class="badge bg-info text-dark ms-1" title="Precio especial para esta salida">*</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $s['status'] === 'scheduled' ? 'success' : 'secondary' ?>">
                                            <?= strtoupper($s['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= base_url("/tours/{$tour['id']}/reserve?schedule_id={$s['id']}") ?>"
                                           class="btn btn-sm btn-outline-primary me-1"
                                            <?= $available === 0 ? 'disabled' : '' ?>>
                                            <i class="bi bi-person-plus"></i> Reservar
                                        </a>
                                        <a href="<?= base_url("/tours/manifest/{$s['id']}") ?>"
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-list-check"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

<?= $this->endSection() ?>