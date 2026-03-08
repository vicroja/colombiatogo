<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= base_url('/inventory') ?>" class="text-decoration-none text-muted mb-2 d-inline-block">&larr; Volver al Inventario</a>
            <h2 class="mb-0">Editar Unidad: <span class="text-primary"><?= esc($unit['name']) ?></span></h2>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4" id="unitTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab"><i class="bi bi-info-circle"></i> Información Básica</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="amenities-tab" data-bs-toggle="tab" data-bs-target="#amenities" type="button" role="tab"><i class="bi bi-star"></i> Características (Amenidades)</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="gallery-tab" data-bs-toggle="tab" data-bs-target="#gallery" type="button" role="tab"><i class="bi bi-images"></i> Galería de la Cabaña</button>
        </li>
    </ul>

    <div class="tab-content" id="unitTabsContent">

        <form action="<?= base_url('/inventory/update-unit/'.$unit['id']) ?>" method="post" id="mainUnitForm">
            <?= csrf_field() ?>

            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Nombre de la Unidad</label>
                                <input type="text" name="name" class="form-control" value="<?= esc($unit['name']) ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label small fw-bold">Precio Base x Noche</label>
                                <div class="input-group">
                                    <span class="input-group-text"><?= session('currency_symbol') ?: '$' ?></span>
                                    <input type="number" step="0.01" name="base_price" class="form-control" value="<?= esc($unit['base_price']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label small fw-bold">Estado</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= $unit['status'] == 'active' ? 'selected' : '' ?>>Activa (Disponible)</option>
                                    <option value="maintenance" <?= $unit['status'] == 'maintenance' ? 'selected' : '' ?>>Mantenimiento</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3 bg-light p-3 rounded border">
                            <div class="col-md-4 mb-2">
                                <label class="form-label small fw-bold text-primary"><i class="bi bi-people"></i> Capacidad Máx.</label>
                                <input type="number" name="max_occupancy" class="form-control" value="<?= esc($unit['max_occupancy']) ?>" required>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label small fw-bold text-primary"><i class="bi bi-moon-stars"></i> Camas</label>
                                <input type="text" name="beds_info" class="form-control" placeholder="Ej. 1 Doble, 2 Sencillas" value="<?= esc($unit['beds_info'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label small fw-bold text-primary"><i class="bi bi-droplet"></i> Baños</label>
                                <input type="number" step="0.5" name="bathrooms" class="form-control" placeholder="Ej. 1 o 1.5" value="<?= esc($unit['bathrooms'] ?? '1') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Descripción (Se mostrará en la web)</label>
                            <textarea name="description" class="form-control" rows="4"><?= esc($unit['description']) ?></textarea>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-end border-top-0 pb-4">
                        <button type="submit" class="btn btn-primary fw-bold shadow-sm px-4">Guardar Cambios</button>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="amenities" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="mb-4 text-primary">¿Qué incluye esta cabaña?</h5>
                        <div class="row g-3">
                            <?php
                            // El catálogo maestro de características
                            $cat = [
                                'wifi' => ['icon' => 'bi-wifi', 'label' => 'WiFi de Alta Velocidad'],
                                'ac' => ['icon' => 'bi-snow', 'label' => 'Aire Acondicionado'],
                                'fan' => ['icon' => 'bi-wind', 'label' => 'Ventilador'],
                                'tv' => ['icon' => 'bi-tv', 'label' => 'Smart TV / Streaming'],
                                'kitchen' => ['icon' => 'bi-cup-hot', 'label' => 'Cocina Equipada'],
                                'minibar' => ['icon' => 'bi-box-seam', 'label' => 'Minibar / Nevera'],
                                'hot_water' => ['icon' => 'bi-droplet-half', 'label' => 'Agua Caliente'],
                                'pet_friendly' => ['icon' => 'bi-suit-heart', 'label' => 'Pet Friendly (Mascotas)'],
                                'balcony' => ['icon' => 'bi-brightness-alt-high', 'label' => 'Balcón / Terraza'],
                                'jacuzzi' => ['icon' => 'bi-water', 'label' => 'Jacuzzi Privado'],
                                'safe' => ['icon' => 'bi-safe', 'label' => 'Caja Fuerte'],
                                'work_desk' => ['icon' => 'bi-pc-display', 'label' => 'Escritorio de Trabajo']
                            ];

                            $saved_amenities = $unit['amenities_array'] ?? [];

                            foreach($cat as $key => $data):
                                $isChecked = in_array($key, $saved_amenities) ? 'checked' : '';
                                ?>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check form-switch border p-2 rounded bg-light shadow-sm">
                                        <input class="form-check-input ms-1 mt-2 cursor-pointer" type="checkbox" name="amenities[]" value="<?= $key ?>" id="amenity_<?= $key ?>" <?= $isChecked ?> style="cursor: pointer;">
                                        <label class="form-check-label ms-2 mt-1 fw-bold text-secondary" for="amenity_<?= $key ?>" style="cursor: pointer;">
                                            <i class="bi <?= $data['icon'] ?> text-primary me-1"></i> <?= $data['label'] ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-end border-top-0 pb-4">
                        <button type="submit" class="btn btn-primary fw-bold shadow-sm px-4">Guardar Características</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="tab-pane fade" id="gallery" role="tabpanel">
            <div class="row">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
                        <div class="card-body">
                            <h6 class="fw-bold"><i class="bi bi-cloud-arrow-up"></i> Subir Foto o Video</h6>
                            <p class="small text-white-50">Sube imágenes exclusivas del interior o exterior de esta unidad.</p>

                            <form action="<?= base_url('/inventory/upload-unit-media') ?>" method="post" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <input type="hidden" name="unit_id" value="<?= $unit['id'] ?>">
                                <div class="mb-3">
                                    <input type="file" name="media_file" class="form-control" accept="image/*,video/*" required>
                                </div>
                                <button type="submit" class="btn btn-light w-100 fw-bold text-primary">Subir Archivo</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body bg-light">
                            <h6 class="fw-bold mb-3 text-secondary">Imágenes Actuales</h6>
                            <div class="row g-2">
                                <?php if(empty($media)): ?>
                                    <div class="col-12 text-center py-5 text-muted">
                                        <i class="bi bi-camera fs-1"></i><br>Aún no has subido fotos para esta cabaña.
                                    </div>
                                <?php else: ?>
                                    <?php foreach($media as $m): ?>
                                        <div class="col-md-4 col-6">
                                            <div class="position-relative rounded overflow-hidden shadow-sm border border-white border-3" style="height: 150px; background: #000;">
                                                <?php if($m['file_type'] == 'video'): ?>
                                                    <video src="<?= base_url($m['file_path']) ?>" class="w-100 h-100" style="object-fit: cover;" muted></video>
                                                    <div class="position-absolute bottom-0 start-0 bg-dark text-white small px-2 py-1"><i class="bi bi-camera-video"></i></div>
                                                <?php else: ?>
                                                    <img src="<?= base_url($m['file_path']) ?>" class="w-100 h-100" style="object-fit: cover;">
                                                <?php endif; ?>

                                                <a href="<?= base_url('/inventory/delete-unit-media/'.$m['id']) ?>" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="return confirm('¿Borrar esta imagen?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
<?= $this->endSection() ?>