<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex align-items-center mb-4">
        <a href="<?= base_url('/tours') ?>" class="btn btn-sm btn-outline-secondary me-3">&larr; Volver</a>
        <h2 class="mb-0">Nuevo Tour</h2>
    </div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

    <form action="<?= base_url('/tours/store') ?>" method="post">
        <?= csrf_field() ?>

        <div class="row g-3">

            <!-- Información básica -->
            <div class="col-md-8">
                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-bold">Información General</div>
                    <div class="card-body row g-3">

                        <div class="col-12">
                            <label class="form-label">Nombre del Tour <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= old('name') ?>" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="description" class="form-control" rows="3"><?= old('description') ?></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Duración (minutos)</label>
                            <input type="number" name="duration_minutes" class="form-control" value="<?= old('duration_minutes', 60) ?>" min="15">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Mínimo de personas</label>
                            <input type="number" name="min_pax" class="form-control" value="<?= old('min_pax', 1) ?>" min="1">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Dificultad</label>
                            <select name="difficulty_level" class="form-select">
                                <option value="easy"     <?= old('difficulty_level') === 'easy'     ? 'selected' : '' ?>>Fácil</option>
                                <option value="moderate" <?= old('difficulty_level') === 'moderate' ? 'selected' : '' ?>>Moderado</option>
                                <option value="hard"     <?= old('difficulty_level') === 'hard'     ? 'selected' : '' ?>>Difícil</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Punto de encuentro</label>
                            <input type="text" name="meeting_point" class="form-control" value="<?= old('meeting_point') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Política de cancelación</label>
                            <select name="cancellation_policy" class="form-select">
                                <option value="flexible">Flexible</option>
                                <option value="moderate">Moderada</option>
                                <option value="strict">Estricta</option>
                                <option value="non_refundable">No reembolsable</option>
                            </select>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Precios -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-bold">Precios Base</div>
                    <div class="card-body row g-3">
                        <div class="col-12">
                            <label class="form-label">Precio Adulto <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="price_adult" class="form-control" value="<?= old('price_adult', '0.00') ?>" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Precio Niño</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="price_child" class="form-control" value="<?= old('price_child', '0.00') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Incluye / No incluye -->
                <div class="card shadow-sm">
                    <div class="card-header fw-bold">¿Qué incluye?</div>
                    <div class="card-body">
                        <div id="included-list">
                            <div class="input-group mb-2">
                                <input type="text" name="included[]" class="form-control form-control-sm" placeholder="Ej: Almuerzo">
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addItem('included-list', 'included[]')">+</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-header fw-bold border-top">No incluye</div>
                    <div class="card-body">
                        <div id="excluded-list">
                            <div class="input-group mb-2">
                                <input type="text" name="excluded[]" class="form-control form-control-sm" placeholder="Ej: Transporte">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="addItem('excluded-list', 'excluded[]')">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /.row -->

        <div class="mt-3 text-end">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-circle"></i> Guardar Tour
            </button>
        </div>
    </form>

    <script>
        // Agregar campos dinámicos para incluidos/excluidos
        function addItem(containerId, fieldName) {
            const container = document.getElementById(containerId);
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            const isIncluded = fieldName === 'included[]';
            div.innerHTML = `
        <input type="text" name="${fieldName}" class="form-control form-control-sm" placeholder="${isIncluded ? 'Ej: Seguro' : 'Ej: Bebidas'}">
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="this.parentElement.remove()">×</button>
    `;
            container.appendChild(div);
        }
    </script>

<?= $this->endSection() ?>