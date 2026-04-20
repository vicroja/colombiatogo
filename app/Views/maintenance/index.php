<?= $this->extend('layouts/pms') ?>
<?= $this->section('content') ?>

    <style>
        /* ── Variables ───────────────────────────────────────────────────────────── */
        :root {
            --k-pending    : #f59e0b;
            --k-progress   : #3b82f6;
            --k-completed  : #22c55e;
            --k-high       : #ef4444;
            --k-med        : #f59e0b;
            --k-low        : #94a3b8;
            --k-radius     : 12px;
            --k-card-radius: 10px;
        }

        /* ── Stats bar ───────────────────────────────────────────────────────────── */
        .maint-stats {
            display               : grid;
            grid-template-columns : repeat(auto-fit, minmax(130px, 1fr));
            gap                   : .75rem;
            margin-bottom         : 1.5rem;
        }
        .mstat {
            background    : #fff;
            border        : 1px solid #e2e8f0;
            border-radius : var(--k-radius);
            padding       : 1rem 1.25rem;
            display       : flex;
            flex-direction: column;
            gap           : .2rem;
        }
        .mstat-n {
            font-size   : 1.75rem;
            font-weight : 700;
            line-height : 1;
            color       : #0f172a;
        }
        .mstat-l {
            font-size   : .72rem;
            color       : #64748b;
            font-weight : 500;
        }
        .mstat.alert-stat { border-color: #fca5a5; background: #fff5f5 }
        .mstat.alert-stat .mstat-n { color: #dc2626 }

        /* ── Filtros ─────────────────────────────────────────────────────────────── */
        .maint-filters {
            display     : flex;
            gap         : .6rem;
            flex-wrap   : wrap;
            align-items : center;
            margin-bottom: 1.25rem;
        }
        .filter-chip {
            display       : inline-flex;
            align-items   : center;
            gap           : .35rem;
            padding       : .35rem .85rem;
            border-radius : 99px;
            font-size     : .78rem;
            font-weight   : 500;
            border        : 1.5px solid #e2e8f0;
            background    : #fff;
            color         : #475569;
            cursor        : pointer;
            text-decoration: none;
            transition    : all .15s;
        }
        .filter-chip:hover,
        .filter-chip.active {
            background   : #f0f4ff;
            border-color : #6366f1;
            color        : #4338ca;
        }
        .filter-chip.priority-alta.active  { background: #fff5f5; border-color: #ef4444; color: #dc2626 }
        .filter-chip.priority-media.active { background: #fffbeb; border-color: #f59e0b; color: #d97706 }
        .filter-chip.priority-baja.active  { background: #f8fafc; border-color: #94a3b8; color: #475569 }

        /* ── Kanban ──────────────────────────────────────────────────────────────── */
        .kanban-board {
            display               : grid;
            grid-template-columns : repeat(3, 1fr);
            gap                   : 1rem;
            align-items           : start;
        }
        .kanban-col {
            background    : #f8fafc;
            border-radius : var(--k-radius);
            border        : 1px solid #e2e8f0;
            overflow      : hidden;
        }
        .kanban-col-header {
            padding      : .85rem 1rem;
            display      : flex;
            align-items  : center;
            justify-content: space-between;
            border-bottom: 1px solid #e2e8f0;
            background   : #fff;
        }
        .kanban-col-title {
            display     : flex;
            align-items : center;
            gap         : .5rem;
            font-size   : .82rem;
            font-weight : 700;
            color       : #0f172a;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .k-dot {
            width         : 8px;
            height        : 8px;
            border-radius : 50%;
        }
        .k-dot.pending     { background: var(--k-pending) }
        .k-dot.in_progress { background: var(--k-progress) }
        .k-dot.completed   { background: var(--k-completed) }

        .kanban-col-count {
            background    : #e2e8f0;
            color         : #475569;
            font-size     : .72rem;
            font-weight   : 700;
            padding       : .15rem .55rem;
            border-radius : 99px;
        }
        .kanban-col-body {
            padding    : .75rem;
            min-height : 400px;
            display    : flex;
            flex-direction: column;
            gap        : .6rem;
        }
        .kanban-empty {
            flex        : 1;
            display     : flex;
            align-items : center;
            justify-content: center;
            flex-direction: column;
            gap         : .4rem;
            color       : #cbd5e1;
            padding     : 2rem 0;
            font-size   : .82rem;
        }

        /* ── Task card ───────────────────────────────────────────────────────────── */
        .task-card {
            background    : #fff;
            border-radius : var(--k-card-radius);
            border        : 1px solid #e2e8f0;
            padding       : .9rem 1rem;
            cursor        : pointer;
            transition    : box-shadow .15s, transform .15s;
            position      : relative;
        }
        .task-card:hover {
            box-shadow : 0 4px 16px rgba(0,0,0,.08);
            transform  : translateY(-1px);
        }
        .task-card.priority-alta  { border-left: 3px solid var(--k-high) }
        .task-card.priority-media { border-left: 3px solid var(--k-med) }
        .task-card.priority-baja  { border-left: 3px solid var(--k-low) }
        .task-card.is-overdue     { background: #fff8f8 }

        .tc-header {
            display     : flex;
            align-items : flex-start;
            justify-content: space-between;
            gap         : .5rem;
            margin-bottom: .5rem;
        }
        .tc-title {
            font-size   : .875rem;
            font-weight : 600;
            color       : #0f172a;
            line-height : 1.4;
            flex        : 1;
        }
        .tc-badges {
            display : flex;
            gap     : .3rem;
            flex-shrink: 0;
        }
        .tc-badge {
            font-size     : .65rem;
            font-weight   : 700;
            padding       : .15rem .5rem;
            border-radius : 4px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .tc-badge.alta   { background: #fee2e2; color: #dc2626 }
        .tc-badge.media  { background: #fef3c7; color: #d97706 }
        .tc-badge.baja   { background: #f1f5f9; color: #64748b }
        .tc-badge.blocked{ background: #1e293b; color: #fff }
        .tc-badge.overdue{ background: #fee2e2; color: #dc2626 }

        .tc-unit {
            font-size     : .75rem;
            color         : #64748b;
            display       : flex;
            align-items   : center;
            gap           : .3rem;
            margin-bottom : .4rem;
        }
        .tc-desc {
            font-size   : .78rem;
            color       : #94a3b8;
            line-height : 1.5;
            margin-bottom: .6rem;
            display     : -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow    : hidden;
        }
        .tc-footer {
            display         : flex;
            align-items     : center;
            justify-content : space-between;
            padding-top     : .6rem;
            border-top      : 1px solid #f1f5f9;
            gap             : .5rem;
        }
        .tc-date {
            font-size     : .7rem;
            color         : #94a3b8;
            display       : flex;
            align-items   : center;
            gap           : .3rem;
        }
        .tc-date.overdue { color: #dc2626; font-weight: 600 }
        .tc-actions {
            display : flex;
            gap     : .4rem;
        }
        .tc-btn {
            background    : none;
            border        : 1px solid #e2e8f0;
            border-radius : 6px;
            padding       : .2rem .5rem;
            font-size     : .72rem;
            cursor        : pointer;
            color         : #475569;
            transition    : all .15s;
            display       : flex;
            align-items   : center;
            gap           : .25rem;
            white-space   : nowrap;
        }
        .tc-btn:hover            { background: #f1f5f9 }
        .tc-btn.btn-complete     { border-color: #bbf7d0; color: #16a34a }
        .tc-btn.btn-complete:hover { background: #f0fdf4 }
        .tc-btn.btn-progress     { border-color: #bfdbfe; color: #2563eb }
        .tc-btn.btn-progress:hover { background: #eff6ff }
        .tc-btn.btn-del          { border-color: #fecaca; color: #dc2626 }
        .tc-btn.btn-del:hover    { background: #fff5f5 }

        /* ── Status change inline select (para completar) ──────────────────────── */
        .status-form { display: inline }

        /* ── Modal crear tarea ───────────────────────────────────────────────────── */
        .maint-modal .modal-content {
            border-radius : 16px;
            border        : none;
            box-shadow    : 0 24px 60px rgba(0,0,0,.2);
        }
        .maint-modal .modal-header {
            background    : #1e293b;
            color         : #fff;
            border-radius : 16px 16px 0 0;
            padding       : 1.25rem 1.5rem;
        }
        .maint-modal .modal-header .btn-close { filter: invert(1) }
        .form-label-sm {
            font-size      : .72rem;
            font-weight    : 600;
            letter-spacing : .06em;
            text-transform : uppercase;
            color          : #475569;
            margin-bottom  : .3rem;
            display        : block;
        }
        .priority-selector {
            display : flex;
            gap     : .5rem;
        }
        .priority-option {
            flex          : 1;
            border        : 2px solid #e2e8f0;
            border-radius : 8px;
            padding       : .5rem;
            text-align    : center;
            cursor        : pointer;
            font-size     : .78rem;
            font-weight   : 600;
            transition    : all .15s;
        }
        .priority-option input { display: none }
        .priority-option.p-alta:has(input:checked)  { border-color: #ef4444; background: #fff5f5; color: #dc2626 }
        .priority-option.p-media:has(input:checked) { border-color: #f59e0b; background: #fffbeb; color: #d97706 }
        .priority-option.p-baja:has(input:checked)  { border-color: #94a3b8; background: #f8fafc; color: #475569 }
        .priority-option:hover { border-color: #94a3b8 }

        .blocks-toggle {
            display       : flex;
            align-items   : flex-start;
            gap           : .75rem;
            background    : #fff8f8;
            border        : 1px solid #fecaca;
            border-radius : 10px;
            padding       : .85rem 1rem;
        }
        .blocks-toggle input { accent-color: #dc2626; margin-top: .15rem }

        /* ── Toast ───────────────────────────────────────────────────────────────── */
        .maint-toast {
            position   : fixed;
            bottom     : 1.5rem;
            left       : 50%;
            transform  : translateX(-50%);
            background : #0f172a;
            color      : #fff;
            padding    : .6rem 1.5rem;
            border-radius: 99px;
            font-size  : .82rem;
            font-weight: 600;
            z-index    : 9999;
            display    : none;
            box-shadow : 0 4px 16px rgba(0,0,0,.3);
        }
        .maint-toast.visible { display: block }

        @media (max-width: 768px) {
            .kanban-board { grid-template-columns: 1fr }
        }
    </style>

    <!-- ── Header ────────────────────────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="bi bi-tools me-2 text-warning"></i>
                Mantenimiento
            </h4>
            <p class="text-muted small mb-0">
                Tablero Kanban · <?= date('d M Y') ?>
            </p>
        </div>
        <button class="btn btn-dark fw-bold shadow-sm"
                data-bs-toggle="modal"
                data-bs-target="#taskModal">
            <i class="bi bi-plus-lg me-1"></i>
            Nueva tarea
        </button>
    </div>

    <!-- ── Stats ─────────────────────────────────────────────────────────────── -->
    <div class="maint-stats">
        <div class="mstat">
            <span class="mstat-n"><?= $stats['total'] ?></span>
            <span class="mstat-l">Total tareas</span>
        </div>
        <div class="mstat">
        <span class="mstat-n" style="color:var(--k-pending)">
            <?= $stats['pending'] ?>
        </span>
            <span class="mstat-l">Pendientes</span>
        </div>
        <div class="mstat">
        <span class="mstat-n" style="color:var(--k-progress)">
            <?= $stats['in_progress'] ?>
        </span>
            <span class="mstat-l">En progreso</span>
        </div>
        <div class="mstat">
        <span class="mstat-n" style="color:var(--k-completed)">
            <?= $stats['completed'] ?>
        </span>
            <span class="mstat-l">Completadas</span>
        </div>
        <?php if ($stats['blocking'] > 0): ?>
            <div class="mstat alert-stat">
                <span class="mstat-n"><?= $stats['blocking'] ?></span>
                <span class="mstat-l">
            <i class="bi bi-lock-fill me-1"></i>Unidades bloqueadas
        </span>
            </div>
        <?php endif; ?>
        <?php if ($stats['overdue'] > 0): ?>
            <div class="mstat alert-stat">
                <span class="mstat-n"><?= $stats['overdue'] ?></span>
                <span class="mstat-l">
            <i class="bi bi-exclamation-triangle me-1"></i>Vencidas
        </span>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── Filtros ────────────────────────────────────────────────────────────── -->
    <div class="maint-filters">
        <span style="font-size:.75rem;color:#64748b;font-weight:600">Filtrar:</span>

        <!-- Por prioridad -->
        <a href="/maintenance" class="filter-chip <?= !$filterPriority && !$filterUnit ? 'active' : '' ?>">
            Todas
        </a>
        <?php foreach (['alta' => '🔴 Alta', 'media' => '🟡 Media', 'baja' => '⚪ Baja'] as $p => $label): ?>
            <a href="/maintenance?priority=<?= $p ?><?= $filterUnit ? '&unit_id=' . $filterUnit : '' ?>"
               class="filter-chip priority-<?= $p ?> <?= $filterPriority === $p ? 'active' : '' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>

        <!-- Por unidad -->
        <?php if (!empty($units)): ?>
            <select class="filter-chip"
                    style="appearance:none;padding-right:1.5rem;
                       background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M7 10l5 5 5-5'/%3E%3C/svg%3E\");
            background-repeat:no-repeat;background-position:right .5rem center"
            onchange="location.href='/maintenance?unit_id='+this.value+'<?= $filterPriority ? '&priority=' . $filterPriority : '' ?>'">
            <option value="">Todas las unidades</option>
            <?php foreach ($units as $u): ?>
                <option value="<?= $u['id'] ?>"
                    <?= $filterUnit == $u['id'] ? 'selected' : '' ?>>
                    <?= esc($u['name']) ?>
                </option>
            <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <?php if ($filterPriority || $filterUnit): ?>
            <a href="/maintenance"
               class="filter-chip"
               style="color:#ef4444;border-color:#fecaca">
                <i class="bi bi-x"></i> Limpiar
            </a>
        <?php endif; ?>
    </div>

    <!-- ── Tablero Kanban ────────────────────────────────────────────────────── -->
    <div class="kanban-board">

        <?php
        $columns = [
            'pending'     => ['label' => 'Pendientes',  'icon' => 'bi-hourglass-split'],
            'in_progress' => ['label' => 'En progreso', 'icon' => 'bi-gear-fill'],
            'completed'   => ['label' => 'Completadas', 'icon' => 'bi-check-circle-fill'],
        ];
        foreach ($columns as $status => $col):
            ?>
            <div class="kanban-col">
                <div class="kanban-col-header">
                    <div class="kanban-col-title">
                        <span class="k-dot <?= $status ?>"></span>
                        <i class="bi <?= $col['icon'] ?>" style="font-size:.85rem"></i>
                        <?= $col['label'] ?>
                    </div>
                    <span class="kanban-col-count">
                <?= count($kanban[$status]) ?>
            </span>
                </div>
                <div class="kanban-col-body" id="col-<?= $status ?>">
                    <?php if (empty($kanban[$status])): ?>
                        <div class="kanban-empty">
                            <i class="bi bi-inbox" style="font-size:1.75rem"></i>
                            <span>Sin tareas</span>
                        </div>
                    <?php else: ?>
                        <?php foreach ($kanban[$status] as $task):
                            $isOverdue = !empty($task['scheduled_date'])
                                && $task['scheduled_date'] < date('Y-m-d')
                                && $task['status'] !== 'completed';
                            ?>
                            <div class="task-card priority-<?= $task['priority'] ?>
                            <?= $isOverdue ? 'is-overdue' : '' ?>"
                                 data-id="<?= $task['id'] ?>"
                                 data-status="<?= $task['status'] ?>">

                                <div class="tc-header">
                                    <div class="tc-title">
                                        <?= esc($task['title']) ?>
                                    </div>
                                    <div class="tc-badges">
                            <span class="tc-badge <?= $task['priority'] ?>">
                                <?= $task['priority'] ?>
                            </span>
                                        <?php if ($task['blocks_unit'] && $task['status'] !== 'completed'): ?>
                                            <span class="tc-badge blocked"
                                                  title="Habitación bloqueada">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                        <?php endif; ?>
                                        <?php if ($isOverdue): ?>
                                            <span class="tc-badge overdue">
                                    Vencida
                                </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if (!empty($task['unit_name'])): ?>
                                    <div class="tc-unit">
                                        <i class="bi bi-door-open"></i>
                                        <?= esc($task['unit_name']) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($task['description'])): ?>
                                    <div class="tc-desc">
                                        <?= esc($task['description']) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="tc-footer">
                                    <!-- Fecha -->
                                    <div class="tc-date <?= $isOverdue ? 'overdue' : '' ?>">
                                        <?php if (!empty($task['scheduled_date'])): ?>
                                            <i class="bi bi-calendar3"></i>
                                            <?= date('d M', strtotime($task['scheduled_date'])) ?>
                                        <?php else: ?>
                                            <i class="bi bi-calendar3"></i>
                                            Sin fecha
                                        <?php endif; ?>
                                    </div>

                                    <!-- Acciones -->
                                    <div class="tc-actions">
                                        <?php if ($task['status'] === 'pending'): ?>
                                            <button class="tc-btn btn-progress"
                                                    onclick="changeStatus(<?= $task['id'] ?>, 'in_progress')">
                                                <i class="bi bi-play-fill"></i> Iniciar
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($task['status'] === 'in_progress'): ?>
                                            <button class="tc-btn btn-complete"
                                                    onclick="changeStatus(<?= $task['id'] ?>, 'completed')">
                                                <i class="bi bi-check-lg"></i> Completar
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($task['status'] === 'completed'): ?>
                                            <button class="tc-btn"
                                                    onclick="changeStatus(<?= $task['id'] ?>, 'pending')"
                                                    title="Reabrir">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        <?php endif; ?>

                                        <!-- Eliminar (form POST — FIX seguridad) -->
                                        <form action="/maintenance/delete/<?= $task['id'] ?>"
                                              method="post" class="status-form"
                                              onsubmit="return confirm('¿Eliminar esta tarea?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="tc-btn btn-del">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

    </div>

    <!-- ════════════════════════════════════════════════════════════
         MODAL — Nueva tarea
    ════════════════════════════════════════════════════════════ -->
    <div class="modal fade maint-modal" id="taskModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="/maintenance/store" method="post">
                    <?= csrf_field() ?>

                    <div class="modal-header">
                        <div>
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-plus-circle me-1"></i>
                                Nueva tarea de mantenimiento
                            </h5>
                            <p class="mb-0"
                               style="font-size:.75rem;opacity:.65;margin-top:.15rem">
                                Todos los campos marcados con * son requeridos
                            </p>
                        </div>
                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="modal">
                        </button>
                    </div>

                    <div class="modal-body p-4">

                        <!-- Título -->
                        <div class="mb-3">
                            <label class="form-label-sm">Título del problema *</label>
                            <input type="text" name="title"
                                   class="form-control"
                                   placeholder="Ej: Fuga de agua en lavamanos"
                                   required maxlength="150">
                        </div>

                        <!-- Unidad y fecha -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-7">
                                <label class="form-label-sm">
                                    Habitación / Área
                                </label>
                                <select name="unit_id" class="form-select">
                                    <option value="">Área común / General</option>
                                    <?php foreach ($units as $u): ?>
                                        <option value="<?= $u['id'] ?>">
                                            <?= esc($u['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label-sm">
                                    Fecha programada
                                </label>
                                <input type="date" name="scheduled_date"
                                       class="form-control"
                                       min="<?= date('Y-m-d') ?>">
                            </div>
                        </div>

                        <!-- Prioridad -->
                        <div class="mb-3">
                            <label class="form-label-sm">Prioridad *</label>
                            <div class="priority-selector">
                                <label class="priority-option p-baja">
                                    <input type="radio" name="priority" value="baja">
                                    ⚪ Baja
                                </label>
                                <label class="priority-option p-media">
                                    <input type="radio" name="priority"
                                           value="media" checked>
                                    🟡 Media
                                </label>
                                <label class="priority-option p-alta">
                                    <input type="radio" name="priority" value="alta">
                                    🔴 Alta
                                </label>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label class="form-label-sm">
                                Descripción / Instrucciones para el técnico
                            </label>
                            <textarea name="description"
                                      class="form-control" rows="3"
                                      placeholder="Detalles del problema, materiales necesarios, acceso..."
                                      maxlength="1000"></textarea>
                        </div>

                        <!-- Bloquear unidad -->
                        <div class="blocks-toggle">
                            <input type="checkbox" name="blocks_unit"
                                   id="blocksUnit" value="1"
                                   class="form-check-input">
                            <div>
                                <label for="blocksUnit"
                                       class="fw-bold text-danger"
                                       style="font-size:.85rem;cursor:pointer">
                                    <i class="bi bi-lock-fill me-1"></i>
                                    Bloquear habitación para reservas
                                </label>
                                <p class="mb-0 text-muted"
                                   style="font-size:.75rem;margin-top:.2rem">
                                    La habitación no aparecerá disponible hasta que
                                    esta tarea esté completada.
                                </p>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button"
                                class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-dark fw-bold px-4">
                            <i class="bi bi-floppy me-1"></i>
                            Guardar tarea
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="maint-toast" id="maintToast"></div>

    <script>
        /**
         * Cambia el estado de una tarea via AJAX sin recargar la página
         * @param {number} taskId
         * @param {string} newStatus
         */
        async function changeStatus(taskId, newStatus) {
            // Leer token CSRF desde cookie
            const csrfToken = document.cookie
                .split('; ')
                .find(r => r.startsWith('csrf_cookie_name='))
                ?.split('=')[1] ?? '';

            const formData = new FormData();
            formData.append('status', newStatus);

            try {
                const res  = await fetch(`/maintenance/update-status/${taskId}`, {
                    method      : 'POST',
                    body        : formData,
                    credentials : 'same-origin',
                    headers     : {
                        'X-Requested-With' : 'XMLHttpRequest',
                        'X-CSRF-TOKEN'     : csrfToken,
                    },
                });

                const data = await res.json();

                if (data.success) {
                    // Mover la tarjeta visualmente o recargar
                    showToast(statusLabel(newStatus));
                    // Recarga suave tras 600ms para actualizar los contadores
                    setTimeout(() => location.reload(), 600);
                } else {
                    showToast('Error al actualizar', true);
                }
            } catch (err) {
                console.error('[Maintenance] Error:', err);
                showToast('Error de conexión', true);
            }
        }

        /**
         * Label legible por estado
         */
        function statusLabel(status) {
            const labels = {
                pending     : '🔄 Tarea marcada como pendiente',
                in_progress : '▶️ Tarea iniciada',
                completed   : '✅ Tarea completada',
            };
            return labels[status] ?? 'Estado actualizado';
        }

        /**
         * Muestra un toast de notificación
         */
        function showToast(msg, isError = false) {
            const toast       = document.getElementById('maintToast');
            toast.textContent = msg;
            toast.style.background = isError ? '#dc2626' : '#0f172a';
            toast.classList.add('visible');
            setTimeout(() => toast.classList.remove('visible'), 2500);
        }

        // Auto-dismiss flash alerts de CI4
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => alert.style.opacity = '0', 3500);
            setTimeout(() => alert.remove(), 4000);
        });
    </script>

<?= $this->endSection() ?>