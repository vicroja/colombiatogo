<?= $this->extend('layouts/pms') ?>

<?= $this->section('title') ?>Nueva Reserva - <?= session('tenant_name') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

    <style>
        /* ── Variables ── */
        :root {
            --c-surface:   #ffffff;
            --c-bg:        #f4f5f7;
            --c-border:    #e2e5ea;
            --c-primary:   #1a56db;
            --c-primary-s: #1648c4;
            --c-success:   #057a55;
            --c-text:      #111827;
            --c-muted:     #6b7280;
            --c-label:     #374151;
            --c-danger:    #e02424;
            --radius:      10px;
            --shadow-sm:   0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.05);
            --shadow-md:   0 4px 12px rgba(0,0,0,.10);
        }

        body { background: var(--c-bg); }

        /* ── Layout ── */
        .rsv-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 24px;
            align-items: start;
        }
        @media (max-width: 991px) {
            .rsv-grid { grid-template-columns: 1fr; }
            .rsv-sticky { position: static !important; }
        }
        .rsv-sticky {
            position: sticky;
            top: 20px;
        }

        /* ── Cards ── */
        .rsv-card {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .rsv-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--c-border);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .rsv-card-header .icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }
        .icon-blue  { background: #eff6ff; color: var(--c-primary); }
        .icon-green { background: #f0fdf4; color: var(--c-success); }
        .icon-amber { background: #fffbeb; color: #b45309; }
        .icon-slate { background: #f8fafc; color: #475569; }

        .rsv-card-header h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: var(--c-text);
            letter-spacing: -.01em;
        }
        .rsv-card-header .sub {
            font-size: 12px;
            color: var(--c-muted);
            margin: 0;
        }
        .rsv-card-body { padding: 20px; }

        /* ── Form controls ── */
        .field-group { margin-bottom: 16px; }
        .field-group:last-child { margin-bottom: 0; }

        .field-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--c-label);
            margin-bottom: 5px;
            letter-spacing: .02em;
            text-transform: uppercase;
        }
        .field-label .req { color: var(--c-danger); margin-left: 2px; }

        .form-control, .form-select {
            border: 1.5px solid var(--c-border);
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 14px;
            color: var(--c-text);
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
            width: 100%;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--c-primary);
            box-shadow: 0 0 0 3px rgba(26,86,219,.12);
            outline: none;
        }
        .form-control::placeholder { color: #9ca3af; }

        /* ── Date range visual ── */
        .date-range-row {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 8px;
            align-items: end;
        }
        .date-sep {
            font-size: 18px;
            color: var(--c-muted);
            padding-bottom: 10px;
            text-align: center;
        }

        /* ── Occupancy stepper ── */
        .occ-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .occ-box {
            border: 1.5px solid var(--c-border);
            border-radius: 8px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fafafa;
        }
        .occ-box-info { line-height: 1.3; }
        .occ-box-info .occ-label {
            font-size: 12px; font-weight: 600;
            color: var(--c-label); text-transform: uppercase; letter-spacing: .02em;
        }
        .occ-box-info .occ-sub { font-size: 11px; color: var(--c-muted); }
        .occ-stepper {
            display: flex; align-items: center; gap: 8px;
        }
        .occ-btn {
            width: 28px; height: 28px;
            border-radius: 50%;
            border: 1.5px solid var(--c-border);
            background: #fff;
            color: var(--c-text);
            font-size: 16px; line-height: 1;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all .15s;
            padding: 0;
        }
        .occ-btn:hover:not(:disabled) {
            border-color: var(--c-primary);
            color: var(--c-primary);
            background: #eff6ff;
        }
        .occ-btn:disabled { opacity: .35; cursor: not-allowed; }
        .occ-val {
            font-size: 16px; font-weight: 700;
            color: var(--c-text); min-width: 20px; text-align: center;
        }
        /* Hidden inputs reales */
        .occ-hidden { display: none; }

        /* ── Guest companion rows ── */
        .companion-row {
            border: 1.5px solid var(--c-border);
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 10px;
            background: #fafafa;
            animation: fadeSlide .2s ease;
        }
        .companion-row:last-child { margin-bottom: 0; }
        .companion-header {
            display: flex; align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .companion-label {
            font-size: 12px; font-weight: 600;
            color: var(--c-muted); text-transform: uppercase; letter-spacing: .04em;
        }
        .companion-type-badge {
            font-size: 11px; padding: 2px 8px;
            border-radius: 20px; font-weight: 500;
        }
        .badge-adult   { background: #eff6ff; color: var(--c-primary); }
        .badge-child   { background: #f0fdf4; color: var(--c-success); }
        .companion-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 100px 1fr;
            gap: 8px;
        }
        @media (max-width: 600px) {
            .companion-grid { grid-template-columns: 1fr 1fr; }
        }
        .empty-companions {
            text-align: center; padding: 20px;
            color: var(--c-muted); font-size: 13px;
            border: 1.5px dashed var(--c-border);
            border-radius: 8px;
        }
        .empty-companions i { font-size: 20px; display: block; margin-bottom: 6px; opacity: .5; }

        /* ── Price panel ── */
        .price-panel {
            background: linear-gradient(135deg, #1a56db 0%, #1e429f 100%);
            border-radius: var(--radius);
            padding: 20px;
            color: #fff;
            margin-bottom: 16px;
        }
        .price-panel .label { font-size: 11px; opacity: .75; text-transform: uppercase; letter-spacing: .06em; }
        .price-panel .amount {
            font-size: 32px; font-weight: 800; letter-spacing: -.03em;
            line-height: 1.1;
        }
        .price-panel .amount input {
            background: transparent;
            border: none;
            border-bottom: 2px solid rgba(255,255,255,.4);
            color: #fff;
            font-size: 32px; font-weight: 800; letter-spacing: -.03em;
            width: 100%;
            padding: 0;
        }
        .price-panel .amount input:focus {
            outline: none;
            border-bottom-color: #fff;
        }
        .price-panel .amount input::placeholder { color: rgba(255,255,255,.4); }

        .price-breakdown {
            background: rgba(255,255,255,.1);
            border-radius: 8px;
            padding: 12px;
            margin-top: 12px;
            font-size: 12.5px;
        }
        .price-breakdown .row-item {
            display: flex; justify-content: space-between;
            padding: 3px 0;
            opacity: .85;
        }
        .price-breakdown .row-item.total {
            border-top: 1px solid rgba(255,255,255,.25);
            margin-top: 6px; padding-top: 8px;
            font-weight: 700; opacity: 1; font-size: 13px;
        }
        .price-breakdown .discount { color: #6ee7b7; }

        .price-loading {
            display: flex; align-items: center; gap: 8px;
            font-size: 12px; opacity: .8; margin-top: 8px;
        }
        .spinner-xs {
            width: 14px; height: 14px;
            border: 2px solid rgba(255,255,255,.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
            display: inline-block;
        }

        /* ── Promo code ── */
        .promo-row {
            display: flex; gap: 8px;
        }
        .promo-row .form-control { flex: 1; }
        .btn-promo {
            padding: 9px 14px;
            background: #fff; color: var(--c-primary);
            border: 1.5px solid var(--c-primary);
            border-radius: 8px; font-size: 13px; font-weight: 600;
            cursor: pointer; white-space: nowrap;
            transition: all .15s;
        }
        .btn-promo:hover { background: var(--c-primary); color: #fff; }

        /* ── Submit button ── */
        .btn-create {
            width: 100%;
            padding: 13px;
            background: #057a55;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px; font-weight: 700;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: background .15s, transform .1s;
            letter-spacing: -.01em;
        }
        .btn-create:hover { background: #046c4e; }
        .btn-create:active { transform: scale(.99); }

        /* ── Status toggle ── */
        .status-pills {
            display: flex; gap: 8px;
        }
        .status-pill {
            flex: 1; padding: 8px 6px;
            border: 1.5px solid var(--c-border);
            border-radius: 8px; background: #fff;
            font-size: 12px; font-weight: 600; color: var(--c-muted);
            cursor: pointer; text-align: center;
            transition: all .15s;
        }
        .status-pill:hover { border-color: #9ca3af; color: var(--c-text); }
        .status-pill.active-pending  { border-color: #f59e0b; background: #fffbeb; color: #b45309; }
        .status-pill.active-confirmed { border-color: var(--c-primary); background: #eff6ff; color: var(--c-primary); }
        .status-pill input { display: none; }

        /* ── Divider with label ── */
        .divider-label {
            display: flex; align-items: center; gap: 10px;
            margin: 18px 0 14px;
            font-size: 11px; font-weight: 600; color: var(--c-muted);
            text-transform: uppercase; letter-spacing: .06em;
        }
        .divider-label::before, .divider-label::after {
            content: ''; flex: 1; height: 1px; background: var(--c-border);
        }

        /* ── Animations ── */
        @keyframes fadeSlide {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Misc ── */
        .page-header {
            display: flex; align-items: center;
            gap: 12px; margin-bottom: 24px;
        }
        .page-header h1 {
            font-size: 20px; font-weight: 700;
            color: var(--c-text); margin: 0; letter-spacing: -.02em;
        }
        .page-header .back-btn {
            padding: 7px 12px;
            border: 1.5px solid var(--c-border);
            border-radius: 8px; background: #fff;
            color: var(--c-muted); font-size: 13px;
            text-decoration: none;
            display: flex; align-items: center; gap: 5px;
            transition: all .15s;
        }
        .page-header .back-btn:hover { border-color: #9ca3af; color: var(--c-text); }

        .hint { font-size: 11.5px; color: var(--c-muted); margin-top: 4px; }
        .unit-select-option-sub { font-size: 11px; color: var(--c-muted); }

        .nights-chip {
            display: inline-flex; align-items: center; gap: 5px;
            background: #eff6ff; color: var(--c-primary);
            padding: 3px 10px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
            margin-top: 6px;
        }
        .nights-chip.hidden { display: none; }

        .invalid-msg {
            font-size: 12px; color: var(--c-danger);
            margin-top: 4px; display: none;
        }
        .is-invalid { border-color: var(--c-danger) !important; }

        /* Agente / Fuente row */
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 480px) { .two-col { grid-template-columns: 1fr; } }
    </style>

    <div class="container-fluid px-3 px-md-4" style="max-width:1200px;">

        <!-- Header -->
        <div class="page-header">
            <a href="<?= base_url('/reservations') ?>" class="back-btn">
                <i class="bi bi-arrow-left"></i> Reservas
            </a>
            <h1>Nueva Reserva</h1>
        </div>

        <form action="<?= base_url('/reservations/store') ?>" method="post" id="reservation-form" novalidate>
            <?= csrf_field() ?>

            <!-- Hidden inputs que realmente se envían al servidor -->
            <input type="hidden" name="num_adults"   id="num_adults_hidden"   value="1">
            <input type="hidden" name="num_children" id="num_children_hidden" value="0">
            <input type="hidden" name="promo_id"     id="promo_id_hidden"     value="">

            <div class="rsv-grid">

                <!-- ══════════════ COLUMNA IZQUIERDA ══════════════ -->
                <div>

                    <!-- 1. ESTANCIA -->
                    <div class="rsv-card">
                        <div class="rsv-card-header">
                            <div class="icon icon-blue"><i class="bi bi-calendar3"></i></div>
                            <div>
                                <h6>Detalles de la Estancia</h6>
                                <p class="sub">Habitación, fechas y tarifa</p>
                            </div>
                        </div>
                        <div class="rsv-card-body">

                            <!-- Habitación -->
                            <div class="field-group">
                                <label class="field-label" for="unit_id">
                                    Habitación / Unidad <span class="req">*</span>
                                </label>
                                <select name="unit_id" id="unit_id" class="form-select trigger-calc" required>
                                    <option value="">Seleccionar unidad...</option>
                                    <?php foreach ($units as $u): ?>
                                        <option value="<?= $u['id'] ?>"
                                                data-capacity="<?= $u['base_occupancy'] ?? 2 ?>"
                                                data-max="<?= $u['max_occupancy'] ?? 10 ?>">
                                            <?= esc($u['name']) ?> — <?= esc($u['type_name']) ?>
                                            <?php if($u['base_occupancy']): ?>(base <?= $u['base_occupancy'] ?> pax)<?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Fechas -->
                            <div class="field-group">
                                <label class="field-label">
                                    Fechas <span class="req">*</span>
                                </label>
                                <div class="date-range-row">
                                    <div>
                                        <label class="hint mb-1 d-block">Check-In</label>
                                        <input type="date" name="check_in" id="check_in"
                                               class="form-control trigger-calc" required
                                               value="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="date-sep">→</div>
                                    <div>
                                        <label class="hint mb-1 d-block">Check-Out</label>
                                        <input type="date" name="check_out" id="check_out"
                                               class="form-control trigger-calc" required
                                               value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                    </div>
                                </div>
                                <div id="nights-chip" class="nights-chip hidden">
                                    <i class="bi bi-moon-stars-fill"></i>
                                    <span id="nights-label">1 noche</span>
                                </div>
                                <div class="invalid-msg" id="dates-error">
                                    <i class="bi bi-exclamation-circle"></i> La salida debe ser posterior a la entrada.
                                </div>
                            </div>

                            <!-- Plan tarifario -->
                            <div class="field-group">
                                <label class="field-label" for="rate_plan_id">
                                    Plan Tarifario <span class="req">*</span>
                                </label>
                                <select name="rate_plan_id" id="rate_plan_id" class="form-select trigger-calc" required>
                                    <?php foreach ($rate_plans as $rp): ?>
                                        <option value="<?= $rp['id'] ?>" <?= $rp['is_default'] ? 'selected' : '' ?>>
                                            <?= esc($rp['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Ocupación -->
                            <div class="field-group">
                                <label class="field-label">Ocupación</label>
                                <div class="occ-grid">
                                    <div class="occ-box">
                                        <div class="occ-box-info">
                                            <div class="occ-label">Adultos</div>
                                            <div class="occ-sub">≥ 18 años</div>
                                        </div>
                                        <div class="occ-stepper">
                                            <button type="button" class="occ-btn" id="adults-minus" disabled>−</button>
                                            <span class="occ-val" id="adults-val">1</span>
                                            <button type="button" class="occ-btn" id="adults-plus">+</button>
                                        </div>
                                    </div>
                                    <div class="occ-box">
                                        <div class="occ-box-info">
                                            <div class="occ-label">Niños</div>
                                            <div class="occ-sub">< 18 años</div>
                                        </div>
                                        <div class="occ-stepper">
                                            <button type="button" class="occ-btn" id="children-minus" disabled>−</button>
                                            <span class="occ-val" id="children-val">0</span>
                                            <button type="button" class="occ-btn" id="children-plus">+</button>
                                        </div>
                                    </div>
                                </div>
                                <p class="hint" id="occ-hint"></p>
                            </div>

                        </div>
                    </div>

                    <!-- 2. TITULAR -->
                    <div class="rsv-card">
                        <div class="rsv-card-header">
                            <div class="icon icon-green"><i class="bi bi-person-badge"></i></div>
                            <div>
                                <h6>Titular de la Reserva</h6>
                                <p class="sub">Quien responde por la estadía</p>
                            </div>
                        </div>
                        <div class="rsv-card-body">
                            <div class="two-col">
                                <div class="field-group" style="grid-column: span 2;">
                                    <label class="field-label" for="full_name">
                                        Nombre Completo <span class="req">*</span>
                                    </label>
                                    <input type="text" name="full_name" id="full_name"
                                           class="form-control" required
                                           placeholder="Ej. María González Restrepo">
                                </div>
                                <div class="field-group">
                                    <label class="field-label" for="document">Documento</label>
                                    <input type="text" name="document" id="document"
                                           class="form-control"
                                           placeholder="CC / Pasaporte / CE">
                                </div>
                                <div class="field-group">
                                    <label class="field-label" for="phone">WhatsApp / Teléfono</label>
                                    <input type="text" name="phone" id="phone"
                                           class="form-control"
                                           placeholder="+57 300 000 0000">
                                </div>
                                <div class="field-group" style="grid-column: span 2;">
                                    <label class="field-label" for="email">Correo Electrónico</label>
                                    <input type="email" name="email" id="email"
                                           class="form-control"
                                           placeholder="cliente@correo.com">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. ACOMPAÑANTES -->
                    <div class="rsv-card" id="companions-card">
                        <div class="rsv-card-header">
                            <div class="icon icon-amber"><i class="bi bi-people"></i></div>
                            <div>
                                <h6>Acompañantes / Manifiesto</h6>
                                <p class="sub">Se genera automáticamente según la ocupación</p>
                            </div>
                            <span class="badge bg-secondary ms-auto" id="companions-badge" style="font-size:11px;">Solo titular</span>
                        </div>
                        <div class="rsv-card-body">
                            <div id="companions-container">
                                <div class="empty-companions">
                                    <i class="bi bi-person-plus"></i>
                                    Agrega adultos o niños en la sección de ocupación para registrar sus datos.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. CONTEXTO -->
                    <div class="rsv-card">
                        <div class="rsv-card-header">
                            <div class="icon icon-slate"><i class="bi bi-sliders"></i></div>
                            <div>
                                <h6>Contexto y Notas</h6>
                                <p class="sub">Origen, agente y observaciones</p>
                            </div>
                        </div>
                        <div class="rsv-card-body">
                            <div class="two-col">
                                <div class="field-group">
                                    <label class="field-label" for="source_id">Origen de la Reserva</label>
                                    <select name="source_id" id="source_id" class="form-select">
                                        <option value="">Sin especificar</option>
                                        <?php foreach ($sources as $s): ?>
                                            <option value="<?= $s['id'] ?>"><?= esc($s['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="field-group">
                                    <label class="field-label" for="agent_id">Agente / Comisionista</label>
                                    <select name="agent_id" id="agent_id" class="form-select">
                                        <option value="">Ninguno</option>
                                        <?php foreach ($agents as $a): ?>
                                            <option value="<?= $a['id'] ?>"><?= esc($a['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="field-group">
                                <label class="field-label" for="notes">Notas Especiales</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3"
                                          placeholder="Alergias, peticiones especiales, hora estimada de llegada..."></textarea>
                            </div>
                        </div>
                    </div>

                </div><!-- /col izquierda -->

                <!-- ══════════════ COLUMNA DERECHA (STICKY) ══════════════ -->
                <div class="rsv-sticky">

                    <!-- Panel de precio -->
                    <div class="price-panel">
                        <div class="label">Total Estimado</div>
                        <div class="amount">
                            <input type="number" step="0.01" name="total_price" id="total_price"
                                   required placeholder="0.00"
                                   title="Puedes editar el precio manualmente">
                        </div>
                        <div id="price-breakdown-wrap" style="display:none;">
                            <div class="price-breakdown" id="price-breakdown"></div>
                        </div>
                        <div class="price-loading" id="price-loading" style="display:none;">
                            <span class="spinner-xs"></span> Calculando...
                        </div>
                    </div>

                    <!-- Cupón -->
                    <div class="rsv-card">
                        <div class="rsv-card-body" style="padding: 14px 16px;">
                            <label class="field-label" for="promo_code">Código de Cupón</label>
                            <div class="promo-row">
                                <input type="text" name="promo_code" id="promo_code"
                                       class="form-control" placeholder="VERANO2026"
                                       style="text-transform:uppercase;">
                                <button type="button" class="btn-promo" id="btn-apply-promo">
                                    Aplicar
                                </button>
                            </div>
                            <div id="promo-feedback" class="hint mt-1"></div>
                        </div>
                    </div>

                    <!-- Estado inicial -->
                    <div class="rsv-card">
                        <div class="rsv-card-body" style="padding: 14px 16px;">
                            <label class="field-label">Estado Inicial</label>
                            <div class="status-pills">
                                <label class="status-pill active-pending" id="pill-pending">
                                    <input type="radio" name="initial_status" value="pending" checked>
                                    <i class="bi bi-clock"></i> Pendiente
                                </label>
                                <label class="status-pill" id="pill-confirmed">
                                    <input type="radio" name="initial_status" value="confirmed">
                                    <i class="bi bi-check-circle"></i> Confirmar ya
                                </label>
                            </div>
                            <p class="hint mt-2">
                                "Confirmar ya" si el pago está acordado y la reserva es firme.
                            </p>
                        </div>
                    </div>

                    <!-- Botón crear -->
                    <button type="submit" class="btn-create" id="btn-submit">
                        <i class="bi bi-calendar-check-fill"></i>
                        Crear Reserva
                    </button>

                    <p class="hint text-center mt-2">
                        Serás redirigido al folio de la reserva.
                    </p>

                </div><!-- /col derecha -->

            </div><!-- /rsv-grid -->
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /* ═══ Referencias DOM ═══ */
            const unitSelect    = document.getElementById('unit_id');
            const checkInInput  = document.getElementById('check_in');
            const checkOutInput = document.getElementById('check_out');
            const ratePlanSel   = document.getElementById('rate_plan_id');
            const promoInput    = document.getElementById('promo_code');
            const promoFeedback = document.getElementById('promo-feedback');
            const totalInput    = document.getElementById('total_price');

            const adultsVal     = document.getElementById('adults-val');
            const childrenVal   = document.getElementById('children-val');
            const adultsMinus   = document.getElementById('adults-minus');
            const adultsPlus    = document.getElementById('adults-plus');
            const childrenMinus = document.getElementById('children-minus');
            const childrenPlus  = document.getElementById('children-plus');
            const occHint       = document.getElementById('occ-hint');

            const adultsHidden   = document.getElementById('num_adults_hidden');
            const childrenHidden = document.getElementById('num_children_hidden');
            const promoIdHidden  = document.getElementById('promo_id_hidden');

            const companionsBadge = document.getElementById('companions-badge');
            const companionsContainer = document.getElementById('companions-container');

            const nightsChip  = document.getElementById('nights-chip');
            const nightsLabel = document.getElementById('nights-label');
            const datesError  = document.getElementById('dates-error');

            const priceLoading      = document.getElementById('price-loading');
            const priceBreakdownWrap = document.getElementById('price-breakdown-wrap');
            const priceBreakdown    = document.getElementById('price-breakdown');

            const pillPending   = document.getElementById('pill-pending');
            const pillConfirmed = document.getElementById('pill-confirmed');

            // CSRF
            window.csrfHash = '<?= csrf_hash() ?>';

            /* ═══ Estado local ═══ */
            let numAdults   = 1;
            let numChildren = 0;
            let maxOccupancy = 10;
            let calcDebounce = null;

            /* ═══ 1. STEPPERS DE OCUPACIÓN ═══ */
            function updateOccupancy() {
                adultsVal.textContent   = numAdults;
                childrenVal.textContent = numChildren;
                adultsHidden.value   = numAdults;
                childrenHidden.value = numChildren;

                adultsMinus.disabled   = numAdults <= 1;
                childrenMinus.disabled = numChildren <= 0;

                const total = numAdults + numChildren;
                adultsPlus.disabled   = total >= maxOccupancy;
                childrenPlus.disabled = total >= maxOccupancy;

                // Hint de capacidad
                const unit = unitSelect.options[unitSelect.selectedIndex];
                const base = unit ? parseInt(unit.dataset.capacity || 2) : 2;
                if (total > base) {
                    const extras = total - base;
                    occHint.innerHTML = `<span style="color:#b45309"><i class="bi bi-person-plus-fill"></i> ${extras} persona${extras>1?'s':''} extra${extras>1?'s':''} — se cobrará tarifa adicional</span>`;
                } else {
                    occHint.textContent = total > 1 ? `${total} personas dentro de la capacidad base (${base} pax)` : '';
                }

                generateCompanionFields();
                triggerCalc();
            }

            adultsPlus.addEventListener('click', () => { numAdults++;   updateOccupancy(); });
            adultsMinus.addEventListener('click', () => { if(numAdults > 1) { numAdults--; updateOccupancy(); } });
            childrenPlus.addEventListener('click', () => { numChildren++; updateOccupancy(); });
            childrenMinus.addEventListener('click', () => { if(numChildren > 0) { numChildren--; updateOccupancy(); } });

            // Actualizar max cuando cambia la unidad
            unitSelect.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                maxOccupancy = parseInt(opt.dataset.max || 10);
                updateOccupancy();
            });

            /* ═══ 2. NOCHES CHIP ═══ */
            function updateNightsChip() {
                const cin  = new Date(checkInInput.value);
                const cout = new Date(checkOutInput.value);
                datesError.style.display = 'none';
                checkInInput.classList.remove('is-invalid');
                checkOutInput.classList.remove('is-invalid');

                if (!checkInInput.value || !checkOutInput.value) {
                    nightsChip.classList.add('hidden'); return;
                }
                if (cout <= cin) {
                    nightsChip.classList.add('hidden');
                    datesError.style.display = 'block';
                    checkOutInput.classList.add('is-invalid');
                    return;
                }
                const nights = Math.round((cout - cin) / 86400000);
                nightsLabel.textContent = nights + (nights === 1 ? ' noche' : ' noches');
                nightsChip.classList.remove('hidden');
            }

            checkInInput.addEventListener('change', () => { updateNightsChip(); triggerCalc(); });
            checkOutInput.addEventListener('change', () => { updateNightsChip(); triggerCalc(); });
            ratePlanSel.addEventListener('change', triggerCalc);

            /* ═══ 3. GENERAR ACOMPAÑANTES ═══ */
            function generateCompanionFields() {
                const totalCompanions = (numAdults - 1) + numChildren; // titular no cuenta
                companionsContainer.innerHTML = '';

                if (totalCompanions <= 0) {
                    companionsBadge.textContent = 'Solo titular';
                    companionsBadge.className = 'badge bg-secondary ms-auto';
                    companionsContainer.innerHTML = `
                <div class="empty-companions">
                    <i class="bi bi-person-plus"></i>
                    Agrega adultos o niños en la sección de ocupación para registrar sus datos.
                </div>`;
                    return;
                }

                companionsBadge.textContent = totalCompanions + (totalCompanions === 1 ? ' acompañante' : ' acompañantes');
                companionsBadge.className = 'badge bg-primary ms-auto';

                let idx = 0;

                // Adultos extra (adultos - 1 titular)
                for (let i = 0; i < numAdults - 1; i++) {
                    idx++;
                    companionsContainer.insertAdjacentHTML('beforeend', companionRowHTML(idx, 'adult', i + 2));
                }

                // Niños
                for (let i = 0; i < numChildren; i++) {
                    idx++;
                    companionsContainer.insertAdjacentHTML('beforeend', companionRowHTML(idx, 'child', i + 1));
                }
            }

            function companionRowHTML(idx, type, num) {
                const label = type === 'adult' ? `Adulto ${num}` : `Niño/a ${num}`;
                const badge = type === 'adult'
                    ? `<span class="companion-type-badge badge-adult"><i class="bi bi-person"></i> Adulto</span>`
                    : `<span class="companion-type-badge badge-child"><i class="bi bi-person-hearts"></i> Niño/a</span>`;

                return `
        <div class="companion-row">
            <div class="companion-header">
                <span class="companion-label">${label}</span>
                ${badge}
            </div>
            <div class="companion-grid">
                <div>
                    <label class="field-label" style="font-size:11px;">Nombres</label>
                    <input type="text" name="extra_guest_name[]"
                           class="form-control form-control-sm" placeholder="Nombre">
                </div>
                <div>
                    <label class="field-label" style="font-size:11px;">Apellidos</label>
                    <input type="text" name="extra_guest_lastname[]"
                           class="form-control form-control-sm" placeholder="Apellido">
                </div>
                <div>
                    <label class="field-label" style="font-size:11px;">Tipo Doc.</label>
                    <select name="extra_guest_doc_type[]" class="form-select form-select-sm">
                        <option value="">—</option>
                        <option value="CC">CC</option>
                        <option value="TI">TI</option>
                        <option value="CE">CE</option>
                        <option value="PA">Pasaporte</option>
                    </select>
                </div>
                <div>
                    <label class="field-label" style="font-size:11px;">Documento</label>
                    <input type="text" name="extra_guest_doc_number[]"
                           class="form-control form-control-sm" placeholder="Número">
                </div>
            </div>
        </div>`;
            }

            /* ═══ 4. CÁLCULO DE PRECIO ═══ */
            function triggerCalc() {
                clearTimeout(calcDebounce);
                calcDebounce = setTimeout(calculatePrice, 300);
            }

            async function calculatePrice() {
                const unitId    = unitSelect.value;
                const checkIn   = checkInInput.value;
                const checkOut  = checkOutInput.value;
                const ratePlanId = ratePlanSel.value;
                const promoCode = promoInput.value.trim();

                if (!unitId || !checkIn || !checkOut || !ratePlanId) return;
                if (new Date(checkIn) >= new Date(checkOut)) return;

                priceLoading.style.display = 'flex';
                priceBreakdownWrap.style.display = 'none';

                try {
                    const fd = new FormData();
                    fd.append('accommodation_unit_id', unitId);
                    fd.append('check_in_date',  checkIn);
                    fd.append('check_out_date', checkOut);
                    fd.append('num_adults',   numAdults);      // ← BUG FIX: enviamos adultos y niños por separado
                    fd.append('num_children', numChildren);    // ← BUG FIX
                    fd.append('rate_plan_id', ratePlanId);
                    fd.append('promo_code',   promoCode);
                    fd.append('<?= csrf_token() ?>', window.csrfHash);

                    const res  = await fetch('<?= base_url('reservations/calculate-price') ?>', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: fd
                    });
                    const data = await res.json();

                    if (data.csrf_token) {
                        window.csrfHash = data.csrf_token;
                        const csrfInput = document.querySelector('input[name="<?= csrf_token() ?>"]');
                        if (csrfInput) csrfInput.value = data.csrf_token;
                    }

                    if (data.success) {
                        totalInput.value = data.total_price;
                        promoIdHidden.value = data.promo_id || '';
                        renderBreakdown(data);
                    } else {
                        priceBreakdown.innerHTML = `<div style="color:#fca5a5;font-size:12px;"><i class="bi bi-exclamation-triangle"></i> ${data.message}</div>`;
                        priceBreakdownWrap.style.display = 'block';
                    }
                } catch(e) {
                    console.error('[RSV] Error en cálculo:', e);
                } finally {
                    priceLoading.style.display = 'none';
                }
            }

            function renderBreakdown(data) {
                const fmt = v => parseFloat(v).toLocaleString('es-CO');
                let html = `<div class="row-item"><span><i class="bi bi-moon"></i> Alojamiento (${data.nights} noche${data.nights>1?'s':''})</span><span>$${fmt(data.room_total)}</span></div>`;

                if (data.extra_persons > 0) {
                    html += `<div class="row-item"><span><i class="bi bi-person-plus"></i> Personas extra (${data.extra_persons})</span><span>$${fmt(data.extra_person_total)}</span></div>`;
                }
                if (data.promo_applied) {
                    html += `<div class="row-item discount"><span><i class="bi bi-tag-fill"></i> Descuento cupón</span><span>−$${fmt(data.discount_amount)}</span></div>`;
                    promoFeedback.innerHTML = `<span style="color:#057a55"><i class="bi bi-check-circle-fill"></i> Cupón aplicado: −$${fmt(data.discount_amount)}</span>`;
                }
                html += `<div class="row-item total"><span>Total</span><span>$${fmt(data.total_price)}</span></div>`;
                html += `<div style="opacity:.6;font-size:11px;margin-top:6px;text-align:right;">Capacidad base: ${data.base_capacity} pax</div>`;

                priceBreakdown.innerHTML = html;
                priceBreakdownWrap.style.display = 'block';
            }

            /* ═══ 5. CUPÓN (botón explícito) ═══ */
            document.getElementById('btn-apply-promo').addEventListener('click', function() {
                promoFeedback.innerHTML = '';
                promoIdHidden.value = '';
                calculatePrice();
            });

            /* ═══ 6. STATUS PILLS ═══ */
            document.querySelectorAll('input[name="initial_status"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    pillPending.className   = 'status-pill' + (this.value === 'pending'   ? ' active-pending'   : '');
                    pillConfirmed.className = 'status-pill' + (this.value === 'confirmed' ? ' active-confirmed' : '');
                });
            });

            /* ═══ 7. VALIDACIÓN SUBMIT ═══ */
            document.getElementById('reservation-form').addEventListener('submit', function(e) {
                let valid = true;

                if (!unitSelect.value)    { unitSelect.classList.add('is-invalid'); valid = false; }
                if (!checkInInput.value)  { checkInInput.classList.add('is-invalid'); valid = false; }
                if (!checkOutInput.value) { checkOutInput.classList.add('is-invalid'); valid = false; }
                if (checkInInput.value && checkOutInput.value && new Date(checkInInput.value) >= new Date(checkOutInput.value)) {
                    datesError.style.display = 'block'; valid = false;
                }
                if (!document.getElementById('full_name').value.trim()) {
                    document.getElementById('full_name').classList.add('is-invalid'); valid = false;
                }
                if (!totalInput.value || parseFloat(totalInput.value) <= 0) {
                    totalInput.classList.add('is-invalid'); valid = false;
                }

                if (!valid) { e.preventDefault(); }
            });

            // Limpiar is-invalid al corregir
            document.querySelectorAll('.form-control, .form-select').forEach(el => {
                el.addEventListener('input', () => el.classList.remove('is-invalid'));
                el.addEventListener('change', () => el.classList.remove('is-invalid'));
            });

            /* ═══ INIT ═══ */
            updateNightsChip();
            updateOccupancy();  // Dibuja estado inicial y dispara primer cálculo si hay datos
        });
    </script>

<?= $this->endSection() ?>