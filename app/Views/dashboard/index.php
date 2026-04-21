<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <style>
        /* ═══════════════════════════════════════════════
           DASHBOARD — Variables y Reset
        ═══════════════════════════════════════════════ */
        :root {
            --d-bg:        #f0f2f5;
            --d-surface:   #ffffff;
            --d-border:    #e4e7ec;
            --d-text:      #101828;
            --d-sub:       #667085;
            --d-muted:     #98a2b3;

            --d-blue:      #1d4ed8;
            --d-blue-lt:   #eff6ff;
            --d-blue-mid:  #bfdbfe;

            --d-green:     #065f46;
            --d-green-lt:  #ecfdf5;
            --d-green-mid: #6ee7b7;

            --d-amber:     #92400e;
            --d-amber-lt:  #fffbeb;
            --d-amber-mid: #fcd34d;

            --d-red:       #991b1b;
            --d-red-lt:    #fef2f2;
            --d-red-mid:   #fca5a5;

            --d-slate:     #334155;
            --d-slate-lt:  #f8fafc;

            --radius:      12px;
            --radius-sm:   8px;
            --shadow:      0 1px 4px rgba(0,0,0,.07), 0 0 0 1px rgba(0,0,0,.04);
            --shadow-md:   0 4px 16px rgba(0,0,0,.10);
        }

        /* ── Layout principal ── */
        .db-wrap {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
            align-items: start;
        }
        @media (max-width: 1100px) { .db-wrap { grid-template-columns: 1fr; } }

        /* ── Header ── */
        .db-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            gap: 12px;
            flex-wrap: wrap;
        }
        .db-header-left h1 {
            font-size: 22px;
            font-weight: 700;
            color: var(--d-text);
            margin: 0 0 4px;
            letter-spacing: -.03em;
        }
        .db-header-left p {
            font-size: 13px;
            color: var(--d-sub);
            margin: 0;
        }
        .btn-new-rsv {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 16px;
            background: var(--d-blue);
            color: #fff;
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            transition: background .15s, transform .1s;
            border: none;
        }
        .btn-new-rsv:hover { background: #1e40af; color: #fff; }
        .btn-new-rsv:active { transform: scale(.98); }

        /* ── KPI Grid ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }
        @media (max-width: 900px)  { .kpi-grid { grid-template-columns: repeat(2,1fr); } }
        @media (max-width: 500px)  { .kpi-grid { grid-template-columns: 1fr; } }

        .kpi {
            background: var(--d-surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 18px 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            animation: fadeUp .35s ease both;
        }
        .kpi:nth-child(1) { animation-delay: .05s; }
        .kpi:nth-child(2) { animation-delay: .10s; }
        .kpi:nth-child(3) { animation-delay: .15s; }
        .kpi:nth-child(4) { animation-delay: .20s; }

        .kpi-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .kpi-icon {
            width: 36px; height: 36px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .ki-blue   { background: var(--d-blue-lt);  color: var(--d-blue);  }
        .ki-green  { background: var(--d-green-lt); color: #059669;        }
        .ki-amber  { background: var(--d-amber-lt); color: #d97706;        }
        .ki-red    { background: var(--d-red-lt);   color: #dc2626;        }

        .kpi-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 20px;
        }
        .kb-up    { background: var(--d-green-lt); color: #059669; }
        .kb-down  { background: var(--d-red-lt);   color: #dc2626; }
        .kb-neu   { background: var(--d-slate-lt); color: var(--d-sub); }
        .kb-warn  { background: var(--d-amber-lt); color: #d97706; }

        .kpi-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--d-text);
            letter-spacing: -.04em;
            line-height: 1;
        }
        .kpi-label {
            font-size: 12.5px;
            color: var(--d-sub);
            font-weight: 500;
        }

        /* Barra de ocupación */
        .occ-bar-wrap {
            height: 4px;
            background: var(--d-border);
            border-radius: 99px;
            overflow: hidden;
            margin-top: 2px;
        }
        .occ-bar-fill {
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, #1d4ed8, #60a5fa);
            transition: width .6s cubic-bezier(.4,0,.2,1);
        }

        /* ── Card genérica ── */
        .db-card {
            background: var(--d-surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 20px;
            animation: fadeUp .4s ease both;
        }
        .db-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--d-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .db-card-header h6 {
            margin: 0;
            font-size: 13.5px;
            font-weight: 700;
            color: var(--d-text);
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .db-card-header h6 i { color: var(--d-muted); font-size: 14px; }
        .db-card-body { padding: 0; }

        .link-sm {
            font-size: 12px;
            color: var(--d-blue);
            text-decoration: none;
            font-weight: 500;
        }
        .link-sm:hover { text-decoration: underline; }

        /* ── Mapa de habitaciones ── */
        .unit-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 16px 20px;
        }
        .unit-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            border: 1.5px solid transparent;
            transition: all .15s;
            cursor: pointer;
        }
        .unit-chip:hover { transform: translateY(-1px); box-shadow: var(--shadow-md); }

        .uc-available   { background: var(--d-green-lt); color: var(--d-green);  border-color: var(--d-green-mid); }
        .uc-occupied    { background: #eff6ff;            color: var(--d-blue);   border-color: var(--d-blue-mid);  }
        .uc-maintenance { background: var(--d-amber-lt);  color: var(--d-amber);  border-color: var(--d-amber-mid); }
        .uc-blocked     { background: var(--d-red-lt);    color: var(--d-red);    border-color: var(--d-red-mid);   }

        .unit-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            padding: 10px 20px 14px;
            border-top: 1px solid var(--d-border);
        }
        .ul-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--d-sub);
        }
        .ul-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* ── Lista de llegadas / in-house ── */
        .arrival-list { list-style: none; margin: 0; padding: 0; }
        .arrival-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 20px;
            border-bottom: 1px solid var(--d-border);
            transition: background .1s;
            text-decoration: none;
            color: inherit;
        }
        .arrival-item:last-child { border-bottom: none; }
        .arrival-item:hover { background: #fafbfc; }

        .arrival-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700;
            flex-shrink: 0;
            color: #fff;
        }
        .av-blue   { background: #3b82f6; }
        .av-green  { background: #10b981; }
        .av-amber  { background: #f59e0b; }

        .arrival-info { flex: 1; min-width: 0; }
        .arrival-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--d-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .arrival-meta {
            font-size: 11.5px;
            color: var(--d-sub);
            margin-top: 1px;
        }
        .arrival-right { text-align: right; flex-shrink: 0; }
        .arrival-unit {
            font-size: 12px;
            font-weight: 600;
            color: var(--d-blue);
        }
        .arrival-date {
            font-size: 11px;
            color: var(--d-muted);
            margin-top: 2px;
        }

        .status-dot {
            display: inline-block;
            width: 7px; height: 7px;
            border-radius: 50%;
            margin-right: 4px;
        }
        .sd-pending   { background: #f59e0b; }
        .sd-confirmed { background: #3b82f6; }
        .sd-in        { background: #10b981; }

        .empty-state {
            padding: 28px 20px;
            text-align: center;
            color: var(--d-muted);
            font-size: 13px;
        }
        .empty-state i { font-size: 22px; display: block; margin-bottom: 6px; opacity: .4; }

        /* ═══════════════════════════════════════════════
           TIMELINE — sin FullCalendar, cero licencias
        ═══════════════════════════════════════════════ */
        .tl-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .tl-wrap::-webkit-scrollbar { height: 6px; }
        .tl-wrap::-webkit-scrollbar-thumb {
            background: var(--d-border);
            border-radius: 3px;
        }

        .tl-table {
            border-collapse: collapse;
            min-width: 100%;
            table-layout: fixed;
        }

        /* Columna de recurso (habitación) */
        .tl-resource-col { width: 110px; min-width: 110px; }

        .tl-resource-cell {
            padding: 8px 12px;
            border-right: 2px solid var(--d-border);
            background: var(--d-slate-lt);
            position: sticky;
            left: 0;
            z-index: 2;
        }
        .tl-resource-name {
            font-size: 12px;
            font-weight: 700;
            color: var(--d-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .tl-resource-sub {
            font-size: 10.5px;
            color: var(--d-muted);
            margin-top: 1px;
        }

        /* Header de días */
        .tl-head-resource {
            background: var(--d-slate-lt);
            position: sticky;
            left: 0;
            z-index: 3;
            padding: 8px 12px;
            border-right: 2px solid var(--d-border);
            border-bottom: 1px solid var(--d-border);
            font-size: 11px;
            font-weight: 700;
            color: var(--d-muted);
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .tl-day-header {
            padding: 6px 2px;
            text-align: center;
            border-bottom: 1px solid var(--d-border);
            border-left: 1px solid var(--d-border);
            font-size: 11px;
            color: var(--d-sub);
            font-weight: 500;
            min-width: 36px;
            background: var(--d-surface);
        }
        .tl-day-header.tl-today {
            background: var(--d-blue-lt);
            color: var(--d-blue);
            font-weight: 700;
        }
        .tl-day-num { font-size: 13px; font-weight: 700; display: block; line-height: 1.2; }
        .tl-day-name { font-size: 9px; text-transform: uppercase; letter-spacing: .04em; opacity: .7; }

        /* Celdas de la grilla */
        .tl-cell {
            border-left: 1px solid #f1f3f5;
            border-bottom: 1px solid #f1f3f5;
            height: 38px;
            position: relative;
            min-width: 36px;
        }
        .tl-cell.tl-today-col { background: #f8faff; }
        .tl-cell.tl-weekend   { background: #fafafa; }

        /* Bloques de reserva */
        .tl-event {
            position: absolute;
            top: 4px; bottom: 4px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            padding: 0 7px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            transition: filter .15s, transform .1s;
            text-decoration: none;
            z-index: 1;
            color: #fff;
        }
        .tl-event:hover {
            filter: brightness(.9);
            transform: scaleY(1.08);
            z-index: 2;
            color: #fff;
        }

        /* Colores por estado */
        .te-confirmed  { background: #3b82f6; }
        .te-pending    { background: #f59e0b; color: #1c1917; }
        .te-checked_in { background: #059669; }
        .te-checked_out{ background: #94a3b8; }

        /* Indicador "hoy" vertical */
        .tl-now-line {
            position: absolute;
            top: 0; bottom: 0;
            width: 2px;
            background: #ef4444;
            z-index: 10;
            pointer-events: none;
        }
        .tl-now-dot {
            width: 8px; height: 8px;
            background: #ef4444;
            border-radius: 50%;
            position: absolute;
            top: -4px;
            left: -3px;
        }

        /* ── Columna derecha (sidebar) ── */
        .db-sidebar { display: flex; flex-direction: column; gap: 0; }

        /* ── Animaciones ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>

    <!-- ════════════════════════════════════════════
         HEADER
    ════════════════════════════════════════════ -->
    <div class="db-header">
        <div class="db-header-left">
            <h1>Buenos días<?php
                $h = (int)date('H');
                if ($h >= 12 && $h < 18) echo ', buenas tardes';
                elseif ($h >= 18) echo ', buenas noches';
                ?><?= $userName ? ', ' . esc(explode(' ', $userName)[0]) : '' ?> 👋</h1>
            <p><i class="bi bi-calendar3"></i> <?= date('l, d \d\e F \d\e Y') ?> &nbsp;·&nbsp; <?= esc($hotelName) ?></p>
        </div>
        <a href="<?= base_url('/reservations/create') ?>" class="btn-new-rsv">
            <i class="bi bi-plus-lg"></i> Nueva Reserva
        </a>
    </div>

    <!-- ════════════════════════════════════════════
         KPIs
    ════════════════════════════════════════════ -->
<?php
$inc     = $metrics['income_today']     ?? 0;
$incY    = $metrics['income_yesterday'] ?? 0;
$diff    = $inc - $incY;
$pct     = $incY > 0 ? round(($diff / $incY) * 100, 1) : null;
$checkins  = $metrics['expected_checkins']  ?? 0;
$checkouts = $metrics['expected_checkouts'] ?? 0;
$occRate   = $metrics['occupancy_rate']     ?? 0;
$occUsed   = $metrics['occupied_units']     ?? 0;
$occTotal  = $metrics['total_units']        ?? 0;
$currency  = session('currency_symbol') ?: '$';
?>
    <div class="kpi-grid">

        <!-- Llegadas -->
        <div class="kpi">
            <div class="kpi-top">
                <div class="kpi-icon ki-blue"><i class="bi bi-box-arrow-in-right"></i></div>
                <?php if ($checkins > 0): ?>
                    <span class="kpi-badge kb-warn"><i class="bi bi-clock"></i> Por llegar</span>
                <?php else: ?>
                    <span class="kpi-badge kb-neu">Sin llegadas</span>
                <?php endif; ?>
            </div>
            <div class="kpi-value"><?= $checkins ?></div>
            <div class="kpi-label">Llegadas hoy</div>
        </div>

        <!-- Salidas -->
        <div class="kpi">
            <div class="kpi-top">
                <div class="kpi-icon ki-amber"><i class="bi bi-box-arrow-right"></i></div>
                <?php if ($checkouts > 0): ?>
                    <span class="kpi-badge kb-warn"><i class="bi bi-door-open"></i> Pendientes</span>
                <?php else: ?>
                    <span class="kpi-badge kb-neu">Sin salidas</span>
                <?php endif; ?>
            </div>
            <div class="kpi-value"><?= $checkouts ?></div>
            <div class="kpi-label">Salidas hoy</div>
        </div>

        <!-- Ingresos -->
        <div class="kpi">
            <div class="kpi-top">
                <div class="kpi-icon ki-green"><i class="bi bi-cash-stack"></i></div>
                <?php if ($pct !== null): ?>
                    <span class="kpi-badge <?= $pct >= 0 ? 'kb-up' : 'kb-down' ?>">
                    <i class="bi bi-arrow-<?= $pct >= 0 ? 'up' : 'down' ?>-short"></i>
                    <?= abs($pct) ?>% vs ayer
                </span>
                <?php else: ?>
                    <span class="kpi-badge kb-neu">Sin comparativa</span>
                <?php endif; ?>
            </div>
            <div class="kpi-value" style="font-size:22px;"><?= $currency ?><?= number_format($inc, 0, ',', '.') ?></div>
            <div class="kpi-label">Ingresos hoy</div>
        </div>

        <!-- Ocupación -->
        <div class="kpi">
            <div class="kpi-top">
                <div class="kpi-icon ki-blue"><i class="bi bi-building"></i></div>
                <span class="kpi-badge kb-neu"><?= $occUsed ?> / <?= $occTotal ?> uds.</span>
            </div>
            <div class="kpi-value"><?= $occRate ?>%</div>
            <div class="kpi-label">Ocupación actual</div>
            <div class="occ-bar-wrap">
                <div class="occ-bar-fill" style="width:<?= $occRate ?>%"></div>
            </div>
        </div>

    </div>

    <!-- ════════════════════════════════════════════
         BODY (dos columnas)
    ════════════════════════════════════════════ -->
    <div class="db-wrap">

        <!-- COLUMNA PRINCIPAL -->
        <div>

            <!-- TIMELINE -->
            <div class="db-card" style="animation-delay:.25s;">
                <div class="db-card-header">
                    <h6><i class="bi bi-calendar-week"></i> Ocupación — próximos 21 días</h6>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <span style="font-size:11px;color:var(--d-muted);" id="tl-range-label"></span>
                        <a href="<?= base_url('/reservations') ?>" class="link-sm">Ver todas →</a>
                    </div>
                </div>
                <div class="db-card-body">
                    <div class="tl-wrap">
                        <div id="timeline-container" style="padding:0 0 4px;">
                            <div style="padding:24px;text-align:center;color:var(--d-muted);font-size:13px;">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Cargando timeline...
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Leyenda -->
                <div style="display:flex;gap:16px;flex-wrap:wrap;padding:10px 20px 14px;border-top:1px solid var(--d-border);">
                    <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--d-sub);">
                        <span style="width:12px;height:12px;border-radius:3px;background:#3b82f6;display:inline-block;"></span> Confirmada
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--d-sub);">
                        <span style="width:12px;height:12px;border-radius:3px;background:#059669;display:inline-block;"></span> In-house
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--d-sub);">
                        <span style="width:12px;height:12px;border-radius:3px;background:#f59e0b;display:inline-block;"></span> Pendiente
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--d-sub);">
                        <span style="width:12px;height:12px;border-radius:3px;background:#94a3b8;display:inline-block;"></span> Check-out
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--d-sub);">
                        <span style="width:2px;height:14px;background:#ef4444;display:inline-block;"></span> Hoy
                    </div>
                </div>
            </div>

            <!-- MAPA DE HABITACIONES -->
            <?php if (!empty($metrics['units_status'])): ?>
                <div class="db-card" style="animation-delay:.30s;">
                    <div class="db-card-header">
                        <h6><i class="bi bi-grid-3x3-gap"></i> Estado de habitaciones</h6>
                        <a href="<?= base_url('/inventory') ?>" class="link-sm">Ver inventario →</a>
                    </div>
                    <div class="db-card-body">
                        <div class="unit-grid">
                            <?php
                            $ucMap = [
                                'available'   => ['cls' => 'uc-available',   'icon' => 'bi-check-circle-fill'],
                                'occupied'    => ['cls' => 'uc-occupied',    'icon' => 'bi-person-fill'],
                                'maintenance' => ['cls' => 'uc-maintenance', 'icon' => 'bi-tools'],
                                'blocked'     => ['cls' => 'uc-blocked',     'icon' => 'bi-slash-circle'],
                            ];
                            foreach ($metrics['units_status'] as $u):
                                $s = $ucMap[$u['status']] ?? ['cls' => 'uc-available', 'icon' => 'bi-question'];
                                ?>
                                <a href="<?= base_url('/reservations?unit=' . $u['id']) ?>"
                                   class="unit-chip <?= $s['cls'] ?>"
                                   title="<?= esc($u['name']) ?> — <?= $u['status'] ?>">
                                    <i class="bi <?= $s['icon'] ?>"></i>
                                    <?= esc($u['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <div class="unit-legend">
                            <div class="ul-item"><div class="ul-dot" style="background:#6ee7b7;"></div> Disponible</div>
                            <div class="ul-item"><div class="ul-dot" style="background:#93c5fd;"></div> Ocupado</div>
                            <div class="ul-item"><div class="ul-dot" style="background:#fcd34d;"></div> Mantenimiento</div>
                            <div class="ul-item"><div class="ul-dot" style="background:#fca5a5;"></div> Bloqueado</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div><!-- /col principal -->

        <!-- SIDEBAR DERECHO -->
        <div class="db-sidebar">

            <!-- Llegadas hoy + mañana -->
            <div class="db-card" style="animation-delay:.35s;">
                <div class="db-card-header">
                    <h6><i class="bi bi-box-arrow-in-right"></i> Próximas llegadas</h6>
                    <a href="<?= base_url('/reservations') ?>" class="link-sm">Ver todas</a>
                </div>
                <div class="db-card-body">
                    <?php $arrivals = $metrics['upcoming_arrivals'] ?? []; ?>
                    <?php if (empty($arrivals)): ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            Sin llegadas hoy ni mañana
                        </div>
                    <?php else: ?>
                        <ul class="arrival-list">
                            <?php foreach ($arrivals as $a):
                                $initials = strtoupper(substr($a['full_name'], 0, 1));
                                $isToday  = $a['check_in_date'] === date('Y-m-d');
                                $sdClass  = $a['status'] === 'confirmed' ? 'sd-confirmed' : 'sd-pending';
                                $avColors = ['av-blue', 'av-green', 'av-amber'];
                                $avCls    = $avColors[crc32($a['full_name']) % 3];
                                ?>
                                <a href="<?= base_url('/reservations/show/' . $a['id']) ?>" class="arrival-item">
                                    <div class="arrival-avatar <?= $avCls ?>"><?= $initials ?></div>
                                    <div class="arrival-info">
                                        <div class="arrival-name"><?= esc($a['full_name']) ?></div>
                                        <div class="arrival-meta">
                                            <span class="status-dot <?= $sdClass ?>"></span>
                                            <?= $a['status'] === 'confirmed' ? 'Confirmada' : 'Pendiente' ?>
                                            · <?= $a['num_adults'] ?>A<?= $a['num_children'] > 0 ? ' '.$a['num_children'].'N' : '' ?>
                                        </div>
                                    </div>
                                    <div class="arrival-right">
                                        <div class="arrival-unit"><?= esc($a['unit_name']) ?></div>
                                        <div class="arrival-date">
                                            <?= $isToday ? '<span style="color:#d97706;font-weight:700;">Hoy</span>' : 'Mañana' ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- In-house ahora -->
            <div class="db-card" style="animation-delay:.40s;">
                <div class="db-card-header">
                    <h6><i class="bi bi-house-check"></i> In-house ahora</h6>
                    <span style="font-size:12px;color:var(--d-sub);"><?= count($metrics['in_house'] ?? []) ?> hab.</span>
                </div>
                <div class="db-card-body">
                    <?php $inHouse = $metrics['in_house'] ?? []; ?>
                    <?php if (empty($inHouse)): ?>
                        <div class="empty-state">
                            <i class="bi bi-building"></i>
                            Sin huéspedes hospedados
                        </div>
                    <?php else: ?>
                        <ul class="arrival-list">
                            <?php foreach ($inHouse as $ih):
                                $initials = strtoupper(substr($ih['full_name'], 0, 1));
                                $avColors = ['av-blue', 'av-green', 'av-amber'];
                                $avCls    = $avColors[crc32($ih['full_name']) % 3];
                                $daysLeft = (int)((strtotime($ih['check_out_date']) - strtotime(date('Y-m-d'))) / 86400);
                                ?>
                                <a href="<?= base_url('/reservations/show/' . $ih['id']) ?>" class="arrival-item">
                                    <div class="arrival-avatar <?= $avCls ?>"><?= $initials ?></div>
                                    <div class="arrival-info">
                                        <div class="arrival-name"><?= esc($ih['full_name']) ?></div>
                                        <div class="arrival-meta">
                                            <span class="status-dot sd-in"></span> In-house
                                            · sale <?= date('d/m', strtotime($ih['check_out_date'])) ?>
                                        </div>
                                    </div>
                                    <div class="arrival-right">
                                        <div class="arrival-unit"><?= esc($ih['unit_name']) ?></div>
                                        <div class="arrival-date" style="<?= $daysLeft <= 0 ? 'color:#dc2626;font-weight:700;' : '' ?>">
                                            <?= $daysLeft <= 0 ? '¡Hoy sale!' : ($daysLeft === 1 ? 'Mañana' : "en {$daysLeft}d") ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /sidebar -->
    </div><!-- /db-wrap -->


    <!-- ════════════════════════════════════════════
         TIMELINE JS — sin FullCalendar
    ════════════════════════════════════════════ -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const DAYS = 21;        // ventana de días a mostrar
            const COL_W = 38;       // ancho de cada columna de día en px
            const today = new Date();
            today.setHours(0,0,0,0);

            // ── Generar rango de fechas ──────────────────────────────────
            const days = [];
            for (let i = -3; i < DAYS - 3; i++) {   // 3 días de contexto pasado
                const d = new Date(today);
                d.setDate(today.getDate() + i);
                days.push(d);
            }

            const startDate = days[0];
            const endDate   = days[days.length - 1];

            // Formato legible en label
            const fmt = d => d.toLocaleDateString('es-CO', { day: '2-digit', month: 'short' });
            document.getElementById('tl-range-label').textContent = fmt(startDate) + ' — ' + fmt(endDate);

            // ── Pedir datos al API ───────────────────────────────────────
            const apiUrl = '<?= base_url('/api/events') ?>?start=' + startDate.toISOString().slice(0,10)
                + '&end=' + endDate.toISOString().slice(0,10);

            const resourceUrl = '<?= base_url('/api/resources') ?>';

            Promise.all([
                fetch(resourceUrl).then(r => r.json()),
                fetch(apiUrl).then(r => r.json())
            ])
                .then(([resources, events]) => {
                    renderTimeline(resources, events);
                })
                .catch(() => {
                    // Si el API falla mostramos un mensaje limpio
                    document.getElementById('timeline-container').innerHTML =
                        '<div style="padding:24px;text-align:center;color:#94a3b8;font-size:13px;">' +
                        '<i class="bi bi-wifi-off" style="font-size:22px;display:block;margin-bottom:8px;opacity:.4;"></i>' +
                        'No se pudo cargar la timeline. <a href="<?= base_url('/reservations') ?>">Ver lista de reservas</a></div>';
                });

            // ── Renderizar la tabla timeline ─────────────────────────────
            function renderTimeline(resources, events) {

                const dayNames = ['Do','Lu','Ma','Mi','Ju','Vi','Sá'];

                let html = '<table class="tl-table"><thead><tr>';

                // Esquina superior izquierda
                html += '<th class="tl-head-resource">Habitación</th>';

                // Headers de días
                days.forEach((d, i) => {
                    const isToday = d.getTime() === today.getTime();
                    const isWE    = d.getDay() === 0 || d.getDay() === 6;
                    html += `<th class="tl-day-header${isToday ? ' tl-today' : ''}${isWE ? ' tl-weekend' : ''}">
                        <span class="tl-day-num">${d.getDate()}</span>
                        <span class="tl-day-name">${dayNames[d.getDay()]}</span>
                     </th>`;
                });
                html += '</tr></thead><tbody>';

                // Filas por recurso (habitación)
                resources.forEach(resource => {
                    html += '<tr>';
                    html += `<td class="tl-resource-cell">
                        <div class="tl-resource-name">${escHtml(resource.title)}</div>
                     </td>`;

                    // Obtener eventos de este recurso en este rango
                    const resEvents = events.filter(e => e.resourceId == resource.id);

                    days.forEach((d) => {
                        const isToday = d.getTime() === today.getTime();
                        const isWE    = d.getDay() === 0 || d.getDay() === 6;
                        html += `<td class="tl-cell${isToday ? ' tl-today-col' : ''}${isWE ? ' tl-weekend' : ''}">`;

                        // Buscar evento que empieza en este día
                        resEvents.forEach(ev => {
                            const evStart = new Date(ev.start); evStart.setHours(0,0,0,0);
                            const evEnd   = new Date(ev.end);   evEnd.setHours(0,0,0,0);
                            if (evStart.getTime() !== d.getTime()) return;

                            // Calcular cuántos días dura (columnas a ocupar)
                            const durationMs   = evEnd - evStart;
                            const durationDays = Math.max(1, Math.round(durationMs / 86400000));

                            // Calcular cuántas columnas caben desde aquí hasta el fin del rango
                            const colsFromHere = days.filter(dd => dd >= evStart && dd < evEnd).length;
                            const spanCols     = Math.max(1, colsFromHere);
                            const blockWidth   = (spanCols * COL_W) - 4; // 2px gap

                            const statusClass  = 'te-' + (ev.extendedProps?.status || 'confirmed');
                            const guestName    = ev.title || 'Reserva';

                            html += `<a href="${ev.url || '#'}"
                                class="tl-event ${statusClass}"
                                style="width:${blockWidth}px;left:2px;"
                                title="${escHtml(guestName)}">${escHtml(guestName)}</a>`;
                        });

                        html += '</td>';
                    });

                    html += '</tr>';
                });

                html += '</tbody></table>';

                document.getElementById('timeline-container').innerHTML = html;
            }

            function escHtml(str) {
                return String(str)
                    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
                    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
            }
        });
    </script>

<?= $this->endSection() ?>