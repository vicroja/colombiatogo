<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= base_url('/rate-plans') ?>" class="btn btn-sm btn-outline-secondary me-2">&larr; Volver a Planes</a>
            <h2 class="d-inline-block mb-0">Matriz de Tarifas Dinámicas</h2>
        </div>
        <p class="text-muted mb-0 small">Define la tarifa base y el costo por huésped adicional.</p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <form action="<?= base_url('/rate-plans/update-matrix') ?>" method="post">
                <?= csrf_field() ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-dark">
                        <tr>
                            <th style="min-width: 200px;">Alojamiento</th>
                            <th class="text-center" style="width: 80px;" title="Huéspedes incluidos en la Tarifa Base">Pax Base</th>
                            <?php foreach($plans as $plan): ?>
                                <th class="text-center" style="min-width: 220px;">
                                    <?= esc($plan['name']) ?>
                                    <br><small class="fw-normal text-warning"><?= $plan['includes_breakfast'] ? '+ Desayuno' : 'Sin Desayuno' ?></small>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(empty($units)): ?>
                            <tr><td colspan="<?= count($plans) + 2 ?>" class="text-center py-4 text-muted">No hay unidades en el inventario.</td></tr>
                        <?php else: ?>
                            <?php foreach($units as $unit): ?>
                                <tr class="<?= !empty($unit['parent_id']) ? 'bg-light' : '' ?>">

                                    <td class="<?= empty($unit['parent_id']) ? 'fw-bold text-primary' : 'ps-4 border-start border-2 border-secondary' ?>">
                                        <?php if(!empty($unit['parent_id'])): ?>
                                            <i class="bi bi-arrow-return-right text-muted"></i>
                                        <?php endif; ?>
                                        <?= esc($unit['name']) ?>
                                    </td>

                                    <td class="text-center fw-bold text-secondary fs-5">
                                        <?= esc($unit['base_occupancy'] ?? 2) ?> <i class="bi bi-person-fill"></i>
                                    </td>

                                    <?php foreach($plans as $plan): ?>
                                        <?php
                                        $rateInfo = $ratesMatrix[$unit['id']][$plan['id']] ?? null;
                                        $basePrice = $rateInfo ? $rateInfo['price_per_night'] : '';
                                        $adultPrice = $rateInfo ? $rateInfo['extra_person_price'] : '';
                                        $childPrice = $rateInfo ? ($rateInfo['extra_child_price'] ?? '') : '';
                                        ?>
                                        <td class="p-2">
                                            <div class="mb-1 input-group input-group-sm shadow-sm">
                                                <span class="input-group-text bg-white" title="Tarifa Base de la Habitación"><i class="bi bi-house-door-fill text-primary"></i></span>
                                                <input type="number" step="0.01" class="form-control text-end border-primary fw-bold"
                                                       name="prices[<?= $unit['id'] ?>][<?= $plan['id'] ?>][base]"
                                                       value="<?= $basePrice ?>" placeholder="Tarifa Base">
                                            </div>
                                            <div class="mb-1 input-group input-group-sm">
                                                <span class="input-group-text bg-light" title="Cobro por Adulto Extra"><i class="bi bi-person-plus-fill text-secondary"></i></span>
                                                <input type="number" step="0.01" class="form-control text-end"
                                                       name="prices[<?= $unit['id'] ?>][<?= $plan['id'] ?>][adult]"
                                                       value="<?= $adultPrice ?>" placeholder="Adulto Extra">
                                            </div>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light" title="Cobro por Niño Extra"><i class="bi bi-emoji-smile-fill text-info"></i></span>
                                                <input type="number" step="0.01" class="form-control text-end"
                                                       name="prices[<?= $unit['id'] ?>][<?= $plan['id'] ?>][child]"
                                                       value="<?= $childPrice ?>" placeholder="Niño Extra">
                                            </div>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-end bg-light p-3 border-top">
                    <button type="submit" class="btn btn-success px-5 fw-bold shadow"><i class="bi bi-save"></i> Actualizar Tarifario</button>
                </div>
            </form>
        </div>
    </div>

<?= $this->endSection() ?>