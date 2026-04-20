<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

<?php
$currencySymbol = $tenant['currency_symbol'] ?? '$';
?>

    <style>
        /* ── Layout ──────────────────────────────────────────────────────────────── */
        .crm-header {
            display         : flex;
            justify-content : space-between;
            align-items     : center;
            margin-bottom   : 1.5rem;
        }

        /* ── Stats cards ─────────────────────────────────────────────────────────── */
        .crm-stats {
            display               : grid;
            grid-template-columns : repeat(auto-fit, minmax(130px, 1fr));
            gap                   : .75rem;
            margin-bottom         : 1.5rem;
        }
        .cstat {
            background    : #fff;
            border        : 1px solid #e2e8f0;
            border-radius : 12px;
            padding       : 1rem 1.25rem;
            cursor        : pointer;
            transition    : all .2s;
            text-decoration: none;
            display       : block;
        }
        .cstat:hover {
            transform    : translateY(-2px);
            box-shadow   : 0 4px 16px rgba(0,0,0,.08);
            border-color : var(--seg-color, #6366f1);
        }
        .cstat.active-filter {
            border-color : var(--seg-color, #6366f1);
            background   : var(--seg-bg, #f0f4ff);
        }
        .cstat-n {
            font-size   : 1.75rem;
            font-weight : 800;
            line-height : 1;
            color       : var(--seg-color, #0f172a);
        }
        .cstat-l {
            font-size   : .72rem;
            color       : #64748b;
            font-weight : 500;
            margin-top  : .2rem;
        }

        /* ── Filtros y búsqueda ──────────────────────────────────────────────────── */
        .crm-toolbar {
            display     : flex;
            gap         : .75rem;
            align-items : center;
            flex-wrap   : wrap;
            margin-bottom: 1.25rem;
        }
        .crm-search {
            flex          : 1;
            min-width     : 200px;
            display       : flex;
            align-items   : center;
            background    : #fff;
            border        : 1.5px solid #e2e8f0;
            border-radius : 10px;
            padding       : .5rem .85rem;
            gap           : .5rem;
            transition    : border-color .2s;
        }
        .crm-search:focus-within { border-color: #6366f1 }
        .crm-search input {
            border     : none;
            outline    : none;
            flex       : 1;
            font-size  : .875rem;
            color      : #0f172a;
            background : transparent;
        }
        .crm-sort {
            background    : #fff;
            border        : 1.5px solid #e2e8f0;
            border-radius : 10px;
            padding       : .5rem .85rem;
            font-size     : .82rem;
            color         : #374151;
            outline       : none;
            cursor        : pointer;
        }

        /* ── Tabla ───────────────────────────────────────────────────────────────── */
        .crm-table-wrap {
            background    : #fff;
            border-radius : 14px;
            border        : 1px solid #e2e8f0;
            overflow      : hidden;
        }
        .crm-table {
            width           : 100%;
            border-collapse : collapse;
            font-size       : .875rem;
        }
        .crm-table thead tr {
            background    : #f8fafc;
            border-bottom : 1px solid #e2e8f0;
        }
        .crm-table th {
            padding        : .75rem 1rem;
            font-size      : .72rem;
            font-weight    : 700;
            color          : #64748b;
            text-transform : uppercase;
            letter-spacing : .05em;
            text-align     : left;
            white-space    : nowrap;
        }
        .crm-table td {
            padding        : .85rem 1rem;
            border-bottom  : 1px solid #f1f5f9;
            vertical-align : middle;
        }
        .crm-table tr:last-child td { border-bottom: none }
        .crm-table tr:hover td      { background: #fafbff }

        /* Avatar */
        .guest-avatar {
            width           : 36px;
            height          : 36px;
            border-radius   : 50%;
            display         : flex;
            align-items     : center;
            justify-content : center;
            font-size       : .8rem;
            font-weight     : 700;
            color           : #fff;
            flex-shrink     : 0;
        }

        /* Score bar */
        .score-bar   { display: flex; align-items: center; gap: .5rem }
        .score-track {
            flex          : 1;
            height        : 6px;
            background    : #f1f5f9;
            border-radius : 99px;
            overflow      : hidden;
            min-width     : 60px;
        }
        .score-fill {
            height        : 6px;
            border-radius : 99px;
            background    : linear-gradient(90deg, #6366f1, #8b5cf6);
            transition    : width .4s ease;
        }
        .score-num {
            font-size   : .8rem;
            font-weight : 700;
            color       : #0f172a;
            min-width   : 24px;
        }

        /* Segment badge */
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

        /* RFM pills */
        .rfm-pills { display: flex; gap: .25rem }
        .rfm-pill  {
            width           : 22px;
            height          : 22px;
            border-radius   : 4px;
            display         : flex;
            align-items     : center;
            justify-content : center;
            font-size       : .65rem;
            font-weight     : 800;
            color           : #fff;
        }

        /* Acción */
        .btn-crm-action {
            background     : none;
            border         : 1px solid #e2e8f0;
            border-radius  : 7px;
            padding        : .3rem .65rem;
            font-size      : .75rem;
            color          : #475569;
            cursor         : pointer;
            transition     : all .15s;
            text-decoration: none;
            display        : inline-flex;
            align-items    : center;
            gap            : .3rem;
        }
        .btn-crm-action:hover {
            background   : #f0f4ff;
            border-color : #6366f1;
            color        : #4338ca;
        }

        /* Empty state */
        .crm-empty {
            text-align : center;
            padding    : 4rem 2rem;
            color      : #94a3b8;
        }
    </style>

    <!-- ── Header ────────────────────────────────────────────────────────────── -->
    <div class="crm-header">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="bi bi-people me-2 text-primary"></i>
                CRM · Huéspedes
            </h4>
            <p class="text-muted small mb-0">
                Scoring RFM &middot; <?= $stats['total'] ?> huéspedes registrados
            </p>
        </div>
        <div class="d-flex gap-2">
        <span class="badge bg-success-subtle text-success
                     border border-success-subtle px-3 py-2">
            <i class="bi bi-arrow-repeat me-1"></i>
            <?= $stats['repeat_pct'] ?>% repiten
        </span>
            <span class="badge bg-primary-subtle text-primary
                     border border-primary-subtle px-3 py-2">
            Score promedio: <?= $stats['avg_score'] ?>
        </span>
        </div>
    </div>

    <!-- ── Stats / filtros por segmento ──────────────────────────────────────── -->
    <div class="crm-stats">

        <a href="/crm<?= $search ? '?q=' . urlencode($search) : '' ?>"
           class="cstat <?= !$segment ? 'active-filter' : '' ?>"
           style="--seg-color:#6366f1;--seg-bg:#f0f4ff">
            <div class="cstat-n" style="color:#6366f1">
                <?= $stats['total'] ?>
            </div>
            <div class="cstat-l">Todos</div>
        </a>

        <?php
        $segCards = [
            'champion'  => ['Champions',     $stats['champions'], '#7c3aed', '#f5f3ff'],
            'loyal'     => ['Leales',         $stats['loyal'],    '#2563eb', '#eff6ff'],
            'at_risk'   => ['En riesgo',      $stats['at_risk'],  '#dc2626', '#fff5f5'],
            'potential' => ['Alto potencial', $stats['potential'],'#059669', '#f0fdf4'],
            'new'       => ['Nuevos',         $stats['new'],      '#0891b2', '#f0f9ff'],
            'lost'      => ['Perdidos',       $stats['lost'],     '#94a3b8', '#f8fafc'],
        ];
        foreach ($segCards as $seg => [$label, $count, $color, $bg]):
            $url = '/crm?segment=' . $seg
                . ($search ? '&q=' . urlencode($search) : '');
            $isActive = $segment === $seg;
            ?>
            <a href="<?= $isActive ? '/crm' . ($search ? '?q=' . urlencode($search) : '') : $url ?>"
               class="cstat <?= $isActive ? 'active-filter' : '' ?>"
               style="--seg-color:<?= $color ?>;--seg-bg:<?= $bg ?>">
                <div class="cstat-n" style="color:<?= $color ?>">
                    <?= $count ?>
                </div>
                <div class="cstat-l"><?= $label ?></div>
            </a>
        <?php endforeach; ?>

    </div>

    <!-- ── Toolbar ───────────────────────────────────────────────────────────── -->
    <form method="GET" action="/crm" class="crm-toolbar">

        <?php if ($segment): ?>
            <input type="hidden" name="segment" value="<?= esc($segment) ?>">
        <?php endif; ?>

        <div class="crm-search">
            <i class="bi bi-search" style="color:#94a3b8;font-size:.85rem"></i>
            <input type="text" name="q"
                   value="<?= esc($search) ?>"
                   placeholder="Buscar por nombre, email o teléfono..."
                   oninput="this.form.submit()">
            <?php if ($search): ?>
                <a href="/crm<?= $segment ? '?segment=' . $segment : '' ?>"
                   style="color:#94a3b8;font-size:.85rem;text-decoration:none">
                    <i class="bi bi-x"></i>
                </a>
            <?php endif; ?>
        </div>

        <select name="sort" class="crm-sort" onchange="this.form.submit()">
            <option value="score"
                <?= $sort === 'score'      ? 'selected' : '' ?>>
                Ordenar: Score
            </option>
            <option value="last_visit"
                <?= $sort === 'last_visit' ? 'selected' : '' ?>>
                Última visita
            </option>
            <option value="spent"
                <?= $sort === 'spent'      ? 'selected' : '' ?>>
                Mayor gasto
            </option>
            <option value="visits"
                <?= $sort === 'visits'     ? 'selected' : '' ?>>
                Más visitas
            </option>
            <option value="name"
                <?= $sort === 'name'       ? 'selected' : '' ?>>
                Nombre A-Z
            </option>
        </select>

    </form>

    <!-- ── Tabla ─────────────────────────────────────────────────────────────── -->
<?php if (empty($guests)): ?>

    <div class="crm-table-wrap">
        <div class="crm-empty">
            <i class="bi bi-people"
               style="font-size:2.5rem;display:block;margin-bottom:.75rem"></i>
            <p class="fw-semibold mb-1">
                <?= ($search || $segment)
                    ? 'Sin resultados para este filtro'
                    : 'Aún no hay huéspedes registrados' ?>
            </p>
            <p style="font-size:.82rem">
                <?php if ($search || $segment): ?>
                    <a href="/crm">Ver todos los huéspedes</a>
                <?php else: ?>
                    Los huéspedes aparecerán aquí cuando
                    completes tu primera reservación.
                <?php endif; ?>
            </p>
        </div>
    </div>

<?php else: ?>

    <div class="crm-table-wrap">
        <table class="crm-table">
            <thead>
            <tr>
                <th>Huésped</th>
                <th>Segmento</th>
                <th>Score RFM</th>
                <th>Visitas</th>
                <th>Gasto total</th>
                <th>Última visita</th>
                <th>Contacto</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php
            // Definición de segmentos — solo dentro del loop
            $segmentDefs = [
                'champion'  => ['label' => 'Champion',      'color' => '#7c3aed', 'bg' => '#f5f3ff'],
                'loyal'     => ['label' => 'Leal',           'color' => '#2563eb', 'bg' => '#eff6ff'],
                'at_risk'   => ['label' => 'En riesgo',      'color' => '#dc2626', 'bg' => '#fff5f5'],
                'potential' => ['label' => 'Alto potencial', 'color' => '#059669', 'bg' => '#f0fdf4'],
                'new'       => ['label' => 'Nuevo',          'color' => '#0891b2', 'bg' => '#f0f9ff'],
                'lost'      => ['label' => 'Perdido',        'color' => '#94a3b8', 'bg' => '#f8fafc'],
                'regular'   => ['label' => 'Regular',        'color' => '#64748b', 'bg' => '#f8fafc'],
            ];

            $rfmColors = [
                1 => '#e2e8f0',
                2 => '#bfdbfe',
                3 => '#93c5fd',
                4 => '#3b82f6',
                5 => '#1d4ed8',
            ];

            $avatarColors = [
                'champion'  => '#7c3aed',
                'loyal'     => '#2563eb',
                'at_risk'   => '#dc2626',
                'potential' => '#059669',
                'new'       => '#0891b2',
                'lost'      => '#94a3b8',
                'regular'   => '#64748b',
            ];

            foreach ($guests as $g):
                $rfm         = $g['rfm'] ?? [];
                $segKey      = $rfm['segment'] ?? 'regular';
                $segDef      = $segmentDefs[$segKey] ?? $segmentDefs['regular'];
                $avatarColor = $avatarColors[$segKey] ?? '#6366f1';

                // Iniciales del nombre
                $nameParts = array_slice(
                    explode(' ', $g['full_name'] ?? 'N N'), 0, 2
                );
                $initials  = implode('', array_map(
                    fn($w) => !empty($w) ? strtoupper($w[0]) : '',
                    $nameParts
                ));
                ?>
                <tr>
                    <!-- Huésped -->
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="guest-avatar"
                                 style="background:<?= $avatarColor ?>">
                                <?= esc($initials) ?>
                            </div>
                            <div>
                                <div class="fw-semibold"
                                     style="color:#0f172a;font-size:.875rem">
                                    <?= esc($g['full_name'] ?? '') ?>
                                </div>
                                <?php if (!empty($g['email'])): ?>
                                    <div style="font-size:.72rem;color:#94a3b8">
                                        <?= esc($g['email']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>

                    <!-- Segmento -->
                    <td>
                        <span class="seg-badge"
                              style="background:<?= $segDef['bg'] ?>;
                                      color:<?= $segDef['color'] ?>">
                            <?= $segDef['label'] ?>
                        </span>
                    </td>

                    <!-- Score RFM -->
                    <td>
                        <div class="score-bar">
                            <div class="score-track">
                                <div class="score-fill"
                                     style="width:<?= (($rfm['score'] ?? 0) / 5) * 100 ?>%">
                                </div>
                            </div>
                            <span class="score-num">
                                <?= $rfm['score'] ?? 0 ?>
                            </span>
                        </div>
                        <div class="rfm-pills mt-1">
                            <?php foreach (['r' => 'R', 'f' => 'F', 'm' => 'M'] as $key => $lbl):
                                $val   = $rfm[$key . '_score'] ?? 1;
                                $bg    = $rfmColors[$val] ?? '#e2e8f0';
                                $txtColor = $val >= 3 ? '#fff' : '#64748b';
                                ?>
                                <div class="rfm-pill"
                                     style="background:<?= $bg ?>;color:<?= $txtColor ?>"
                                     title="<?= $lbl ?>: <?= $val ?>/5">
                                    <?= $lbl ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </td>

                    <!-- Visitas -->
                    <td>
                        <span class="fw-semibold">
                            <?= $g['completed_reservations'] ?? 0 ?>
                        </span>
                        <span style="font-size:.72rem;color:#94a3b8"> vez/veces</span>
                    </td>

                    <!-- Gasto -->
                    <td>
                        <span class="fw-semibold">
                            <?= $currencySymbol ?>
                            <?= number_format($g['total_spent'] ?? 0, 0, ',', '.') ?>
                        </span>
                    </td>

                    <!-- Última visita -->
                    <td>
                        <?php if (!empty($g['last_visit'])): ?>
                            <div style="font-size:.82rem">
                                <?= date('d M Y', strtotime($g['last_visit'])) ?>
                            </div>
                            <div style="font-size:.72rem;
                                 color:<?= ($rfm['days_since'] ?? 0) > 180
                                ? '#dc2626' : '#94a3b8' ?>">
                                hace <?= $rfm['days_since'] ?? '?' ?> días
                            </div>
                        <?php else: ?>
                            <span style="color:#94a3b8;font-size:.78rem">
                                Sin visitas
                            </span>
                        <?php endif; ?>
                    </td>

                    <!-- Contacto -->
                    <td>
                        <?php if (!empty($g['phone'])): ?>
                            <a href="https://wa.me/<?= preg_replace('/\D/', '', $g['phone']) ?>"
                               target="_blank"
                               class="btn-crm-action"
                               style="color:#16a34a;border-color:#bbf7d0"
                               title="WhatsApp">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($g['email'])): ?>
                            <a href="mailto:<?= esc($g['email']) ?>"
                               class="btn-crm-action"
                               title="Email">
                                <i class="bi bi-envelope"></i>
                            </a>
                        <?php endif; ?>
                    </td>

                    <!-- Ver perfil -->
                    <td>
                        <a href="/crm/guest/<?= $g['id'] ?>"
                           class="btn-crm-action">
                            <i class="bi bi-person-lines-fill"></i>
                            Ver perfil
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<?= $this->endSection() ?>