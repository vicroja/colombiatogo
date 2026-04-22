<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Unidad — <?= session('tenant_name') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* ══════════════════════════════════════════
           INVENTORY WIZARD — Design System
        ══════════════════════════════════════════ */
        :root {
            --iw-dark:    #0f172a;
            --iw-accent:  #1d4ed8;
            --iw-accent2: #3b82f6;
            --iw-success: #059669;
            --iw-surface: #ffffff;
            --iw-bg:      #f4f6f9;
            --iw-border:  #e4e7ec;
            --iw-text:    #0f172a;
            --iw-sub:     #64748b;
            --iw-muted:   #94a3b8;
            --iw-sidebar: 260px;
        }

        * { box-sizing: border-box; }
        body { background: var(--iw-bg); margin: 0; font-family: 'Segoe UI', system-ui, sans-serif; min-height: 100vh; }

        /* ── Wrapper ── */
        .iw-wrapper { display: flex; min-height: 100vh; }

        /* ── Sidebar ── */
        .iw-sidebar {
            width: var(--iw-sidebar);
            background: var(--iw-dark);
            display: flex; flex-direction: column;
            padding: 28px 20px;
            position: fixed; top: 0; left: 0; bottom: 0;
            overflow-y: auto; z-index: 100;
        }
        .iw-brand {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 32px;
            text-decoration: none;
        }
        .iw-brand-icon {
            width: 36px; height: 36px; border-radius: 9px;
            background: var(--iw-accent);
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; color: #fff; flex-shrink: 0;
        }
        .iw-brand-name {
            font-size: 13px; font-weight: 700; color: #fff;
            line-height: 1.3;
        }
        .iw-brand-sub { font-size: 11px; color: #64748b; }

        /* Steps nav */
        .iw-steps { flex: 1; }
        .iw-step {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 10px 8px; border-radius: 10px;
            margin-bottom: 4px; cursor: default;
            transition: background .15s;
            position: relative;
        }
        .iw-step::after {
            content: '';
            position: absolute;
            left: 22px; top: 44px;
            width: 2px; height: 20px;
            background: #1e293b;
        }
        .iw-step:last-child::after { display: none; }

        .iw-step-circle {
            width: 28px; height: 28px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; flex-shrink: 0;
            background: #1e293b; color: #475569;
            border: 2px solid #1e293b;
            transition: all .2s;
        }
        .iw-step-info .iw-step-num {
            font-size: 10px; color: #475569;
            text-transform: uppercase; letter-spacing: .06em;
        }
        .iw-step-info .iw-step-title {
            font-size: 13px; font-weight: 600; color: #64748b;
            line-height: 1.3;
        }
        .iw-step-badge {
            font-size: 9.5px; padding: 1px 6px; border-radius: 20px;
            font-weight: 600; margin-left: 4px;
        }
        .badge-req { background: #1e3a5f; color: #60a5fa; }
        .badge-opt { background: #1e293b; color: #475569; }

        /* Estado: completado */
        .iw-step.done .iw-step-circle {
            background: var(--iw-success); border-color: var(--iw-success); color: #fff;
        }
        .iw-step.done .iw-step-title { color: #94a3b8; }
        .iw-step.done::after { background: var(--iw-success); opacity: .3; }

        /* Estado: activo */
        .iw-step.active { background: #1e293b; }
        .iw-step.active .iw-step-circle {
            background: var(--iw-accent); border-color: var(--iw-accent); color: #fff;
        }
        .iw-step.active .iw-step-title { color: #fff; font-weight: 700; }
        .iw-step.active .iw-step-num   { color: #60a5fa; }

        /* Botón volver al PMS */
        .iw-back-link {
            display: flex; align-items: center; gap: 7px;
            color: #475569; font-size: 12.5px; text-decoration: none;
            padding: 8px; border-radius: 8px; margin-top: auto;
            transition: all .15s;
        }
        .iw-back-link:hover { color: #94a3b8; background: #1e293b; }

        /* ── Contenido principal ── */
        .iw-main {
            margin-left: var(--iw-sidebar);
            flex: 1; padding: 40px 48px;
            max-width: calc(100vw - var(--iw-sidebar));
        }
        @media (max-width: 768px) {
            .iw-sidebar { display: none; }
            .iw-main { margin-left: 0; padding: 24px 20px; max-width: 100vw; }
        }

        /* ── Progress bar top ── */
        .iw-progress-bar {
            height: 3px; background: var(--iw-border);
            border-radius: 2px; margin-bottom: 32px; overflow: hidden;
        }
        .iw-progress-fill {
            height: 100%; background: linear-gradient(90deg, var(--iw-accent), var(--iw-accent2));
            border-radius: 2px;
            transition: width .4s cubic-bezier(.4,0,.2,1);
        }

        /* ── Header del paso ── */
        .iw-step-header { margin-bottom: 28px; }
        .iw-step-eyebrow {
            font-size: 11.5px; font-weight: 700; color: var(--iw-accent);
            text-transform: uppercase; letter-spacing: .08em; margin-bottom: 6px;
        }
        .iw-step-title-main {
            font-size: 24px; font-weight: 800;
            color: var(--iw-text); letter-spacing: -.03em;
            margin: 0 0 6px;
        }
        .iw-step-hint { font-size: 13.5px; color: var(--iw-sub); margin: 0; }

        /* ── Cards de contenido ── */
        .iw-card {
            background: var(--iw-surface);
            border: 1px solid var(--iw-border);
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
        }
        .iw-card-title {
            font-size: 13px; font-weight: 700;
            color: var(--iw-text); margin-bottom: 16px;
            display: flex; align-items: center; gap: 8px;
        }
        .iw-card-title i { color: var(--iw-muted); }

        /* ── Form fields ── */
        .iw-label {
            font-size: 11.5px; font-weight: 700;
            color: #374151; margin-bottom: 5px;
            display: block; text-transform: uppercase; letter-spacing: .04em;
        }
        .iw-label .req { color: #ef4444; margin-left: 2px; }
        .iw-input, .iw-select, .iw-textarea {
            border: 1.5px solid var(--iw-border);
            border-radius: 9px; padding: 10px 13px;
            font-size: 14px; color: var(--iw-text);
            width: 100%; transition: border-color .15s, box-shadow .15s;
            background: #fff;
        }
        .iw-input:focus, .iw-select:focus, .iw-textarea:focus {
            border-color: var(--iw-accent);
            box-shadow: 0 0 0 3px rgba(29,78,216,.1);
            outline: none;
        }
        .iw-input::placeholder, .iw-textarea::placeholder { color: var(--iw-muted); }
        .iw-textarea { resize: vertical; }
        .iw-hint { font-size: 11.5px; color: var(--iw-muted); margin-top: 4px; }

        /* ── Stepper de ocupación ── */
        .occ-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .occ-box {
            border: 1.5px solid var(--iw-border);
            border-radius: 10px; padding: 12px 14px;
            display: flex; align-items: center;
            justify-content: space-between;
            background: #fafafa;
        }
        .occ-label { font-size: 12px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .04em; }
        .occ-sub   { font-size: 11px; color: var(--iw-muted); }
        .occ-stepper { display: flex; align-items: center; gap: 10px; }
        .occ-btn {
            width: 30px; height: 30px; border-radius: 50%;
            border: 1.5px solid var(--iw-border); background: #fff;
            font-size: 17px; cursor: pointer; display: flex;
            align-items: center; justify-content: center;
            transition: all .15s; color: var(--iw-text);
        }
        .occ-btn:hover:not(:disabled) { border-color: var(--iw-accent); color: var(--iw-accent); background: #eff6ff; }
        .occ-btn:disabled { opacity: .3; cursor: not-allowed; }
        .occ-val { font-size: 18px; font-weight: 800; min-width: 22px; text-align: center; }

        /* ── Selector de modo (simple / compuesto) ── */
        .mode-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 8px; }
        .mode-card {
            border: 2px solid var(--iw-border);
            border-radius: 12px; padding: 16px;
            cursor: pointer; text-align: center;
            transition: all .15s; background: #fff;
            position: relative;
        }
        .mode-card:hover { border-color: #93c5fd; }
        .mode-card.active { border-color: var(--iw-accent); background: #eff6ff; }
        .mode-card input { display: none; }
        .mode-icon { font-size: 24px; display: block; margin-bottom: 6px; }
        .mode-title { font-size: 13px; font-weight: 700; color: var(--iw-text); }
        .mode-desc  { font-size: 11.5px; color: var(--iw-sub); margin-top: 3px; }
        .mode-check {
            position: absolute; top: 10px; right: 10px;
            width: 18px; height: 18px; border-radius: 50%;
            background: var(--iw-accent); color: #fff;
            font-size: 9px; display: none;
            align-items: center; justify-content: center;
        }
        .mode-card.active .mode-check { display: flex; }

        /* ── Sub-habitaciones (modo compuesto) ── */
        .room-card {
            background: #f8faff; border: 1.5px solid #dbeafe;
            border-radius: 10px; padding: 16px; margin-bottom: 10px;
            animation: fadeSlide .2s ease;
        }
        .room-card-header {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 12px;
        }
        .room-num { font-size: 11px; font-weight: 700; color: var(--iw-accent); text-transform: uppercase; letter-spacing: .06em; }
        .btn-remove {
            background: none; border: none; color: #e11d48;
            cursor: pointer; font-size: 12px; padding: 3px 8px;
            border-radius: 6px; transition: background .1s;
        }
        .btn-remove:hover { background: #fff1f2; }

        .room-grid { display: grid; grid-template-columns: 1fr 1fr 90px; gap: 10px; margin-bottom: 10px; }
        @media (max-width: 600px) { .room-grid { grid-template-columns: 1fr; } }

        .bed-row { display: flex; gap: 8px; align-items: center; margin-bottom: 6px; }
        .bed-row select { flex: 1; }
        .bed-row input  { width: 70px; }
        .btn-rm-bed { background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 16px; padding: 0 4px; }
        .btn-rm-bed:hover { color: #e11d48; }

        .btn-add-bed {
            background: none; border: 1.5px dashed #93c5fd;
            color: var(--iw-accent); border-radius: 8px;
            padding: 5px 12px; font-size: 12px; font-weight: 600;
            cursor: pointer; transition: all .15s; margin-top: 4px;
        }
        .btn-add-bed:hover { background: #eff6ff; }

        .btn-add-room {
            display: flex; align-items: center; gap: 7px;
            border: 2px dashed var(--iw-border);
            background: transparent; border-radius: 10px;
            padding: 12px 16px; width: 100%;
            color: var(--iw-sub); font-size: 13px; font-weight: 600;
            cursor: pointer; transition: all .15s; justify-content: center;
        }
        .btn-add-room:hover { border-color: var(--iw-accent); color: var(--iw-accent); background: #eff6ff; }

        /* ── Amenidades (paso 2) ── */
        .amenity-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 10px; }
        .amenity-toggle {
            border: 1.5px solid var(--iw-border);
            border-radius: 10px; padding: 12px;
            cursor: pointer; transition: all .15s;
            display: flex; align-items: center; gap: 10px;
            background: #fafafa;
        }
        .amenity-toggle:hover { border-color: #93c5fd; background: #fff; }
        .amenity-toggle.active { border-color: var(--iw-accent); background: #eff6ff; }
        .amenity-toggle input { display: none; }
        .amenity-icon-box {
            width: 32px; height: 32px; border-radius: 8px;
            background: #e5e7eb; display: flex;
            align-items: center; justify-content: center;
            font-size: 14px; color: var(--iw-sub);
            flex-shrink: 0; transition: all .15s;
        }
        .amenity-toggle.active .amenity-icon-box { color: #fff; }
        .amenity-label-text { font-size: 12.5px; font-weight: 600; color: var(--iw-sub); transition: color .15s; }
        .amenity-toggle.active .amenity-label-text { color: var(--iw-accent); }

        /* ── Upload de fotos (paso 3) ── */
        .upload-zone {
            border: 2px dashed var(--iw-border);
            border-radius: 14px; padding: 40px 20px;
            text-align: center; cursor: pointer;
            transition: all .2s; background: #fafafa;
        }
        .upload-zone:hover, .upload-zone.drag-over {
            border-color: var(--iw-accent); background: #eff6ff;
        }
        .upload-zone i { font-size: 32px; color: var(--iw-muted); display: block; margin-bottom: 10px; }
        .upload-zone p { font-size: 13.5px; color: var(--iw-sub); margin: 0; }
        .upload-zone span { font-size: 12px; color: var(--iw-muted); }

        .photo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; margin-top: 16px; }
        .photo-item {
            position: relative; border-radius: 10px;
            overflow: hidden; aspect-ratio: 4/3;
            background: #e5e7eb;
            animation: fadeSlide .2s ease;
        }
        .photo-item img { width: 100%; height: 100%; object-fit: cover; }
        .photo-remove {
            position: absolute; top: 6px; right: 6px;
            width: 24px; height: 24px; border-radius: 50%;
            background: rgba(0,0,0,.6); color: #fff;
            border: none; cursor: pointer; font-size: 13px;
            display: flex; align-items: center; justify-content: center;
            transition: background .1s;
        }
        .photo-remove:hover { background: #ef4444; }

        /* ── Footer de navegación ── */
        .iw-footer {
            display: flex; align-items: center;
            justify-content: space-between;
            margin-top: 28px; padding-top: 20px;
            border-top: 1px solid var(--iw-border);
            gap: 12px;
        }
        .btn-iw-skip {
            padding: 10px 16px; border-radius: 9px;
            border: 1.5px solid var(--iw-border);
            background: #fff; color: var(--iw-sub);
            font-size: 13px; font-weight: 600;
            cursor: pointer; text-decoration: none;
            transition: all .15s;
        }
        .btn-iw-skip:hover { border-color: #9ca3af; color: var(--iw-text); }

        .btn-iw-next {
            padding: 11px 28px; border-radius: 9px;
            background: var(--iw-accent); color: #fff;
            border: none; font-size: 14px; font-weight: 700;
            cursor: pointer; display: flex; align-items: center; gap: 8px;
            transition: background .15s, transform .1s;
        }
        .btn-iw-next:hover  { background: #1e40af; }
        .btn-iw-next:active { transform: scale(.98); }

        .btn-iw-back {
            padding: 10px 16px; border-radius: 9px;
            border: 1.5px solid var(--iw-border);
            background: #fff; color: var(--iw-sub);
            font-size: 13px; font-weight: 600;
            cursor: pointer; display: flex; align-items: center; gap: 6px;
            transition: all .15s; text-decoration: none;
        }
        .btn-iw-back:hover { color: var(--iw-text); border-color: #9ca3af; }

        /* ── Alert ── */
        .iw-alert {
            padding: 12px 16px; border-radius: 9px;
            margin-bottom: 20px; font-size: 13px;
            display: flex; align-items: center; gap: 8px;
        }
        .iw-alert-error   { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
        .iw-alert-success { background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46; }

        /* ── Animaciones ── */
        @keyframes fadeSlide {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── dos columnas para form ── */
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .three-col { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }
        @media (max-width: 600px) {
            .two-col, .three-col { grid-template-columns: 1fr; }
            .occ-grid, .mode-grid { grid-template-columns: 1fr; }
        }
        .field { margin-bottom: 0; }
        .span-2 { grid-column: span 2; }
    </style>
</head>
<body>
<div class="iw-wrapper">

    <!-- ══ Sidebar ══ -->
    <aside class="iw-sidebar">
        <a href="<?= base_url('/inventory') ?>" class="iw-brand">
            <div class="iw-brand-icon"><i class="bi bi-building"></i></div>
            <div>
                <div class="iw-brand-name"><?= esc(session('tenant_name') ?? 'PMS') ?></div>
                <div class="iw-brand-sub">Nueva Unidad</div>
            </div>
        </a>

        <nav class="iw-steps">
            <?php
            $steps = [
                1 => ['title' => 'Información base',  'icon' => 'bi-house-door',  'required' => true],
                2 => ['title' => 'Amenidades',         'icon' => 'bi-star',        'required' => false],
                3 => ['title' => 'Fotos',              'icon' => 'bi-images',      'required' => false],
            ];
            foreach ($steps as $n => $s):
                $cls = 'iw-step';
                if ($n < $currentStep) $cls .= ' done';
                if ($n === $currentStep) $cls .= ' active';
                ?>
                <div class="<?= $cls ?>">
                    <div class="iw-step-circle">
                        <?php if ($n < $currentStep): ?>
                            <i class="bi bi-check"></i>
                        <?php else: ?>
                            <?= $n ?>
                        <?php endif; ?>
                    </div>
                    <div class="iw-step-info">
                        <div class="iw-step-num">Paso <?= $n ?> <span class="iw-step-badge <?= $s['required'] ? 'badge-req' : 'badge-opt' ?>"><?= $s['required'] ? 'Requerido' : 'Opcional' ?></span></div>
                        <div class="iw-step-title"><?= $s['title'] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </nav>

        <a href="<?= base_url('/inventory') ?>" class="iw-back-link">
            <i class="bi bi-arrow-left-circle"></i> Volver al inventario
        </a>
    </aside>

    <!-- ══ Contenido ══ -->
    <main class="iw-main">

        <!-- Progress bar -->
        <div class="iw-progress-bar">
            <div class="iw-progress-fill" style="width:<?= round(($currentStep / 3) * 100) ?>%"></div>
        </div>

        <?php if (session('error')): ?>
            <div class="iw-alert iw-alert-error">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?= session('error') ?>
            </div>
        <?php endif; ?>
        <?php if (session('success')): ?>
            <div class="iw-alert iw-alert-success">
                <i class="bi bi-check-circle-fill"></i>
                <?= session('success') ?>
            </div>
        <?php endif; ?>

        <?php
        // Cargar el paso correspondiente
        switch ($currentStep) {
            case 1: include APPPATH . 'Views/inventory/wizard/step1_base.php'; break;
            case 2: include APPPATH . 'Views/inventory/wizard/step2_amenities.php'; break;
            case 3: include APPPATH . 'Views/inventory/wizard/step3_photos.php'; break;
        }
        ?>

    </main>
</div>
</body>
</html>