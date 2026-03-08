<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-globe"></i> Constructor de Sitio Web</h2>
        <a href="<?= base_url('/book/' . $tenant['slug']) ?>" class="btn btn-outline-primary shadow-sm fw-bold" target="_blank">
            <i class="bi bi-box-arrow-up-right"></i> Ver Mi Sitio Web
        </a>
    </div>

    <div class="row">
        <div class="col-md-7">
            <form action="<?= base_url('/website/update') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $website['id'] ?>">

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-primary">Diseño y Contenido</h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" <?= $website['is_published'] ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold text-success" for="is_published">Publicar Web</label>
                        </div>
                    </div>
                    <div class="card-body bg-light">

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Plantilla (Theme)</label>
                                <select name="theme_slug" class="form-select">
                                    <option value="resort" <?= $website['theme_slug'] == 'resort' ? 'selected' : '' ?>>Resort / Cabañas (Recomendado)</option>
                                    <option value="boutique" <?= $website['theme_slug'] == 'boutique' ? 'selected' : '' ?>>Boutique / Minimalista</option>
                                    <option value="corporate" <?= $website['theme_slug'] == 'corporate' ? 'selected' : '' ?>>Corporativo / Ciudad</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Color Principal (Botones)</label>
                                <input type="color" name="primary_color" class="form-control form-control-color w-100" value="<?= esc($website['primary_color']) ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Título Principal (Hero)</label>
                            <input type="text" name="hero_title" class="form-control font-weight-bold fs-5" placeholder="Ej. Escapa a la naturaleza en Casa Lucerito" value="<?= esc($website['hero_title']) ?>">
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Subtítulo</label>
                            <input type="text" name="hero_subtitle" class="form-control" placeholder="Ej. El mejor descanso en Laureles, Medellín" value="<?= esc($website['hero_subtitle']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Texto "Acerca de Nosotros"</label>
                            <textarea name="about_text" class="form-control" rows="4" placeholder="Cuéntale a tus huéspedes por qué tu propiedad es especial..."><?= esc($website['about_text']) ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Políticas de Estadía (Letra chica)</label>
                            <textarea name="policies_text" class="form-control text-muted" rows="3" placeholder="Ej. No se aceptan mascotas. Check-in a las 15:00."><?= esc($website['policies_text']) ?></textarea>
                        </div>

                        <h6 class="fw-bold mt-4 border-bottom pb-2">Contacto y Redes Sociales</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">WhatsApp (Reservas)</label>
                                <input type="text" name="whatsapp_number" class="form-control" placeholder="Ej. 573001234567" value="<?= esc($website['whatsapp_number']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Link Instagram</label>
                                <input type="url" name="instagram_url" class="form-control" placeholder="https://instagram.com/tu_hotel" value="<?= esc($website['instagram_url']) ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold mt-3 shadow-sm">Guardar Textos y Diseño</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-images"></i> Galería del Hotel</h5>
                    <small>Fotos y videos de áreas comunes, piscinas, fachada.</small>
                </div>

                <div class="card-body border-bottom">
                    <form action="<?= base_url('/website/upload-media') ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <input type="file" name="media_file" class="form-control" accept="image/*,video/*" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold"><i class="bi bi-cloud-arrow-up"></i> Subir Foto/Video</button>
                    </form>
                </div>

                <div class="card-body bg-light p-3">
                    <div class="row g-2">
                        <?php if(empty($media)): ?>
                            <div class="col-12 text-center py-4 text-muted">
                                <i class="bi bi-camera fs-1"></i><br>Aún no has subido fotos de tu hotel.
                            </div>
                        <?php else: ?>
                            <?php foreach($media as $m): ?>
                                <div class="col-6">
                                    <div class="position-relative rounded overflow-hidden shadow-sm" style="height: 120px; background: #000;">
                                        <?php if($m['file_type'] == 'video'): ?>
                                            <video src="<?= base_url($m['file_path']) ?>" class="w-100 h-100" style="object-fit: cover;" muted></video>
                                            <div class="position-absolute bottom-0 start-0 bg-dark text-white small px-2 py-1"><i class="bi bi-camera-video"></i></div>
                                        <?php else: ?>
                                            <img src="<?= base_url($m['file_path']) ?>" class="w-100 h-100" style="object-fit: cover;">
                                        <?php endif; ?>

                                        <a href="<?= base_url('/website/delete-media/'.$m['id']) ?>" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="return confirm('¿Borrar archivo?');">
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

<?= $this->endSection() ?>