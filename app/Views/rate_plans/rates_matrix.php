<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <style>
        :root {
            --m-bg:      #f4f5f7;
            --m-surface: #ffffff;
            --m-border:  #e4e7ec;
            --m-text:    #111827;
            --m-sub:     #6b7280;
            --m-muted:   #9ca3af;
            --m-blue:    #1d4ed8;
            --m-blue-lt: #eff6ff;
            --m-green:   #059669;
            --m-amber:   #d97706;
            --m-red:     #dc2626;
            --radius:    12px;
            --shadow:    0 1px 4px rgba(0,0,0,.07), 0 0 0 1px rgba(0,0,0,.04);
        }

        /* ── Header ── */
        .mx-header {
            display: flex; align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 24px; gap: 12px; flex-wrap: wrap;
        }
        .mx-header-left { display: flex; align-items: center; gap: 12px; }
        .mx-header-left h1 {
            font-size: 20px; font-weight: 700;
            color: var(--m-text); margin: 0; letter-spacing: -.02em;
        }
        .btn-back {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 12px; border: 1.5px solid var(--m-border);
            border-radius: 8px; background: #fff; color: var(--m-sub);
            font-size: 13px; font-weight: 500; text-decoration: none;
            transition: all .15s;
        }
        .btn-back:hover { border-color: #9ca3af; color: var(--m-text); }

        .btn-save {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 10px 20px; background: var(--m-green);
            color: #fff; border: none; border-radius: 8px;
            font-size: 14px; font-weight: 700; cursor: pointer;
            transition: background .15s;
        }
        .btn-save:hover { background: #047857; }

        /* ── Tabla container ── */
        .mx-card {
            background: var(--m-surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .mx-scroll { overflow-x: auto; }
        .mx-scroll::-webkit-scrollbar { height: 6px; }
        .mx-scroll::-webkit-scrollbar-thumb { background: var(--m-border); border-radius: 3px; }

        /* ── Tabla ── */
        .mx-table {
            border-collapse: collapse;
            width: 100%;
            min-width: 600px;
        }

        /* Columna habitación */
        .mx-col-unit { width: 200px; min-width: 180px; }

        /* Columna pax base */
        .mx-col-pax { width: 72px; }

        /* Columna de plan */
        .mx-col-plan { min-width: 220px; }

        /* ── Headers ── */
        .mx-th {
            padding: 14px 16px;
            background: #1e293b;
            color: #fff;
            font-size: 12px; font-weight: 700;
            text-align: left;
            border-right: 1px solid #334155;
            position: sticky; top: 0; z-index: 2;
        }
        .mx-th:last-child { border-right: none; }
        .mx-th-unit { text-align: left; }
        .mx-th-pax  { text-align: center; }
        .mx-th-plan { text-align: center; }

        /* Plan header content */
        .plan-th-name {
            font-size: 13px; font-weight: 700;
            margin-bottom: 6px; letter-spacing: -.01em;
        }
        .plan-th-chips {
            display: flex; flex-wrap: wrap;
            gap: 4px; justify-content: center;
            margin-bottom: 4px;
        }
        .plan-th-chip {
            display: inline-flex; align-items: center; gap: 3px;
            padding: 2px 6px; border-radius: 20px;
            font-size: 10px; font-weight: 600;
            background: rgba(255,255,255,.15);
            color: rgba(255,255,255,.9);
        }
        .plan-th-cancel {
            font-size: 10px; color: rgba(255,255,255,.6);
            text-align: center;
        }
        .plan-th-min {
            font-size: 10px; color: rgba(255,255,255,.55);
            margin-top: 2px;
        }

        /* ── Filas ── */
        .mx-tr { border-bottom: 1px solid var(--m-border); }
        .mx-tr:last-child { border-bottom: none; }
        .mx-tr:hover .mx-td { background: #f8fafc; }
        .mx-tr.is-child:hover .mx-td { background: #f0f4ff; }

        .mx-td {
            padding: 10px 12px;
            border-right: 1px solid var(--m-border);
            vertical-align: middle;
            transition: background .1s;
        }
        .mx-td:last-child { border-right: none; }

        /* Celda habitación */
        .unit-name {
            font-size: 13px; font-weight: 700;
            color: var(--m-text);
        }
        .unit-name.is-child {
            font-weight: 500; color: var(--m-sub);
            padding-left: 16px;
            border-left: 2px solid #cbd5e1;
        }
        .unit-sub { font-size: 11px; color: var(--m-muted); margin-top: 2px; }

        /* Celda pax base */
        .pax-badge {
            display: flex; align-items: center; justify-content: center;
            gap: 4px; font-size: 13px; font-weight: 700;
            color: var(--m-sub);
        }

        /* ── Celda de precios ── */
        .price-cell { padding: 8px 10px !important; }
        .price-cell-inner { display: flex; flex-direction: column; gap: 5px; }

        .price-row {
            display: flex; align-items: center; gap: 6px;
        }
        .price-icon {
            width: 22px; height: 22px;
            border-radius: 6px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px;
        }
        .pi-base   { background: #eff6ff; color: var(--m-blue); }
        .pi-adult  { background: #f0fdf4; color: var(--m-green); }
        .pi-child  { background: #fef3c7; color: var(--m-amber); }
        .pi-nights { background: #f5f3ff; color: #7c3aed; }

        .price-input {
            flex: 1;
            border: 1.5px solid var(--m-border);
            border-radius: 7px;
            padding: 5px 8px;
            font-size: 12.5px; font-weight: 600;
            color: var(--m-text);
            text-align: right;
            transition: border-color .15s, box-shadow .15s;
            width: 100%;
            background: #fff;
        }
        .price-input:focus {
            border-color: var(--m-blue);
            box-shadow: 0 0 0 3px rgba(29,78,216,.1);
            outline: none;
        }
        .price-input.base-input {
            border-color: #bfdbfe;
            background: var(--m-blue-lt);
        }
        .price-input.base-input:focus {
            border-color: var(--m-blue);
        }
        .price-input::placeholder { color: var(--m-muted); font-weight: 400; }

        /* Celda vacía (sin tarifa base configurada) */
        .cell-empty .price-input.base-input {
            border-color: #fecdd3;
            background: #fef2f2;
        }

        /* ── Footer ── */
        .mx-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--m-border);
            background: #f8fafc;
            display: flex; align-items: center;
            justify-content: space-between; flex-wrap: wrap; gap: 12px;
        }
        .mx-footer-note {
            font-size: 12px; color: var(--m-sub);
            display: flex; align-items: center; gap: 6px;
        }

        /* ── Toast de guardado ── */
        .save-toast {
            position: fixed; bottom: 24px; right: 24px; z-index: 999;
            background: #1e293b; color: #fff;
            padding: 12px 18px; border-radius: 10px;
            font-size: 13px; font-weight: 600;
            display: flex; align-items: center; gap: 8px;
            box-shadow: 0 8px 24px rgba(0,0,0,.2);
            transform: translateY(80px); opacity: 0;
            transition: all .3s cubic-bezier(.34,1.56,.64,1);
            pointer-events: none;
        }
        .save-toast.show { transform: translateY(0); opacity: 1; }
        .save-toast i { color: #4ade80; font-size: 16px; }

        /* ── Alert ── */
        .mx-alert {
            padding: 12px 16px; border-radius: 8px;
            margin-bottom: 20px; font-size: 13px;
            display: flex; align-items: center; gap: 8px;
        }
        .mx-alert-success { background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46; }
    </style>

    <!-- Header -->
    <div class="mx-header">
        <div class="mx-header-left">
            <a href="<?= base_url('/rate-plans') ?>" class="btn-back">
                <i class="bi bi-arrow-left"></i> Planes
            </a>
            <h1>Matriz de Precios</h1>
        </div>
        <button type="submit" form="matrix-form" class="btn-save">
            <i class="bi bi-floppy-fill"></i> Guardar Tarifario
        </button>
    </div>

<?php if (session('success')): ?>
    <div class="mx-alert mx-alert-success">
        <i class="bi bi-check-circle-fill"></i> <?= session('success') ?>
    </div>
<?php endif; ?>

<?php
$cancelLabels = [
    'flexible'       => 'Flexible',
    'moderate'       => 'Moderada',
    'strict'         => 'Estricta',
    'non_refundable' => 'No Reembolsable',
];
?>

    <div class="mx-card">
        <form action="<?= base_url('/rate-plans/update-matrix') ?>" method="post" id="matrix-form">
            <?= csrf_field() ?>

            <div class="mx-scroll">
                <table class="mx-table">
                    <thead>
                    <tr>
                        <th class="mx-th mx-th-unit mx-col-unit">Alojamiento</th>
                        <th class="mx-th mx-th-pax mx-col-pax" title="Capacidad base incluida en la tarifa">
                            <i class="bi bi-people"></i> Base
                        </th>
                        <?php foreach ($plans as $plan):
                            $planAmens = is_array($plan['amenities_json'])
                                ? $plan['amenities_json']
                                : (json_decode($plan['amenities_json'] ?? '{}', true) ?? []);
                            $activeAmens = array_keys(array_filter($planAmens));
                            $cancelLabel = $cancelLabels[$plan['cancellation_policy']] ?? 'Flexible';
                            $minN = $plan['min_nights_default'] ?? 1;
                            ?>
                            <th class="mx-th mx-th-plan mx-col-plan">
                                <div class="plan-th-name"><?= esc($plan['name']) ?></div>
                                <?php if (!empty($activeAmens)): ?>
                                    <div class="plan-th-chips">
                                        <?php foreach (array_slice($activeAmens, 0, 4) as $ak):
                                            if (!isset($amenities[$ak])) continue;
                                            ?>
                                            <span class="plan-th-chip" title="<?= $amenities[$ak]['label'] ?>">
                                            <i class="bi <?= $amenities[$ak]['icon'] ?>"></i>
                                            <?= $amenities[$ak]['label'] ?>
                                        </span>
                                        <?php endforeach; ?>
                                        <?php if (count($activeAmens) > 4): ?>
                                            <span class="plan-th-chip">+<?= count($activeAmens) - 4 ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="plan-th-chips">
                                        <span class="plan-th-chip"><i class="bi bi-house"></i> Solo alojamiento</span>
                                    </div>
                                <?php endif; ?>
                                <div class="plan-th-cancel">
                                    <i class="bi bi-shield"></i> <?= $cancelLabel ?>
                                </div>
                                <?php if ($minN > 1): ?>
                                    <div class="plan-th-min">Mín. <?= $minN ?> noches</div>
                                <?php endif; ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($units)): ?>
                        <tr>
                            <td colspan="<?= count($plans) + 2 ?>"
                                style="padding:40px;text-align:center;color:var(--m-muted);font-size:13px;">
                                <i class="bi bi-building" style="font-size:24px;display:block;margin-bottom:8px;opacity:.4;"></i>
                                No hay unidades de alojamiento configuradas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($units as $unit):
                            $isChild = !empty($unit['parent_id']);
                            $baseOcc = $unit['base_occupancy'] ?? 2;
                            ?>
                            <tr class="mx-tr <?= $isChild ? 'is-child' : '' ?>">

                                <!-- Habitación -->
                                <td class="mx-td">
                                    <div class="unit-name <?= $isChild ? 'is-child' : '' ?>">
                                        <?php if ($isChild): ?>
                                            <i class="bi bi-arrow-return-right"
                                               style="font-size:11px;margin-right:4px;color:var(--m-muted)"></i>
                                        <?php endif; ?>
                                        <?= esc($unit['name']) ?>
                                    </div>
                                    <?php if (!$isChild): ?>
                                        <div class="unit-sub"><?= esc($unit['type_name'] ?? '') ?></div>
                                    <?php endif; ?>
                                </td>

                                <!-- Pax base -->
                                <td class="mx-td" style="text-align:center;">
                                    <div class="pax-badge">
                                        <?= $baseOcc ?> <i class="bi bi-person-fill" style="font-size:12px;"></i>
                                    </div>
                                </td>

                                <!-- Celdas de precio por plan -->
                                <?php foreach ($plans as $plan):
                                    $rateInfo  = $ratesMatrix[$unit['id']][$plan['id']] ?? null;
                                    $basePrice = $rateInfo ? $rateInfo['price_per_night']    : '';
                                    $adultPrice= $rateInfo ? $rateInfo['extra_person_price'] : '';
                                    $childPrice= $rateInfo ? ($rateInfo['extra_child_price'] ?? '') : '';
                                    $minN      = $rateInfo ? ($rateInfo['min_nights'] ?? 1)  : 1;
                                    $isEmpty   = $basePrice === '' || $basePrice == 0;
                                    $uid = $unit['id']; $pid = $plan['id'];
                                    ?>
                                    <td class="mx-td price-cell <?= $isEmpty ? 'cell-empty' : '' ?>">
                                        <div class="price-cell-inner">

                                            <!-- Tarifa base -->
                                            <div class="price-row">
                                                <div class="price-icon pi-base" title="Tarifa base de la habitación">
                                                    <i class="bi bi-house-door-fill"></i>
                                                </div>
                                                <input type="number" step="0.01" min="0"
                                                       class="price-input base-input"
                                                       name="prices[<?= $uid ?>][<?= $pid ?>][base]"
                                                       value="<?= $basePrice ?>"
                                                       placeholder="Base / noche">
                                            </div>

                                            <!-- Adulto extra -->
                                            <div class="price-row">
                                                <div class="price-icon pi-adult" title="Cobro por adulto extra">
                                                    <i class="bi bi-person-plus-fill"></i>
                                                </div>
                                                <input type="number" step="0.01" min="0"
                                                       class="price-input"
                                                       name="prices[<?= $uid ?>][<?= $pid ?>][adult]"
                                                       value="<?= $adultPrice ?>"
                                                       placeholder="Adulto extra">
                                            </div>

                                            <!-- Niño extra -->
                                            <div class="price-row">
                                                <div class="price-icon pi-child" title="Cobro por niño extra">
                                                    <i class="bi bi-emoji-smile-fill"></i>
                                                </div>
                                                <input type="number" step="0.01" min="0"
                                                       class="price-input"
                                                       name="prices[<?= $uid ?>][<?= $pid ?>][child]"
                                                       value="<?= $childPrice ?>"
                                                       placeholder="Niño extra">
                                            </div>

                                            <!-- Noches mínimas por celda -->
                                            <div class="price-row">
                                                <div class="price-icon pi-nights" title="Noches mínimas para esta combinación">
                                                    <i class="bi bi-moon-stars-fill"></i>
                                                </div>
                                                <input type="number" step="1" min="1" max="30"
                                                       class="price-input"
                                                       name="prices[<?= $uid ?>][<?= $pid ?>][min_nights]"
                                                       value="<?= $minN ?>"
                                                       placeholder="Mín. noches">
                                            </div>

                                        </div>
                                    </td>
                                <?php endforeach; ?>

                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="mx-footer">
                <div class="mx-footer-note">
                    <i class="bi bi-info-circle"></i>
                    Las celdas en <span style="background:#fef2f2;border:1px solid #fecdd3;padding:1px 6px;border-radius:4px;font-size:11px;">rojo</span>
                    no tienen tarifa base configurada — esa combinación no estará disponible para reservar.
                </div>
                <button type="submit" class="btn-save">
                    <i class="bi bi-floppy-fill"></i> Guardar Tarifario
                </button>
            </div>

        </form>
    </div>

    <!-- Toast -->
    <div class="save-toast" id="save-toast">
        <i class="bi bi-check-circle-fill"></i> Cambios guardados
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Resaltar celda al editar
            document.querySelectorAll('.price-input').forEach(input => {
                input.addEventListener('focus', () => {
                    input.closest('tr').querySelectorAll('.mx-td').forEach(td => {
                        td.style.background = '#f0f7ff';
                    });
                });
                input.addEventListener('blur', () => {
                    input.closest('tr').querySelectorAll('.mx-td').forEach(td => {
                        td.style.background = '';
                    });
                    // Marcar celda vacía/llena dinámicamente
                    const cell = input.closest('.price-cell');
                    if (!cell) return;
                    const baseInput = cell.querySelector('.base-input');
                    if (baseInput) {
                        cell.classList.toggle('cell-empty', !baseInput.value || parseFloat(baseInput.value) <= 0);
                    }
                });
            });

            // Toast al guardar
            document.getElementById('matrix-form').addEventListener('submit', () => {
                const toast = document.getElementById('save-toast');
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 2500);
            });

            // Keyboard nav: Enter avanza al siguiente input
            const inputs = Array.from(document.querySelectorAll('.price-input'));
            inputs.forEach((inp, i) => {
                inp.addEventListener('keydown', e => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const next = inputs[i + 1];
                        if (next) next.focus();
                    }
                });
            });
        });
    </script>

<?= $this->endSection() ?>