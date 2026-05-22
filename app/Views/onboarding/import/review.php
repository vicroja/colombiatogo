<?php
/**
 * onboarding/import/review.php
 * Pantalla editable. Tabs por sección. Todos los campos son editables;
 * el usuario puede excluir items con el checkbox `include`.
 */

$has_acc   = (bool)($profile['has_accommodation'] ?? false);
$has_tours = (bool)($profile['has_tours']         ?? false);

$bs       = $data['business_summary']    ?? [];
$types    = $data['accommodation_types'] ?? [];
$units    = $data['accommodation_units'] ?? [];
$plans    = $data['rate_plans']          ?? [];
$rates    = $data['unit_rates']          ?? [];
$tours    = $data['tours']               ?? [];
$schedules= $data['tour_schedules']      ?? [];
$products = $data['products']            ?? [];
$notes    = $data['extraction_notes']    ?? null;
$vertical = $data['detected_vertical']   ?? 'unknown';
$confidence = $data['vertical_confidence'] ?? 'medium';

$totalCount = function(array $items): int {
    return count(array_filter($items, fn($i) => !empty($i['include'] ?? true)));
};

// Helper de input editable
function ed(string $name, $value, string $type = 'text', array $attrs = []): string {
    $attrStr = '';
    foreach ($attrs as $k => $v) $attrStr .= ' ' . $k . '="' . esc($v) . '"';
    $val = esc($value ?? '');
    if ($type === 'textarea') {
        return "<textarea name=\"{$name}\" class=\"form-control form-control-sm\"{$attrStr}>{$val}</textarea>";
    }
    return "<input type=\"{$type}\" name=\"{$name}\" value=\"{$val}\" class=\"form-control form-control-sm\"{$attrStr}>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Revisar importación — Onboarding</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f8fafc; font-family: 'Inter', system-ui, sans-serif; }
        .nav-tabs .nav-link { color: #64748b; border: none; padding: .65rem 1rem; font-size: .9rem; }
        .nav-tabs .nav-link.active { color: #6366f1; border-bottom: 2px solid #6366f1; background: transparent; font-weight: 600; }
        .nav-tabs .nav-link .badge { font-size: .7rem; }

        .item-card {
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 10px; padding: 1rem; margin-bottom: .75rem;
            transition: all .15s;
        }
        .item-card.excluded { opacity: .4; background: #f8fafc; }

        .item-card-header {
            display: flex; align-items: center; gap: .75rem; margin-bottom: .75rem;
        }
        .item-thumb {
            width: 56px; height: 56px; border-radius: 8px;
            background: #f1f5f9 center/cover no-repeat;
            flex-shrink: 0;
        }
        .item-thumb.empty {
            display: flex; align-items: center; justify-content: center;
            color: #94a3b8; font-size: 1.5rem;
        }

        .form-grid { display: grid; gap: .5rem; }
        .form-grid.cols-2 { grid-template-columns: 1fr 1fr; }
        .form-grid.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
        .form-grid.cols-4 { grid-template-columns: 1fr 1fr 1fr 1fr; }
        @media (max-width: 768px) {
            .form-grid.cols-2, .form-grid.cols-3, .form-grid.cols-4 {
                grid-template-columns: 1fr;
            }
        }

        .field-label {
            font-size: .72rem; font-weight: 600; color: #64748b;
            text-transform: uppercase; letter-spacing: .04em; margin-bottom: .15rem;
        }

        .confidence-badge {
            font-size: .65rem; padding: .15rem .5rem; border-radius: 99px;
            font-weight: 600; text-transform: uppercase;
        }
        .confidence-explicit { background: #d1fae5; color: #065f46; }
        .confidence-inferred { background: #fef3c7; color: #92400e; }
        .confidence-null     { background: #e0e7ff; color: #3730a3; }

        .empty-section {
            background: #f8fafc; border: 1px dashed #cbd5e1;
            border-radius: 10px; padding: 2rem; text-align: center; color: #94a3b8;
        }

        .sticky-footer {
            position: sticky; bottom: 0; background: #fff;
            border-top: 1px solid #e2e8f0; padding: 1rem 0;
            margin-top: 2rem;
        }

        .btn-primary-wiz {
            background: #6366f1; color: #fff; border: none;
            padding: .75rem 2rem; border-radius: 10px; font-weight: 600;
        }
        .btn-primary-wiz:hover { background: #4f46e5; color: #fff; }
        .btn-primary-wiz:disabled { opacity: .6; }
    </style>
</head>
<body>

<form action="/onboarding/import/confirm/<?= (int)$staging['id'] ?>" method="POST" id="reviewForm">
    <?= csrf_field() ?>

    <div class="container py-4" style="max-width: 1100px;">

        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h2 class="mb-1">📋 Revisa la información extraída</h2>
                <p class="text-muted mb-0">
                    Edita lo que necesites, desmarca lo que no quieras importar, y confirma al final.
                </p>
            </div>
            <div class="text-end">
                <span class="badge bg-light text-dark border">
                    Detectado: <strong><?= esc($vertical) ?></strong>
                </span>
                <span class="badge bg-light text-dark border">
                    Confianza: <strong><?= esc($confidence) ?></strong>
                </span>
            </div>
        </div>

        <?php if (session('error')): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-1"></i> <?= esc(session('error')) ?>
            </div>
        <?php endif ?>

        <?php if ($notes): ?>
            <div class="alert alert-warning small">
                <i class="bi bi-chat-square-text me-1"></i>
                <strong>Notas de Gemini:</strong> <?= esc($notes) ?>
            </div>
        <?php endif ?>

        <input type="hidden" name="confirmed_vertical" value="<?= esc($vertical) ?>">

        <!-- ══════════════════════════════════════════════════════════════
             NAV TABS
        ══════════════════════════════════════════════════════════════ -->
        <ul class="nav nav-tabs flex-wrap mt-3" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#t-bs">
                    <i class="bi bi-building me-1"></i> Negocio
                </button>
            </li>
            <?php if ($has_acc): ?>
                <li class="nav-item">
                    <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#t-units">
                        <i class="bi bi-door-open me-1"></i>
                        Habitaciones <span class="badge bg-secondary"><?= $totalCount($units) ?></span>
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#t-rates">
                        <i class="bi bi-currency-dollar me-1"></i>
                        Tarifas <span class="badge bg-secondary"><?= $totalCount($rates) ?></span>
                    </button>
                </li>
            <?php endif ?>
            <?php if ($has_tours): ?>
                <li class="nav-item">
                    <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#t-tours">
                        <i class="bi bi-compass me-1"></i>
                        Tours <span class="badge bg-secondary"><?= $totalCount($tours) ?></span>
                    </button>
                </li>
            <?php endif ?>
            <li class="nav-item">
                <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#t-products">
                    <i class="bi bi-box-seam me-1"></i>
                    Productos <span class="badge bg-secondary"><?= $totalCount($products) ?></span>
                </button>
            </li>
        </ul>

        <div class="tab-content bg-white border border-top-0 rounded-bottom p-4">

            <!-- ══════════════════════════════════════════════════════════════
                 TAB: Business summary
            ══════════════════════════════════════════════════════════════ -->
            <div class="tab-pane fade show active" id="t-bs">
                <div class="form-grid cols-2">
                    <div>
                        <div class="field-label">Nombre</div>
                        <?= ed('business_summary[name]', $bs['name'] ?? '') ?>
                    </div>
                    <div>
                        <div class="field-label">Sitio web</div>
                        <?= ed('business_summary[website]', $bs['website'] ?? '') ?>
                    </div>
                    <div>
                        <div class="field-label">Teléfono</div>
                        <?= ed('business_summary[phone]', $bs['phone'] ?? '') ?>
                    </div>
                    <div>
                        <div class="field-label">Email</div>
                        <?= ed('business_summary[email]', $bs['email'] ?? '', 'email') ?>
                    </div>
                    <div>
                        <div class="field-label">Ciudad</div>
                        <?= ed('business_summary[city]', $bs['city'] ?? '') ?>
                    </div>
                    <div>
                        <div class="field-label">País</div>
                        <?= ed('business_summary[country]', $bs['country'] ?? '') ?>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <div class="field-label">Dirección</div>
                        <?= ed('business_summary[address]', $bs['address'] ?? '') ?>
                    </div>
                    <div>
                        <div class="field-label">Check-in</div>
                        <?= ed('business_summary[checkin_time]', $bs['checkin_time'] ?? '15:00', 'time') ?>
                    </div>
                    <div>
                        <div class="field-label">Check-out</div>
                        <?= ed('business_summary[checkout_time]', $bs['checkout_time'] ?? '12:00', 'time') ?>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <div class="field-label">Descripción</div>
                        <?= ed('business_summary[description]', $bs['description'] ?? '', 'textarea', ['rows' => 3]) ?>
                    </div>
                </div>

                <?php if (!empty($bs['logo_url'])): ?>
                    <div class="mt-3 d-flex align-items-center gap-3 p-2 rounded"
                         style="background:#f1f5f9">
                        <img src="<?= esc($bs['logo_url']) ?>" alt="Logo"
                             style="width:60px;height:60px;border-radius:8px;object-fit:cover">
                        <div class="small">
                            <strong>Logo detectado.</strong> Se descargará al confirmar.
                            <input type="hidden" name="business_summary[logo_url]" value="<?= esc($bs['logo_url']) ?>">
                        </div>
                    </div>
                <?php endif ?>
            </div>

            <!-- ══════════════════════════════════════════════════════════════
                 TAB: Acomodaciones (units + types)
            ══════════════════════════════════════════════════════════════ -->
            <?php if ($has_acc): ?>
                <div class="tab-pane fade" id="t-units">

                    <?php if (empty($units)): ?>
                        <div class="empty-section">
                            <i class="bi bi-door-closed fs-2 d-block mb-2"></i>
                            No se detectaron habitaciones en la fuente.
                        </div>
                    <?php else: ?>
                        <!-- Types ocultos: se mandan tal cual al backend -->
                        <?php foreach ($types as $idx => $t): ?>
                            <input type="hidden" name="accommodation_types[<?= $idx ?>][name]"           value="<?= esc($t['name'] ?? '') ?>">
                            <input type="hidden" name="accommodation_types[<?= $idx ?>][description]"    value="<?= esc($t['description'] ?? '') ?>">
                            <input type="hidden" name="accommodation_types[<?= $idx ?>][base_capacity]"  value="<?= esc($t['base_capacity'] ?? 2) ?>">
                            <input type="hidden" name="accommodation_types[<?= $idx ?>][max_capacity]"   value="<?= esc($t['max_capacity']  ?? 2) ?>">
                            <input type="hidden" name="accommodation_types[<?= $idx ?>][include]"        value="1">
                        <?php endforeach ?>

                        <?php foreach ($units as $idx => $u):
                            $included = $u['include'] ?? true;
                            $beds     = $u['beds']      ?? [];
                            $amen     = $u['amenities'] ?? [];
                            $bedsCsv  = implode(', ', array_map(fn($b) => trim(($b['bed_type_name'] ?? '') . ($b['quantity'] ?? 1) > 1 ? ' (x' . ($b['quantity'] ?? 1) . ')' : ''), $beds));
                            // Más simple y editable: dejar el CSV de nombres de cama
                            $bedNames = array_map(fn($b) => $b['bed_type_name'] ?? '', $beds);
                            $amenCsv  = implode(', ', $amen);
                            ?>
                            <div class="item-card <?= $included ? '' : 'excluded' ?>" data-item="unit-<?= $idx ?>">
                                <div class="item-card-header">
                                    <?php if (!empty($u['image_url'])): ?>
                                        <div class="item-thumb" style="background-image:url('<?= esc($u['image_url']) ?>')"></div>
                                    <?php else: ?>
                                        <div class="item-thumb empty"><i class="bi bi-image"></i></div>
                                    <?php endif ?>
                                    <div class="form-check form-switch ms-auto">
                                        <input class="form-check-input include-toggle" type="checkbox"
                                               name="accommodation_units[<?= $idx ?>][include]" value="1"
                                               <?= $included ? 'checked' : '' ?>>
                                        <label class="form-check-label small">Importar</label>
                                    </div>
                                </div>

                                <div class="form-grid cols-3">
                                    <div>
                                        <div class="field-label">Nombre</div>
                                        <?= ed("accommodation_units[{$idx}][name]", $u['name'] ?? '') ?>
                                    </div>
                                    <div>
                                        <div class="field-label">Tipo</div>
                                        <?= ed("accommodation_units[{$idx}][type_name]", $u['type_name'] ?? '') ?>
                                    </div>
                                    <div>
                                        <div class="field-label">Modo</div>
                                        <select name="accommodation_units[<?= $idx ?>][mode]" class="form-select form-select-sm">
                                            <?php foreach (['simple','compound','child'] as $m): ?>
                                                <option value="<?= $m ?>" <?= ($u['mode'] ?? 'simple') === $m ? 'selected' : '' ?>><?= $m ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                </div>

                                <?php if (($u['mode'] ?? '') === 'child'): ?>
                                    <div class="mt-2">
                                        <div class="field-label">Nombre del padre (debe coincidir con otra unidad)</div>
                                        <?= ed("accommodation_units[{$idx}][parent_name]", $u['parent_name'] ?? '') ?>
                                    </div>
                                <?php else: ?>
                                    <input type="hidden" name="accommodation_units[<?= $idx ?>][parent_name]" value="<?= esc($u['parent_name'] ?? '') ?>">
                                <?php endif ?>

                                <div class="form-grid cols-3 mt-2">
                                    <div>
                                        <div class="field-label">Ocupación base</div>
                                        <?= ed("accommodation_units[{$idx}][base_occupancy]", $u['base_occupancy'] ?? 2, 'number', ['min' => 1]) ?>
                                    </div>
                                    <div>
                                        <div class="field-label">Ocupación máxima</div>
                                        <?= ed("accommodation_units[{$idx}][max_occupancy]", $u['max_occupancy'] ?? 2, 'number', ['min' => 1]) ?>
                                    </div>
                                    <div>
                                        <div class="field-label">Baños</div>
                                        <?= ed("accommodation_units[{$idx}][bathrooms]", $u['bathrooms'] ?? 1.0, 'number', ['step' => '0.5', 'min' => 0]) ?>
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <div class="field-label">Descripción</div>
                                    <?= ed("accommodation_units[{$idx}][description]", $u['description'] ?? '', 'textarea', ['rows' => 2]) ?>
                                </div>

                                <div class="form-grid cols-2 mt-2">
                                    <div>
                                        <div class="field-label">Camas (nombres separados por coma)</div>
                                        <?= ed("accommodation_units[{$idx}][beds]", implode(', ', $bedNames)) ?>
                                        <small class="text-muted">Ej: <em>Cama Queen, Cama Sencilla</em></small>
                                    </div>
                                    <div>
                                        <div class="field-label">Amenidades (separadas por coma)</div>
                                        <?= ed("accommodation_units[{$idx}][amenities]", $amenCsv) ?>
                                    </div>
                                </div>

                                <input type="hidden" name="accommodation_units[<?= $idx ?>][image_url]" value="<?= esc($u['image_url'] ?? '') ?>">
                            </div>
                        <?php endforeach ?>
                    <?php endif ?>
                </div>

                <!-- ══════════════════════════════════════════════════════════════
                     TAB: Tarifas
                ══════════════════════════════════════════════════════════════ -->
                <div class="tab-pane fade" id="t-rates">

                    <?php if (empty($plans) && empty($rates)): ?>
                        <div class="empty-section">
                            <i class="bi bi-currency-exchange fs-2 d-block mb-2"></i>
                            No se detectaron tarifas. Podrás configurarlas manualmente después.
                        </div>
                    <?php endif ?>

                    <?php if (!empty($plans)): ?>
                        <h6 class="text-muted small fw-bold text-uppercase">Planes tarifarios</h6>
                        <?php foreach ($plans as $idx => $p):
                            $included = $p['include'] ?? true;
                            $amenList = is_array($p['amenities'] ?? null) ? implode(', ', $p['amenities']) : '';
                            ?>
                            <div class="item-card <?= $included ? '' : 'excluded' ?>">
                                <div class="item-card-header">
                                    <strong><?= esc($p['name'] ?? '') ?></strong>
                                    <div class="form-check form-switch ms-auto">
                                        <input class="form-check-input include-toggle" type="checkbox"
                                               name="rate_plans[<?= $idx ?>][include]" value="1"
                                               <?= $included ? 'checked' : '' ?>>
                                        <label class="form-check-label small">Importar</label>
                                    </div>
                                </div>
                                <div class="form-grid cols-2">
                                    <div>
                                        <div class="field-label">Nombre</div>
                                        <?= ed("rate_plans[{$idx}][name]", $p['name'] ?? '') ?>
                                    </div>
                                    <div>
                                        <div class="field-label">Política de cancelación</div>
                                        <select name="rate_plans[<?= $idx ?>][cancellation_policy]" class="form-select form-select-sm">
                                            <?php foreach (['flexible','moderate','strict','non_refundable'] as $cp): ?>
                                                <option value="<?= $cp ?>" <?= ($p['cancellation_policy'] ?? 'flexible') === $cp ? 'selected' : '' ?>><?= $cp ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                    <div>
                                        <div class="field-label">Noches mínimas (default)</div>
                                        <?= ed("rate_plans[{$idx}][min_nights_default]", $p['min_nights_default'] ?? 1, 'number', ['min' => 1]) ?>
                                    </div>
                                    <div>
                                        <div class="field-label">¿Es plan por defecto?</div>
                                        <select name="rate_plans[<?= $idx ?>][is_default]" class="form-select form-select-sm">
                                            <option value="1" <?= !empty($p['is_default']) ? 'selected' : '' ?>>Sí</option>
                                            <option value="0" <?= empty($p['is_default'])  ? 'selected' : '' ?>>No</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="field-label">Amenidades incluidas (keys separadas por coma)</div>
                                    <?= ed("rate_plans[{$idx}][amenities]", $amenList) ?>
                                    <small class="text-muted">
                                        Keys válidas: breakfast, lunch, dinner, all_inclusive, airport_transfer, late_checkout, free_cancellation, non_refundable, wifi_premium, parking
                                    </small>
                                </div>
                                <div class="mt-2">
                                    <div class="field-label">Descripción</div>
                                    <?= ed("rate_plans[{$idx}][description]", $p['description'] ?? '', 'textarea', ['rows' => 2]) ?>
                                </div>
                            </div>
                        <?php endforeach ?>
                    <?php endif ?>

                    <?php if (!empty($rates)): ?>
                        <h6 class="text-muted small fw-bold text-uppercase mt-4">Precios por unidad</h6>
                        <?php foreach ($rates as $idx => $r):
                            $included = $r['include'] ?? true;
                            $conf     = $r['price_confidence'] ?? 'null';
                            ?>
                            <div class="item-card <?= $included ? '' : 'excluded' ?>">
                                <div class="item-card-header">
                                    <strong>
                                        <?= esc($r['unit_name'] ?? '') ?> · <?= esc($r['rate_plan_name'] ?? '') ?>
                                    </strong>
                                    <span class="confidence-badge confidence-<?= esc($conf) ?> ms-2"><?= esc($conf) ?></span>
                                    <div class="form-check form-switch ms-auto">
                                        <input class="form-check-input include-toggle" type="checkbox"
                                               name="unit_rates[<?= $idx ?>][include]" value="1"
                                               <?= $included ? 'checked' : '' ?>>
                                        <label class="form-check-label small">Importar</label>
                                    </div>
                                </div>
                                <div class="form-grid cols-4">
                                    <div>
                                        <div class="field-label">Unidad (debe coincidir)</div>
                                        <?= ed("unit_rates[{$idx}][unit_name]", $r['unit_name'] ?? '') ?>
                                    </div>
                                    <div>
                                        <div class="field-label">Plan (debe coincidir)</div>
                                        <?= ed("unit_rates[{$idx}][rate_plan_name]", $r['rate_plan_name'] ?? '') ?>
                                    </div>
                                    <div>
                                        <div class="field-label">Precio / noche</div>
                                        <?= ed("unit_rates[{$idx}][price_per_night]", $r['price_per_night'] ?? 0, 'number', ['step' => '0.01']) ?>
                                    </div>
                                    <div>
                                        <div class="field-label">Mín. noches</div>
                                        <?= ed("unit_rates[{$idx}][min_nights]", $r['min_nights'] ?? 1, 'number', ['min' => 1]) ?>
                                    </div>
                                    <div>
                                        <div class="field-label">Persona extra</div>
                                        <?= ed("unit_rates[{$idx}][extra_person_price]", $r['extra_person_price'] ?? 0, 'number', ['step' => '0.01']) ?>
                                    </div>
                                    <div>
                                        <div class="field-label">Niño extra</div>
                                        <?= ed("unit_rates[{$idx}][extra_child_price]", $r['extra_child_price'] ?? 0, 'number', ['step' => '0.01']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    <?php endif ?>
                </div>
            <?php endif ?>

            <!-- ══════════════════════════════════════════════════════════════
                 TAB: Tours
            ══════════════════════════════════════════════════════════════ -->
            <?php if ($has_tours): ?>
                <div class="tab-pane fade" id="t-tours">
                    <?php if (empty($tours)): ?>
                        <div class="empty-section">
                            <i class="bi bi-compass fs-2 d-block mb-2"></i>
                            No se detectaron tours.
                        </div>
                    <?php else: ?>
                        <?php foreach ($tours as $idx => $t):
                            $included = $t['include'] ?? true;
                            $conf     = $t['price_confidence'] ?? 'null';
                            $incCsv   = is_array($t['included'] ?? null) ? implode(', ', $t['included']) : '';
                            $excCsv   = is_array($t['excluded'] ?? null) ? implode(', ', $t['excluded']) : '';
                            ?>
                            <div class="item-card <?= $included ? '' : 'excluded' ?>">
                                <div class="item-card-header">
                                    <?php if (!empty($t['image_url'])): ?>
                                        <div class="item-thumb" style="background-image:url('<?= esc($t['image_url']) ?>')"></div>
                                    <?php else: ?>
                                        <div class="item-thumb empty"><i class="bi bi-compass"></i></div>
                                    <?php endif ?>
                                    <strong><?= esc($t['name'] ?? '') ?></strong>
                                    <span class="confidence-badge confidence-<?= esc($conf) ?> ms-2"><?= esc($conf) ?></span>
                                    <div class="form-check form-switch ms-auto">
                                        <input class="form-check-input include-toggle" type="checkbox"
                                               name="tours[<?= $idx ?>][include]" value="1"
                                               <?= $included ? 'checked' : '' ?>>
                                        <label class="form-check-label small">Importar</label>
                                    </div>
                                </div>
                                <div class="form-grid cols-2">
                                    <div>
                                        <div class="field-label">Nombre</div>
                                        <?= ed("tours[{$idx}][name]", $t['name'] ?? '') ?>
                                    </div>
                                    <div>
                                        <div class="field-label">Punto de encuentro</div>
                                        <?= ed("tours[{$idx}][meeting_point]", $t['meeting_point'] ?? '') ?>
                                    </div>
                                </div>
                                <div class="form-grid cols-4 mt-2">
                                    <div>
                                        <div class="field-label">Duración (min)</div>
                                        <?= ed("tours[{$idx}][duration_minutes]", $t['duration_minutes'] ?? 60, 'number', ['min' => 1]) ?>
                                    </div>
                                    <div>
                                        <div class="field-label">Mín. pax</div>
                                        <?= ed("tours[{$idx}][min_pax]", $t['min_pax'] ?? 1, 'number', ['min' => 1]) ?>
                                    </div>
                                    <div>
                                        <div class="field-label">Precio adulto</div>
                                        <?= ed("tours[{$idx}][price_adult]", $t['price_adult'] ?? 0, 'number', ['step' => '0.01']) ?>
                                    </div>
                                    <div>
                                        <div class="field-label">Precio niño</div>
                                        <?= ed("tours[{$idx}][price_child]", $t['price_child'] ?? 0, 'number', ['step' => '0.01']) ?>
                                    </div>
                                </div>
                                <div class="form-grid cols-2 mt-2">
                                    <div>
                                        <div class="field-label">Dificultad</div>
                                        <select name="tours[<?= $idx ?>][difficulty_level]" class="form-select form-select-sm">
                                            <?php foreach (['easy','moderate','hard'] as $d): ?>
                                                <option value="<?= $d ?>" <?= ($t['difficulty_level'] ?? 'easy') === $d ? 'selected' : '' ?>><?= $d ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                    <div>
                                        <div class="field-label">Cancelación</div>
                                        <select name="tours[<?= $idx ?>][cancellation_policy]" class="form-select form-select-sm">
                                            <?php foreach (['flexible','moderate','strict','non_refundable'] as $cp): ?>
                                                <option value="<?= $cp ?>" <?= ($t['cancellation_policy'] ?? 'flexible') === $cp ? 'selected' : '' ?>><?= $cp ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="field-label">Descripción</div>
                                    <?= ed("tours[{$idx}][description]", $t['description'] ?? '', 'textarea', ['rows' => 2]) ?>
                                </div>
                                <div class="form-grid cols-2 mt-2">
                                    <div>
                                        <div class="field-label">Incluye (separado por coma)</div>
                                        <?= ed("tours[{$idx}][included]", $incCsv) ?>
                                    </div>
                                    <div>
                                        <div class="field-label">No incluye (separado por coma)</div>
                                        <?= ed("tours[{$idx}][excluded]", $excCsv) ?>
                                    </div>
                                </div>
                                <input type="hidden" name="tours[<?= $idx ?>][image_url]" value="<?= esc($t['image_url'] ?? '') ?>">
                            </div>
                        <?php endforeach ?>
                    <?php endif ?>
                </div>
            <?php endif ?>

            <!-- ══════════════════════════════════════════════════════════════
                 TAB: Productos
            ══════════════════════════════════════════════════════════════ -->
            <div class="tab-pane fade" id="t-products">
                <?php if (empty($products)): ?>
                    <div class="empty-section">
                        <i class="bi bi-box fs-2 d-block mb-2"></i>
                        No se detectaron productos o servicios adicionales.
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $idx => $p):
                        $included = $p['include'] ?? true;
                        $conf     = $p['price_confidence'] ?? 'null';
                        ?>
                        <div class="item-card <?= $included ? '' : 'excluded' ?>">
                            <div class="item-card-header">
                                <?php if (!empty($p['image_url'])): ?>
                                    <div class="item-thumb" style="background-image:url('<?= esc($p['image_url']) ?>')"></div>
                                <?php else: ?>
                                    <div class="item-thumb empty"><i class="bi bi-box"></i></div>
                                <?php endif ?>
                                <strong><?= esc($p['name'] ?? '') ?></strong>
                                <span class="confidence-badge confidence-<?= esc($conf) ?> ms-2"><?= esc($conf) ?></span>
                                <div class="form-check form-switch ms-auto">
                                    <input class="form-check-input include-toggle" type="checkbox"
                                           name="products[<?= $idx ?>][include]" value="1"
                                           <?= $included ? 'checked' : '' ?>>
                                    <label class="form-check-label small">Importar</label>
                                </div>
                            </div>
                            <div class="form-grid cols-3">
                                <div>
                                    <div class="field-label">Nombre</div>
                                    <?= ed("products[{$idx}][name]", $p['name'] ?? '') ?>
                                </div>
                                <div>
                                    <div class="field-label">Categoría</div>
                                    <?= ed("products[{$idx}][category]", $p['category'] ?? 'General') ?>
                                </div>
                                <div>
                                    <div class="field-label">Precio</div>
                                    <?= ed("products[{$idx}][unit_price]", $p['unit_price'] ?? 0, 'number', ['step' => '0.01']) ?>
                                </div>
                            </div>
                            <div class="mt-2">
                                <div class="field-label">Descripción</div>
                                <?= ed("products[{$idx}][description]", $p['description'] ?? '', 'textarea', ['rows' => 2]) ?>
                            </div>
                            <input type="hidden" name="products[<?= $idx ?>][image_url]" value="<?= esc($p['image_url'] ?? '') ?>">
                        </div>
                    <?php endforeach ?>
                <?php endif ?>
            </div>

        </div>

        <!-- ══════════════════════════════════════════════════════════════
             FOOTER
        ══════════════════════════════════════════════════════════════ -->
        <div class="sticky-footer d-flex justify-content-between align-items-center">
            <a href="/onboarding/import" class="text-decoration-none text-muted">
                <i class="bi bi-arrow-left"></i> Cancelar y empezar de nuevo
            </a>
            <button type="submit" class="btn-primary-wiz" id="btnConfirm">
                <i class="bi bi-check-lg me-1"></i> Aplicar al PMS
            </button>
        </div>

    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle visual de exclusión
    document.querySelectorAll('.include-toggle').forEach(cb => {
        cb.addEventListener('change', () => {
            cb.closest('.item-card').classList.toggle('excluded', !cb.checked);
        });
    });

    // Submit con loading
    document.getElementById('reviewForm').addEventListener('submit', () => {
        const btn = document.getElementById('btnConfirm');
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Aplicando...';
    });
</script>

</body>
</html>
