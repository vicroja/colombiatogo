<?php
/**
 * onboarding/layout.php
 * Shell principal del wizard. Renderiza el sidebar de progreso
 * y carga dinámicamente el paso actual como contenido central.
 */

// Determinar si un paso está completado
$isCompleted = fn(int $s) => in_array($s, $completed);
$isCurrent   = fn(int $s) => $s === $currentStep;
$isLocked    = function(int $s) use ($steps, $completed): bool {
    for ($i = 1; $i < $s; $i++) {
        if ($steps[$i]['required'] && !in_array($i, $completed)) return true;
    }
    return false;
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración inicial — <?= esc($tenant['name']) ?></title>

    <!-- Bootstrap 5 + Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* ── Variables de color ──────────────────────────────────── */
        :root {
            --wiz-sidebar-bg   : #0f172a;
            --wiz-sidebar-w    : 290px;
            --wiz-accent       : #6366f1;
            --wiz-accent-light : #818cf8;
            --wiz-success      : #22c55e;
            --wiz-text-muted   : #94a3b8;
            --wiz-step-size    : 36px;
        }

        /* ── Layout base ─────────────────────────────────────────── */
        body {
            background: #f8fafc;
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
        }

        .wizard-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ─────────────────────────────────────────────── */
        .wizard-sidebar {
            width: var(--wiz-sidebar-w);
            background: var(--wiz-sidebar-bg);
            display: flex;
            flex-direction: column;
            padding: 2rem 1.5rem;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            overflow-y: auto;
            z-index: 100;
        }

        .wizard-sidebar .brand {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: 2.5rem;
        }

        .wizard-sidebar .brand img {
            width: 40px; height: 40px;
            border-radius: 8px;
            object-fit: cover;
        }

        .wizard-sidebar .brand .brand-name {
            color: #fff;
            font-weight: 700;
            font-size: .95rem;
            line-height: 1.2;
        }

        .wizard-sidebar .brand .brand-sub {
            color: var(--wiz-text-muted);
            font-size: .75rem;
        }

        /* ── Progress bar superior del sidebar ───────────────────── */
        .sidebar-progress {
            margin-bottom: 2rem;
        }

        .sidebar-progress .progress-label {
            display: flex;
            justify-content: space-between;
            color: var(--wiz-text-muted);
            font-size: .75rem;
            margin-bottom: .4rem;
        }

        .sidebar-progress .progress {
            height: 4px;
            background: #1e293b;
            border-radius: 99px;
        }

        .sidebar-progress .progress-bar {
            background: var(--wiz-accent);
            border-radius: 99px;
            transition: width .4s ease;
        }

        /* ── Lista de pasos ──────────────────────────────────────── */
        .step-list {
            list-style: none;
            padding: 0; margin: 0;
            display: flex;
            flex-direction: column;
            gap: .25rem;
        }

        .step-item a,
        .step-item span {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: .6rem .75rem;
            border-radius: 10px;
            text-decoration: none;
            transition: background .2s;
            cursor: default;
        }

        /* Estado: completado */
        .step-item.is-completed a {
            cursor: pointer;
            color: #e2e8f0;
        }
        .step-item.is-completed a:hover {
            background: #1e293b;
        }

        /* Estado: activo */
        .step-item.is-current a,
        .step-item.is-current span {
            background: var(--wiz-accent);
            color: #fff;
            cursor: default;
        }

        /* Estado: bloqueado / pendiente */
        .step-item.is-locked span,
        .step-item.is-pending span {
            color: var(--wiz-text-muted);
        }

        /* Círculo indicador */
        .step-bullet {
            width: var(--wiz-step-size);
            height: var(--wiz-step-size);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            font-weight: 700;
            flex-shrink: 0;
            border: 2px solid transparent;
        }

        .is-completed .step-bullet {
            background: var(--wiz-success);
            color: #fff;
            border-color: var(--wiz-success);
        }

        .is-current .step-bullet {
            background: rgba(255,255,255,.2);
            color: #fff;
            border-color: rgba(255,255,255,.5);
        }

        .is-locked .step-bullet,
        .is-pending .step-bullet {
            background: #1e293b;
            color: var(--wiz-text-muted);
            border-color: #334155;
        }

        .step-meta { line-height: 1.3; }
        .step-meta .step-title {
            font-size: .82rem;
            font-weight: 600;
        }
        .step-meta .step-badge {
            font-size: .68rem;
            opacity: .7;
        }

        /* ── Footer del sidebar ──────────────────────────────────── */
        .sidebar-footer {
            margin-top: auto;
            padding-top: 1.5rem;
            border-top: 1px solid #1e293b;
            color: var(--wiz-text-muted);
            font-size: .75rem;
        }

        /* ── Contenido principal ─────────────────────────────────── */
        .wizard-content {
            margin-left: var(--wiz-sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header del contenido */
        .content-header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.25rem 2.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .content-header .step-info h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .content-header .step-info p {
            font-size: .8rem;
            color: #64748b;
            margin: 0;
        }

        /* Área de scroll del contenido */
        .content-body {
            flex: 1;
            padding: 2.5rem;
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
        }

        /* ── Cards del wizard ────────────────────────────────────── */
        .wizard-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }

        .wizard-card .card-eyebrow {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--wiz-accent);
            margin-bottom: .5rem;
        }

        .wizard-card h5 {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: .25rem;
        }

        .wizard-card .card-hint {
            font-size: .83rem;
            color: #64748b;
            margin-bottom: 1.5rem;
        }

        /* ── Botones de navegación ───────────────────────────────── */
        .wizard-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2.5rem;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            position: sticky;
            bottom: 0;
        }

        .btn-wiz-primary {
            background: var(--wiz-accent);
            color: #fff;
            border: none;
            padding: .65rem 1.75rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: .9rem;
            transition: background .2s, transform .1s;
        }

        .btn-wiz-primary:hover {
            background: #4f46e5;
            color: #fff;
        }

        .btn-wiz-primary:active { transform: scale(.98); }

        .btn-wiz-secondary {
            background: transparent;
            color: #64748b;
            border: 1px solid #e2e8f0;
            padding: .65rem 1.25rem;
            border-radius: 10px;
            font-weight: 500;
            font-size: .9rem;
        }

        .btn-wiz-secondary:hover {
            background: #f8fafc;
            color: #0f172a;
        }

        .btn-wiz-skip {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: .82rem;
            padding: .4rem .75rem;
            cursor: pointer;
        }

        .btn-wiz-skip:hover { color: #475569; text-decoration: underline; }

        /* ── AI Badge ────────────────────────────────────────────── */
        .ai-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            font-size: .72rem;
            font-weight: 700;
            padding: .25rem .65rem;
            border-radius: 99px;
            letter-spacing: .03em;
        }

        /* ── Botón AI inline ─────────────────────────────────────── */
        .btn-ai {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: .45rem 1rem;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity .2s;
        }

        .btn-ai:hover { opacity: .88; color: #fff; }
        .btn-ai:disabled { opacity: .5; cursor: not-allowed; }

        /* ── Loading spinner IA ──────────────────────────────────── */
        .ai-loading {
            display: none;
            align-items: center;
            gap: .5rem;
            font-size: .83rem;
            color: #6366f1;
        }

        .ai-loading.visible { display: flex; }

        /* ── Alerts flash ────────────────────────────────────────── */
        .flash-zone {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: .5rem;
            min-width: 300px;
            max-width: 420px;
        }

        /* ── Responsive ──────────────────────────────────────────── */
        @media (max-width: 768px) {
            .wizard-sidebar { display: none; }
            .wizard-content { margin-left: 0; }
            .content-body   { padding: 1.25rem; }
            .content-header { padding: 1rem 1.25rem; }
            .wizard-nav     { padding: 1rem 1.25rem; }
        }
    </style>
</head>
<body>

<!-- ── Flash de alertas ──────────────────────────────────────────────────── -->
<div class="flash-zone" id="flashZone">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible d-flex align-items-center shadow-sm mb-0" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <span><?= esc(session()->getFlashdata('success')) ?></span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible d-flex align-items-center shadow-sm mb-0" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            <span><?= esc(session()->getFlashdata('error')) ?></span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('warning')): ?>
        <div class="alert alert-warning alert-dismissible d-flex align-items-center shadow-sm mb-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <span><?= esc(session()->getFlashdata('warning')) ?></span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
</div>

<div class="wizard-wrapper">

    <!-- ════════════════════════════════════════════════════════════
         SIDEBAR
    ════════════════════════════════════════════════════════════ -->
    <aside class="wizard-sidebar">

        <!-- Brand -->
        <div class="brand">
            <?php if (!empty($tenant['logo_path'])): ?>
                <img src="<?= base_url($tenant['logo_path']) ?>" alt="Logo">
            <?php else: ?>
                <div style="width:40px;height:40px;border-radius:8px;background:#1e293b;
                            display:flex;align-items:center;justify-content:center;color:#6366f1;font-size:1.1rem;">
                    <i class="bi bi-building"></i>
                </div>
            <?php endif; ?>
            <div>
                <div class="brand-name"><?= esc($tenant['name']) ?></div>
                <div class="brand-sub">Configuración inicial</div>
            </div>
        </div>

        <!-- Barra de progreso global -->
        <?php
        $totalRequired  = count(array_filter(array_column($steps, 'required')));
        $doneRequired   = count(array_filter(array_keys($steps), fn($s) => $steps[$s]['required'] && $isCompleted($s)));
        $progressPct    = $totalRequired > 0 ? round(($doneRequired / $totalRequired) * 100) : 0;
        ?>
        <div class="sidebar-progress">
            <div class="progress-label">
                <span>Progreso</span>
                <span><?= $progressPct ?>%</span>
            </div>
            <div class="progress">
                <div class="progress-bar" style="width: <?= $progressPct ?>%"></div>
            </div>
        </div>

        <!-- Lista de pasos -->
        <ul class="step-list">
            <?php foreach ($steps as $num => $step):
                $done    = $isCompleted($num);
                $current = $isCurrent($num);
                $locked  = !$done && !$current && $isLocked($num);
                $pending = !$done && !$current && !$locked;

                $stateClass = match(true) {
                    $done    => 'is-completed',
                    $current => 'is-current',
                    $locked  => 'is-locked',
                    default  => 'is-pending',
                };

                // Solo los completados tienen link clickeable
                $tag  = $done ? 'a' : 'span';
                $href = $done ? "href='/onboarding/step/{$num}'" : '';
                ?>
            <li class="step-item <?= $stateClass ?>">
                <<?= $tag ?> <?= $href ?>>
                <div class="step-bullet">
                    <?php if ($done): ?>
                        <i class="bi bi-check-lg"></i>
                    <?php elseif ($locked): ?>
                        <i class="bi bi-lock-fill" style="font-size:.7rem"></i>
                    <?php else: ?>
                        <?= $num ?>
                    <?php endif; ?>
                </div>
                <div class="step-meta">
                    <div class="step-title"><?= esc($step['title']) ?></div>
                    <div class="step-badge">
                        <?php if ($done): ?>
                            <span style="color:var(--wiz-success)">Completado</span>
                        <?php elseif ($current): ?>
                            En curso
                        <?php elseif ($locked): ?>
                            Bloqueado
                        <?php else: ?>
                            <?= $step['required'] ? 'Requerido' : 'Opcional' ?>
                        <?php endif; ?>
                    </div>
                </div>
                </<?= $tag ?>>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Footer -->
        <div class="sidebar-footer">
            <div class="mb-1">
                <i class="bi bi-shield-check me-1"></i>
                Tus datos están seguros
            </div>
            <div>
                <a href="/dashboard" style="color:var(--wiz-text-muted);text-decoration:none;">
                    <i class="bi bi-arrow-left me-1"></i>Volver al dashboard
                </a>
            </div>
        </div>

    </aside>

    <!-- ════════════════════════════════════════════════════════════
         CONTENIDO PRINCIPAL
    ════════════════════════════════════════════════════════════ -->
    <main class="wizard-content">

        <!-- Header sticky con info del paso actual -->
        <div class="content-header">
            <div class="step-info">
                <h4>
                    <i class="bi <?= esc($steps[$currentStep]['icon']) ?> me-2"
                       style="color:var(--wiz-accent)"></i>
                    <?= esc($steps[$currentStep]['title']) ?>
                </h4>
                <p>Paso <?= $currentStep ?> de <?= count($steps) ?>
                    <?= $steps[$currentStep]['required'] ? '' : '· <em>Opcional</em>' ?>
                </p>
            </div>

            <!-- Chips de estado en el header -->
            <div class="d-flex align-items-center gap-2">
                <?php if (in_array($currentStep, [3, 5])): ?>
                    <span class="ai-badge">
                        <i class="bi bi-stars"></i> Con IA
                    </span>
                <?php endif; ?>
                <span class="badge bg-light text-secondary border">
                    <?= count($completed) ?>/<?= count($steps) ?> completados
                </span>
            </div>
        </div>

        <!-- Cuerpo del paso (incluye la vista del paso actual) -->
        <div class="content-body">
            <?= view(
                "onboarding/steps/step_{$steps[$currentStep]['view']}",
                $stepData + ['tenant' => $tenant, 'currentStep' => $currentStep, 'settings' => $settings]
            ) ?>
        </div>

    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    /**
     * Wizard JS global
     * - Auto-dismiss de flash alerts
     * - Helper fetch para llamadas AI
     * - Función skip de pasos opcionales
     */

    // Auto-dismiss alerts después de 4s
    document.querySelectorAll('.flash-zone .alert').forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 4000);
    });

    /**
     * Llama al endpoint de IA del wizard.
     * @param {string} action   - 'generate_description' | 'generate_prompt' | 'generate_hero'
     * @param {object} payload  - datos adicionales para el prompt
     * @returns {Promise<object>}
     */
    async function wizardAI(action, payload = {}) {
        // Leer el token CSRF desde la cookie (CI4 con csrfProtection='cookie')
        const csrfToken = document.cookie
            .split('; ')
            .find(r => r.startsWith('csrf_cookie_name='))
            ?.split('=')[1] ?? '';

        const res = await fetch('/onboarding/ai/generate', {
            method      : 'POST',
            credentials : 'same-origin',
            headers     : {
                'Content-Type'     : 'application/json',
                'X-Requested-With' : 'XMLHttpRequest',
                'X-CSRF-TOKEN'     : csrfToken,
            },
            body: JSON.stringify({ action, ...payload })
        });

        return await res.json();
    }

    /**
     * Muestra/oculta el spinner de IA
     * @param {string} spinnerId  - ID del elemento .ai-loading
     * @param {boolean} visible
     */
    function setAiLoading(spinnerId, visible) {
        const el = document.getElementById(spinnerId);
        if (el) el.classList.toggle('visible', visible);
    }

    /**
     * Salta un paso opcional y avanza al siguiente
     * @param {number} currentStep
     */
    function skipStep(currentStep) {
        window.location.href = '/onboarding/step/' + (currentStep + 1);
    }

    /**
     * Muestra una notificación flash dinámica (sin recargar)
     * @param {string} type    - 'success' | 'danger' | 'warning'
     * @param {string} message
     */
    function showFlash(type, message) {
        const zone  = document.getElementById('flashZone');
        const icons = { success: 'check-circle-fill', danger: 'exclamation-circle-fill', warning: 'exclamation-triangle-fill' };
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible d-flex align-items-center shadow-sm mb-0`;
        alert.innerHTML = `
        <i class="bi bi-${icons[type] ?? 'info-circle'} me-2"></i>
        <span>${message}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>`;
        zone.appendChild(alert);
        setTimeout(() => bootstrap.Alert.getOrCreateInstance(alert)?.close(), 4000);
    }
</script>

</body>
</html>