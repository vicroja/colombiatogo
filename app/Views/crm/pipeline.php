<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

<?php
// ── Definiciones de etapas ──────────────────────────────────────────────────
$stageDefs = [
    'cold'         => ['label' => 'Nuevos',       'icon' => 'bi-snow2',          'color' => '#3b82f6', 'bg' => '#e6f1fb', 'text' => '#0c447c'],
    'interested'   => ['label' => 'Interesados',  'icon' => 'bi-hand-index',     'color' => '#0d9488', 'bg' => '#e1f5ee', 'text' => '#085041'],
    'evaluating'   => ['label' => 'Evaluando',    'icon' => 'bi-hourglass-split','color' => '#d97706', 'bg' => '#faeeda', 'text' => '#633806'],
    'objecting'    => ['label' => 'Con dudas',    'icon' => 'bi-shield-exclamation','color'=>'#e11d48','bg' => '#fcebeb', 'text' => '#791f1f'],
    'ready_close'  => ['label' => 'Por cerrar',   'icon' => 'bi-check2-circle',  'color' => '#16a34a', 'bg' => '#eaf3de', 'text' => '#27500a'],
    'post_booking' => ['label' => 'Reservados',   'icon' => 'bi-calendar-check', 'color' => '#7c3aed', 'bg' => '#eeedfe', 'text' => '#3c3489'],
];

$urgColors = [
    'ok'     => ['bg' => '#eaf3de', 'text' => '#3b6d11', 'border' => 'transparent'],
    'warn'   => ['bg' => '#faeeda', 'text' => '#854f0b', 'border' => 'transparent'],
    'danger' => ['bg' => '#fcebeb', 'text' => '#a32d2d', 'border' => '#e24b4a'],
];

$conversiones = $stats['conversiones'] ?? [];
?>

    <style>
        /* ── Layout ──────────────────────────────────────────────────────────────── */
        .pl-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        /* ── Métricas superiores ─────────────────────────────────────────────────── */
        .pl-metrics {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: .75rem;
            margin-bottom: 1.25rem;
        }
        .pl-metric {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: .85rem 1rem;
        }
        .pl-metric-label {
            font-size: .7rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .pl-metric-val {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1.1;
            margin-top: .15rem;
        }
        .pl-metric-sub {
            font-size: .72rem;
            color: #94a3b8;
            margin-top: .1rem;
        }

        /* ── Panel de alertas ────────────────────────────────────────────────────── */
        .pl-alerts {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 1.25rem;
            overflow: hidden;
        }
        .pl-alerts-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .75rem 1rem;
            background: #fefce8;
            border-bottom: 1px solid #fde68a;
        }
        .pl-alerts-title {
            font-size: .82rem;
            font-weight: 700;
            color: #854d0e;
        }
        .pl-alerts-count {
            font-size: .7rem;
            font-weight: 700;
            background: #dc2626;
            color: #fff;
            padding: .15rem .5rem;
            border-radius: 99px;
        }
        .pl-alert-row {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .65rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            transition: background .15s;
        }
        .pl-alert-row:last-child { border-bottom: none; }
        .pl-alert-row:hover { background: #fafbff; }
        .pl-alert-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .pl-alert-dot.danger { background: #e24b4a; }
        .pl-alert-dot.warn   { background: #f59e0b; }
        .pl-alert-body {
            flex: 1;
            min-width: 0;
        }
        .pl-alert-name {
            font-size: .82rem;
            font-weight: 700;
            color: #0f172a;
        }
        .pl-alert-detail {
            font-size: .75rem;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pl-alert-meta {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-shrink: 0;
        }
        .pl-alert-time {
            font-size: .7rem;
            font-weight: 700;
            padding: .15rem .5rem;
            border-radius: 99px;
        }
        .pl-alert-btn {
            background: none;
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            padding: .25rem .6rem;
            font-size: .72rem;
            color: #475569;
            cursor: pointer;
            transition: all .15s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .25rem;
        }
        .pl-alert-btn:hover {
            background: #f0f4ff;
            border-color: #6366f1;
            color: #4338ca;
        }
        .pl-alert-btn.wa {
            color: #16a34a;
            border-color: #bbf7d0;
        }
        .pl-alert-btn.wa:hover {
            background: #f0fdf4;
        }
        .pl-alerts-toggle {
            display: none;
            text-align: center;
            padding: .5rem;
        }
        .pl-alerts-toggle a {
            font-size: .75rem;
            color: #6366f1;
            cursor: pointer;
            text-decoration: none;
        }
        .pl-alerts-toggle a:hover {
            text-decoration: underline;
        }

        /* ── Pipeline Kanban ─────────────────────────────────────────────────────── */
        .pl-kanban {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 6px;
            min-height: 400px;
        }
        .pl-col {
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .pl-col-header {
            padding: .6rem .5rem;
            border-radius: 10px 10px 0 0;
            text-align: center;
            position: relative;
        }
        .pl-col-count {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1;
        }
        .pl-col-label {
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-top: .1rem;
        }
        .pl-col-conv {
            position: absolute;
            right: -16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: .62rem;
            font-weight: 800;
            color: #94a3b8;
            background: #fff;
            padding: .1rem .25rem;
            border-radius: 4px;
            z-index: 5;
            white-space: nowrap;
        }
        .pl-col-body {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0  10px 10px;
            padding: 6px;
            background: #fafbfc;
            overflow-y: auto;
            max-height: 520px;
        }

        /* ── Tarjetas de lead ────────────────────────────────────────────────────── */
        .pl-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: .55rem .65rem;
            margin-bottom: 5px;
            transition: all .15s;
            cursor: default;
        }
        .pl-card:hover {
            border-color: #c7d2fe;
            box-shadow: 0 2px 8px rgba(99,102,241,.08);
        }
        .pl-card.urgent {
            border-left: 3px solid #e24b4a;
        }
        .pl-card.warning {
            border-left: 3px solid #f59e0b;
        }
        .pl-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: .35rem;
        }
        .pl-card-name {
            font-size: .78rem;
            font-weight: 700;
            color: #0f172a;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
            min-width: 0;
        }
        .pl-card-time {
            font-size: .6rem;
            font-weight: 700;
            padding: .1rem .4rem;
            border-radius: 99px;
            flex-shrink: 0;
            white-space: nowrap;
        }
        .pl-card-ctx {
            font-size: .68rem;
            color: #64748b;
            margin-top: .2rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .pl-card-tags {
            display: flex;
            gap: 3px;
            margin-top: .3rem;
            flex-wrap: wrap;
        }
        .pl-card-tag {
            font-size: .58rem;
            font-weight: 600;
            padding: .1rem .35rem;
            border-radius: 4px;
            background: #f1f5f9;
            color: #64748b;
        }
        .pl-card-tag.bot-off {
            background: #fef2f2;
            color: #dc2626;
        }
        .pl-card-tag.waiting {
            background: #fefce8;
            color: #a16207;
        }
        .pl-card-tag.obj {
            background: #fff1f2;
            color: #be123c;
        }
        .pl-card-actions {
            display: flex;
            gap: 3px;
            margin-top: .35rem;
            opacity: 0;
            transition: opacity .15s;
        }
        .pl-card:hover .pl-card-actions {
            opacity: 1;
        }
        .pl-card-btn {
            font-size: .65rem;
            padding: .15rem .4rem;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            background: #fff;
            color: #64748b;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .2rem;
            transition: all .12s;
        }
        .pl-card-btn:hover {
            border-color: #6366f1;
            color: #4338ca;
            background: #f0f4ff;
        }
        .pl-card-btn.wa {
            color: #16a34a;
            border-color: #bbf7d0;
        }
        .pl-card-btn.wa:hover {
            background: #f0fdf4;
        }

        /* ── More card ───────────────────────────────────────────────────────────── */
        .pl-more {
            text-align: center;
            padding: .4rem;
            font-size: .7rem;
            color: #94a3b8;
            cursor: pointer;
        }
        .pl-more:hover { color: #6366f1; }

        /* ── Empty column ────────────────────────────────────────────────────────── */
        .pl-empty {
            text-align: center;
            padding: 2rem .5rem;
            color: #cbd5e1;
            font-size: .72rem;
        }

        /* ── Responsive ──────────────────────────────────────────────────────────── */
        @media (max-width: 1100px) {
            .pl-kanban {
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
            }
            .pl-col-conv { display: none; }
        }
        @media (max-width: 700px) {
            .pl-kanban {
                grid-template-columns: 1fr;
            }
            .pl-metrics {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>

    <!-- ── Header ────────────────────────────────────────────────────────────── -->
    <div class="pl-header">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="bi bi-funnel me-2 text-primary"></i>
                Pipeline de conversión
            </h4>
            <p class="text-muted small mb-0">
                Embudo de ventas en tiempo real &middot;
                <?= $stats['total_activos'] ?> conversaciones activas
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="/crm" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-people me-1"></i> CRM Huéspedes
            </a>
            <button class="btn btn-sm btn-outline-primary"
                    onclick="location.reload()">
                <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
            </button>
        </div>
    </div>

    <!-- ── Métricas ──────────────────────────────────────────────────────────── -->
    <div class="pl-metrics">
        <div class="pl-metric">
            <div class="pl-metric-label">Leads activos</div>
            <div class="pl-metric-val" style="color:#3b82f6">
                <?= $stats['total_activos'] ?>
            </div>
            <div class="pl-metric-sub">en pipeline ahora</div>
        </div>
        <div class="pl-metric">
            <div class="pl-metric-label">Tasa de conversión</div>
            <div class="pl-metric-val" style="color:#16a34a">
                <?= $conversiones['global'] ?? 0 ?>%
            </div>
            <div class="pl-metric-sub">nuevo → reserva (30d)</div>
        </div>
        <div class="pl-metric">
            <div class="pl-metric-label">Esperando cliente</div>
            <div class="pl-metric-val" style="color:#d97706">
                <?= $stats['esperando_cliente'] ?>
            </div>
            <div class="pl-metric-sub">bot respondió, sin respuesta</div>
        </div>
        <div class="pl-metric">
            <div class="pl-metric-label">Requieren acción</div>
            <div class="pl-metric-val" style="color:#dc2626">
                <?= $stats['requieren_accion'] ?>
            </div>
            <div class="pl-metric-sub">
                estancados o IA desactivada
                <?php if ($stats['ia_desactivada'] > 0): ?>
                    <span style="color:#dc2626">(<?= $stats['ia_desactivada'] ?> sin bot)</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Alertas de intervención ────────────────────────────────────────────── -->
<?php if (!empty($alertas)): ?>
    <div class="pl-alerts" id="alertPanel">
        <div class="pl-alerts-header">
        <span class="pl-alerts-title">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Intervención sugerida
        </span>
            <span class="pl-alerts-count"><?= count($alertas) ?></span>
        </div>

        <?php
        $maxVisible = 4;
        foreach ($alertas as $idx => $alerta):
            $g = $alerta['guest'];
            $urg = $alerta['urgencia'];
            $urgStyle = $urgColors[$urg];
            $hidden = $idx >= $maxVisible ? 'style="display:none" data-extra-alert' : '';
            ?>
            <div class="pl-alert-row" <?= $hidden ?>>
                <div class="pl-alert-dot <?= $urg ?>"></div>
                <div class="pl-alert-body">
                    <div class="pl-alert-name">
                        <?= esc($g['full_name']) ?>
                        <span style="font-weight:400;color:#94a3b8;font-size:.72rem">
                    — <?= $stageDefs[$g['funnel_stage']]['label'] ?>
                </span>
                    </div>
                    <div class="pl-alert-detail"><?= esc($alerta['motivo']) ?></div>
                </div>
                <div class="pl-alert-meta">
            <span class="pl-alert-time"
                  style="background:<?= $urgStyle['bg'] ?>;color:<?= $urgStyle['text'] ?>">
                <?= $alerta['tiempo'] ?>
            </span>
                    <a href="/whatsapp/chat/<?= esc($g['phone']) ?>"
                       class="pl-alert-btn wa"
                       title="Ver conversación">
                        <i class="bi bi-chat-dots"></i> Chat
                    </a>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (count($alertas) > $maxVisible): ?>
            <div class="pl-alerts-toggle" id="alertToggle" style="display:block">
                <a onclick="document.querySelectorAll('[data-extra-alert]').forEach(e=>e.style.display='flex');this.parentElement.style.display='none'">
                    Ver <?= count($alertas) - $maxVisible ?> alertas más
                </a>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

    <!-- ── Pipeline Kanban ───────────────────────────────────────────────────── -->
    <div class="pl-kanban">
        <?php
        $stageKeys = array_keys($stageDefs);
        $convKeys  = [
            'cold'        => 'cold_to_interested',
            'interested'  => 'interested_to_evaluating',
            'evaluating'  => 'evaluating_to_objecting',
            'objecting'   => 'objecting_to_ready_close',
            'ready_close' => 'ready_close_to_post_booking',
        ];

        foreach ($stageKeys as $stageIdx => $stage):
            $def   = $stageDefs[$stage];
            $leads = $pipeline[$stage] ?? [];
            $count = count($leads);

            // Tasa de conversión a la siguiente etapa
            $convKey  = $convKeys[$stage] ?? null;
            $convRate = $convKey ? ($conversiones[$convKey] ?? null) : null;
            ?>
            <div class="pl-col">
                <!-- Header de columna -->
                <div class="pl-col-header" style="background:<?= $def['bg'] ?>;color:<?= $def['text'] ?>">
                    <div class="pl-col-count"><?= $count ?></div>
                    <div class="pl-col-label">
                        <i class="bi <?= $def['icon'] ?>" style="font-size:.65rem"></i>
                        <?= $def['label'] ?>
                    </div>

                    <?php if ($convRate !== null && $stageIdx < count($stageKeys) - 1): ?>
                        <div class="pl-col-conv">→ <?= $convRate ?>%</div>
                    <?php endif; ?>
                </div>

                <!-- Body con tarjetas -->
                <div class="pl-col-body">
                    <?php if (empty($leads)): ?>
                        <div class="pl-empty">
                            <i class="bi <?= $def['icon'] ?>" style="font-size:1.2rem;display:block;margin-bottom:.3rem"></i>
                            Sin leads
                        </div>
                    <?php else: ?>
                        <?php
                        $maxCards = 5;
                        $visibleLeads = array_slice($leads, 0, $maxCards);
                        $hiddenCount  = $count - $maxCards;

                        foreach ($visibleLeads as $g):
                            $urg     = $g['urgencia'];
                            $urgStyle= $urgColors[$urg];
                            $ctx     = $g['contexto'];
                            $minutos = (int) $g['minutos_en_etapa'];

                            // Texto de contexto según etapa
                            $ctxText = '';
                            if (!empty($ctx['ultimo_tour_consultado'])) {
                                $ctxText = $ctx['ultimo_tour_consultado'];
                            } elseif (!empty($ctx['ultima_unidad_consultada'])) {
                                $ctxText = $ctx['ultima_unidad_consultada'];
                            }

                            // Tiempo humanizado
                            if ($minutos < 60) { $tText = "{$minutos}m"; }
                            elseif ($minutos < 1440) { $tText = round($minutos / 60) . "h"; }
                            else { $tText = round($minutos / 1440) . "d"; }

                            // Clase de urgencia para el borde
                            $cardClass = 'pl-card';
                            if ($urg === 'danger') $cardClass .= ' urgent';
                            elseif ($urg === 'warn') $cardClass .= ' warning';
                            ?>
                            <div class="<?= $cardClass ?>">
                                <div class="pl-card-top">
                                    <div class="pl-card-name"
                                         title="<?= esc($g['full_name']) ?>">
                                        <?= esc($g['full_name']) ?>
                                    </div>
                                    <span class="pl-card-time"
                                          style="background:<?= $urgStyle['bg'] ?>;
                                              color:<?= $urgStyle['text'] ?>">
                            <?= $tText ?>
                        </span>
                                </div>

                                <?php if ($ctxText): ?>
                                    <div class="pl-card-ctx" title="<?= esc($ctxText) ?>">
                                        <?= esc($ctxText) ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Tags de estado -->
                                <div class="pl-card-tags">
                                    <?php if ($g['ai_active'] == 0): ?>
                                        <span class="pl-card-tag bot-off">
                                <i class="bi bi-robot"></i> Bot off
                            </span>
                                    <?php endif; ?>

                                    <?php if ($g['ultimo_msg_direction'] === 'outgoing'): ?>
                                        <span class="pl-card-tag waiting">Esperando</span>
                                    <?php endif; ?>

                                    <?php
                                    $objeciones = $ctx['objeciones_detectadas'] ?? [];
                                    foreach (array_slice($objeciones, 0, 2) as $obj):
                                        ?>
                                        <span class="pl-card-tag obj"><?= esc($obj) ?></span>
                                    <?php endforeach; ?>

                                    <?php if ($g['tiene_reserva'] || $g['tiene_tour']): ?>
                                        <span class="pl-card-tag"
                                              style="background:#f0fdf4;color:#16a34a">
                                <?= $g['tiene_tour'] ? 'Tour' : 'Reserva' ?>
                            </span>
                                    <?php endif; ?>

                                    <?php if ($g['total_mensajes'] > 0): ?>
                                        <span class="pl-card-tag">
                                <?= $g['total_mensajes'] ?> msgs
                            </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Acciones (visibles al hover) -->
                                <div class="pl-card-actions">
                                    <a href="/whatsapp/chat/<?= esc($g['phone']) ?>"
                                       class="pl-card-btn wa"
                                       title="Ver conversación">
                                        <i class="bi bi-chat-dots"></i> Chat
                                    </a>
                                    <a href="/crm/guest/<?= $g['id'] ?>"
                                       class="pl-card-btn"
                                       title="Ver perfil CRM">
                                        <i class="bi bi-person"></i>
                                    </a>
                                    <?php if ($g['phone']): ?>
                                        <a href="https://wa.me/<?= preg_replace('/\D/', '', $g['phone']) ?>"
                                           target="_blank"
                                           class="pl-card-btn"
                                           title="Abrir en WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if ($hiddenCount > 0): ?>
                            <div class="pl-more"
                                 onclick="this.parentElement.querySelectorAll('.pl-card-hidden').forEach(e=>e.style.display='block');this.style.display='none'">
                                +<?= $hiddenCount ?> más
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Leyenda ───────────────────────────────────────────────────────────── -->
    <div class="d-flex gap-3 mt-3 flex-wrap" style="font-size:.7rem;color:#94a3b8">
    <span>
        <span style="display:inline-block;width:10px;height:10px;background:#eaf3de;border:1px solid #97c459;border-radius:2px;vertical-align:middle"></span>
        Normal
    </span>
        <span>
        <span style="display:inline-block;width:10px;height:10px;background:#faeeda;border:1px solid #f59e0b;border-radius:2px;vertical-align:middle"></span>
        Atención pronto
    </span>
        <span>
        <span style="display:inline-block;width:10px;height:10px;background:#fcebeb;border:1px solid #e24b4a;border-radius:2px;vertical-align:middle"></span>
        Intervenir ahora
    </span>
        <span style="margin-left:auto">
        Datos actualizados: <?= date('H:i') ?>
    </span>
    </div>

    <!-- ── Auto-refresh cada 2 minutos ───────────────────────────────────────── -->
    <script>
        setTimeout(function() { location.reload(); }, 120000);
    </script>

<?= $this->endSection() ?>