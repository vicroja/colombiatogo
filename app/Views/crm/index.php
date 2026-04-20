<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

<?php
$currencySymbol = $tenant['currency_symbol'] ?? '$';
$rfm            = $rfm ?? [];
$prefs          = $preferences ?? [];

$segmentDefs = [
    'champion'  => ['label' => 'Champion',       'color' => '#7c3aed', 'bg' => '#f5f3ff'],
    'loyal'     => ['label' => 'Leal',            'color' => '#2563eb', 'bg' => '#eff6ff'],
    'at_risk'   => ['label' => 'En riesgo',       'color' => '#dc2626', 'bg' => '#fff5f5'],
    'potential' => ['label' => 'Alto potencial',  'color' => '#059669', 'bg' => '#f0fdf4'],
    'new'       => ['label' => 'Nuevo',           'color' => '#0891b2', 'bg' => '#f0f9ff'],
    'lost'      => ['label' => 'Perdido',         'color' => '#94a3b8', 'bg' => '#f8fafc'],
    'regular'   => ['label' => 'Regular',         'color' => '#64748b', 'bg' => '#f8fafc'],
];
$segDef  = $segmentDefs[$rfm['segment'] ?? 'regular'] ?? $segmentDefs['regular'];
$initials = implode('', array_map(
    fn($w) => strtoupper($w[0]),
    array_slice(explode(' ', $guest['full_name']), 0, 2)
));

$goalOptions = [
    'reactivar'  => 'Reactivar cliente',
    'fidelizar'  => 'Fidelizar / agradecer',
    'promocion'  => 'Enviar promocion',
    'bienvenida' => 'Bienvenida segunda visita',
    'cumpleanos' => 'Felicitacion',
];
?>

    <style>
        .profile-wrap {
            display               : grid;
            grid-template-columns : 320px 1fr;
            gap                   : 1.5rem;
            align-items           : start;
        }

        .seg-badge {
            display       : inline-flex;
            align-items   : center;
            gap           : .3rem;
            padding       : .25rem .7rem;
            border-radius : 99px;
            font-size     : .72rem;
            font-weight   : 700;
            white-space   : nowrap;
        }

        /* Sidebar del perfil */
        .profile-sidebar {
            display        : flex;
            flex-direction : column;
            gap            : 1rem;
        }
        .profile-card {
            background    : #fff;
            border        : 1px solid #e2e8f0;
            border-radius : 14px;
            overflow      : hidden;
        }
        .profile-card-header {
            padding       : 1.25rem;
            border-bottom : 1px solid #f1f5f9;
            font-size     : .72rem;
            font-weight   : 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color         : #94a3b8;
        }
        .profile-card-body { padding: 1.25rem }

        /* Avatar grande */
        .profile-avatar {
            width           : 64px;
            height          : 64px;
            border-radius   : 50%;
            display         : flex;
            align-items     : center;
            justify-content : center;
            font-size       : 1.3rem;
            font-weight     : 800;
            color           : #fff;
            margin          : 0 auto 1rem;
        }

        /* Score gauge */
        .rfm-gauge {
            display         : flex;
            justify-content : center;
            gap             : .5rem;
            margin          : 1rem 0;
        }
        .rfm-bar-item {
            flex            : 1;
            display         : flex;
            flex-direction  : column;
            align-items     : center;
            gap             : .3rem;
        }
        .rfm-bar-track {
            width         : 100%;
            height        : 64px;
            background    : #f1f5f9;
            border-radius : 6px;
            overflow      : hidden;
            display       : flex;
            align-items   : flex-end;
        }
        .rfm-bar-fill {
            width         : 100%;
            border-radius : 6px 6px 0 0;
            transition    : height .5s ease;
        }
        .rfm-bar-label {
            font-size   : .65rem;
            font-weight : 700;
            color       : #64748b;
            text-transform: uppercase;
        }
        .rfm-bar-val {
            font-size   : .8rem;
            font-weight : 800;
            color       : #0f172a;
        }

        /* Preferencias */
        .pref-item {
            display       : flex;
            align-items   : center;
            gap           : .65rem;
            padding       : .5rem 0;
            border-bottom : 1px solid #f1f5f9;
            font-size     : .82rem;
        }
        .pref-item:last-child { border-bottom: none }
        .pref-icon {
            width           : 28px;
            height          : 28px;
            border-radius   : 7px;
            background      : #f0f4ff;
            display         : flex;
            align-items     : center;
            justify-content : center;
            color           : #6366f1;
            flex-shrink     : 0;
            font-size       : .8rem;
        }
        .pref-label { color: #64748b; font-size: .72rem }
        .pref-val   { color: #0f172a; font-weight: 600 }

        /* Contenido principal */
        .profile-main { display: flex; flex-direction: column; gap: 1rem }
        .section-card {
            background    : #fff;
            border        : 1px solid #e2e8f0;
            border-radius : 14px;
            overflow      : hidden;
        }
        .section-card-header {
            padding         : 1rem 1.25rem;
            border-bottom   : 1px solid #f1f5f9;
            display         : flex;
            align-items     : center;
            justify-content : space-between;
        }
        .section-card-title {
            font-size   : .82rem;
            font-weight : 700;
            color       : #0f172a;
            display     : flex;
            align-items : center;
            gap         : .4rem;
        }
        .section-card-body { padding: 1.25rem }

        /* Timeline reservaciones */
        .timeline { display: flex; flex-direction: column; gap: .75rem }
        .timeline-item {
            display       : grid;
            grid-template-columns: auto 1fr auto;
            gap           : 1rem;
            align-items   : center;
            padding       : .85rem 1rem;
            background    : #f8fafc;
            border-radius : 10px;
            border        : 1px solid #f1f5f9;
            transition    : border-color .2s;
        }
        .timeline-item:hover { border-color: #c7d2fe }
        .timeline-dot {
            width         : 10px;
            height        : 10px;
            border-radius : 50%;
            flex-shrink   : 0;
        }
        .tl-dates { font-size: .72rem; color: #94a3b8; margin-top: .1rem }
        .tl-price {
            font-weight : 700;
            font-size   : .9rem;
            color       : #0f172a;
            text-align  : right;
        }
        .tl-nights { font-size: .7rem; color: #94a3b8; text-align: right }

        /* Notas */
        .note-item {
            padding       : .85rem 1rem;
            background    : #fffbeb;
            border-radius : 8px;
            border        : 1px solid #fde68a;
            font-size     : .85rem;
            margin-bottom : .6rem;
        }
        .note-meta { font-size: .7rem; color: #92400e; margin-top: .3rem }

        /* Mensajes enviados */
        .msg-item {
            padding       : .85rem 1rem;
            background    : #f0fdf4;
            border-radius : 8px;
            border        : 1px solid #bbf7d0;
            font-size     : .85rem;
            margin-bottom : .6rem;
            position      : relative;
        }
        .msg-item.ai-msg {
            background : #f5f3ff;
            border-color: #c4b5fd;
        }
        .msg-meta { font-size: .7rem; color: #64748b; margin-top: .35rem }
        .ai-pill  {
            display       : inline-flex;
            align-items   : center;
            gap           : .25rem;
            background    : #ede9fe;
            color         : #6d28d9;
            font-size     : .65rem;
            font-weight   : 700;
            padding       : .1rem .45rem;
            border-radius : 4px;
        }

        /* Generador de mensajes IA */
        .ai-generator {
            background    : linear-gradient(135deg, #f0f4ff, #faf5ff);
            border        : 1px solid #c4b5fd;
            border-radius : 12px;
            padding       : 1.25rem;
        }
        .ai-gen-title {
            font-size   : .85rem;
            font-weight : 700;
            color       : #4338ca;
            margin-bottom: .75rem;
            display     : flex;
            align-items : center;
            gap         : .4rem;
        }
        .btn-ai-gen {
            background    : linear-gradient(135deg, #6366f1, #8b5cf6);
            color         : #fff;
            border        : none;
            border-radius : 8px;
            padding       : .55rem 1.25rem;
            font-size     : .82rem;
            font-weight   : 600;
            cursor        : pointer;
            transition    : opacity .2s;
            display       : flex;
            align-items   : center;
            gap           : .4rem;
        }
        .btn-ai-gen:hover    { opacity: .88 }
        .btn-ai-gen:disabled { opacity: .5; cursor: not-allowed }
        .msg-preview {
            background    : #fff;
            border        : 1.5px solid #c4b5fd;
            border-radius : 10px;
            padding       : .85rem 1rem;
            font-size     : .875rem;
            line-height   : 1.65;
            min-height    : 80px;
            white-space   : pre-wrap;
            color         : #0f172a;
        }

        @media (max-width: 900px) {
            .profile-wrap { grid-template-columns: 1fr }
        }
    </style>

    <!-- ── Breadcrumb ─────────────────────────────────────────────────────────── -->
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="/crm" class="text-muted text-decoration-none"
           style="font-size:.82rem">
            <i class="bi bi-people me-1"></i>Huéspedes
        </a>
        <span style="color:#cbd5e1">/</span>
        <span style="font-size:.82rem;color:#0f172a;font-weight:600">
        <?= esc($guest['full_name']) ?>
    </span>
    </div>

    <div class="profile-wrap">

        <!-- ════════════════════════════════
             SIDEBAR
        ════════════════════════════════ -->
        <div class="profile-sidebar">

            <!-- Identidad + score -->
            <div class="profile-card">
                <div class="profile-card-body text-center">
                    <div class="profile-avatar"
                         style="background:<?= $segDef['color'] ?>">
                        <?= $initials ?>
                    </div>
                    <h5 class="fw-bold mb-1" style="font-size:1rem">
                        <?= esc($guest['full_name']) ?>
                    </h5>
                    <?php if (!empty($guest['document'])): ?>
                        <p style="font-size:.75rem;color:#94a3b8;margin-bottom:.5rem">
                            Doc: <?= esc($guest['document']) ?>
                        </p>
                    <?php endif; ?>

                    <span class="seg-badge mb-3 d-inline-flex"
                          style="background:<?= $segDef['bg'] ?>;
                              color:<?= $segDef['color'] ?>">
                    <?= $segDef['label'] ?>
                </span>

                    <p style="font-size:.75rem;color:#64748b;line-height:1.5;margin-bottom:1rem">
                        <?= esc($rfm['segment_desc'] ?? '') ?>
                    </p>

                    <!-- Score gauge RFM -->
                    <div class="rfm-gauge">
                        <?php
                        $rfmItems = [
                            'r' => ['label' => 'R', 'title' => 'Recency',   'color' => '#3b82f6'],
                            'f' => ['label' => 'F', 'title' => 'Frequency', 'color' => '#8b5cf6'],
                            'm' => ['label' => 'M', 'title' => 'Monetary',  'color' => '#06b6d4'],
                        ];
                        foreach ($rfmItems as $key => $item):
                            $val = $rfm[$key . '_score'] ?? 1;
                            $pct = ($val / 5) * 100;
                            ?>
                            <div class="rfm-bar-item" title="<?= $item['title'] ?>: <?= $val ?>/5">
                                <div class="rfm-bar-track">
                                    <div class="rfm-bar-fill"
                                         style="height:<?= $pct ?>%;
                                             background:<?= $item['color'] ?>">
                                    </div>
                                </div>
                                <div class="rfm-bar-val"><?= $val ?></div>
                                <div class="rfm-bar-label"><?= $item['label'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Score total -->
                    <div style="font-size:2rem;font-weight:800;color:<?= $segDef['color'] ?>;
                        line-height:1;margin:.5rem 0 .25rem">
                        <?= $rfm['score'] ?? 0 ?>
                    </div>
                    <div style="font-size:.7rem;color:#94a3b8;text-transform:uppercase;
                            letter-spacing:.08em">
                        Score total / 5
                    </div>
                </div>
            </div>

            <!-- Datos de contacto -->
            <div class="profile-card">
                <div class="profile-card-header">Contacto</div>
                <div class="profile-card-body" style="padding:.75rem 1.25rem">
                    <?php if (!empty($guest['phone'])): ?>
                        <div class="pref-item">
                            <div class="pref-icon">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <div>
                                <div class="pref-label">Teléfono</div>
                                <div class="pref-val"><?= esc($guest['phone']) ?></div>
                            </div>
                            <a href="https://wa.me/<?= preg_replace('/\D/', '', $guest['phone']) ?>"
                               target="_blank"
                               style="color:#16a34a;margin-left:auto;font-size:1.1rem">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($guest['email'])): ?>
                        <div class="pref-item">
                            <div class="pref-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div>
                                <div class="pref-label">Email</div>
                                <div class="pref-val"><?= esc($guest['email']) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="profile-card">
                <div class="profile-card-header">Estadísticas</div>
                <div class="profile-card-body" style="padding:.75rem 1.25rem">
                    <div class="pref-item">
                        <div class="pref-icon"><i class="bi bi-calendar-check"></i></div>
                        <div>
                            <div class="pref-label">Total visitas</div>
                            <div class="pref-val">
                                <?= $rfm['total_visits'] ?? 0 ?> vez/veces
                            </div>
                        </div>
                    </div>
                    <div class="pref-item">
                        <div class="pref-icon"><i class="bi bi-cash-coin"></i></div>
                        <div>
                            <div class="pref-label">Gasto total</div>
                            <div class="pref-val">
                                <?= $currencySymbol ?>
                                <?= number_format($rfm['total_spent'] ?? 0, 0, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                    <?php if (($rfm['days_since'] ?? 999) < 999): ?>
                        <div class="pref-item">
                            <div class="pref-icon"><i class="bi bi-clock-history"></i></div>
                            <div>
                                <div class="pref-label">Última visita</div>
                                <div class="pref-val"
                                     style="color:<?= ($rfm['days_since'] ?? 0) > 180 ? '#dc2626' : '#0f172a' ?>">
                                    hace <?= $rfm['days_since'] ?> días
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Preferencias detectadas -->
            <?php if (!empty($prefs)): ?>
                <div class="profile-card">
                    <div class="profile-card-header">
                        <i class="bi bi-magic me-1"></i>
                        Preferencias detectadas
                    </div>
                    <div class="profile-card-body" style="padding:.75rem 1.25rem">
                        <?php if (!empty($prefs['favorite_unit'])): ?>
                            <div class="pref-item">
                                <div class="pref-icon"><i class="bi bi-house-heart"></i></div>
                                <div>
                                    <div class="pref-label">Unidad favorita</div>
                                    <div class="pref-val"><?= esc($prefs['favorite_unit']) ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($prefs['favorite_month'])): ?>
                            <div class="pref-item">
                                <div class="pref-icon"><i class="bi bi-calendar3"></i></div>
                                <div>
                                    <div class="pref-label">Mes favorito</div>
                                    <div class="pref-val"><?= esc($prefs['favorite_month']) ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="pref-item">
                            <div class="pref-icon"><i class="bi bi-people"></i></div>
                            <div>
                                <div class="pref-label">Grupo promedio</div>
                                <div class="pref-val"><?= $prefs['avg_adults'] ?? 2 ?> adultos</div>
                            </div>
                        </div>
                        <div class="pref-item">
                            <div class="pref-icon"><i class="bi bi-moon-stars"></i></div>
                            <div>
                                <div class="pref-label">Estadía promedio</div>
                                <div class="pref-val"><?= $prefs['avg_nights'] ?? 1 ?> noches</div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div><!-- /sidebar -->

        <!-- ════════════════════════════════
             CONTENIDO PRINCIPAL
        ════════════════════════════════ -->
        <div class="profile-main">

            <!-- Generador de mensajes IA -->
            <div class="section-card" id="mensajes">
                <div class="section-card-header">
                    <div class="section-card-title">
                        <i class="bi bi-stars" style="color:#6366f1"></i>
                        Generar mensaje con IA
                    </div>
                </div>
                <div class="section-card-body">
                    <div class="ai-generator">
                        <div class="ai-gen-title">
                            <i class="bi bi-stars"></i>
                            Mensaje personalizado para <?= esc(explode(' ', $guest['full_name'])[0]) ?>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-5">
                                <label style="font-size:.72rem;font-weight:600;
                                          color:#64748b;display:block;margin-bottom:.3rem">
                                    Objetivo del mensaje
                                </label>
                                <select id="aiGoal" class="form-select form-select-sm">
                                    <?php foreach ($goalOptions as $val => $label): ?>
                                        <option value="<?= $val ?>"
                                            <?= $rfm['segment'] === 'at_risk' && $val === 'reactivar' ? 'selected' : '' ?>
                                            <?= $rfm['segment'] === 'loyal'   && $val === 'fidelizar' ? 'selected' : '' ?>
                                            <?= $rfm['segment'] === 'new'     && $val === 'bienvenida'? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label style="font-size:.72rem;font-weight:600;
                                          color:#64748b;display:block;margin-bottom:.3rem">
                                    Promocion o detalle especial (opcional)
                                </label>
                                <input type="text" id="aiPromo"
                                       class="form-control form-control-sm"
                                       placeholder="Ej: 15% de descuento este fin de semana">
                            </div>
                        </div>

                        <button class="btn-ai-gen w-100 mb-3"
                                id="btnGenMsg" onclick="generateMessage()">
                            <i class="bi bi-stars"></i>
                            Generar mensaje personalizado
                        </button>

                        <!-- Preview del mensaje generado -->
                        <div id="msgPreviewWrap" style="display:none">
                            <div class="msg-preview" id="msgPreview"
                                 contenteditable="true"></div>
                            <div class="d-flex gap-2 mt-2 justify-content-between">
                            <span style="font-size:.72rem;color:#6d28d9">
                                <i class="bi bi-stars me-1"></i>
                                Generado por IA · Editable
                            </span>
                                <div class="d-flex gap-2">
                                    <?php if (!empty($guest['phone'])): ?>
                                        <a id="waLink"
                                           href="#"
                                           target="_blank"
                                           class="btn btn-sm btn-success">
                                            <i class="bi bi-whatsapp me-1"></i>
                                            Abrir en WhatsApp
                                        </a>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-primary"
                                            onclick="saveMessage()">
                                        <i class="bi bi-floppy me-1"></i>
                                        Guardar registro
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Historial de reservaciones -->
            <div class="section-card">
                <div class="section-card-header">
                    <div class="section-card-title">
                        <i class="bi bi-calendar-check"></i>
                        Historial de reservaciones
                        <span style="background:#f1f5f9;color:#64748b;
                                 font-size:.7rem;padding:.1rem .45rem;
                                 border-radius:4px">
                        <?= count($reservations) ?>
                    </span>
                    </div>
                </div>
                <div class="section-card-body">
                    <?php if (empty($reservations)): ?>
                        <p class="text-muted text-center py-3" style="font-size:.85rem">
                            Sin reservaciones registradas
                        </p>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($reservations as $res):
                                $statusColors = [
                                    'checked_out' => '#22c55e',
                                    'cancelled'   => '#ef4444',
                                    'confirmed'   => '#3b82f6',
                                    'pending'     => '#f59e0b',
                                    'checked_in'  => '#8b5cf6',
                                ];
                                $dotColor = $statusColors[$res['status']] ?? '#94a3b8';
                                ?>
                                <div class="timeline-item">
                                    <div class="timeline-dot"
                                         style="background:<?= $dotColor ?>"></div>
                                    <div>
                                        <div class="fw-semibold" style="font-size:.875rem">
                                            <?= esc($res['unit_name'] ?? 'Unidad') ?>
                                        </div>
                                        <div class="tl-dates">
                                            <?= date('d M Y', strtotime($res['check_in_date'])) ?>
                                            &rarr;
                                            <?= date('d M Y', strtotime($res['check_out_date'])) ?>
                                            &middot;
                                            <?= $res['nights'] ?? 1 ?> noche(s)
                                            &middot;
                                            <?= $res['num_adults'] ?? 1 ?> adulto(s)
                                        </div>
                                        <?php if (!empty($res['consumptions'])): ?>
                                            <div style="font-size:.72rem;color:#94a3b8;margin-top:.2rem">
                                                + <?= count($res['consumptions']) ?> consumo(s)
                                                (<?= $currencySymbol ?>
                                                <?= number_format($res['total_consumptions'], 0, ',', '.') ?>)
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="tl-price">
                                            <?= $currencySymbol ?>
                                            <?= number_format($res['total_price'], 0, ',', '.') ?>
                                        </div>
                                        <div class="tl-nights">
                                            <?= ucfirst(str_replace('_', ' ', $res['status'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notas del personal -->
            <div class="section-card" id="notas">
                <div class="section-card-header">
                    <div class="section-card-title">
                        <i class="bi bi-journal-text"></i>
                        Notas del personal
                        <span style="background:#f1f5f9;color:#64748b;
                                 font-size:.7rem;padding:.1rem .45rem;
                                 border-radius:4px">
                        <?= count($notes) ?>
                    </span>
                    </div>
                </div>
                <div class="section-card-body">
                    <!-- Form agregar nota -->
                    <form action="/crm/guest/<?= $guest['id'] ?>/note"
                          method="POST" class="mb-3">
                        <?= csrf_field() ?>
                        <div class="d-flex gap-2">
                            <input type="text" name="note"
                                   class="form-control form-control-sm"
                                   placeholder="Agregar nota sobre este huésped..."
                                   required>
                            <button type="submit"
                                    class="btn btn-sm btn-outline-primary"
                                    style="white-space:nowrap">
                                <i class="bi bi-plus"></i> Agregar
                            </button>
                        </div>
                    </form>

                    <?php if (empty($notes)): ?>
                        <p class="text-muted" style="font-size:.82rem">
                            Sin notas aún.
                        </p>
                    <?php else: ?>
                        <?php foreach ($notes as $note): ?>
                            <div class="note-item">
                                <?= esc($note['note']) ?>
                                <div class="note-meta">
                                    <?= esc($note['author_name'] ?? 'Personal') ?>
                                    &middot;
                                    <?= date('d M Y H:i', strtotime($note['created_at'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Historial de mensajes CRM -->
            <?php if (!empty($messages)): ?>
                <div class="section-card">
                    <div class="section-card-header">
                        <div class="section-card-title">
                            <i class="bi bi-chat-dots"></i>
                            Mensajes enviados
                            <span style="background:#f1f5f9;color:#64748b;
                                 font-size:.7rem;padding:.1rem .45rem;
                                 border-radius:4px">
                        <?= count($messages) ?>
                    </span>
                        </div>
                    </div>
                    <div class="section-card-body">
                        <?php foreach ($messages as $msg): ?>
                            <div class="msg-item <?= $msg['ai_generated'] ? 'ai-msg' : '' ?>">
                                <?php if ($msg['ai_generated']): ?>
                                    <span class="ai-pill mb-1 d-inline-flex">
                                <i class="bi bi-stars"></i> IA
                            </span>
                                <?php endif; ?>
                                <div><?= nl2br(esc($msg['message_body'])) ?></div>
                                <div class="msg-meta">
                                    <i class="bi bi-<?= $msg['channel'] === 'whatsapp' ? 'whatsapp' : 'envelope' ?> me-1"></i>
                                    <?= ucfirst($msg['channel']) ?>
                                    &middot;
                                    <?= $msg['sent_at']
                                        ? date('d M Y H:i', strtotime($msg['sent_at']))
                                        : 'Borrador' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div><!-- /main -->
    </div>

    <!-- Form oculto para guardar mensaje -->
    <form id="formSaveMsg"
          action="/crm/guest/<?= $guest['id'] ?>/message"
          method="POST" style="display:none">
        <?= csrf_field() ?>
        <input type="hidden" name="message_body"  id="hiddenMsgBody">
        <input type="hidden" name="channel"       value="whatsapp">
        <input type="hidden" name="ai_generated"  value="1">
    </form>

    <script>
        const guestId   = <?= $guest['id'] ?>;
        const guestPhone= '<?= preg_replace('/\D/', '', $guest['phone'] ?? '') ?>';

        /**
         * Llama al endpoint de IA para generar un mensaje personalizado
         */
        async function generateMessage() {
            const goal  = document.getElementById('aiGoal').value;
            const promo = document.getElementById('aiPromo').value;
            const btn   = document.getElementById('btnGenMsg');

            btn.disabled    = true;
            btn.innerHTML   = '<span class="spinner-border spinner-border-sm me-1"></span> Generando...';

            const csrfInput = document.querySelector('input[name="csrf_test_name"]');
            const csrfToken = csrfInput ? csrfInput.value : '';

            try {
                const res  = await fetch('/crm/ai/message', {
                    method      : 'POST',
                    credentials : 'same-origin',
                    headers     : {
                        'Content-Type'     : 'application/json',
                        'X-Requested-With' : 'XMLHttpRequest',
                        'X-CSRF-TOKEN'     : csrfToken,
                    },
                    body: JSON.stringify({ guest_id: guestId, goal, promo })
                });

                const data = await res.json();

                if (data.success) {
                    const preview = document.getElementById('msgPreview');
                    const wrap    = document.getElementById('msgPreviewWrap');

                    preview.textContent = data.message;
                    wrap.style.display  = 'block';

                    // Actualizar link de WhatsApp
                    if (guestPhone) {
                        const waMsg = encodeURIComponent(data.message);
                        document.getElementById('waLink').href =
                            `https://wa.me/${guestPhone}?text=${waMsg}`;
                    }

                    wrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    alert('Error: ' + (data.message || 'No se pudo generar el mensaje'));
                }
            } catch (err) {
                console.error('[CRM/AI]', err);
                alert('Error de conexion');
            } finally {
                btn.disabled  = false;
                btn.innerHTML = '<i class="bi bi-stars me-1"></i> Generar mensaje personalizado';
            }
        }

        /**
         * Guarda el mensaje generado en el historial CRM
         */
        function saveMessage() {
            const body = document.getElementById('msgPreview').textContent.trim();
            if (!body) return;

            // Renovar token CSRF
            const csrfInput = document.querySelector('input[name="csrf_test_name"]');
            const formCsrf  = document.querySelector('#formSaveMsg input[name="csrf_test_name"]');
            if (csrfInput && formCsrf) {
                formCsrf.value = csrfInput.value;
            }

            document.getElementById('hiddenMsgBody').value = body;
            document.getElementById('formSaveMsg').submit();
        }
    </script>

<?= $this->endSection() ?>