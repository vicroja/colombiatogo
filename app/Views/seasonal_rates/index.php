<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tarifas de Temporada</h2>
        <a href="<?= base_url('/seasonal-rates/test-calculator') ?>" target="_blank" class="btn btn-info fw-bold text-white">
            <i class="bi bi-calculator"></i> Probar Calculadora
        </a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-primary mb-4">
                <div class="card-header bg-primary text-white fw-bold">Nueva Temporada</div>
                <div class="card-body">
                    <form action="<?= base_url('/seasonal-rates/store') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label small">Nombre (Ej. Semana Santa)</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label small">Desde</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Hasta</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Tipo de Ajuste</label>
                            <select name="modifier_type" class="form-select" required>
                                <option value="percent_increase">Aumento en Porcentaje (%)</option>
                                <option value="percent_decrease">Descuento en Porcentaje (%)</option>
                                <option value="fixed">Fijar Precio Exacto ($)</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small">Valor del Ajuste</label>
                            <input type="number" step="0.01" name="modifier_value" class="form-control" required placeholder="Ej. 30 para un 30%">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Crear Temporada</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Temporada</th>
                            <th>Fechas</th>
                            <th>Ajuste Aplicado</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(empty($seasons)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No hay temporadas configuradas. Se usará la tarifa base.</td></tr>
                        <?php else: ?>
                            <?php foreach($seasons as $s): ?>
                                <tr>
                                    <td><strong><?= esc($s['name']) ?></strong></td>
                                    <td class="small">
                                        Del: <?= date('d/m/Y', strtotime($s['start_date'])) ?><br>
                                        Al: <?= date('d/m/Y', strtotime($s['end_date'])) ?>
                                    </td>
                                    <td>
                                        <?php if($s['modifier_type'] == 'percent_increase'): ?>
                                            <span class="badge bg-danger">+ <?= $s['modifier_value'] ?>%</span>
                                        <?php elseif($s['modifier_type'] == 'percent_decrease'): ?>
                                            <span class="badge bg-success">- <?= $s['modifier_value'] ?>%</span>
                                        <?php else: ?>
                                            <span class="badge bg-info text-dark">Fijo: $<?= $s['modifier_value'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('/seasonal-rates/delete/'.$s['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar temporada?');">Borrar</a>
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