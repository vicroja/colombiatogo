<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Catálogo de Productos y Servicios</h2>
        <button type="button" class="btn btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#categoryModal">
            + Nueva Categoría
        </button>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-success mb-4">
                <div class="card-header bg-success text-white fw-bold">Agregar al Catálogo</div>
                <div class="card-body">
                    <form action="<?= base_url('/products/store-product') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label small">Categoría</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach($categories as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?> (<?= esc($c['type']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Nombre del Producto/Servicio</label>
                            <input type="text" name="name" class="form-control" placeholder="Ej. Coca-Cola 350ml" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Precio de Venta</label>
                            <div class="input-group">
                                <span class="input-group-text"><?= session('currency_symbol') ?: '$' ?></span>
                                <input type="number" step="0.01" name="unit_price" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small">SKU / Código (Opcional)</label>
                            <input type="text" name="sku" class="form-control" placeholder="Ej. BEB-001">
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold">Guardar Producto</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>SKU</th>
                            <th class="text-end">Precio</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(empty($products)): ?>
                            <tr><td colspan="4" class="text-center py-4">El catálogo está vacío.</td></tr>
                        <?php else: ?>
                            <?php foreach($products as $p): ?>
                                <tr>
                                    <td><strong><?= esc($p['name']) ?></strong></td>
                                    <td><span class="badge bg-secondary"><?= esc($p['category_name']) ?></span></td>
                                    <td class="text-muted small"><?= esc($p['sku']) ?: '-' ?></td>
                                    <td class="text-end fw-bold text-success">
                                        <?= session('currency_symbol') ?: '$' ?><?= number_format($p['unit_price'], 2) ?>
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

    <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?= base_url('/products/store-category') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Nueva Categoría</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="name" class="form-control" placeholder="Ej. Lavandería" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <select name="type" class="form-select" required>
                                <option value="product">Producto (Físico)</option>
                                <option value="service">Servicio (Intangible)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Categoría</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>