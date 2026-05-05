<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMS - <?= session('tenant_name') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/modern-pastel.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/sidebar.css') ?>">
</head>
<body class="pms-body">

<?= $this->include('partials/_sidebar') ?>

<!-- Botón hamburguesa (solo móvil) -->
<button id="sidebar-mobile-toggle" class="sidebar-mobile-btn d-lg-none">
    <i class="bi bi-list"></i>
</button>

<!-- Contenido principal -->
<main id="pms-main" class="pms-main">

    <!-- Topbar mínima -->
    <div class="pms-topbar">
        <nav aria-label="breadcrumb" class="pms-breadcrumb">
            <span class="pms-topbar-title"><?= esc(session('tenant_name')) ?></span>
        </nav>
        <div class="pms-topbar-right">
            <a href="<?= base_url('/reservations/create') ?>" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg"></i> Nueva reserva
            </a>
        </div>
    </div>

    <!-- Flash messages -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mx-4 mt-3" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mx-4 mt-3" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Zona de contenido de cada vista -->
    <div class="pms-content">
        <?= $this->renderSection('content') ?>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        const sidebar   = document.getElementById('pms-sidebar');
        const main      = document.getElementById('pms-main');
        const overlay   = document.getElementById('sidebar-overlay');
        const toggleBtn = document.getElementById('sidebar-toggle');
        const mobileBtn = document.getElementById('sidebar-mobile-toggle');
        const searchInput = document.getElementById('sidebar-search-input');

        const COLLAPSED_KEY = 'pms_sidebar_collapsed';

        // ── Estado inicial (recordado en localStorage) ──────────
        if (localStorage.getItem(COLLAPSED_KEY) === '1') {
            sidebar.classList.add('collapsed');
            main.classList.add('sidebar-collapsed');
        }

        // ── Toggle desktop ───────────────────────────────────────
        toggleBtn?.addEventListener('click', () => {
            const isNowCollapsed = sidebar.classList.toggle('collapsed');
            main.classList.toggle('sidebar-collapsed', isNowCollapsed);
            localStorage.setItem(COLLAPSED_KEY, isNowCollapsed ? '1' : '0');
        });

        // ── Toggle móvil ─────────────────────────────────────────
        mobileBtn?.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        });

        overlay?.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        });

        // ── Búsqueda rápida en el menú ───────────────────────────
        searchInput?.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('#sidebar-nav .sidebar-item, #sidebar-nav .sidebar-item-group');

            items.forEach(li => {
                if (!q) {
                    li.style.display = '';
                    return;
                }
                const text = li.textContent.toLowerCase();
                li.style.display = text.includes(q) ? '' : 'none';
            });

            // Si hay búsqueda activa, expandir todos los grupos para mostrar hijos
            if (q) {
                document.querySelectorAll('.sidebar-item-group .collapse').forEach(c => {
                    c.classList.add('show');
                });
            }
        });

        // ── Cerrar submenús al colapsar sidebar ──────────────────
        // (en modo collapsed, los submenús aparecen como tooltip/flyout)
        sidebar?.addEventListener('transitionend', () => {
            if (sidebar.classList.contains('collapsed')) {
                document.querySelectorAll('.sidebar-item-group .collapse.show').forEach(c => {
                    // No cerramos — Bootstrap los maneja; solo evitamos overflow
                });
            }
        });
    })();
</script>
</body>
</html>