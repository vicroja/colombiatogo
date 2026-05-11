<?= $this->extend('sales/layout/main') ?>
<?= $this->section('content') ?>
<h4>Nuevo lead</h4>
<form method="post" action="/sales/leads/store" class="row g-3 mt-2">
    <?= csrf_field() ?>

    <div class="col-md-6">
        <h6>Contacto</h6>
        <input type="text" name="contact_name" class="form-control mb-2" placeholder="Nombre*" required>
        <input type="email" name="contact_email" class="form-control mb-2" placeholder="Email">
        <input type="text" name="contact_phone" class="form-control mb-2" placeholder="Teléfono">
        <input type="text" name="contact_position" class="form-control mb-2" placeholder="Cargo (Dueño, Gerente...)">
    </div>

    <div class="col-md-6">
        <h6>Hotel</h6>
        <input type="text" name="property_name" class="form-control mb-2" placeholder="Nombre del hotel*" required>
        <select name="property_type" class="form-select mb-2">
            <option value="hotel">Hotel</option>
            <option value="hostel">Hostal</option>
            <option value="aparthotel">Apart-hotel</option>
            <option value="motel">Motel</option>
            <option value="glamping">Glamping</option>
            <option value="other">Otro</option>
        </select>
        <input type="number" name="rooms_count" class="form-control mb-2" placeholder="Número de habitaciones">
        <input type="text" name="property_city" class="form-control mb-2" placeholder="Ciudad">
        <input type="text" name="property_country" class="form-control mb-2" placeholder="País" value="Colombia">
        <input type="text" name="current_pms" class="form-control mb-2" placeholder="PMS actual (si tiene)">
        <input type="url" name="property_website" class="form-control mb-2" placeholder="Sitio web">
    </div>

    <div class="col-md-6">
        <h6>Comercial</h6>
        <select name="source_id" class="form-select mb-2">
            <option value="">Origen del lead</option>
            <?php foreach ($sources as $s): ?>
                <option value="<?= $s['id'] ?>"><?= esc($s['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" step="0.01" name="estimated_value" class="form-control mb-2" placeholder="Valor estimado">
        <input type="date" name="expected_close_date" class="form-control mb-2">
    </div>

    <div class="col-12">
        <textarea name="notes" class="form-control" rows="3" placeholder="Notas iniciales..."></textarea>
    </div>

    <div class="col-12">
        <button class="btn btn-primary">Crear lead</button>
        <a href="/sales/leads" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
<?= $this->endSection() ?>
