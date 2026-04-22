<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <style>
        :root {
            --rp-bg:      #f4f5f7;
            --rp-surface: #ffffff;
            --rp-border:  #e4e7ec;
            --rp-text:    #111827;
            --rp-sub:     #6b7280;
            --rp-muted:   #9ca3af;
            --rp-blue:    #1d4ed8;
            --rp-blue-lt: #eff6ff;
            --rp-green:   #059669;
            --rp-red:     #dc2626;
            --rp-amber:   #d97706;
            --radius:     12px;
            --shadow:     0 1px 4px rgba(0,0,0,.07), 0 0 0 1px rgba(0,0,0,.04);
        }

        /* ── Layout ── */
        .rp-grid {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 24px;
            align-items: start;
        }
        @media (max-width: 960px) { .rp-grid { grid-template-columns: 1fr; } }

        /* ── Header ── */
        .rp-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .rp-header h1 {
            font-size: 20px; font-weight: 700;
            color: var(--rp-text); margin: 0;
            letter-spacing: -.02em;
        }
        .rp-header p { font-size: 13px; color: var(--rp-sub); margin: 4px 0 0; }

        .btn-matrix {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 16px;
            background: #fff;
            border: 1.5px solid var(--rp-border);
            border-radius: 8px;
            font-size: 13px; font-weight: 600; color: var(--rp-text);
            text-decoration: none;
            transition: all .15s;
        }
        .btn-matrix:hover { border-color: var(--rp-blue); color: var(--rp-blue); }
        .btn-matrix i { color: var(--rp-amber); }

        /* ── Card ── */
        .rp-card {
            background: var(--rp-surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .rp-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--rp-border);
        }
        .rp-card-header h6 {
            margin: 0; font-size: 14px; font-weight: 700;
            color: var(--rp-text);
        }
        .rp-card-header p { margin: 3px 0 0; font-size: 12px; color: var(--rp-sub); }
        .rp-card-body { padding: 20px; }

        /* ── Form fields ── */
        .field { margin-bottom: 16px; }
        .field:last-child { margin-bottom: 0; }
        .field-label {
            display: block;
            font-size: 11.5px; font-weight: 700;
            color: #374151; margin-bottom: 5px;
            text-transform: uppercase; letter-spacing: .04em;
        }
        .field-label .req { color: var(--rp-red); margin-left: 2px; }
        .form-control, .form-select {
            border: 1.5px solid var(--rp-border);
            border-radius: 8px; padding: 9px 12px;
            font-size: 13.5px; color: var(--rp-text);
            width: 100%; transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--rp-blue);
            box-shadow: 0 0 0 3px rgba(29,78,216,.1);
            outline: none;
        }
        .form-control::placeholder { color: var(--rp-muted); }

        /* ── Amenities grid ── */
        .amenities-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .amenity-toggle {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px;
            border: 1.5px solid var(--rp-border);
            border-radius: 8px;
            cursor: pointer;
            transition: all .15s;
            user-select: none;
            background: #fafafa;
        }
        .amenity-toggle:hover { border-color: #9ca3af; background: #fff; }
        .amenity-toggle.active {
            border-color: var(--rp-blue);
            background: var(--rp-blue-lt);
        }
        .amenity-toggle input { display: none; }
        .amenity-icon {
            width: 28px; height: 28px;
            border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; flex-shrink: 0;
            background: #e5e7eb;
            color: var(--rp-sub);
            transition: all .15s;
        }
        .amenity-toggle.active .amenity-icon { color: #fff; }
        .amenity-label {
            font-size: 12px; font-weight: 600;
            color: var(--rp-sub); line-height: 1.2;
            transition: color .15s;
        }
        .amenity-toggle.active .amenity-label { color: var(--rp-blue); }
        .amenity-check {
            margin-left: auto;
            width: 16px; height: 16px;
            border-radius: 50%;
            border: 1.5px solid var(--rp-border);
            background: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 9px; color: transparent;
            transition: all .15s; flex-shrink: 0;
        }
        .amenity-toggle.active .amenity-check {
            background: var(--rp-blue);
            border-color: var(--rp-blue);
            color: #fff;
        }

        /* ── Cancellation pills ── */
        .cancel-pills { display: flex; flex-direction: column; gap: 6px; }
        .cancel-pill {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 10px 12px;
            border: 1.5px solid var(--rp-border);
            border-radius: 8px; cursor: pointer;
            transition: all .15s; background: #fafafa;
        }
        .cancel-pill:hover { border-color: #9ca3af; }
        .cancel-pill.active-flexible   { border-color: #059669; background: #ecfdf5; }
        .cancel-pill.active-moderate   { border-color: #2563eb; background: #eff6ff; }
        .cancel-pill.active-strict     { border-color: #d97706; background: #fffbeb; }
        .cancel-pill.active-non_refundable { border-color: #dc2626; background: #fef2f2; }
        .cancel-pill input { display: none; }
        .cancel-pill-icon { font-size: 15px; margin-top: 1px; flex-shrink: 0; }
        .cancel-pill-info .name {
            font-size: 12.5px; font-weight: 700; color: var(--rp-text);
        }
        .cancel-pill-info .desc { font-size: 11px; color: var(--rp-sub); margin-top: 2px; }

        /* ── Submit ── */
        .btn-create {
            width: 100%; padding: 11px;
            background: var(--rp-blue); color: #fff;
            border: none; border-radius: 8px;
            font-size: 14px; font-weight: 700;
            cursor: pointer; display: flex;
            align-items: center; justify-content: center; gap: 8px;
            transition: background .15s;
            margin-top: 20px;
        }
        .btn-create:hover { background: #1e40af; }

        /* ── Plans list ── */
        .plan-row {
            padding: 16px 20px;
            border-bottom: 1px solid var(--rp-border);
            display: flex; align-items: flex-start;
            gap: 14px;
            animation: fadeUp .3s ease both;
            transition: background .1s;
        }
        .plan-row:last-child { border-bottom: none; }
        .plan-row:hover { background: #fafbfc; }

        .plan-index {
            width: 32px; height: 32px;
            border-radius: 8px; background: var(--rp-blue-lt);
            color: var(--rp-blue); font-size: 12px; font-weight: 800;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .plan-index.is-default { background: #fef3c7; color: #92400e; }

        .plan-info { flex: 1; min-width: 0; }
        .plan-name {
            font-size: 14px; font-weight: 700; color: var(--rp-text);
            display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
        }
        .plan-desc {
            font-size: 12px; color: var(--rp-sub);
            margin-top: 3px; margin-bottom: 8px;
        }
        .plan-meta {
            display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
            margin-top: 6px;
        }

        /* Amenity chips en el listado */
        .amenity-chip {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 11px; font-weight: 600;
            background: #f1f5f9; color: #475569;
            border: 1px solid #e2e8f0;
        }
        .cancel-chip {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 8px; border-radius: 20px;
            font-size: 11px; font-weight: 600;
        }
        .cc-flexible    { background: #ecfdf5; color: #065f46; }
        .cc-moderate    { background: #eff6ff; color: #1e40af; }
        .cc-strict      { background: #fffbeb; color: #92400e; }
        .cc-non_refundable { background: #fef2f2; color: #991b1b; }

        .badge-default {
            font-size: 10px; padding: 2px 7px;
            border-radius: 20px; font-weight: 700;
            background: #fef3c7; color: #92400e;
        }
        .badge-inactive {
            font-size: 10px; padding: 2px 7px;
            border-radius: 20px; font-weight: 700;
            background: #f1f5f9; color: #64748b;
        }

        /* Acciones del plan */
        .plan-actions { display: flex; gap: 6px; align-items: center; flex-shrink: 0; }
        .btn-action {
            padding: 5px 10px;
            border-radius: 7px; border: 1.5px solid var(--rp-border);
            background: #fff; font-size: 11.5px; font-weight: 600;
            color: var(--rp-sub); cursor: pointer;
            text-decoration: none; display: inline-flex;
            align-items: center; gap: 4px;
            transition: all .15s; white-space: nowrap;
        }
        .btn-action:hover { border-color: #9ca3af; color: var(--rp-text); }
        .btn-action.btn-star:hover { border-color: #f59e0b; color: #d97706; }
        .btn-action.btn-star.is-default { background: #fef3c7; border-color: #fcd34d; color: #92400e; }
        .btn-action.btn-toggle-off { color: var(--rp-red); border-color: #fca5a5; background: #fef2f2; }
        .btn-action.btn-toggle-off:hover { background: #fee2e2; }

        .empty-plans {
            padding: 40px 20px; text-align: center;
            color: var(--rp-muted);
        }
        .empty-plans i { font-size: 28px; display: block; margin-bottom: 8px; opacity: .4; }

        /* Noches mínimas inline */
        .min-nights-row {
            display: flex; align-items: center; gap: 10px;
        }
        .min-nights-row input {
            width: 80px;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>

    <!-- Header -->
    <div class="rp-header">
        <div>
            <h1>Planes Tarifarios</h1>
            <p>Define qué incluye cada plan y sus condiciones. Los precios se configuran en la matriz.</p>
        </div>
        <a href="<?= base_url('/rate-plans/matrix') ?>" class="btn-matrix">
            <i class="bi bi-grid-3x3"></i> Abrir Matriz de Precios
        </a>
    </div>

<?php if (session('success')): ?>
    <div style="background:#ecfdf5;border:1px solid #6ee7b7;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#065f46;display:flex;align-items:center;gap:8px;">
        <i class="bi bi-check-circle-fill"></i> <?= session('success') ?>
    </div>
<?php endif; ?>

<?php
// Definición canónica de cancelación para la vista
$cancelPolicies = [
    'flexible'      => ['icon' => 'bi-shield-check-fill',  'color' => '#059669', 'name' => 'Flexible',         'desc' => 'Cancelación gratuita hasta 24h antes'],
    'moderate'      => ['icon' => 'bi-shield-half',        'color' => '#2563eb', 'name' => 'Moderada',         'desc' => 'Cancelación gratuita hasta 5 días antes'],
    'strict'        => ['icon' => 'bi-shield-exclamation', 'color' => '#d97706', 'name' => 'Estricta',         'desc' => 'Sin reembolso en los últimos 7 días'],
    'non_refundable'=> ['icon' => 'bi-shield-x-fill',      'color' => '#dc2626', 'name' => 'No Reembolsable',  'desc' => 'Descuento a cambio de pago sin reembolso'],
];
?>

    <div class="rp-grid">

        <!-- ══ FORMULARIO CREAR PLAN ══ -->
        <div class="rp-card" style="position:sticky;top:20px;">
            <div class="rp-card-header">
                <h6><i class="bi bi-plus-circle me-2" style="color:var(--rp-blue)"></i>Nuevo Plan Tarifario</h6>
                <p>Configura qué incluye y sus condiciones</p>
            </div>
            <div class="rp-card-body">
                <form action="<?= base_url('/rate-plans/store') ?>" method="post" id="plan-form">
                    <?= csrf_field() ?>

                    <!-- Nombre -->
                    <div class="field">
                        <label class="field-label" for="plan-name">Nombre del plan <span class="req">*</span></label>
                        <input type="text" name="name" id="plan-name" class="form-control" required
                               placeholder="Ej. Todo Incluido, Tarifa Corporativa…">
                    </div>

                    <!-- Descripción -->
                    <div class="field">
                        <label class="field-label" for="plan-desc">Descripción</label>
                        <textarea name="description" id="plan-desc" class="form-control" rows="2"
                                  placeholder="Qué incluye, condiciones especiales…"></textarea>
                    </div>

                    <!-- Noches mínimas -->
                    <div class="field">
                        <label class="field-label">Noches mínimas</label>
                        <div class="min-nights-row">
                            <input type="number" name="min_nights_default" class="form-control"
                                   value="1" min="1" max="30">
                            <span style="font-size:12px;color:var(--rp-sub);">noche(s) requeridas para reservar con este plan</span>
                        </div>
                    </div>

                    <!-- Política de cancelación -->
                    <div class="field">
                        <label class="field-label">Política de cancelación</label>
                        <div class="cancel-pills" id="cancel-pills">
                            <?php foreach ($cancelPolicies as $key => $cp): ?>
                                <label class="cancel-pill <?= $key === 'flexible' ? 'active-flexible' : '' ?>" data-key="<?= $key ?>">
                                    <input type="radio" name="cancellation_policy" value="<?= $key ?>"
                                        <?= $key === 'flexible' ? 'checked' : '' ?>>
                                    <span class="cancel-pill-icon" style="color:<?= $cp['color'] ?>">
                                    <i class="bi <?= $cp['icon'] ?>"></i>
                                </span>
                                    <div class="cancel-pill-info">
                                        <div class="name"><?= $cp['name'] ?></div>
                                        <div class="desc"><?= $cp['desc'] ?></div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Beneficios incluidos -->
                    <div class="field">
                        <label class="field-label">Beneficios incluidos</label>
                        <div class="amenities-grid">
                            <?php foreach ($amenities as $key => $am): ?>
                                <label class="amenity-toggle" data-key="<?= $key ?>">
                                    <input type="checkbox" name="amenities[]" value="<?= $key ?>">
                                    <div class="amenity-icon" data-color="<?= $am['color'] ?>">
                                        <i class="bi <?= $am['icon'] ?>"></i>
                                    </div>
                                    <span class="amenity-label"><?= $am['label'] ?></span>
                                    <div class="amenity-check"><i class="bi bi-check"></i></div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn-create">
                        <i class="bi bi-plus-lg"></i> Crear Plan
                    </button>
                </form>
            </div>
        </div>

        <!-- ══ LISTADO DE PLANES ══ -->
        <div class="rp-card">
            <div class="rp-card-header" style="display:flex;align-items:center;justify-content:space-between;">
                <h6><i class="bi bi-list-ul me-2" style="color:var(--rp-sub)"></i>Planes configurados</h6>
                <span style="font-size:12px;color:var(--rp-sub);"><?= count($plans) ?> plan<?= count($plans) !== 1 ? 'es' : '' ?></span>
            </div>

            <?php if (empty($plans)): ?>
                <div class="empty-plans">
                    <i class="bi bi-grid"></i>
                    Aún no hay planes. Crea el primero desde el formulario.
                </div>
            <?php else: ?>
                <?php foreach ($plans as $i => $p):
                    // Decodificar amenities
                    $planAmenities = is_string($p['amenities_json'])
                        ? (json_decode($p['amenities_json'], true) ?? [])
                        : ($p['amenities_json'] ?? []);
                    $activeAmens = array_keys(array_filter($planAmenities));
                    $cp = $cancelPolicies[$p['cancellation_policy']] ?? $cancelPolicies['flexible'];
                    ?>
                    <div class="plan-row" style="animation-delay:<?= $i * .05 ?>s">

                        <div class="plan-index <?= $p['is_default'] ? 'is-default' : '' ?>">
                            <?= $p['is_default'] ? '★' : ($i + 1) ?>
                        </div>

                        <div class="plan-info">
                            <div class="plan-name">
                                <?= esc($p['name']) ?>
                                <?php if ($p['is_default']): ?>
                                    <span class="badge-default">Por defecto</span>
                                <?php endif; ?>
                                <?php if (!$p['is_active']): ?>
                                    <span class="badge-inactive">Inactivo</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($p['description'])): ?>
                                <div class="plan-desc"><?= esc($p['description']) ?></div>
                            <?php endif; ?>

                            <div class="plan-meta">
                                <!-- Política de cancelación -->
                                <span class="cancel-chip cc-<?= $p['cancellation_policy'] ?>">
                                <i class="bi <?= $cp['icon'] ?>"></i>
                                <?= $cp['name'] ?>
                            </span>

                                <!-- Noches mínimas -->
                                <?php if (($p['min_nights_default'] ?? 1) > 1): ?>
                                    <span class="amenity-chip">
                                    <i class="bi bi-moon"></i>
                                    Mín. <?= $p['min_nights_default'] ?> noches
                                </span>
                                <?php endif; ?>

                                <!-- Amenities activos -->
                                <?php foreach ($activeAmens as $ak):
                                    if (!isset($amenities[$ak])) continue;
                                    $am = $amenities[$ak];
                                    ?>
                                    <span class="amenity-chip" title="<?= $am['label'] ?>" style="border-color:<?= $am['color'] ?>20;background:<?= $am['color'] ?>10;color:<?= $am['color'] ?>">
                                    <i class="bi <?= $am['icon'] ?>"></i>
                                    <?= $am['label'] ?>
                                </span>
                                <?php endforeach; ?>

                                <?php if (empty($activeAmens)): ?>
                                    <span style="font-size:11px;color:var(--rp-muted);">Solo alojamiento</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="plan-actions">
                            <!-- Marcar como default -->
                            <form action="<?= base_url('/rate-plans/set-default/' . $p['id']) ?>" method="post">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-action btn-star <?= $p['is_default'] ? 'is-default' : '' ?>"
                                        title="<?= $p['is_default'] ? 'Plan predeterminado' : 'Marcar como predeterminado' ?>">
                                    <i class="bi bi-star<?= $p['is_default'] ? '-fill' : '' ?>"></i>
                                </button>
                            </form>

                            <!-- Activar/Desactivar -->
                            <form action="<?= base_url('/rate-plans/toggle-active/' . $p['id']) ?>" method="post">
                                <?= csrf_field() ?>
                                <button type="submit"
                                        class="btn-action <?= $p['is_active'] ? '' : 'btn-toggle-off' ?>"
                                        title="<?= $p['is_active'] ? 'Desactivar plan' : 'Activar plan' ?>">
                                    <i class="bi bi-<?= $p['is_active'] ? 'eye' : 'eye-slash' ?>"></i>
                                    <?= $p['is_active'] ? 'Activo' : 'Inactivo' ?>
                                </button>
                            </form>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // ── Amenity toggles ──────────────────────────────────────────
            document.querySelectorAll('.amenity-toggle').forEach(label => {
                const cb    = label.querySelector('input[type=checkbox]');
                const icon  = label.querySelector('.amenity-icon');
                const color = icon.dataset.color;

                function sync() {
                    if (cb.checked) {
                        label.classList.add('active');
                        icon.style.background = color;
                    } else {
                        label.classList.remove('active');
                        icon.style.background = '';
                    }
                }
                cb.addEventListener('change', sync);
                sync(); // estado inicial
            });

            // ── Cancellation policy pills ────────────────────────────────
            document.querySelectorAll('#cancel-pills .cancel-pill').forEach(pill => {
                const radio = pill.querySelector('input[type=radio]');
                function syncPill() {
                    document.querySelectorAll('#cancel-pills .cancel-pill').forEach(p => {
                        p.className = 'cancel-pill';
                    });
                    if (radio.checked) {
                        pill.classList.add('active-' + pill.dataset.key);
                    }
                }
                radio.addEventListener('change', () => {
                    document.querySelectorAll('#cancel-pills input[type=radio]').forEach(r => {
                        r.closest('.cancel-pill').className = 'cancel-pill';
                    });
                    pill.classList.add('active-' + pill.dataset.key);
                });
                syncPill();
            });
        });
    </script>

<?= $this->endSection() ?>