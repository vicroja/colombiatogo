<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tablero de Mantenimiento</h2>
        <button type="button" class="btn btn-danger fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#taskModal">
            <i class="bi bi-tools"></i> Reportar Daño / Tarea
        </button>
    </div>

    <div class="row">

        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-header bg-secondary text-white fw-bold">
                    Pendientes (<?= count($kanban['pending']) ?>)
                </div>
                <div class="card-body p-2" style="min-height: 500px;">
                    <?php foreach($kanban['pending'] as $t): ?>
                        <?= view_cell('App\Views\maintenance\_task_card', ['task' => $t]) ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-header bg-warning text-dark fw-bold">
                    En Progreso (<?= count($kanban['in_progress']) ?>)
                </div>
                <div class="card-body p-2" style="min-height: 500px;">
                    <?php foreach($kanban['in_progress'] as $t): ?>
                        <?= view_cell('App\Views\maintenance\_task_card', ['task' => $t]) ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-header bg-success text-white fw-bold">
                    Completadas (<?= count($kanban['completed']) ?>)
                </div>
                <div class="card-body p-2" style="min-height: 500px;">
                    <?php foreach($kanban['completed'] as $t): ?>
                        <?= view_cell('App\Views\maintenance\_task_card', ['task' => $t]) ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>

    <div class="modal fade" id="taskModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form action="<?= base_url('/maintenance/store') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Nuevo Reporte</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Título del Problema</label>
                            <input type="text" name="title" class="form-control" required placeholder="Ej. Fuga de agua en lavamanos">
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Habitación (Opcional)</label>
                                <select name="unit_id" class="form-select">
                                    <option value="">Área Común / Otro</option>
                                    <?php foreach($units as $u): ?>
                                        <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Prioridad</label>
                                <select name="priority" class="form-select" required>
                                    <option value="baja">Baja</option>
                                    <option value="media" selected>Media</option>
                                    <option value="alta">Alta (Urgente)</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Descripción</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Detalles para el técnico..."></textarea>
                        </div>
                        <div class="form-check form-switch mt-4 bg-light p-3 rounded border">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="blocks_unit" id="blocksUnit" value="1">
                            <label class="form-check-label fw-bold text-danger" for="blocksUnit">Bloquear Habitación</label>
                            <small class="d-block text-muted ms-4">Si se marca, la habitación no podrá recibir reservas hasta que la tarea esté completada.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger fw-bold">Guardar Reporte</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>