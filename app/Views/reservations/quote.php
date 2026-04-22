<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <style>
        /* ══════════════════════════════════════════
           COTIZADOR — Design System
           Tono: operacional premium, warm dark
        ══════════════════════════════════════════ */
        @import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&display=swap');

        :root {
            --q-bg:        #f2f3f5;
            --q-surface:   #ffffff;
            --q-border:    #e4e7ec;
            --q-text:      #0d1117;
            --q-sub:       #5c6472;
            --q-muted:     #9aa3ae;

            --q-ink:       #1a1f2e;       /* dark panel */
            --q-gold:      #c9a84c;       /* accent cálido */
            --q-gold-lt:   #fdf6e3;
            --q-blue:      #1d4ed8;
            --q-blue-lt:   #eff6ff;
            --q-green:     #059669;
            --q-green-lt:  #ecfdf5;
            --q-red:       #dc2626;
            --q-red-lt:    #fef2f2;
            --q-amber:     #d97706;
            --q-amber-lt:  #fffbeb;

            --radius:      14px;
            --radius-sm:   9px;
            --shadow:      0 1px 4px rgba(0,0,0,.07), 0 0 0 1px rgba(0,0,0,.04);
            --shadow-lg:   0 8px 32px rgba(0,0,0,.12);
        }

        body { font-family: 'DM Sans', system-ui, sans-serif; background: var(--q-bg); }

        /* ── Header ── */
        .q-header {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;
        }
        .q-header-left h1 {
            font-family: 'DM Serif Display', serif;
            font-size: 26px; color: var(--q-text);
            margin: 0 0 3px; letter-spacing: -.02em;
        }
        .q-header-left p { font-size: 13.5px; color: var(--q-sub); margin: 0; }

        /* ── Panel de búsqueda ── */
        .q-search-panel {
            background: var(--q-ink);
            border-radius: var(--radius);
            padding: 24px 28px;
            margin-bottom: 28px;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }
        .q-search-panel::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(201,168,76,.15) 0%, transparent 70%);
            pointer-events: none;
        }
        .q-search-title {
            font-family: 'DM Serif Display', serif;
            font-size: 15px; color: rgba(255,255,255,.9);
            margin: 0 0 18px; display: flex; align-items: center; gap: 8px;
        }
        .q-search-title i { color: var(--q-gold); }

        .q-search-grid {
            display: grid;
            grid-template-columns: 1fr 1fr auto auto auto auto;
            gap: 10px; align-items: end;
        }
        @media (max-width: 900px) {
            .q-search-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 500px) {
            .q-search-grid { grid-template-columns: 1fr; }
        }

        .q-field label {
            display: block; font-size: 11px; font-weight: 600;
            color: rgba(255,255,255,.5); margin-bottom: 5px;
            text-transform: uppercase; letter-spacing: .07em;
        }
        .q-input, .q-select {
            border: 1.5px solid rgba(255,255,255,.12);
            border-radius: var(--radius-sm); padding: 10px 13px;
            font-size: 14px; color: #fff;
            background: rgba(255,255,255,.08);
            width: 100%; transition: all .15s;
            font-family: 'DM Sans', sans-serif;
        }
        .q-input:focus, .q-select:focus {
            border-color: var(--q-gold);
            background: rgba(255,255,255,.12);
            outline: none;
            box-shadow: 0 0 0 3px rgba(201,168,76,.2);
        }
        .q-input::placeholder { color: rgba(255,255,255,.3); }
        .q-select option { background: #1a1f2e; color: #fff; }

        /* Steppers ocupación */
        .q-occ {
            display: flex; align-items: center; gap: 8px;
        }
        .q-occ-btn {
            width: 30px; height: 30px; border-radius: 50%;
            border: 1.5px solid rgba(255,255,255,.2);
            background: rgba(255,255,255,.08); color: #fff;
            font-size: 16px; cursor: pointer; display: flex;
            align-items: center; justify-content: center;
            transition: all .15s;
        }
        .q-occ-btn:hover:not(:disabled) {
            border-color: var(--q-gold); background: rgba(201,168,76,.2); color: var(--q-gold);
        }
        .q-occ-btn:disabled { opacity: .3; cursor: not-allowed; }
        .q-occ-val {
            font-size: 17px; font-weight: 700; color: #fff;
            min-width: 22px; text-align: center;
        }
        .q-occ-label {
            font-size: 11px; color: rgba(255,255,255,.4);
            text-align: center; margin-top: 3px; text-transform: uppercase; letter-spacing: .05em;
        }

        /* Botón buscar */
        .btn-quote {
            padding: 11px 24px;
            background: var(--q-gold);
            color: var(--q-ink);
            border: none; border-radius: var(--radius-sm);
            font-size: 14px; font-weight: 700;
            cursor: pointer; white-space: nowrap;
            display: flex; align-items: center; gap: 7px;
            transition: all .15s; font-family: 'DM Sans', sans-serif;
            height: 44px;
        }
        .btn-quote:hover  { background: #b8963d; transform: translateY(-1px); }
        .btn-quote:active { transform: scale(.98); }
        .btn-quote:disabled { opacity: .6; cursor: not-allowed; transform: none; }

        /* Noches calculadas */
        .nights-pill {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(201,168,76,.2); color: var(--q-gold);
            padding: 4px 10px; border-radius: 20px;
            font-size: 12px; font-weight: 600; margin-top: 12px;
        }

        /* ── Banner IA ── */
        .ai-banner {
            background: linear-gradient(135deg, #1a1f2e 0%, #0f172a 100%);
            border: 1px solid rgba(201,168,76,.3);
            border-radius: var(--radius);
            padding: 18px 22px;
            margin-bottom: 20px;
            display: flex; align-items: center; gap: 16px;
            animation: fadeUp .4s ease;
        }
        .ai-banner-icon {
            width: 44px; height: 44px; border-radius: 12px;
            background: linear-gradient(135deg, #c9a84c, #e6c56b);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; flex-shrink: 0;
        }
        .ai-banner-content { flex: 1; min-width: 0; }
        .ai-banner-title {
            font-size: 13px; font-weight: 700; color: #fff;
            margin-bottom: 4px;
        }
        .ai-banner-chips { display: flex; flex-wrap: wrap; gap: 8px; }

        .ai-chip {
            display: flex; align-items: flex-start; gap: 8px;
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 9px; padding: 8px 12px;
            flex: 1; min-width: 200px;
            transition: background .15s;
        }
        .ai-chip:hover { background: rgba(255,255,255,.1); }
        .ai-chip-badge {
            font-size: 10px; font-weight: 700; padding: 2px 7px;
            border-radius: 20px; white-space: nowrap; flex-shrink: 0;
            margin-top: 1px;
        }
        .badge-guest  { background: rgba(201,168,76,.3); color: var(--q-gold); }
        .badge-hotel  { background: rgba(29,78,216,.4);  color: #93c5fd; }
        .ai-chip-text { font-size: 12px; color: rgba(255,255,255,.75); line-height: 1.4; }
        .ai-insight {
            font-size: 11.5px; color: rgba(255,255,255,.45);
            margin-top: 8px; display: flex; align-items: center; gap: 5px;
        }

        .ai-loading {
            display: flex; align-items: center; gap: 10px;
            color: rgba(255,255,255,.5); font-size: 13px;
        }
        .ai-spinner {
            width: 18px; height: 18px;
            border: 2px solid rgba(201,168,76,.3);
            border-top-color: var(--q-gold);
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        /* ── Grid de resultados ── */
        .q-results-header {
            display: flex; align-items: center;
            justify-content: space-between; margin-bottom: 16px;
            flex-wrap: wrap; gap: 8px;
        }
        .q-results-title {
            font-size: 14px; font-weight: 700; color: var(--q-sub);
            text-transform: uppercase; letter-spacing: .06em;
        }
        .q-results-count {
            font-size: 12px; color: var(--q-muted);
            background: #fff; padding: 3px 10px;
            border-radius: 20px; border: 1px solid var(--q-border);
        }

        .q-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 18px;
        }
        @media (max-width: 760px) {
            .q-grid { grid-template-columns: 1fr; }
        }

        /* ── Card de unidad ── */
        .unit-card {
            background: var(--q-surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1.5px solid var(--q-border);
            transition: box-shadow .2s, transform .15s, border-color .15s;
            animation: fadeUp .35s ease both;
            display: flex; flex-direction: column;
        }
        .unit-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }
        .unit-card.unavailable { opacity: .6; }
        .unit-card.ai-guest-pick {
            border-color: var(--q-gold);
            box-shadow: 0 0 0 2px rgba(201,168,76,.25), var(--shadow-lg);
        }
        .unit-card.ai-hotel-pick {
            border-color: var(--q-blue);
            box-shadow: 0 0 0 2px rgba(29,78,216,.2), var(--shadow-lg);
        }

        /* Cabecera de la card */
        .unit-card-head {
            padding: 16px 18px 14px;
            border-bottom: 1px solid var(--q-border);
            display: flex; align-items: flex-start;
            justify-content: space-between; gap: 10px;
        }
        .unit-name {
            font-family: 'DM Serif Display', serif;
            font-size: 17px; color: var(--q-text);
            margin: 0 0 3px; line-height: 1.2;
        }
        .unit-meta {
            font-size: 12px; color: var(--q-sub);
            display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
        }
        .unit-meta-sep { color: var(--q-border); }

        .avail-badge {
            padding: 4px 10px; border-radius: 20px;
            font-size: 11.5px; font-weight: 700;
            white-space: nowrap; flex-shrink: 0;
        }
        .avail-ok  { background: var(--q-green-lt); color: var(--q-green); }
        .avail-no  { background: var(--q-red-lt);   color: var(--q-red); }

        /* Amenidades */
        .unit-amenities {
            display: flex; flex-wrap: wrap; gap: 5px;
            padding: 10px 18px; border-bottom: 1px solid var(--q-border);
        }
        .amenity-pill {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 8px; border-radius: 20px;
            font-size: 11px; font-weight: 500;
            background: var(--q-bg); color: var(--q-sub);
            border: 1px solid var(--q-border);
        }

        /* Planes (tabs) */
        .plan-tabs {
            display: flex; border-bottom: 1px solid var(--q-border);
            overflow-x: auto; padding: 0 18px; gap: 0;
        }
        .plan-tab {
            padding: 9px 14px; font-size: 12.5px; font-weight: 600;
            color: var(--q-sub); border: none; background: none;
            cursor: pointer; white-space: nowrap;
            border-bottom: 2.5px solid transparent;
            transition: all .15s; flex-shrink: 0;
            font-family: 'DM Sans', sans-serif;
        }
        .plan-tab:hover  { color: var(--q-text); }
        .plan-tab.active { color: var(--q-blue); border-bottom-color: var(--q-blue); }

        /* Contenido del plan activo */
        .plan-content { padding: 14px 18px; flex: 1; }

        .price-hero {
            display: flex; align-items: baseline; gap: 8px;
            margin-bottom: 10px;
        }
        .price-total {
            font-family: 'DM Serif Display', serif;
            font-size: 30px; color: var(--q-text); line-height: 1;
        }
        .price-per-night {
            font-size: 12.5px; color: var(--q-sub);
        }

        .price-breakdown {
            display: flex; flex-direction: column; gap: 4px;
            margin-bottom: 12px;
        }
        .price-row {
            display: flex; justify-content: space-between;
            font-size: 12.5px;
        }
        .price-row .label { color: var(--q-sub); }
        .price-row .value { font-weight: 600; color: var(--q-text); }
        .price-row.extra  .value { color: var(--q-amber); }
        .price-row.season .value { color: var(--q-green); }
        .price-row.divider {
            border-top: 1px solid var(--q-border);
            padding-top: 4px; margin-top: 2px;
        }
        .price-row.divider .label { font-weight: 700; color: var(--q-text); }
        .price-row.divider .value { font-weight: 800; font-size: 14px; }

        /* Plan amenities chips */
        .plan-includes {
            display: flex; flex-wrap: wrap; gap: 4px;
            margin-bottom: 12px;
        }
        .plan-inc-chip {
            display: inline-flex; align-items: center; gap: 3px;
            padding: 2px 7px; border-radius: 20px;
            font-size: 11px; font-weight: 600;
            background: var(--q-green-lt); color: var(--q-green);
        }

        /* Cancelación */
        .cancel-badge {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 11px; padding: 2px 8px; border-radius: 20px;
            font-weight: 600; margin-bottom: 12px;
        }
        .cancel-flexible    { background: var(--q-green-lt); color: var(--q-green); }
        .cancel-moderate    { background: var(--q-blue-lt);  color: var(--q-blue); }
        .cancel-strict      { background: var(--q-amber-lt); color: var(--q-amber); }
        .cancel-non_refundable { background: var(--q-red-lt); color: var(--q-red); }

        /* Footer de la card */
        .unit-card-footer {
            padding: 12px 18px;
            border-top: 1px solid var(--q-border);
            background: var(--q-bg);
            display: flex; gap: 8px; align-items: center;
        }
        .btn-reserve {
            flex: 1; padding: 10px;
            background: var(--q-blue); color: #fff;
            border: none; border-radius: var(--radius-sm);
            font-size: 13px; font-weight: 700;
            cursor: pointer; text-align: center;
            text-decoration: none; display: block;
            transition: background .15s;
            font-family: 'DM Sans', sans-serif;
        }
        .btn-reserve:hover { background: #1e40af; color: #fff; }
        .btn-reserve:disabled { opacity: .4; cursor: not-allowed; background: var(--q-muted); }

        .btn-detail {
            padding: 10px 12px;
            border: 1.5px solid var(--q-border);
            background: #fff; border-radius: var(--radius-sm);
            font-size: 13px; color: var(--q-sub); cursor: pointer;
            transition: all .15s; font-family: 'DM Sans', sans-serif;
        }
        .btn-detail:hover { border-color: #9ca3af; color: var(--q-text); }

        /* ── AI pick ribbon ── */
        .ai-ribbon {
            position: absolute; top: 10px; left: -26px;
            width: 100px; padding: 4px 0;
            background: var(--q-gold); color: var(--q-ink);
            font-size: 9.5px; font-weight: 800;
            text-align: center; transform: rotate(-35deg);
            text-transform: uppercase; letter-spacing: .06em;
            pointer-events: none; z-index: 2;
        }

        /* ── Empty / loading states ── */
        .q-empty {
            text-align: center; padding: 60px 20px;
            color: var(--q-muted);
        }
        .q-empty i { font-size: 36px; display: block; margin-bottom: 12px; opacity: .3; }
        .q-empty h4 { font-size: 16px; font-weight: 700; color: var(--q-sub); margin-bottom: 6px; }
        .q-empty p  { font-size: 13px; margin: 0; }

        .q-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.4s infinite;
            border-radius: 8px;
        }
        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* ── Daily detail toggle ── */
        .daily-toggle {
            font-size: 11.5px; color: var(--q-blue);
            cursor: pointer; background: none; border: none;
            padding: 0; display: flex; align-items: center; gap: 4px;
            font-family: 'DM Sans', sans-serif; margin-bottom: 8px;
        }
        .daily-detail {
            display: none; background: var(--q-bg);
            border-radius: 8px; padding: 10px 12px; margin-bottom: 8px;
            max-height: 180px; overflow-y: auto;
        }
        .daily-row {
            display: flex; justify-content: space-between;
            font-size: 11.5px; padding: 2px 0;
            border-bottom: 1px solid var(--q-border);
        }
        .daily-row:last-child { border-bottom: none; }
        .daily-row .season-tag {
            font-size: 10px; color: var(--q-gold); font-weight: 600;
            margin-left: 4px;
        }

        /* ── Animaciones ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>

    <!-- ════════════════════ HEADER ════════════════════ -->
    <div class="q-header">
        <div class="q-header-left">
            <h1>Cotizador de Reservas</h1>
            <p>Consulta disponibilidad, precios y planes en tiempo real</p>
        </div>
        <a href="<?= base_url('/reservations/create') ?>"
           style="font-size:13px;color:var(--q-sub);text-decoration:none;display:flex;align-items:center;gap:5px;">
            <i class="bi bi-plus-lg"></i> Nueva Reserva Manual
        </a>
    </div>

    <!-- ════════════════════ PANEL DE BÚSQUEDA ════════════════════ -->
    <div class="q-search-panel">
        <div class="q-search-title">
            <i class="bi bi-search"></i> ¿Para cuándo y cuántas personas?
        </div>
        <div class="q-search-grid">

            <!-- Check-in -->
            <div class="q-field">
                <label>Check-In</label>
                <input type="date" id="q-checkin" class="q-input"
                       value="<?= date('Y-m-d') ?>"
                       min="<?= date('Y-m-d') ?>">
            </div>

            <!-- Check-out -->
            <div class="q-field">
                <label>Check-Out</label>
                <input type="date" id="q-checkout" class="q-input"
                       value="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
            </div>

            <!-- Adultos -->
            <div class="q-field">
                <label>Adultos</label>
                <div>
                    <div class="q-occ">
                        <button type="button" class="q-occ-btn" id="ad-minus" disabled>−</button>
                        <span class="q-occ-val" id="ad-val">2</span>
                        <button type="button" class="q-occ-btn" id="ad-plus">+</button>
                    </div>
                    <div class="q-occ-label">≥ 18 años</div>
                </div>
            </div>

            <!-- Niños -->
            <div class="q-field">
                <label>Niños</label>
                <div>
                    <div class="q-occ">
                        <button type="button" class="q-occ-btn" id="ch-minus" disabled>−</button>
                        <span class="q-occ-val" id="ch-val">0</span>
                        <button type="button" class="q-occ-btn" id="ch-plus">+</button>
                    </div>
                    <div class="q-occ-label">< 18 años</div>
                </div>
            </div>

            <!-- Spacer + botón -->
            <div></div>
            <div class="q-field" style="display:flex;align-items:flex-end;">
                <button type="button" class="btn-quote" id="btn-search" onclick="runSearch()">
                    <i class="bi bi-search"></i> Buscar opciones
                </button>
            </div>
        </div>

        <div id="nights-display" style="display:none;">
        <span class="nights-pill">
            <i class="bi bi-moon-stars-fill"></i>
            <span id="nights-count">1 noche</span>
        </span>
        </div>
    </div>

    <!-- ════════════════════ RECOMENDACIÓN IA ════════════════════ -->
    <div id="ai-section" style="display:none;"></div>

    <!-- ════════════════════ RESULTADOS ════════════════════ -->
    <div id="results-section" style="display:none;">
        <div class="q-results-header">
            <div class="q-results-title">Opciones disponibles</div>
            <div class="q-results-count" id="results-count"></div>
        </div>
        <div class="q-grid" id="results-grid"></div>
    </div>

    <!-- Estado inicial -->
    <div id="empty-state" class="q-empty">
        <i class="bi bi-calendar-heart"></i>
        <h4>Ingresa las fechas para comenzar</h4>
        <p>Verás todas las unidades disponibles con precios, planes y la recomendación IA.</p>
    </div>

    <!-- Loading skeleton -->
    <div id="loading-state" style="display:none;">
        <div class="q-grid">
            <?php for($i=0;$i<4;$i++): ?>
                <div style="background:#fff;border-radius:14px;border:1.5px solid #e4e7ec;overflow:hidden;height:320px;padding:20px;">
                    <div class="q-skeleton" style="height:20px;width:60%;margin-bottom:10px;"></div>
                    <div class="q-skeleton" style="height:14px;width:40%;margin-bottom:20px;"></div>
                    <div class="q-skeleton" style="height:36px;width:50%;margin-bottom:16px;"></div>
                    <div class="q-skeleton" style="height:12px;width:80%;margin-bottom:8px;"></div>
                    <div class="q-skeleton" style="height:12px;width:65%;margin-bottom:8px;"></div>
                    <div class="q-skeleton" style="height:12px;width:70%;"></div>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <script>
        // ════════════════════════════════════════════════
        // ESTADO GLOBAL
        // ════════════════════════════════════════════════
        let adults   = 2;
        let children = 0;
        let lastResults  = [];
        let lastMeta     = {};
        let aiSuggestion = null;

        const currency = '<?= session('currency_symbol') ?: '$' ?>';

        // Catálogo de amenidades para mostrar chips
        const amenityCatalog = {
            wifi:         { label: 'WiFi',          icon: 'bi-wifi' },
            ac:           { label: 'A/C',           icon: 'bi-snow' },
            tv:           { label: 'Smart TV',      icon: 'bi-tv' },
            kitchen:      { label: 'Cocina',        icon: 'bi-cup-hot' },
            minibar:      { label: 'Minibar',       icon: 'bi-box-seam' },
            hot_water:    { label: 'Agua caliente', icon: 'bi-droplet-half' },
            pet_friendly: { label: 'Pet Friendly',  icon: 'bi-suit-heart' },
            balcony:      { label: 'Balcón',        icon: 'bi-brightness-alt-high' },
            jacuzzi:      { label: 'Jacuzzi',       icon: 'bi-water' },
            pool:         { label: 'Piscina',       icon: 'bi-water' },
            parking:      { label: 'Parking',       icon: 'bi-car-front' },
            safe:         { label: 'Caja fuerte',   icon: 'bi-safe' },
            fan:          { label: 'Ventilador',    icon: 'bi-wind' },
            bbq:          { label: 'BBQ',           icon: 'bi-fire' },
            work_desk:    { label: 'Escritorio',    icon: 'bi-pc-display' },
            breakfast:    { label: 'Desayuno',      icon: 'bi-cup-hot-fill' },
            lunch:        { label: 'Almuerzo',      icon: 'bi-sun-fill' },
            dinner:       { label: 'Cena',          icon: 'bi-moon-stars-fill' },
            all_inclusive:{ label: 'Todo Incl.',    icon: 'bi-stars' },
            airport_transfer: { label: 'Traslado', icon: 'bi-airplane-fill' },
            late_checkout:{ label: 'Late C/O',     icon: 'bi-clock-history' },
            free_cancellation: { label: 'Cancelación gratis', icon: 'bi-shield-check-fill' },
            non_refundable:    { label: 'No reembolsable',    icon: 'bi-shield-x-fill' },
            wifi_premium:      { label: 'WiFi Premium',       icon: 'bi-wifi' },
        };

        const cancelLabels = {
            flexible:       { label: 'Cancelación flexible',   cls: 'cancel-flexible'     },
            moderate:       { label: 'Cancelación moderada',   cls: 'cancel-moderate'     },
            strict:         { label: 'Cancelación estricta',   cls: 'cancel-strict'       },
            non_refundable: { label: 'No reembolsable',        cls: 'cancel-non_refundable' },
        };

        // ════════════════════════════════════════════════
        // STEPPERS
        // ════════════════════════════════════════════════
        function syncAdults() {
            document.getElementById('ad-val').textContent = adults;
            document.getElementById('ad-minus').disabled  = adults <= 1;
        }
        function syncChildren() {
            document.getElementById('ch-val').textContent  = children;
            document.getElementById('ch-minus').disabled   = children <= 0;
        }
        document.getElementById('ad-plus') .addEventListener('click', () => { adults++;   syncAdults(); });
        document.getElementById('ad-minus').addEventListener('click', () => { if(adults>1) { adults--; syncAdults(); } });
        document.getElementById('ch-plus') .addEventListener('click', () => { children++; syncChildren(); });
        document.getElementById('ch-minus').addEventListener('click', () => { if(children>0) { children--; syncChildren(); } });
        syncAdults(); syncChildren();

        // ════════════════════════════════════════════════
        // NOCHES CHIP
        // ════════════════════════════════════════════════
        function updateNightsChip() {
            const ci = new Date(document.getElementById('q-checkin').value);
            const co = new Date(document.getElementById('q-checkout').value);
            if (!isNaN(ci) && !isNaN(co) && co > ci) {
                const n = Math.round((co - ci) / 86400000);
                document.getElementById('nights-count').textContent = n + (n===1?' noche':' noches');
                document.getElementById('nights-display').style.display = 'block';
                // Sincronizar min del checkout
                document.getElementById('q-checkout').min = document.getElementById('q-checkin').value;
            } else {
                document.getElementById('nights-display').style.display = 'none';
            }
        }
        document.getElementById('q-checkin') .addEventListener('change', updateNightsChip);
        document.getElementById('q-checkout').addEventListener('change', updateNightsChip);
        updateNightsChip();

        // ════════════════════════════════════════════════
        // BÚSQUEDA PRINCIPAL
        // ════════════════════════════════════════════════
        async function runSearch() {
            const checkIn  = document.getElementById('q-checkin').value;
            const checkOut = document.getElementById('q-checkout').value;

            if (!checkIn || !checkOut || new Date(checkIn) >= new Date(checkOut)) {
                alert('Selecciona fechas válidas (el check-out debe ser posterior al check-in).');
                return;
            }

            // UI: mostrar loading
            document.getElementById('empty-state')   .style.display = 'none';
            document.getElementById('results-section').style.display = 'none';
            document.getElementById('ai-section')     .style.display = 'none';
            document.getElementById('loading-state')  .style.display = 'block';
            document.getElementById('btn-search').disabled = true;

            try {
                const url = `<?= base_url('/reservations/quote/search') ?>?check_in=${checkIn}&check_out=${checkOut}&adults=${adults}&children=${children}`;
                const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();

                document.getElementById('loading-state').style.display = 'none';
                document.getElementById('btn-search').disabled = false;

                if (!data.success) {
                    showError(data.message || 'Error al buscar opciones.');
                    return;
                }

                lastResults = data.results;
                lastMeta    = data.meta;
                aiSuggestion= null;

                renderResults(data.results, data.meta);

                // Lanzar IA en paralelo (solo si hay resultados disponibles)
                const available = data.results.filter(r => r.is_available);
                if (available.length > 0) {
                    renderAiLoading();
                    fetchAiSuggestion(data.results, data.meta);
                }

            } catch (e) {
                document.getElementById('loading-state').style.display = 'none';
                document.getElementById('btn-search').disabled = false;
                showError('Error de conexión. Intenta de nuevo.');
                console.error(e);
            }
        }

        // ════════════════════════════════════════════════
        // RENDER DE RESULTADOS
        // ════════════════════════════════════════════════
        function renderResults(results, meta) {
            const grid   = document.getElementById('results-grid');
            const section= document.getElementById('results-section');
            const count  = document.getElementById('results-count');

            const avail = results.filter(r => r.is_available).length;
            count.textContent = `${avail} disponible${avail!==1?'s':''} · ${results.length} total`;

            if (results.length === 0) {
                grid.innerHTML = `<div class="q-empty" style="grid-column:1/-1;">
            <i class="bi bi-calendar-x"></i>
            <h4>Sin unidades con tarifa configurada</h4>
            <p>Verifica la matriz de precios en Planes Tarifarios.</p>
        </div>`;
            } else {
                grid.innerHTML = results.map((r, i) =>
                    renderUnitCard(r, i, meta)
                ).join('');
            }

            section.style.display = 'block';
        }

        function renderUnitCard(r, idx, meta) {
            const isGuest = aiSuggestion && aiSuggestion.best_for_guest?.unit_id == r.unit_id;
            const isHotel = aiSuggestion && aiSuggestion.best_for_hotel?.unit_id == r.unit_id;
            let extraClass = '';
            if (isGuest) extraClass = 'ai-guest-pick';
            else if (isHotel) extraClass = 'ai-hotel-pick';
            if (!r.is_available) extraClass += ' unavailable';

            const delay = (idx * 0.05).toFixed(2);

            // Amenidades (máx 5)
            const amenChips = (r.amenities || []).slice(0,5).map(k => {
                const a = amenityCatalog[k];
                if (!a) return '';
                return `<span class="amenity-pill"><i class="bi ${a.icon}"></i> ${a.label}</span>`;
            }).join('');

            // Planes tabs
            const tabs = r.plan_options.map((p, pi) =>
                `<button class="plan-tab ${pi===0?'active':''}"
                 onclick="switchPlan(${r.unit_id}, ${pi})"
                 data-unit="${r.unit_id}" data-plan-idx="${pi}">
            ${p.plan_name}
        </button>`
            ).join('');

            // Contenido de cada plan (generamos todos, mostramos uno)
            const planContents = r.plan_options.map((p, pi) =>
                renderPlanContent(p, pi === 0, r.unit_id, meta)
            ).join('');

            const ribbonHtml = (isGuest || isHotel) ? `
        <div style="position:absolute;top:0;left:0;right:0;bottom:0;pointer-events:none;overflow:hidden;border-radius:14px;">
            <div class="ai-ribbon">${isGuest ? '★ Huésped' : '★ Hotel'}</div>
        </div>` : '';

            const availBadge = r.is_available
                ? `<span class="avail-badge avail-ok"><i class="bi bi-check-circle-fill"></i> Disponible</span>`
                : `<span class="avail-badge avail-no"><i class="bi bi-x-circle-fill"></i> Ocupada</span>`;

            const reserveBtn = r.is_available
                ? `<a href="${makeReserveUrl(r, meta)}" class="btn-reserve">
               <i class="bi bi-calendar-check"></i> Reservar con este plan
           </a>`
                : `<button class="btn-reserve" disabled>No disponible</button>`;

            return `
    <div class="unit-card ${extraClass}" style="animation-delay:${delay}s;position:relative;"
         id="card-${r.unit_id}">
        ${ribbonHtml}
        <div class="unit-card-head">
            <div>
                <div class="unit-name">${escHtml(r.unit_name)}</div>
                <div class="unit-meta">
                    <span>${escHtml(r.unit_type)}</span>
                    <span class="unit-meta-sep">·</span>
                    <span><i class="bi bi-people-fill"></i> Base ${r.base_occupancy} · Máx ${r.max_occupancy}</span>
                    ${r.beds_info ? `<span class="unit-meta-sep">·</span><span><i class="bi bi-moon"></i> ${escHtml(r.beds_info)}</span>` : ''}
                    ${r.bathrooms ? `<span class="unit-meta-sep">·</span><span><i class="bi bi-droplet"></i> ${r.bathrooms} baño${r.bathrooms>1?'s':''}</span>` : ''}
                </div>
            </div>
            ${availBadge}
        </div>
        ${amenChips ? `<div class="unit-amenities">${amenChips}</div>` : ''}
        <div class="plan-tabs">${tabs}</div>
        <div id="plan-contents-${r.unit_id}">${planContents}</div>
        <div class="unit-card-footer">
            <span id="reserve-btn-${r.unit_id}">${reserveBtn}</span>
            <a href="<?= base_url('/inventory/unit/edit/') ?>${r.unit_id}"
               class="btn-detail" title="Ver detalles de la unidad" target="_blank">
                <i class="bi bi-info-circle"></i>
            </a>
        </div>
    </div>`;
        }

        function renderPlanContent(p, isActive, unitId, meta) {
            const nights = p.nights || meta.nights || 1;
            const fmt = v => currency + Number(v).toLocaleString('es-CO');

            // Breakdown de precio
            let breakdown = `
        <div class="price-row">
            <span class="label">Alojamiento (${nights} noche${nights!==1?'s':''})</span>
            <span class="value">${fmt(p.room_total)}</span>
        </div>`;

            if (p.extra_total > 0) {
                breakdown += `
        <div class="price-row extra">
            <span class="label">
                Extras
                ${p.extra_adults>0 ? `(${p.extra_adults} adulto${p.extra_adults>1?'s':''} extra)` : ''}
                ${p.extra_children>0 ? `(${p.extra_children} niño${p.extra_children>1?'s':''} extra)` : ''}
            </span>
            <span class="value">+ ${fmt(p.extra_total)}</span>
        </div>`;
            }

            if (p.seasons_applied && p.seasons_applied.length > 0) {
                breakdown += `
        <div class="price-row season">
            <span class="label">Temporada: ${escHtml(p.seasons_applied[0])}</span>
            <span class="value">Aplicada</span>
        </div>`;
            }

            breakdown += `
        <div class="price-row divider">
            <span class="label">Total estadía</span>
            <span class="value">${fmt(p.total_price)}</span>
        </div>`;

            // Amenidades del plan
            const planAmenHtml = (p.plan_amenities || []).slice(0,4).map(k => {
                const a = amenityCatalog[k];
                if (!a) return '';
                return `<span class="plan-inc-chip"><i class="bi ${a.icon}"></i> ${a.label}</span>`;
            }).join('');

            // Política de cancelación
            const cancel = cancelLabels[p.cancellation] || cancelLabels.flexible;

            // Detalle diario
            const dailyRows = (p.daily_details || []).map(d => `
        <div class="daily-row">
            <span>${d.date}</span>
            <span>${fmt(d.price)}
                ${d.season && d.season !== 'Tarifa Base' ? `<span class="season-tag">${escHtml(d.season)}</span>` : ''}
            </span>
        </div>`).join('');

            return `
    <div class="plan-content" id="plan-${unitId}-${p.plan_id}" style="display:${isActive?'flex':'none'};flex-direction:column;">
        <div class="price-hero">
            <span class="price-total">${fmt(p.total_price)}</span>
            <span class="price-per-night">${fmt(p.price_per_night)}/noche · ${nights} noche${nights!==1?'s':''}</span>
        </div>
        <div class="price-breakdown">${breakdown}</div>
        ${planAmenHtml ? `<div class="plan-includes">${planAmenHtml}</div>` : ''}
        <span class="cancel-badge ${cancel.cls}">
            <i class="bi bi-shield"></i> ${cancel.label}
        </span>
        ${dailyRows ? `
        <button class="daily-toggle" onclick="toggleDaily(this)">
            <i class="bi bi-chevron-down"></i> Ver desglose diario
        </button>
        <div class="daily-detail">${dailyRows}</div>
        ` : ''}
    </div>`;
        }

        function switchPlan(unitId, planIdx) {
            // Tabs
            document.querySelectorAll(`[data-unit="${unitId}"]`).forEach((btn, i) => {
                btn.classList.toggle('active', i === planIdx);
            });
            // Contenidos
            const contents = document.getElementById(`plan-contents-${unitId}`);
            contents.querySelectorAll('.plan-content').forEach((div, i) => {
                div.style.display = i === planIdx ? 'flex' : 'none';
            });
            // Actualizar URL del botón reservar con el plan activo
            const result = lastResults.find(r => r.unit_id == unitId);
            if (result) {
                const plan = result.plan_options[planIdx];
                if (plan) {
                    const btn = document.getElementById(`reserve-btn-${unitId}`);
                    if (btn && result.is_available) {
                        const url = makeReserveUrl(result, lastMeta, plan.plan_id);
                        btn.innerHTML = `<a href="${url}" class="btn-reserve">
                    <i class="bi bi-calendar-check"></i> Reservar con este plan
                </a>`;
                    }
                }
            }
        }

        function toggleDaily(btn) {
            const detail = btn.nextElementSibling;
            const open   = detail.style.display === 'block';
            detail.style.display = open ? 'none' : 'block';
            btn.querySelector('i').className = open ? 'bi bi-chevron-down' : 'bi bi-chevron-up';
        }

        function makeReserveUrl(r, meta, planId = null) {
            const pid = planId || r.lowest_plan_id || '';
            const params = new URLSearchParams({
                unit_id:    r.unit_id,
                check_in:   meta.check_in,
                check_out:  meta.check_out,
                adults:     meta.adults,
                children:   meta.children,
                rate_plan:  pid,
            });
            return `<?= base_url('/reservations/create') ?>?${params.toString()}`;
        }

        // ════════════════════════════════════════════════
        // IA
        // ════════════════════════════════════════════════
        function renderAiLoading() {
            const section = document.getElementById('ai-section');
            section.style.display = 'block';
            section.innerHTML = `
    <div class="ai-banner">
        <div class="ai-banner-icon">✨</div>
        <div class="ai-banner-content">
            <div class="ai-banner-title">Analizando opciones con IA...</div>
            <div class="ai-loading">
                <div class="ai-spinner"></div>
                Gemini está evaluando ocupación y preferencias del huésped
            </div>
        </div>
    </div>`;
        }

        async function fetchAiSuggestion(results, meta) {
            try {
                const res  = await fetch('<?= base_url('/reservations/quote/ai-suggest') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ results, meta })
                });
                const data = await res.json();

                if (data.success && data.suggestion) {
                    aiSuggestion = data.suggestion;
                    renderAiBanner(data.suggestion);
                    // Re-render cards con los highlights de IA
                    renderResults(lastResults, lastMeta);
                } else {
                    document.getElementById('ai-section').style.display = 'none';
                }
            } catch(e) {
                document.getElementById('ai-section').style.display = 'none';
                console.error('[IA] Error:', e);
            }
        }

        function renderAiBanner(s) {
            const section = document.getElementById('ai-section');
            const guestCard = `
        <div class="ai-chip">
            <span class="ai-chip-badge badge-guest">★ Para el huésped</span>
            <div>
                <div style="font-size:12.5px;font-weight:700;color:#fff;margin-bottom:3px;">
                    ${escHtml(s.best_for_guest?.unit_name || '')}
                </div>
                <div class="ai-chip-text">${escHtml(s.best_for_guest?.reason || '')}</div>
            </div>
        </div>`;

            const hotelCard = `
        <div class="ai-chip">
            <span class="ai-chip-badge badge-hotel">★ Para el hotel</span>
            <div>
                <div style="font-size:12.5px;font-weight:700;color:#fff;margin-bottom:3px;">
                    ${escHtml(s.best_for_hotel?.unit_name || '')}
                </div>
                <div class="ai-chip-text">${escHtml(s.best_for_hotel?.reason || '')}</div>
            </div>
        </div>`;

            const upsellHtml = s.upsell ? `
        <div class="ai-chip">
            <span class="ai-chip-badge" style="background:rgba(16,185,129,.3);color:#6ee7b7;">↑ Upsell</span>
            <div class="ai-chip-text" style="margin-top:2px;">${escHtml(s.upsell)}</div>
        </div>` : '';

            section.innerHTML = `
    <div class="ai-banner">
        <div class="ai-banner-icon">✨</div>
        <div class="ai-banner-content">
            <div class="ai-banner-title">Recomendación IA · Gemini</div>
            <div class="ai-banner-chips">
                ${guestCard}
                ${hotelCard}
                ${upsellHtml}
            </div>
            ${s.occupancy_insight ? `
            <div class="ai-insight">
                <i class="bi bi-graph-up-arrow"></i>
                ${escHtml(s.occupancy_insight)}
            </div>` : ''}
        </div>
    </div>`;
        }

        // ════════════════════════════════════════════════
        // UTILS
        // ════════════════════════════════════════════════
        function showError(msg) {
            document.getElementById('empty-state').innerHTML = `
        <div class="q-empty">
            <i class="bi bi-exclamation-circle" style="color:#dc2626;"></i>
            <h4>Error</h4>
            <p>${escHtml(msg)}</p>
        </div>`;
            document.getElementById('empty-state').style.display = 'block';
        }

        function escHtml(str) {
            return String(str || '')
                .replace(/&/g,'&amp;').replace(/</g,'&lt;')
                .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        // Enter en el panel busca
        document.addEventListener('keydown', e => {
            if (e.key === 'Enter' && document.activeElement.closest('.q-search-panel')) {
                runSearch();
            }
        });
    </script>

<?= $this->endSection() ?>