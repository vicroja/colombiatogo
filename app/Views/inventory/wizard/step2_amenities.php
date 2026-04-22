<?php /** inventory/wizard/step2_amenities.php */ ?>

<div class="iw-step-header">
    <div class="iw-step-eyebrow">Paso 2 de 3 · Opcional</div>
    <h1 class="iw-step-title-main">Amenidades</h1>
    <p class="iw-step-hint">¿Qué incluye esta unidad? Puedes completar esto ahora o más tarde desde la edición.</p>
</div>

<form action="<?= base_url('/inventory/wizard/save/2') ?>" method="post" id="step2-form">
    <?= csrf_field() ?>

    <?php
    // Catálogo canónico de amenidades con íconos y colores
    $amenitiesCatalog = [
        'wifi'         => ['label' => 'WiFi',               'icon' => 'bi-wifi',                  'color' => '#0ea5e9'],
        'ac'           => ['label' => 'Aire Acondicionado', 'icon' => 'bi-snow',                   'color' => '#06b6d4'],
        'fan'          => ['label' => 'Ventilador',         'icon' => 'bi-wind',                   'color' => '#64748b'],
        'tv'           => ['label' => 'Smart TV',           'icon' => 'bi-tv',                     'color' => '#6366f1'],
        'kitchen'      => ['label' => 'Cocina equipada',    'icon' => 'bi-cup-hot',                'color' => '#f59e0b'],
        'minibar'      => ['label' => 'Minibar / Nevera',   'icon' => 'bi-box-seam',               'color' => '#10b981'],
        'hot_water'    => ['label' => 'Agua Caliente',      'icon' => 'bi-droplet-half',           'color' => '#3b82f6'],
        'pet_friendly' => ['label' => 'Pet Friendly',       'icon' => 'bi-suit-heart',             'color' => '#ec4899'],
        'balcony'      => ['label' => 'Balcón / Terraza',   'icon' => 'bi-brightness-alt-high',    'color' => '#84cc16'],
        'jacuzzi'      => ['label' => 'Jacuzzi Privado',    'icon' => 'bi-water',                  'color' => '#8b5cf6'],
        'safe'         => ['label' => 'Caja Fuerte',        'icon' => 'bi-safe',                   'color' => '#64748b'],
        'work_desk'    => ['label' => 'Escritorio',         'icon' => 'bi-pc-display',             'color' => '#0f172a'],
        'parking'      => ['label' => 'Parqueadero',        'icon' => 'bi-car-front',              'color' => '#475569'],
        'pool'         => ['label' => 'Piscina',            'icon' => 'bi-water',                  'color' => '#0ea5e9'],
        'bbq'          => ['label' => 'Zona BBQ',           'icon' => 'bi-fire',                   'color' => '#ef4444'],
        'garden'       => ['label' => 'Jardín',             'icon' => 'bi-tree',                   'color' => '#22c55e'],
    ];

    // Si la unidad ya fue guardada en sesión, preseleccionar
    $savedAmenities = session('wizard_amenities') ?? [];
    ?>

    <div class="iw-card">
        <div class="iw-card-title"><i class="bi bi-star"></i> Características incluidas</div>
        <div class="iw-hint" style="margin-bottom:16px;">
            Selecciona todo lo que aplique. Los huéspedes verán esto al consultar disponibilidad.
        </div>

        <div class="amenity-grid">
            <?php foreach ($amenitiesCatalog as $key => $am): ?>
                <label class="amenity-toggle <?= in_array($key, $savedAmenities) ? 'active' : '' ?>"
                       data-color="<?= $am['color'] ?>">
                    <input type="checkbox" name="amenities[]" value="<?= $key ?>"
                        <?= in_array($key, $savedAmenities) ? 'checked' : '' ?>>
                    <div class="amenity-icon-box" data-color="<?= $am['color'] ?>"
                         style="<?= in_array($key, $savedAmenities) ? 'background:' . $am['color'] . ';color:#fff' : '' ?>">
                        <i class="bi <?= $am['icon'] ?>"></i>
                    </div>
                    <span class="amenity-label-text"><?= $am['label'] ?></span>
                </label>
            <?php endforeach; ?>
        </div>

        <!-- Amenidades personalizadas del tenant (si existen en la BD) -->
        <?php if (!empty($amenities)): ?>
            <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--iw-border);">
                <div class="iw-label" style="margin-bottom:12px;">Características personalizadas del hotel</div>
                <div class="amenity-grid">
                    <?php foreach ($amenities as $a): ?>
                        <label class="amenity-toggle">
                            <input type="checkbox" name="custom_amenities[]" value="<?= $a['id'] ?>">
                            <div class="amenity-icon-box">
                                <i class="bi bi-check2-square"></i>
                            </div>
                            <span class="amenity-label-text"><?= esc($a['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="iw-footer">
        <a href="<?= base_url('/inventory/wizard/step/1') ?>" class="btn-iw-back">
            <i class="bi bi-arrow-left"></i> Atrás
        </a>
        <div style="display:flex;gap:10px;">
            <a href="<?= base_url('/inventory/wizard/skip/2') ?>" class="btn-iw-skip">
                Saltar por ahora
            </a>
            <button type="submit" class="btn-iw-next">
                Continuar <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>
</form>

<script>
    document.querySelectorAll('.amenity-toggle').forEach(label => {
        const cb    = label.querySelector('input[type=checkbox]');
        const icon  = label.querySelector('.amenity-icon-box');
        const color = icon.dataset.color;

        function sync() {
            if (cb.checked) {
                label.classList.add('active');
                icon.style.background = color;
                icon.style.color = '#fff';
            } else {
                label.classList.remove('active');
                icon.style.background = '';
                icon.style.color = '';
            }
        }
        cb.addEventListener('change', sync);
        // Estado inicial
        sync();
    });
</script>