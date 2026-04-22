<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <style>
        /* ══════════════════════════════════════════
           RESERVATIONS INDEX — Design System
        ══════════════════════════════════════════ */
        :root {
            --ri-bg:       #f0f2f5;
            --ri-surface:  #ffffff;
            --ri-border:   #e4e7ec;
            --ri-text:     #0f172a;
            --ri-sub:      #64748b;
            --ri-muted:    #94a3b8;

            --ri-blue:     #1d4ed8;
            --ri-blue-lt:  #eff6ff;
            --ri-green:    #059669;
            --ri-green-lt: #ecfdf5;
            --ri-amber:    #d97706;
            --ri-amber-lt: #fffbeb;
            --ri-red:      #dc2626;
            --ri-red-lt:   #fef2f2;
            --ri-slate:    #475569;
            --ri-slate-lt: #f8fafc;

            --shadow:    0 1px 3px rgba(0,0,0,.07), 0 0 0 1px rgba(0,0,0,.04);
            --shadow-md: 0 4px 16px rgba(0,0,0,.10);
            --radius:    12px;
            --radius-sm: 8px;
        }

        /* ── Header ── */
        .ri-header {
            display: flex; justify-content: space-between;
            align-items: flex-start; margin-bottom: 20px;
            flex-wrap: wrap; gap: 12px;
        }
        .ri-header h1 {
            font-size: 21px; font-weight: 800;
            color: var(--ri-text); margin: 0;
            letter-spacing: -.03em;
        }
        .ri-header p { font-size: 13px; color: var(--ri-sub); margin: 3px 0 0; }

        .btn-new {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 18px; background: var(--ri-blue);
            color: #fff; border: none; border-radius: var(--radius-sm);
            font-size: 13.5px; font-weight: 700; text-decoration: none;
            transition: background .15s, transform .1s;
            white-space: nowrap;
        }
        .btn-new:hover  { background: #1e40af; color: #fff; }
        .btn-new:active { transform: scale(.98); }

        /* ── Chips de resumen ── */
        .summary-chips {
            display: flex; gap: 10px; flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .s-chip {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 16px;
            background: var(--ri-surface);
            border: 1.5px solid var(--ri-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-decoration: none; cursor: pointer;
            transition: all .15s; flex: 1; min-width: 140px;
            animation: fadeUp .3s ease both;
        }
        .s-chip:nth-child(1) { animation-delay: .05s; }
        .s-chip:nth-child(2) { animation-delay: .10s; }
        .s-chip:nth-child(3) { animation-delay: .15s; }
        .s-chip:nth-child(4) { animation-delay: .20s; }
        .s-chip:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }
        .s-chip.active { border-width: 2px; }

        .s-chip-icon {
            width: 36px; height: 36px; border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }
        .s-chip-body { min-width: 0; }
        .s-chip-num {
            font-size: 22px; font-weight: 800;
            line-height: 1; letter-spacing: -.04em;
        }
        .s-chip-label { font-size: 11.5px; font-weight: 500; color: var(--ri-sub); margin-top: 1px; }

        /* chip colores */
        .sc-green  .s-chip-icon { background: var(--ri-green-lt); color: var(--ri-green); }
        .sc-green  .s-chip-num  { color: var(--ri-green); }
        .sc-green.active { border-color: var(--ri-green); background: var(--ri-green-lt); }

        .sc-blue   .s-chip-icon { background: var(--ri-blue-lt);  color: var(--ri-blue);  }
        .sc-blue   .s-chip-num  { color: var(--ri-blue);  }
        .sc-blue.active { border-color: var(--ri-blue);  background: var(--ri-blue-lt); }

        .sc-amber  .s-chip-icon { background: var(--ri-amber-lt); color: var(--ri-amber); }
        .sc-amber  .s-chip-num  { color: var(--ri-amber); }
        .sc-amber.active { border-color: var(--ri-amber); background: var(--ri-amber-lt); }

        .sc-slate  .s-chip-icon { background: var(--ri-slate-lt); color: var(--ri-slate); }
        .sc-slate  .s-chip-num  { color: var(--ri-slate); }
        .sc-slate.active { border-color: var(--ri-slate); }

        /* ── Barra de filtros ── */
        .filter-bar {
            background: var(--ri-surface);
            border: 1px solid var(--ri-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 14px 16px;
            margin-bottom: 16px;
            display: flex; gap: 10px; flex-wrap: wrap;
            align-items: center;
            animation: fadeUp .35s ease .25s both;
        }
        .filter-search {
            flex: 1; min-width: 200px;
            display: flex; align-items: center; gap: 8px;
            border: 1.5px solid var(--ri-border);
            border-radius: var(--radius-sm);
            padding: 7px 12px;
            background: var(--ri-slate-lt);
            transition: border-color .15s;
        }
        .filter-search:focus-within { border-color: var(--ri-blue); background: #fff; }
        .filter-search i { color: var(--ri-muted); font-size: 14px; flex-shrink: 0; }
        .filter-search input {
            border: none; background: transparent;
            font-size: 13.5px; color: var(--ri-text);
            width: 100%; outline: none;
        }
        .filter-search input::placeholder { color: var(--ri-muted); }

        .filter-select {
            border: 1.5px solid var(--ri-border);
            border-radius: var(--radius-sm);
            padding: 7px 10px;
            font-size: 13px; color: var(--ri-text);
            background: var(--ri-slate-lt);
            cursor: pointer; outline: none;
            transition: border-color .15s;
            min-width: 120px;
        }
        .filter-select:focus { border-color: var(--ri-blue); background: #fff; }

        .filter-date {
            border: 1.5px solid var(--ri-border);
            border-radius: var(--radius-sm);
            padding: 7px 10px;
            font-size: 13px; color: var(--ri-text);
            background: var(--ri-slate-lt);
            cursor: pointer; outline: none;
            transition: border-color .15s;
        }
        .filter-date:focus { border-color: var(--ri-blue); }

        .btn-filter-clear {
            padding: 7px 12px; border-radius: var(--radius-sm);
            border: 1.5px solid var(--ri-border);
            background: #fff; color: var(--ri-sub);
            font-size: 12.5px; font-weight: 600;
            cursor: pointer; white-space: nowrap;
            text-decoration: none;
            display: inline-flex; align-items: center; gap: 5px;
            transition: all .15s;
        }
        .btn-filter-clear:hover { border-color: #9ca3af; color: var(--ri-text); }

        /* ── Tabla ── */
        .ri-card {
            background: var(--ri-surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            animation: fadeUp .4s ease .3s both;
        }
        .ri-card-header {
            padding: 13px 20px;
            border-bottom: 1px solid var(--ri-border);
            display: flex; align-items: center;
            justify-content: space-between;
        }
        .ri-card-header span {
            font-size: 12.5px; font-weight: 700; color: var(--ri-sub);
        }
        .ri-count {
            font-size: 12px; color: var(--ri-muted);
        }

        .ri-table { width: 100%; border-collapse: collapse; }

        .ri-table thead th {
            padding: 10px 14px;
            font-size: 11px; font-weight: 700;
            color: var(--ri-muted); text-transform: uppercase;
            letter-spacing: .06em; text-align: left;
            border-bottom: 1px solid var(--ri-border);
            background: var(--ri-slate-lt);
            white-space: nowrap;
        }
        .ri-table thead th.text-right { text-align: right; }

        /* ── Filas ── */
        .ri-row {
            border-bottom: 1px solid var(--ri-border);
            transition: background .1s;
            animation: fadeUp .3s ease both;
        }
        .ri-row:last-child { border-bottom: none; }
        .ri-row:hover { background: #f8fafc; }

        /* Borde izquierdo por estado */
        .ri-row td:first-child { border-left: 3px solid transparent; }
        .ri-row.s-checked_in  td:first-child { border-left-color: var(--ri-green); }
        .ri-row.s-confirmed   td:first-child { border-left-color: var(--ri-blue); }
        .ri-row.s-pending     td:first-child { border-left-color: var(--ri-amber); }
        .ri-row.s-checked_out td:first-child { border-left-color: var(--ri-border); }
        .ri-row.s-cancelled   td:first-child { border-left-color: var(--ri-red); }

        .ri-row.arriving-today { background: #fffbf0; }
        .ri-row.departing-today { background: #f0fdf8; }

        .ri-td { padding: 11px 14px; vertical-align: middle; }

        /* Celda huésped */
        .guest-name {
            font-size: 13.5px; font-weight: 700; color: var(--ri-text);
            display: flex; align-items: center; gap: 7px;
        }
        .guest-avatar {
            width: 28px; height: 28px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 800; color: #fff;
            flex-shrink: 0;
        }
        .guest-doc { font-size: 11.5px; color: var(--ri-muted); margin-top: 2px; }

        /* Celda habitación */
        .unit-name { font-size: 13px; font-weight: 600; color: var(--ri-text); }
        .unit-pax  { font-size: 11px; color: var(--ri-muted); margin-top: 2px; }

        /* Celda fechas */
        .date-range { font-size: 12.5px; color: var(--ri-sub); }
        .date-range strong { color: var(--ri-text); font-weight: 700; }
        .nights-chip {
            display: inline-flex; align-items: center; gap: 3px;
            background: var(--ri-slate-lt);
            padding: 2px 7px; border-radius: 20px;
            font-size: 11px; font-weight: 600; color: var(--ri-sub);
            margin-top: 4px;
        }

        /* Celda monto */
        .amount { font-size: 14px; font-weight: 800; color: var(--ri-text); text-align: right; }
        .source-dot {
            display: inline-block; width: 8px; height: 8px;
            border-radius: 50%; margin-right: 5px; flex-shrink: 0;
        }
        .source-name { font-size: 11px; color: var(--ri-muted); margin-top: 3px; text-align: right; }

        /* ── Badges de estado ── */
        .st-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 9px; border-radius: 20px;
            font-size: 11.5px; font-weight: 700;
            white-space: nowrap;
        }
        .st-badge i { font-size: 10px; }
        .sb-checked_in  { background: var(--ri-green-lt); color: var(--ri-green); }
        .sb-confirmed   { background: var(--ri-blue-lt);  color: var(--ri-blue);  }
        .sb-pending     { background: var(--ri-amber-lt); color: var(--ri-amber); }
        .sb-checked_out { background: var(--ri-slate-lt); color: var(--ri-slate); }
        .sb-cancelled   { background: var(--ri-red-lt);   color: var(--ri-red);   }

        /* ── Botones de acción por fila ── */
        .row-actions { display: flex; gap: 5px; align-items: center; justify-content: flex-end; flex-wrap: nowrap; }

        .btn-row {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 5px 10px; border-radius: 7px;
            font-size: 12px; font-weight: 600;
            border: 1.5px solid; cursor: pointer;
            text-decoration: none; white-space: nowrap;
            transition: all .15s;
        }
        .btn-row:hover { transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,.1); }

        .btn-view    { border-color: var(--ri-border); background: #fff; color: var(--ri-sub); }
        .btn-view:hover { border-color: #9ca3af; color: var(--ri-text); }

        .btn-checkin  { border-color: #93c5fd; background: var(--ri-blue-lt);  color: var(--ri-blue);  }
        .btn-checkin:hover  { background: #dbeafe; }

        .btn-confirm  { border-color: #fde68a; background: var(--ri-amber-lt); color: var(--ri-amber); }
        .btn-confirm:hover  { background: #fef3c7; }

        .btn-checkout { border-color: #6ee7b7; background: var(--ri-green-lt); color: var(--ri-green); }
        .btn-checkout:hover { background: #d1fae5; }

        .btn-cancel   { border-color: #fca5a5; background: var(--ri-red-lt);   color: var(--ri-red);   }
        .btn-cancel:hover   { background: #fee2e2; }

        /* ── Urgency tag ── */
        .urgency-tag {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 10.5px; font-weight: 700;
            padding: 2px 7px; border-radius: 20px; margin-left: 6px;
        }
        .ut-arriving { background: #fef3c7; color: #92400e; }
        .ut-departing { background: #d1fae5; color: #065f46; }

        /* ── Empty state ── */
        .ri-empty {
            padding: 48px 20px; text-align: center;
        }
        .ri-empty i { font-size: 32px; color: var(--ri-muted); display: block; margin-bottom: 10px; opacity: .4; }
        .ri-empty h4 { font-size: 15px; font-weight: 700; color: var(--ri-sub); margin-bottom: 6px; }
        .ri-empty p  { font-size: 13px; color: var(--ri-muted); margin: 0; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .col-hide-sm { display: none; }
            .summary-chips { gap: 8px; }
            .s-chip { min-width: 120px; }
        }
    </style>

<?php
// ── Helpers de vista ───────────────────────────────────────
$currency = session('currency_symbol') ?: '$';
$today    = $today ?? date('Y-m-d');

$statusLabels = [
    'pending'     => ['label' => 'Pendiente',   'icon' => 'bi-clock',            'cls' => 'sb-pending'],
    'confirmed'   => ['label' => 'Confirmada',  'icon' => 'bi-check-circle',     'cls' => 'sb-confirmed'],
    'checked_in'  => ['label' => 'En Casa',     'icon' => 'bi-house-check-fill', 'cls' => 'sb-checked_in'],
    'checked_out' => ['label' => 'Check-out',   'icon' => 'bi-box-arrow-right',  'cls' => 'sb-checked_out'],
    'cancelled'   => ['label' => 'Cancelada',   'icon' => 'bi-x-circle',         'cls' => 'sb-cancelled'],
];

$avatarColors = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ec4899','#0ea5e9','#14b8a6'];

function avatarColor(string $name): string {
    $colors = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ec4899','#0ea5e9','#14b8a6'];
    return $colors[abs(crc32($name)) % count($colors)];
}

// Construir URL de filtro manteniendo los demás parámetros
function filterUrl(string $param, string $value): string {
    $params = $_GET;
    $params[$param] = $value;
    return base_url('/reservations') . '?' . http_build_query($params);
}
function clearUrl(): string {
    return base_url('/reservations');
}
$hasFilters = $filterSearch !== '' || $filterDate !== '' || $filterUnit !== '';
?>

    <!-- ── Header ── -->
    <div class="ri-header">
        <div>
            <h1>Reservas</h1>
            <p><?= date('l, d \d\e F', strtotime($today)) ?></p>
        </div>
        <a href="<?= base_url('/reservations/create') ?>" class="btn-new">
            <i class="bi bi-plus-lg"></i> Nueva Reserva
        </a>
    </div>

    <!-- ── Chips de resumen / acceso rápido ── -->
    <div class="summary-chips">

        <a href="<?= base_url('/reservations?status=checked_in') ?>"
           class="s-chip sc-green <?= $filterStatus === 'checked_in' ? 'active' : '' ?>">
            <div class="s-chip-icon"><i class="bi bi-house-check-fill"></i></div>
            <div class="s-chip-body">
                <div class="s-chip-num"><?= $counts['in_house'] ?></div>
                <div class="s-chip-label">En casa ahora</div>
            </div>
        </a>

        <a href="<?= base_url('/reservations?status=arriving_today') ?>"
           class="s-chip sc-blue <?= $filterStatus === 'arriving_today' ? 'active' : '' ?>">
            <div class="s-chip-icon"><i class="bi bi-box-arrow-in-right"></i></div>
            <div class="s-chip-body">
                <div class="s-chip-num"><?= $counts['arriving'] ?></div>
                <div class="s-chip-label">Llegan hoy</div>
            </div>
        </a>

        <a href="<?= base_url('/reservations?status=departing_today') ?>"
           class="s-chip sc-amber <?= $filterStatus === 'departing_today' ? 'active' : '' ?>">
            <div class="s-chip-icon"><i class="bi bi-box-arrow-right"></i></div>
            <div class="s-chip-body">
                <div class="s-chip-num"><?= $counts['departing'] ?></div>
                <div class="s-chip-label">Salen hoy</div>
            </div>
        </a>

        <a href="<?= base_url('/reservations?status=pending') ?>"
           class="s-chip sc-slate <?= $filterStatus === 'pending' ? 'active' : '' ?>">
            <div class="s-chip-icon"><i class="bi bi-clock"></i></div>
            <div class="s-chip-body">
                <div class="s-chip-num"><?= $counts['pending'] ?></div>
                <div class="s-chip-label">Sin confirmar</div>
            </div>
        </a>

    </div>

    <!-- ── Barra de filtros ── -->
    <form method="get" action="<?= base_url('/reservations') ?>" class="filter-bar" id="filter-form">

        <!-- Búsqueda -->
        <div class="filter-search">
            <i class="bi bi-search"></i>
            <input type="text" name="q" placeholder="Buscar huésped o # reserva…"
                   value="<?= esc($filterSearch) ?>" autocomplete="off">
        </div>

        <!-- Estado -->
        <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="active"          <?= $filterStatus === 'active'          ? 'selected' : '' ?>>Activas</option>
            <option value="checked_in"      <?= $filterStatus === 'checked_in'      ? 'selected' : '' ?>>En casa</option>
            <option value="arriving_today"  <?= $filterStatus === 'arriving_today'  ? 'selected' : '' ?>>Llegan hoy</option>
            <option value="departing_today" <?= $filterStatus === 'departing_today' ? 'selected' : '' ?>>Salen hoy</option>
            <option value="pending"         <?= $filterStatus === 'pending'         ? 'selected' : '' ?>>Pendientes</option>
            <option value="confirmed"       <?= $filterStatus === 'confirmed'       ? 'selected' : '' ?>>Confirmadas</option>
            <option value="checked_out"     <?= $filterStatus === 'checked_out'     ? 'selected' : '' ?>>Check-out</option>
            <option value="cancelled"       <?= $filterStatus === 'cancelled'       ? 'selected' : '' ?>>Canceladas</option>
            <option value="all"             <?= $filterStatus === 'all'             ? 'selected' : '' ?>>Todas</option>
        </select>

        <!-- Filtro por habitación -->
        <select name="unit" class="filter-select" onchange="this.form.submit()">
            <option value="">Todas las hab.</option>
            <?php foreach ($units as $u): ?>
                <option value="<?= $u['id'] ?>" <?= $filterUnit == $u['id'] ? 'selected' : '' ?>>
                    <?= esc($u['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Filtro por fecha check-in -->
        <input type="date" name="date" class="filter-date"
               value="<?= esc($filterDate) ?>" onchange="this.form.submit()"
               title="Filtrar por fecha de llegada">

        <!-- Botón buscar (para el campo de texto) -->
        <button type="submit" style="display:none;"></button>

        <?php if ($hasFilters): ?>
            <a href="<?= base_url('/reservations?status=' . esc($filterStatus)) ?>"
               class="btn-filter-clear">
                <i class="bi bi-x"></i> Limpiar
            </a>
        <?php endif; ?>

    </form>

    <!-- ── Tabla de reservas ── -->
    <div class="ri-card">
        <div class="ri-card-header">
        <span>
            <?php
            $labels = [
                'active'          => 'Reservas activas',
                'checked_in'      => 'Huéspedes en casa',
                'arriving_today'  => 'Llegadas de hoy',
                'departing_today' => 'Salidas de hoy',
                'pending'         => 'Sin confirmar',
                'confirmed'       => 'Confirmadas',
                'checked_out'     => 'Check-outs',
                'cancelled'       => 'Canceladas',
                'all'             => 'Todas las reservas',
            ];
            echo $labels[$filterStatus] ?? 'Reservas';
            ?>
        </span>
            <span class="ri-count"><?= count($reservations) ?> resultado<?= count($reservations) !== 1 ? 's' : '' ?></span>
        </div>

        <?php if (empty($reservations)): ?>
            <div class="ri-empty">
                <i class="bi bi-calendar-x"></i>
                <h4>Sin resultados</h4>
                <p>No hay reservas que coincidan con los filtros aplicados.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="ri-table">
                    <thead>
                    <tr>
                        <th style="width:32px;">#</th>
                        <th>Huésped</th>
                        <th>Habitación</th>
                        <th>Fechas</th>
                        <th class="col-hide-sm">Estado</th>
                        <th class="text-right">Monto</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($reservations as $i => $r):
                        $isArrivingToday  = $r['check_in_date']  === $today && in_array($r['status'], ['pending','confirmed']);
                        $isDepartingToday = $r['check_out_date'] === $today && $r['status'] === 'checked_in';
                        $st = $statusLabels[$r['status']] ?? $statusLabels['pending'];
                        $nights = (int)($r['nights'] ?? 1);
                        $initials = strtoupper(substr($r['full_name'], 0, 1));
                        $avColor = avatarColor($r['full_name']);
                        $rowClass = 's-' . $r['status'];
                        if ($isArrivingToday)  $rowClass .= ' arriving-today';
                        if ($isDepartingToday) $rowClass .= ' departing-today';
                        ?>
                        <tr class="ri-row <?= $rowClass ?>" style="animation-delay:<?= min($i * .03, .4) ?>s">

                            <!-- # ID -->
                            <td class="ri-td" style="color:var(--ri-muted);font-size:12px;font-weight:700;">
                                <?= str_pad($r['id'], 4, '0', STR_PAD_LEFT) ?>
                            </td>

                            <!-- Huésped -->
                            <td class="ri-td">
                                <div class="guest-name">
                                    <div class="guest-avatar" style="background:<?= $avColor ?>">
                                        <?= $initials ?>
                                    </div>
                                    <div>
                                        <a href="<?= base_url('/reservations/show/'.$r['id']) ?>"
                                           style="color:var(--ri-text);text-decoration:none;font-weight:700;font-size:13.5px;">
                                            <?= esc($r['full_name']) ?>
                                            <?php if ($isArrivingToday): ?>
                                                <span class="urgency-tag ut-arriving"><i class="bi bi-arrow-down-right"></i> Hoy</span>
                                            <?php elseif ($isDepartingToday): ?>
                                                <span class="urgency-tag ut-departing"><i class="bi bi-arrow-up-right"></i> Sale hoy</span>
                                            <?php endif; ?>
                                        </a>
                                        <?php if (!empty($r['document'])): ?>
                                            <div class="guest-doc"><?= esc($r['document']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Habitación -->
                            <td class="ri-td">
                                <div class="unit-name"><?= esc($r['unit_name']) ?></div>
                                <div class="unit-pax">
                                    <i class="bi bi-people-fill"></i>
                                    <?= $r['num_adults'] ?>A<?= $r['num_children'] > 0 ? ' + '.$r['num_children'].'N' : '' ?>
                                </div>
                            </td>

                            <!-- Fechas -->
                            <td class="ri-td col-hide-sm">
                                <div class="date-range">
                                    <strong><?= date('d/m', strtotime($r['check_in_date'])) ?></strong>
                                    <span style="color:var(--ri-muted);margin:0 4px;">→</span>
                                    <strong><?= date('d/m', strtotime($r['check_out_date'])) ?></strong>
                                </div>
                                <div>
                            <span class="nights-chip">
                                <i class="bi bi-moon-stars-fill"></i> <?= $nights ?> noche<?= $nights !== 1 ? 's' : '' ?>
                            </span>
                                </div>
                            </td>

                            <!-- Estado -->
                            <td class="ri-td col-hide-sm">
                        <span class="st-badge <?= $st['cls'] ?>">
                            <i class="bi <?= $st['icon'] ?>"></i>
                            <?= $st['label'] ?>
                        </span>
                            </td>

                            <!-- Monto -->
                            <td class="ri-td" style="text-align:right;">
                                <div class="amount"><?= $currency ?><?= number_format($r['total_price'], 0, ',', '.') ?></div>
                                <?php if (!empty($r['source_name'])): ?>
                                    <div class="source-name">
                                        <span class="source-dot" style="background:<?= esc($r['source_color'] ?? '#94a3b8') ?>"></span>
                                        <?= esc($r['source_name']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Acciones contextuales -->
                            <td class="ri-td">
                                <div class="row-actions">

                                    <!-- Ver folio siempre disponible -->
                                    <a href="<?= base_url('/reservations/show/'.$r['id']) ?>"
                                       class="btn-row btn-view" title="Ver folio completo">
                                        <i class="bi bi-file-text"></i>
                                        <span class="col-hide-sm">Folio</span>
                                    </a>

                                    <!-- Acción principal según estado -->
                                    <?php if ($r['status'] === 'pending'): ?>
                                        <form method="post" action="<?= base_url('/reservations/update-status/'.$r['id']) ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="new_status" value="confirmed">
                                            <button type="submit" class="btn-row btn-confirm">
                                                <i class="bi bi-check-circle"></i> Confirmar
                                            </button>
                                        </form>

                                    <?php elseif ($r['status'] === 'confirmed'): ?>
                                        <form method="post" action="<?= base_url('/reservations/update-status/'.$r['id']) ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="new_status" value="checked_in">
                                            <button type="submit" class="btn-row btn-checkin">
                                                <i class="bi bi-box-arrow-in-right"></i> Check-in
                                            </button>
                                        </form>

                                    <?php elseif ($r['status'] === 'checked_in'): ?>
                                        <a href="<?= base_url('/reservations/closure/'.$r['id']) ?>"
                                           class="btn-row btn-checkout">
                                            <i class="bi bi-receipt"></i> Check-out
                                        </a>
                                    <?php endif; ?>

                                </div>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Buscar al presionar Enter
        document.querySelector('.filter-search input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('filter-form').submit();
            }
        });
    </script>

<?= $this->endSection() ?>