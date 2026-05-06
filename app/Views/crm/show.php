<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

<?php
$currencySymbol = $tenant['currency_symbol'] ?? '$';

$segmentDefs = [
    'champion'  => ['label' => 'Champion',       'color' => '#7c3aed', 'bg' => '#f5f3ff', 'icon' => 'bi-trophy'],
    'loyal'     => ['label' => 'Leal',            'color' => '#2563eb', 'bg' => '#eff6ff', 'icon' => 'bi-heart'],
    'at_risk'   => ['label' => 'En riesgo',       'color' => '#dc2626', 'bg' => '#fff5f5', 'icon' => 'bi-exclamation-triangle'],
    'potential' => ['label' => 'Alto potencial',  'color' => '#059669', 'bg' => '#f0fdf4', 'icon' => 'bi-graph-up-arrow'],
    'new'       => ['label' => 'Nuevo',           'color' => '#0891b2', 'bg' => '#f0f9ff', 'icon' => 'bi-stars'],
    'lost'      => ['label' => 'Perdido',         'color' => '#94a3b8', 'bg' => '#f8fafc', 'icon' => 'bi-moon'],
    'regular'   => ['label' => 'Regular',         'color' => '#64748b', 'bg' => '#f8fafc', 'icon' => 'bi-person'],
];

$funnelDefs = [
    'cold'         => ['label' => 'Nuevo',        'color' => '#3b82f6', 'bg' => '#e6f1fb', 'icon' => 'bi-snow2'],
    'interested'   => ['label' => 'Interesado',   'color' => '#0d9488', 'bg' => '#e1f5ee', 'icon' => 'bi-hand-index'],
    'evaluating'   => ['label' => 'Evaluando',    'color' => '#d97706', 'bg' => '#faeeda', 'icon' => 'bi-hourglass-split'],
    'objecting'    => ['label' => 'Con dudas',    'color' => '#e11d48', 'bg' => '#fcebeb', 'icon' => 'bi-shield-exclamation'],
    'ready_close'  => ['label' => 'Por cerrar',   'color' => '#16a34a', 'bg' => '#eaf3de', 'icon' => 'bi-check2-circle'],
    'post_booking' => ['label' => 'Reservado',    'color' => '#7c3aed', 'bg' => '#eeedfe', 'icon' => 'bi-calendar-check'],
];

$funnelStages = array_keys($funnelDefs);
$currentStage = $guest['funnel_stage'] ?? 'cold';
$currentStageIdx = array_search($currentStage, $funnelStages);
$segKey = $rfm['segment'] ?? 'regular';
$segDef = $segmentDefs[$segKey];
$funDef = $funnelDefs[$currentStage] ?? $funnelDefs['cold'];

$rfmColors = [1 => '#e2e8f0', 2 => '#bfdbfe', 3 => '#93c5fd', 4 => '#3b82f6', 5 => '#1d4ed8'];

// Iniciales
$nameParts = array_slice(explode(' ', $guest['full_name'] ?? 'N N'), 0, 2);
$initials  = implode('', array_map(fn($w) => !empty($w) ? strtoupper($w[0]) : '', $nameParts));

// Status labels
$statusLabels = [
    'pending'     => ['Pendiente',   '#d97706', '#faeeda'],
    'confirmed'   => ['Confirmada',  '#16a34a', '#eaf3de'],
    'checked_in'  => ['Hospedado',   '#2563eb', '#eff6ff'],
    'checked_out' => ['Completada',  '#64748b', '#f1f5f9'],
    'cancelled'   => ['Cancelada',   '#dc2626', '#fff5f5'],
    'completed'   => ['Completado',  '#64748b', '#f1f5f9'],
    'no_show'     => ['No asistió',  '#94a3b8', '#f8fafc'],
    'refunded'    => ['Reembolsado', '#9333ea', '#f5f3ff'],
];

$guestPhone = preg_replace('/\D/', '', $guest['phone'] ?? '');
?>

    <style>
        /* ── Layout principal ────────────────────────────────────────────────────── */
        .gp-header {
            display: flex; justify-content: space-between; align-items: flex-start;
            margin-bottom: 1.25rem; gap: 1rem; flex-wrap: wrap;
        }
        .gp-back {
            font-size: .82rem; color: #6366f1; text-decoration: none;
            display: inline-flex; align-items: center; gap: .3rem; margin-bottom: .5rem;
        }
        .gp-back:hover { text-decoration: underline; }

        /* ── Card de identidad ───────────────────────────────────────────────────── */
        .gp-id-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
            padding: 1.25rem 1.5rem; margin-bottom: 1rem;
            display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap;
        }
        .gp-avatar {
            width: 56px; height: 56px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; font-weight: 800; color: #fff; flex-shrink: 0;
        }
        .gp-id-info { flex: 1; min-width: 180px; }
        .gp-id-name { font-size: 1.15rem; font-weight: 800; color: #0f172a; }
        .gp-id-meta { font-size: .78rem; color: #64748b; margin-top: .15rem; }
        .gp-id-meta a { color: #6366f1; text-decoration: none; }
        .gp-id-badges { display: flex; gap: .4rem; flex-wrap: wrap; }
        .gp-badge {
            display: inline-flex; align-items: center; gap: .25rem;
            padding: .2rem .6rem; border-radius: 99px;
            font-size: .68rem; font-weight: 700; white-space: nowrap;
        }
        .gp-id-actions { display: flex; gap: .4rem; flex-shrink: 0; }
        .gp-btn {
            background: none; border: 1px solid #e2e8f0; border-radius: 8px;
            padding: .35rem .7rem; font-size: .78rem; color: #475569;
            cursor: pointer; transition: all .15s; text-decoration: none;
            display: inline-flex; align-items: center; gap: .3rem;
        }
        .gp-btn:hover { background: #f0f4ff; border-color: #6366f1; color: #4338ca; }
        .gp-btn.wa { color: #16a34a; border-color: #bbf7d0; }
        .gp-btn.wa:hover { background: #f0fdf4; }
        .gp-btn.primary { background: #6366f1; color: #fff; border-color: #6366f1; }
        .gp-btn.primary:hover { background: #4f46e5; }

        /* ── Métricas resumen ────────────────────────────────────────────────────── */
        .gp-metrics {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: .6rem; margin-bottom: 1.25rem;
        }
        .gp-metric {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: .7rem .85rem;
        }
        .gp-metric-label { font-size: .65rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; }
        .gp-metric-val { font-size: 1.3rem; font-weight: 800; line-height: 1.15; margin-top: .1rem; }
        .gp-metric-sub { font-size: .68rem; color: #94a3b8; }

        /* ── Funnel progress ─────────────────────────────────────────────────────── */
        .gp-funnel {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 1rem 1.25rem; margin-bottom: 1.25rem;
        }
        .gp-funnel-title {
            font-size: .72rem; font-weight: 700; color: #64748b;
            text-transform: uppercase; letter-spacing: .04em; margin-bottom: .65rem;
        }
        .gp-funnel-bar {
            display: flex; gap: 3px;
        }
        .gp-funnel-step {
            flex: 1; text-align: center; padding: .45rem .25rem;
            border-radius: 6px; transition: all .2s; position: relative;
        }
        .gp-funnel-step-label {
            font-size: .6rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .03em;
        }
        .gp-funnel-step-icon {
            font-size: .75rem; display: block; margin-bottom: .15rem;
        }
        .gp-funnel-step.completed { opacity: .5; }
        .gp-funnel-step.active { transform: scale(1.05); box-shadow: 0 2px 8px rgba(0,0,0,.08); }

        /* ── Contexto de conversación ────────────────────────────────────────────── */
        .gp-context {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 1rem 1.25rem; margin-bottom: 1.25rem;
        }
        .gp-ctx-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: .5rem;
        }
        .gp-ctx-item {
            display: flex; align-items: center; gap: .5rem;
            padding: .4rem .6rem; background: #f8fafc; border-radius: 8px;
            font-size: .78rem;
        }
        .gp-ctx-icon { color: #94a3b8; font-size: .85rem; flex-shrink: 0; }
        .gp-ctx-label { color: #64748b; font-size: .68rem; }
        .gp-ctx-val { font-weight: 600; color: #0f172a; }

        /* ── Secciones con tabs ──────────────────────────────────────────────────── */
        .gp-tabs {
            display: flex; gap: 0; border-bottom: 2px solid #e2e8f0;
            margin-bottom: 1rem; flex-wrap: wrap;
        }
        .gp-tab {
            padding: .55rem 1rem; font-size: .78rem; font-weight: 600;
            color: #94a3b8; cursor: pointer; border-bottom: 2px solid transparent;
            margin-bottom: -2px; transition: all .15s; text-decoration: none;
            display: inline-flex; align-items: center; gap: .3rem;
        }
        .gp-tab:hover { color: #475569; }
        .gp-tab.active { color: #6366f1; border-bottom-color: #6366f1; }
        .gp-tab-count {
            font-size: .6rem; font-weight: 800; background: #f1f5f9;
            color: #64748b; padding: .1rem .35rem; border-radius: 99px;
        }
        .gp-tab.active .gp-tab-count { background: #e0e7ff; color: #4338ca; }
        .gp-section { display: none; }
        .gp-section.active { display: block; }

        /* ── Card genérica ───────────────────────────────────────────────────────── */
        .gp-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            overflow: hidden; margin-bottom: .75rem;
        }
        .gp-card-header {
            display: flex; justify-content: space-between; align-items: center;
            padding: .65rem 1rem; background: #f8fafc;
            border-bottom: 1px solid #e2e8f0; font-size: .82rem; font-weight: 700;
        }
        .gp-card-body { padding: .85rem 1rem; }

        /* ── Tabla interna ───────────────────────────────────────────────────────── */
        .gp-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
        .gp-table th {
            padding: .5rem .65rem; font-size: .65rem; font-weight: 700;
            color: #94a3b8; text-transform: uppercase; letter-spacing: .04em;
            text-align: left; border-bottom: 1px solid #e2e8f0;
        }
        .gp-table td {
            padding: .55rem .65rem; border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .gp-table tr:last-child td { border-bottom: none; }
        .gp-table tr:hover td { background: #fafbff; }

        /* ── Status pill ─────────────────────────────────────────────────────────── */
        .gp-status {
            display: inline-flex; padding: .15rem .5rem; border-radius: 99px;
            font-size: .65rem; font-weight: 700;
        }

        /* ── Chat preview ────────────────────────────────────────────────────────── */
        .gp-chat-wrap {
            max-height: 320px; overflow-y: auto; padding: .75rem;
            background: #f0f2f5; border-radius: 8px;
        }
        .gp-chat-msg {
            max-width: 80%; padding: .4rem .65rem; margin-bottom: .35rem;
            border-radius: 8px; font-size: .78rem; line-height: 1.4;
            clear: both; position: relative;
        }
        .gp-chat-msg.incoming {
            background: #fff; float: left; border-bottom-left-radius: 2px;
        }
        .gp-chat-msg.outgoing {
            background: #dcf8c6; float: right; border-bottom-right-radius: 2px;
        }
        .gp-chat-time {
            font-size: .58rem; color: #94a3b8; margin-top: .1rem;
            text-align: right;
        }
        .gp-chat-clear { clear: both; }

        /* ── Notas ───────────────────────────────────────────────────────────────── */
        .gp-note {
            padding: .6rem .85rem; border-left: 3px solid #c7d2fe;
            background: #fafbff; margin-bottom: .5rem; border-radius: 0 8px 8px 0;
        }
        .gp-note-text { font-size: .82rem; color: #0f172a; }
        .gp-note-meta { font-size: .68rem; color: #94a3b8; margin-top: .15rem; }

        /* ── RFM detail ──────────────────────────────────────────────────────────── */
        .gp-rfm-bar {
            display: flex; align-items: center; gap: .5rem; margin-bottom: .35rem;
        }
        .gp-rfm-label { font-size: .72rem; font-weight: 600; color: #64748b; min-width: 75px; }
        .gp-rfm-track {
            flex: 1; height: 8px; background: #f1f5f9; border-radius: 99px;
            overflow: hidden;
        }
        .gp-rfm-fill { height: 8px; border-radius: 99px; transition: width .4s; }
        .gp-rfm-val { font-size: .75rem; font-weight: 800; color: #0f172a; min-width: 20px; text-align: right; }

        /* ── AI message generator ────────────────────────────────────────────────── */
        .gp-ai-box {
            background: #f8fafc; border: 1px dashed #c7d2fe; border-radius: 10px;
            padding: .85rem 1rem;
        }
        .gp-ai-label { font-size: .72rem; font-weight: 700; color: #6366f1; margin-bottom: .5rem; }
        .gp-ai-btns { display: flex; gap: .35rem; flex-wrap: wrap; margin-bottom: .5rem; }
        .gp-ai-btn {
            padding: .25rem .6rem; border: 1px solid #e2e8f0; border-radius: 6px;
            font-size: .7rem; background: #fff; color: #475569; cursor: pointer;
            transition: all .12s;
        }
        .gp-ai-btn:hover { border-color: #6366f1; color: #4338ca; background: #f0f4ff; }
        .gp-ai-btn.active { border-color: #6366f1; color: #fff; background: #6366f1; }
        .gp-ai-preview {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
            padding: .65rem .85rem; min-height: 60px; font-size: .82rem;
            color: #0f172a; line-height: 1.5; margin-top: .5rem;
        }
        .gp-ai-actions { display: flex; gap: .35rem; margin-top: .5rem; justify-content: flex-end; }

        /* ── Empty state ─────────────────────────────────────────────────────────── */
        .gp-empty {
            text-align: center; padding: 2rem 1rem; color: #cbd5e1; font-size: .82rem;
        }

        /* ── Responsive ──────────────────────────────────────────────────────────── */
        @media (max-width: 700px) {
            .gp-id-card { flex-direction: column; text-align: center; }
            .gp-id-badges { justify-content: center; }
            .gp-id-actions { justify-content: center; }
            .gp-funnel-step-label { font-size: .5rem; }
            .gp-metrics { grid-template-columns: repeat(2, 1fr); }
        }
    </style>

    <!-- ── Breadcrumb ─────────────────────────────────────────────────────────── -->
    <a href="/crm" class="gp-back">
        <i class="bi bi-arrow-left"></i> Volver al CRM
    </a>

    <!-- ── Card de identidad ─────────────────────────────────────────────────── -->
    <div class="gp-id-card">
        <div class="gp-avatar" style="background:<?= $segDef['color'] ?>">
            <?= esc($initials) ?>
        </div>
        <div class="gp-id-info">
            <div class="gp-id-name"><?= esc($guest['full_name']) ?></div>
            <div class="gp-id-meta">
                <?php if (!empty($guest['phone'])): ?>
                    <i class="bi bi-phone"></i> <?= esc($guest['phone']) ?>
                <?php endif; ?>
                <?php if (!empty($guest['email'])): ?>
                    &nbsp;·&nbsp; <i class="bi bi-envelope"></i>
                    <a href="mailto:<?= esc($guest['email']) ?>"><?= esc($guest['email']) ?></a>
                <?php endif; ?>
                <?php if (!empty($guest['document'])): ?>
                    &nbsp;·&nbsp; <i class="bi bi-card-text"></i> <?= esc($guest['document']) ?>
                <?php endif; ?>
            </div>
            <div class="gp-id-badges" style="margin-top:.4rem">
                <!-- Segmento RFM -->
                <span class="gp-badge" style="background:<?= $segDef['bg'] ?>;color:<?= $segDef['color'] ?>">
                <i class="bi <?= $segDef['icon'] ?>"></i> <?= $segDef['label'] ?>
            </span>
                <!-- Etapa funnel -->
                <span class="gp-badge" style="background:<?= $funDef['bg'] ?>;color:<?= $funDef['color'] ?>">
                <i class="bi <?= $funDef['icon'] ?>"></i> <?= $funDef['label'] ?>
            </span>
                <!-- Estado del bot -->
                <?php if ($guest['ai_active'] == 0): ?>
                    <span class="gp-badge" style="background:#fef2f2;color:#dc2626">
                    <i class="bi bi-robot"></i> Bot desactivado
                </span>
                <?php endif; ?>
                <!-- Chat state -->
                <?php if ($guest['chat_state'] === 'ACTIVE'): ?>
                    <span class="gp-badge" style="background:#eaf3de;color:#16a34a">
                    <i class="bi bi-circle-fill" style="font-size:.4rem"></i> Activo
                </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="gp-id-actions">
            <?php if ($guestPhone): ?>
                <a href="/whatsapp/chat/<?= $guestPhone ?>"
                   class="gp-btn primary">
                    <i class="bi bi-chat-dots"></i> Ver chat
                </a>
                <a href="https://wa.me/<?= $guestPhone ?>"
                   target="_blank" class="gp-btn wa">
                    <i class="bi bi-whatsapp"></i> WhatsApp
                </a>
            <?php endif; ?>
            <a href="/crm/pipeline" class="gp-btn">
                <i class="bi bi-funnel"></i> Pipeline
            </a>
        </div>
    </div>

    <!-- ── Métricas resumen ──────────────────────────────────────────────────── -->
    <div class="gp-metrics">
        <div class="gp-metric">
            <div class="gp-metric-label">Score RFM</div>
            <div class="gp-metric-val" style="color:<?= $segDef['color'] ?>"><?= $rfm['score'] ?></div>
            <div class="gp-metric-sub">de 5.0</div>
        </div>
        <div class="gp-metric">
            <div class="gp-metric-label">Total gastado</div>
            <div class="gp-metric-val">
                <?= $currencySymbol ?><?= number_format($rfm['total_spent'] ?? 0, 0, ',', '.') ?>
            </div>
            <div class="gp-metric-sub"><?= $rfm['total_visits'] ?> visita(s)</div>
        </div>
        <div class="gp-metric">
            <div class="gp-metric-label">Total pagado</div>
            <div class="gp-metric-val" style="color:#16a34a">
                <?= $currencySymbol ?><?= number_format($totalPagado, 0, ',', '.') ?>
            </div>
            <div class="gp-metric-sub"><?= count($payments) ?> pago(s)</div>
        </div>
        <div class="gp-metric">
            <div class="gp-metric-label">Saldo pendiente</div>
            <div class="gp-metric-val" style="color:<?= $saldoPendiente > 0 ? '#d97706' : '#16a34a' ?>">
                <?= $currencySymbol ?><?= number_format($saldoPendiente, 0, ',', '.') ?>
            </div>
            <div class="gp-metric-sub"><?= $saldoPendiente > 0 ? 'por cobrar' : 'al día' ?></div>
        </div>
        <div class="gp-metric">
            <div class="gp-metric-label">Última actividad</div>
            <div class="gp-metric-val" style="font-size:1rem">
                <?= $rfm['days_since'] < 999
                    ? ($rfm['days_since'] == 0 ? 'Hoy' : 'Hace ' . $rfm['days_since'] . 'd')
                    : 'Sin visitas' ?>
            </div>
            <div class="gp-metric-sub">
                <?= $rfm['last_visit'] ? date('d M Y', strtotime($rfm['last_visit'])) : '—' ?>
            </div>
        </div>
        <div class="gp-metric">
            <div class="gp-metric-label">Tours</div>
            <div class="gp-metric-val"><?= count($tourReservations) ?></div>
            <div class="gp-metric-sub">
                <?= count(array_filter($tourReservations, fn($t) => in_array($t['status'], ['confirmed','pending']))) ?>
                activo(s)
            </div>
        </div>
    </div>

    <!-- ── Progreso del funnel ───────────────────────────────────────────────── -->
    <div class="gp-funnel">
        <div class="gp-funnel-title">Progreso en el embudo de conversión</div>
        <div class="gp-funnel-bar">
            <?php foreach ($funnelStages as $idx => $stage):
                $fDef = $funnelDefs[$stage];
                $isCompleted = $idx < $currentStageIdx;
                $isActive    = $idx === $currentStageIdx;
                $cls = $isActive ? 'active' : ($isCompleted ? 'completed' : '');
                ?>
                <div class="gp-funnel-step <?= $cls ?>"
                     style="background:<?= ($isActive || $isCompleted) ? $fDef['bg'] : '#f8fafc' ?>;
                             color:<?= ($isActive || $isCompleted) ? $fDef['color'] : '#cbd5e1' ?>">
            <span class="gp-funnel-step-icon">
                <i class="bi <?= ($isCompleted && !$isActive) ? 'bi-check-lg' : $fDef['icon'] ?>"></i>
            </span>
                    <span class="gp-funnel-step-label"><?= $fDef['label'] ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── Contexto de conversación ──────────────────────────────────────────── -->
<?php if (!empty($convContext)): ?>
    <div class="gp-context">
        <div class="gp-funnel-title">Contexto de la conversación</div>
        <div class="gp-ctx-grid">
            <?php if (!empty($convContext['ultimo_tour_consultado'])): ?>
                <div class="gp-ctx-item">
                    <i class="bi bi-compass gp-ctx-icon"></i>
                    <div>
                        <div class="gp-ctx-label">Último tour consultado</div>
                        <div class="gp-ctx-val"><?= esc($convContext['ultimo_tour_consultado']) ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($convContext['ultima_unidad_consultada'])): ?>
                <div class="gp-ctx-item">
                    <i class="bi bi-house gp-ctx-icon"></i>
                    <div>
                        <div class="gp-ctx-label">Última unidad consultada</div>
                        <div class="gp-ctx-val"><?= esc($convContext['ultima_unidad_consultada']) ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="gp-ctx-item">
                <i class="bi bi-tag gp-ctx-icon"></i>
                <div>
                    <div class="gp-ctx-label">Precio revelado</div>
                    <div class="gp-ctx-val"><?= !empty($convContext['precio_revelado']) ? 'Sí' : 'No' ?></div>
                </div>
            </div>

            <div class="gp-ctx-item">
                <i class="bi bi-search gp-ctx-icon"></i>
                <div>
                    <div class="gp-ctx-label">Disponibilidad consultada</div>
                    <div class="gp-ctx-val"><?= !empty($convContext['disponibilidad_consultada']) ? 'Sí' : 'No' ?></div>
                </div>
            </div>

            <?php if (!empty($convContext['objeciones_detectadas'])): ?>
                <div class="gp-ctx-item" style="grid-column: span 2">
                    <i class="bi bi-exclamation-diamond gp-ctx-icon" style="color:#e11d48"></i>
                    <div>
                        <div class="gp-ctx-label">Objeciones detectadas</div>
                        <div class="gp-ctx-val" style="color:#e11d48">
                            <?= esc(implode(', ', $convContext['objeciones_detectadas'])) ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

    <!-- ── RFM detallado ─────────────────────────────────────────────────────── -->
    <div class="gp-card" style="margin-bottom:1.25rem">
        <div class="gp-card-header">
            <span><i class="bi bi-bar-chart me-1"></i> Score RFM detallado</span>
            <span class="gp-badge" style="background:<?= $segDef['bg'] ?>;color:<?= $segDef['color'] ?>">
            <?= $segDef['label'] ?>: <?= $rfm['score'] ?>/5
        </span>
        </div>
        <div class="gp-card-body">
            <?php
            $rfmLabels = [
                'r' => ['Recencia',    'Qué tan reciente fue su última visita'],
                'f' => ['Frecuencia',  'Cuántas veces ha visitado'],
                'm' => ['Monetario',   'Cuánto ha gastado en total'],
            ];
            foreach ($rfmLabels as $key => [$label, $desc]):
                $val = $rfm[$key . '_score'];
                $barColor = $rfmColors[$val] ?? '#e2e8f0';
                ?>
                <div class="gp-rfm-bar">
                    <span class="gp-rfm-label" title="<?= $desc ?>"><?= $label ?></span>
                    <div class="gp-rfm-track">
                        <div class="gp-rfm-fill" style="width:<?= ($val / 5) * 100 ?>%;background:<?= $barColor ?>"></div>
                    </div>
                    <span class="gp-rfm-val"><?= $val ?></span>
                </div>
            <?php endforeach; ?>

            <?php if (!empty($preferences)): ?>
                <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-top:.65rem;padding-top:.65rem;border-top:1px solid #f1f5f9">
                    <?php if (!empty($preferences['favorite_unit'])): ?>
                        <div style="font-size:.75rem">
                            <span style="color:#94a3b8"><i class="bi bi-house me-1"></i>Favorita:</span>
                            <strong><?= esc($preferences['favorite_unit']) ?></strong>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($preferences['favorite_month'])): ?>
                        <div style="font-size:.75rem">
                            <span style="color:#94a3b8"><i class="bi bi-calendar3 me-1"></i>Mes favorito:</span>
                            <strong><?= esc($preferences['favorite_month']) ?></strong>
                        </div>
                    <?php endif; ?>
                    <div style="font-size:.75rem">
                        <span style="color:#94a3b8"><i class="bi bi-people me-1"></i>Grupo prom.:</span>
                        <strong><?= $preferences['avg_adults'] ?? '—' ?> adultos</strong>
                    </div>
                    <div style="font-size:.75rem">
                        <span style="color:#94a3b8"><i class="bi bi-moon me-1"></i>Estadía prom.:</span>
                        <strong><?= $preferences['avg_nights'] ?? '—' ?> noches</strong>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Tabs de secciones ─────────────────────────────────────────────────── -->
    <div class="gp-tabs" id="gpTabs">
        <a class="gp-tab active" data-tab="reservas" onclick="switchTab('reservas')">
            <i class="bi bi-calendar3"></i> Reservas
            <span class="gp-tab-count"><?= count($reservations) ?></span>
        </a>
        <a class="gp-tab" data-tab="tours" onclick="switchTab('tours')">
            <i class="bi bi-compass"></i> Tours
            <span class="gp-tab-count"><?= count($tourReservations) ?></span>
        </a>
        <a class="gp-tab" data-tab="pagos" onclick="switchTab('pagos')">
            <i class="bi bi-credit-card"></i> Pagos
            <span class="gp-tab-count"><?= count($payments) ?></span>
        </a>
        <a class="gp-tab" data-tab="chat" onclick="switchTab('chat')">
            <i class="bi bi-chat-dots"></i> Chat reciente
            <span class="gp-tab-count"><?= count($recentChats) ?></span>
        </a>
        <a class="gp-tab" data-tab="notas" onclick="switchTab('notas')">
            <i class="bi bi-sticky"></i> Notas
            <span class="gp-tab-count"><?= count($notes) ?></span>
        </a>
        <a class="gp-tab" data-tab="mensajes" onclick="switchTab('mensajes')">
            <i class="bi bi-megaphone"></i> Mensajes CRM
            <span class="gp-tab-count"><?= count($messages) ?></span>
        </a>
    </div>

    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <!-- TAB: RESERVAS DE ALOJAMIENTO                                          -->
    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <div class="gp-section active" id="sec-reservas">
        <?php if (empty($reservations)): ?>
            <div class="gp-empty">
                <i class="bi bi-calendar-x" style="font-size:1.5rem;display:block;margin-bottom:.4rem"></i>
                Sin reservas de alojamiento registradas
            </div>
        <?php else: ?>
            <div class="gp-card">
                <table class="gp-table">
                    <thead>
                    <tr>
                        <th>Unidad</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Noches</th>
                        <th>Huéspedes</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($reservations as $res):
                        $st = $statusLabels[$res['status']] ?? ['—', '#94a3b8', '#f8fafc'];
                        ?>
                        <tr>
                            <td><strong><?= esc($res['unit_name'] ?? '—') ?></strong></td>
                            <td><?= date('d M Y', strtotime($res['check_in_date'])) ?></td>
                            <td><?= date('d M Y', strtotime($res['check_out_date'])) ?></td>
                            <td><?= $res['nights'] ?? '—' ?></td>
                            <td><?= $res['num_adults'] ?> ad. <?= $res['num_children'] ? '+ ' . $res['num_children'] . ' niñ.' : '' ?></td>
                            <td><strong><?= $currencySymbol ?><?= number_format($res['total_price'], 0, ',', '.') ?></strong></td>
                            <td>
                        <span class="gp-status" style="background:<?= $st[2] ?>;color:<?= $st[1] ?>">
                            <?= $st[0] ?>
                        </span>
                            </td>
                        </tr>
                        <?php if (!empty($res['consumptions'])): ?>
                        <tr>
                            <td colspan="7" style="padding:.3rem 1rem .6rem;background:#fafbff">
                        <span style="font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase">
                            Consumos adicionales:
                        </span>
                                <?php foreach ($res['consumptions'] as $c): ?>
                                    <span style="font-size:.75rem;margin-left:.5rem">
                                <?= esc($c['description'] ?? $c['item_name'] ?? 'Item') ?>
                                (<?= $currencySymbol ?><?= number_format($c['subtotal'], 0, ',', '.') ?>)
                            </span>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <!-- TAB: TOURS                                                            -->
    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <div class="gp-section" id="sec-tours">
        <?php if (empty($tourReservations)): ?>
            <div class="gp-empty">
                <i class="bi bi-compass" style="font-size:1.5rem;display:block;margin-bottom:.4rem"></i>
                Sin tours reservados
            </div>
        <?php else: ?>
            <div class="gp-card">
                <table class="gp-table">
                    <thead>
                    <tr>
                        <th>Tour</th>
                        <th>Fecha salida</th>
                        <th>Personas</th>
                        <th>Total</th>
                        <th>Punto recogida</th>
                        <th>Estado</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($tourReservations as $tr):
                        $st = $statusLabels[$tr['status']] ?? ['—', '#94a3b8', '#f8fafc'];
                        $isFuture = strtotime($tr['start_datetime']) > time();
                        ?>
                        <tr>
                            <td>
                                <strong><?= esc($tr['tour_name']) ?></strong>
                                <div style="font-size:.68rem;color:#94a3b8">
                                    <?= $tr['duration_minutes'] ?> min
                                    <?php if ($tr['meeting_point']): ?>
                                        · <?= esc($tr['meeting_point']) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?= date('d M Y', strtotime($tr['start_datetime'])) ?>
                                <div style="font-size:.68rem;color:#94a3b8">
                                    <?= date('h:i A', strtotime($tr['start_datetime'])) ?>
                                </div>
                                <?php if ($isFuture): ?>
                                    <span style="font-size:.6rem;color:#16a34a;font-weight:600">Próximo</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $tr['num_adults'] ?> ad. <?= $tr['num_children'] ? '+ ' . $tr['num_children'] . ' niñ.' : '' ?></td>
                            <td><strong><?= $currencySymbol ?><?= number_format($tr['total_price'], 0, ',', '.') ?></strong></td>
                            <td style="font-size:.75rem"><?= esc($tr['pickup_location'] ?? '—') ?></td>
                            <td>
                        <span class="gp-status" style="background:<?= $st[2] ?>;color:<?= $st[1] ?>">
                            <?= $st[0] ?>
                        </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <!-- TAB: PAGOS                                                            -->
    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <div class="gp-section" id="sec-pagos">
        <?php if (empty($payments)): ?>
            <div class="gp-empty">
                <i class="bi bi-credit-card" style="font-size:1.5rem;display:block;margin-bottom:.4rem"></i>
                Sin pagos registrados
            </div>
        <?php else: ?>
            <div class="gp-card">
                <table class="gp-table">
                    <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Referencia</th>
                        <th>Banco</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($payments as $p):
                        $metodos = [
                            'cash'           => ['Efectivo',      'bi-cash-coin',    '#16a34a'],
                            'bank_transfer'  => ['Transferencia', 'bi-bank',         '#2563eb'],
                            'credit_card'    => ['Tarjeta',       'bi-credit-card',  '#7c3aed'],
                        ];
                        $met = $metodos[$p['payment_method']] ?? ['Otro', 'bi-wallet', '#64748b'];
                        ?>
                        <tr>
                            <td>
                                <?= date('d M Y', strtotime($p['created_at'])) ?>
                                <div style="font-size:.65rem;color:#94a3b8"><?= date('h:i A', strtotime($p['created_at'])) ?></div>
                            </td>
                            <td>
                        <span class="gp-badge" style="background:#f1f5f9;color:#475569">
                            <?= $p['_label'] ?? 'Reserva' ?>
                        </span>
                            </td>
                            <td>
                                <strong style="color:#16a34a">
                                    <?= $currencySymbol ?><?= number_format($p['amount'], 0, ',', '.') ?>
                                </strong>
                            </td>
                            <td>
                                <i class="bi <?= $met[1] ?>" style="color:<?= $met[2] ?>"></i>
                                <?= $met[0] ?>
                            </td>
                            <td style="font-size:.75rem"><?= esc($p['reference'] ?? '—') ?></td>
                            <td style="font-size:.75rem"><?= esc($p['bank_name'] ?? '—') ?></td>
                            <td>
                                <?php if (!empty($p['attachment_path'])): ?>
                                    <a href="/<?= esc($p['attachment_path']) ?>" target="_blank"
                                       class="gp-btn" style="font-size:.68rem;padding:.2rem .4rem">
                                        <i class="bi bi-paperclip"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="padding:.5rem 1rem;background:#f8fafc;border-top:1px solid #e2e8f0;
                        display:flex;justify-content:flex-end;gap:1.5rem;font-size:.82rem">
                <span>
                    Total pagado:
                    <strong style="color:#16a34a"><?= $currencySymbol ?><?= number_format($totalPagado, 0, ',', '.') ?></strong>
                </span>
                    <?php if ($saldoPendiente > 0): ?>
                        <span>
                    Saldo pendiente:
                    <strong style="color:#d97706"><?= $currencySymbol ?><?= number_format($saldoPendiente, 0, ',', '.') ?></strong>
                </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <!-- TAB: CHAT RECIENTE                                                    -->
    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <div class="gp-section" id="sec-chat">
        <?php if (empty($recentChats)): ?>
            <div class="gp-empty">
                <i class="bi bi-chat" style="font-size:1.5rem;display:block;margin-bottom:.4rem"></i>
                Sin mensajes recientes
            </div>
        <?php else: ?>
            <div class="gp-card">
                <div class="gp-card-header">
                    <span>Últimos 15 mensajes</span>
                    <?php if ($guestPhone): ?>
                        <a href="/whatsapp/chat/<?= $guestPhone ?>" class="gp-btn" style="font-size:.7rem">
                            <i class="bi bi-arrow-up-right"></i> Ver chat completo
                        </a>
                    <?php endif; ?>
                </div>
                <div class="gp-chat-wrap">
                    <?php foreach ($recentChats as $msg):
                        $dir = $msg['direction'] === 'incoming' ? 'incoming' : 'outgoing';
                        $body = $msg['message_body'] ?? '';
                        // Truncar mensajes muy largos
                        if (mb_strlen($body) > 300) $body = mb_substr($body, 0, 300) . '…';
                        ?>
                        <div class="gp-chat-msg <?= $dir ?>">
                            <?= nl2br(esc($body)) ?>
                            <div class="gp-chat-time"><?= date('d/m h:i A', strtotime($msg['created_at'])) ?></div>
                        </div>
                        <div class="gp-chat-clear"></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <!-- TAB: NOTAS DEL PERSONAL                                               -->
    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <div class="gp-section" id="sec-notas">
        <!-- Formulario para agregar nota -->
        <div class="gp-card" style="margin-bottom:.75rem">
            <div class="gp-card-body">
                <form method="POST" action="/crm/guest/<?= $guest['id'] ?>/note"
                      style="display:flex;gap:.5rem;align-items:flex-start">
                    <?= csrf_field() ?>
                    <textarea name="note" rows="2"
                              placeholder="Agregar una nota sobre este cliente..."
                              style="flex:1;font-size:.82rem;border:1px solid #e2e8f0;border-radius:8px;
                                 padding:.5rem .75rem;resize:vertical;outline:none;font-family:inherit"
                              required></textarea>
                    <button type="submit" class="gp-btn primary" style="align-self:flex-end">
                        <i class="bi bi-plus"></i> Agregar
                    </button>
                </form>
            </div>
        </div>

        <?php if (empty($notes)): ?>
            <div class="gp-empty">
                <i class="bi bi-sticky" style="font-size:1.5rem;display:block;margin-bottom:.4rem"></i>
                Sin notas del personal
            </div>
        <?php else: ?>
            <?php foreach ($notes as $note): ?>
                <div class="gp-note">
                    <div class="gp-note-text"><?= nl2br(esc($note['note'])) ?></div>
                    <div class="gp-note-meta">
                        <i class="bi bi-person"></i>
                        <?= esc($note['author_name'] ?? 'Sistema') ?>
                        · <?= date('d M Y h:i A', strtotime($note['created_at'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <!-- TAB: MENSAJES CRM + GENERADOR IA                                      -->
    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <div class="gp-section" id="sec-mensajes">
        <!-- Generador de mensajes con IA -->
        <div class="gp-ai-box" style="margin-bottom:1rem">
            <div class="gp-ai-label">
                <i class="bi bi-magic"></i> Generar mensaje con IA
            </div>
            <div class="gp-ai-btns" id="aiGoals">
                <button class="gp-ai-btn" data-goal="reactivar" onclick="selectGoal(this)">
                    <i class="bi bi-arrow-counterclockwise"></i> Reactivar
                </button>
                <button class="gp-ai-btn" data-goal="fidelizar" onclick="selectGoal(this)">
                    <i class="bi bi-heart"></i> Fidelizar
                </button>
                <button class="gp-ai-btn" data-goal="promocion" onclick="selectGoal(this)">
                    <i class="bi bi-gift"></i> Promoción
                </button>
                <button class="gp-ai-btn" data-goal="bienvenida" onclick="selectGoal(this)">
                    <i class="bi bi-hand-thumbs-up"></i> Bienvenida
                </button>
                <button class="gp-ai-btn" data-goal="cumpleanos" onclick="selectGoal(this)">
                    <i class="bi bi-cake2"></i> Cumpleaños
                </button>
            </div>
            <div style="display:flex;gap:.5rem;align-items:center">
                <input type="text" id="aiPromo" placeholder="Detalle adicional opcional (ej: 20% descuento en julio)"
                       style="flex:1;font-size:.78rem;border:1px solid #e2e8f0;border-radius:6px;
                          padding:.35rem .6rem;outline:none;font-family:inherit">
                <button class="gp-btn primary" id="aiGenBtn" onclick="generateAiMsg()" disabled>
                    <i class="bi bi-magic"></i> Generar
                </button>
            </div>
            <div class="gp-ai-preview" id="aiPreview" style="display:none">
                <div id="aiMsgText"></div>
            </div>
            <div class="gp-ai-actions" id="aiActions" style="display:none">
                <?php if ($guestPhone): ?>
                    <a id="aiWaLink" href="#" target="_blank" class="gp-btn wa">
                        <i class="bi bi-whatsapp"></i> Enviar por WhatsApp
                    </a>
                <?php endif; ?>
                <button class="gp-btn" onclick="copyAiMsg()">
                    <i class="bi bi-clipboard"></i> Copiar
                </button>
                <form method="POST" action="/crm/guest/<?= $guest['id'] ?>/message" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="message_body" id="aiMsgHidden">
                    <input type="hidden" name="channel" value="whatsapp">
                    <input type="hidden" name="ai_generated" value="1">
                    <button type="submit" class="gp-btn">
                        <i class="bi bi-save"></i> Guardar en historial
                    </button>
                </form>
            </div>
        </div>

        <!-- Historial de mensajes CRM -->
        <?php if (empty($messages)): ?>
            <div class="gp-empty">
                <i class="bi bi-megaphone" style="font-size:1.5rem;display:block;margin-bottom:.4rem"></i>
                Sin mensajes CRM enviados
            </div>
        <?php else: ?>
            <?php foreach ($messages as $msg): ?>
                <div class="gp-card" style="margin-bottom:.5rem">
                    <div class="gp-card-body" style="padding:.55rem .85rem">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.2rem">
                    <span style="font-size:.68rem;color:#94a3b8">
                        <?= date('d M Y h:i A', strtotime($msg['created_at'])) ?>
                        · <?= ucfirst($msg['channel'] ?? 'whatsapp') ?>
                        <?php if (!empty($msg['ai_generated'])): ?>
                            <span class="gp-badge" style="background:#f0f4ff;color:#6366f1;font-size:.58rem">
                                <i class="bi bi-magic"></i> IA
                            </span>
                        <?php endif; ?>
                    </span>
                            <span class="gp-status" style="background:#eaf3de;color:#16a34a;font-size:.6rem">
                        <?= ucfirst($msg['status'] ?? 'sent') ?>
                    </span>
                        </div>
                        <div style="font-size:.82rem;color:#0f172a;line-height:1.5">
                            <?= nl2br(esc($msg['message_body'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <!-- SCRIPTS                                                               -->
    <!-- ════════════════════════════════════════════════════════════════════════ -->
    <script>
        const guestId    = <?= (int)$guest['id'] ?>;
        const guestPhone = '<?= $guestPhone ?>';

        // ── Tabs ────────────────────────────────────────────────────────────────
        function switchTab(tab) {
            document.querySelectorAll('.gp-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.gp-section').forEach(s => s.classList.remove('active'));
            document.querySelector(`.gp-tab[data-tab="${tab}"]`).classList.add('active');
            document.getElementById('sec-' + tab).classList.add('active');
            // Guardar en URL para persistencia
            history.replaceState(null, '', '#' + tab);
        }

        // Restaurar tab desde hash
        if (window.location.hash) {
            const tab = window.location.hash.substring(1);
            const el = document.querySelector(`.gp-tab[data-tab="${tab}"]`);
            if (el) switchTab(tab);
        }

        // ── AI Message Generator ────────────────────────────────────────────────
        let selectedGoal = null;

        function selectGoal(btn) {
            document.querySelectorAll('#aiGoals .gp-ai-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            selectedGoal = btn.dataset.goal;
            document.getElementById('aiGenBtn').disabled = false;
        }

        async function generateAiMsg() {
            if (!selectedGoal) return;
            const btn = document.getElementById('aiGenBtn');
            const preview = document.getElementById('aiPreview');
            const textEl  = document.getElementById('aiMsgText');
            const actions = document.getElementById('aiActions');
            const promo   = document.getElementById('aiPromo').value;

            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Generando...';
            preview.style.display = 'block';
            textEl.innerHTML = '<span style="color:#94a3b8">Generando mensaje personalizado...</span>';

            try {
                const res = await fetch('/crm/ai-message', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                    body: JSON.stringify({ guest_id: guestId, goal: selectedGoal, promo: promo })
                });
                const data = await res.json();

                if (data.success) {
                    textEl.textContent = data.message;
                    document.getElementById('aiMsgHidden').value = data.message;
                    actions.style.display = 'flex';

                    if (guestPhone) {
                        const waMsg = encodeURIComponent(data.message);
                        document.getElementById('aiWaLink').href = `https://wa.me/${guestPhone}?text=${waMsg}`;
                    }
                } else {
                    textEl.innerHTML = `<span style="color:#dc2626">${data.message || 'Error al generar'}</span>`;
                }
            } catch (e) {
                textEl.innerHTML = '<span style="color:#dc2626">Error de conexión</span>';
            }

            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-magic"></i> Generar';
        }

        function copyAiMsg() {
            const text = document.getElementById('aiMsgText').textContent;
            navigator.clipboard.writeText(text).then(() => {
                const btn = event.target.closest('.gp-btn');
                btn.innerHTML = '<i class="bi bi-check"></i> Copiado';
                setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard"></i> Copiar', 2000);
            });
        }
    </script>

<?= $this->endSection() ?>