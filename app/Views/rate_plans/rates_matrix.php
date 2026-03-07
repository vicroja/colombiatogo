<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= base_url('/rate-plans') ?>" class="btn btn-sm btn-outline-secondary me-2">&larr; Volver a Planes</a>
            <h2 class="d-inline-block mb-0">Matriz de Tarifas Base</h2>
        </div>
        <p class="text-muted mb-0 small">Define el precio por noche para cada combinación.</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <form action="<?= base_url('/rate-plans/update-matrix') ?>" method="post">
                <?= csrf_field() ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-dark">
                        <tr>
                            <th style="width: 250px;">Habitación \ Plan</th>
                            <?php foreach($plans as $plan): ?>
                                <th class="text-center">
                                    <?= esc($plan['name']) ?>
                                    <br><small class="fw-normal text-light"><?= $plan['includes_breakfast'] ? '+ Desayuno' : '' ?></small>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(empty($units)): ?>
                            <tr><td colspan="<?= count($plans) + 1 ?>" class="text-center py-4">No hay habitaciones disponibles para configurar.</td></tr>
                        <?php else: ?>
                            <?php foreach($units as $unit): ?>
                                <tr>
                                    <td class="bg-light fw-bold">
                                        <?= esc($unit['name']) ?>
                                    </td>
                                    <?php foreach($plans as $plan): ?>
                                        <?php
                                        // Buscamos si ya hay un precio guardado en la matriz
                                        $currentPrice = isset($ratesMatrix[$unit['id']][$plan['id']])
                                            ? $ratesMatrix[$unit['id']][$plan['id']]['price_per_night']
                                            : '';
                                        ?>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><?= session('currency_symbol') ?: '$' ?></span>
                                                <input type="number" step="0.01" class="form-control text-end"
                                                       name="prices[<?= $unit['id'] ?>][<?= $plan['id'] ?>]"
                                                       value="<?= $currentPrice ?>" placeholder="0.00">
                                            </div>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-end bg-white p-3">
                    <button type="submit" class="btn btn-success px-5 fw-bold">Guardar Matriz de Precios</button>
                </div>
            </form>
        </div>
    </div>

<?= $this->endSection() ?>


