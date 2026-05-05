<?php
/**
 * app/Views/partials/_sidebar.php
 *
 * Menú lateral retraíble del PMS.
 * Requiere: Bootstrap 5, Bootstrap Icons, sidebar.css
 */

use App\Config\Menu;

$menuConfig  = new Menu();
$menuItems   = $menuConfig->items;
$currentPath = '/' . ltrim(uri_string(), '/');   // ruta actual
$userRole    = session('user_role') ?? '';

/**
 * Determina si un ítem (o alguno de sus hijos) está activo.
 */
function menuIsActive(array $item, string $current): bool
{
    if (!empty($item['url'])) {
        return str_starts_with($current, $item['url']);
    }
    foreach ($item['children'] ?? [] as $child) {
        if (str_starts_with($current, $child['url'] ?? '')) {
            return true;
        }
    }
    return false;
}

/**
 * Comprueba si el rol del usuario tiene permiso para ver el ítem.
 */
function menuCanSee(array $item, string $role): bool
{
    return empty($item['roles']) || in_array($role, $item['roles'], true);
}
?>

<aside id="pms-sidebar" class="pms-sidebar">

    <!-- Cabecera: logo + toggle ─────────────────────── -->
    <div class="sidebar-header">
        <a href="<?= base_url('/dashboard') ?>" class="sidebar-brand">
            <i class="bi bi-building-fill-check sidebar-brand-icon"></i>
            <span class="sidebar-brand-text"><?= esc(session('tenant_name')) ?></span>
        </a>
        <button id="sidebar-toggle" class="sidebar-toggle-btn" title="Colapsar menú">
            <i class="bi bi-layout-sidebar-reverse"></i>
        </button>
    </div>

    <!-- Búsqueda rápida ─────────────────────────────── -->
    <div class="sidebar-search sidebar-label-show">
        <i class="bi bi-search"></i>
        <input type="text" id="sidebar-search-input" placeholder="Buscar…" autocomplete="off">
    </div>

    <!-- Navegación ──────────────────────────────────── -->
    <nav class="sidebar-nav" id="sidebar-nav">
        <ul class="sidebar-menu">
            <?php foreach ($menuItems as $i => $item):
                if (!menuCanSee($item, $userRole)) continue;
                $isActive  = menuIsActive($item, $currentPath);
                $hasChildren = !empty($item['children']);
                $collapseId  = 'smenu-' . $i;
                ?>

                <?php if (!empty($item['divider'])): ?>
                <li class="sidebar-divider"></li>
            <?php endif; ?>

                <?php if ($hasChildren): ?>
                <!-- Ítem con submenú -->
                <li class="sidebar-item sidebar-item-group <?= $isActive ? 'open' : '' ?>">
                    <button class="sidebar-link sidebar-link-toggle <?= $isActive ? 'active' : '' ?>"
                            data-bs-toggle="collapse"
                            data-bs-target="#<?= $collapseId ?>"
                            aria-expanded="<?= $isActive ? 'true' : 'false' ?>">
                        <i class="bi <?= esc($item['icon']) ?> sidebar-icon"></i>
                        <span class="sidebar-label"><?= esc($item['label']) ?></span>
                        <i class="bi bi-chevron-down sidebar-chevron ms-auto"></i>
                    </button>

                    <div id="<?= $collapseId ?>"
                         class="collapse <?= $isActive ? 'show' : '' ?>">
                        <ul class="sidebar-submenu">
                            <?php foreach ($item['children'] as $child):
                                if (!menuCanSee($child, $userRole)) continue;
                                $childActive = str_starts_with($currentPath, $child['url'] ?? '');
                                ?>
                                <li>
                                    <a href="<?= base_url($child['url']) ?>"
                                       class="sidebar-sublink <?= $childActive ? 'active' : '' ?>">
                                        <i class="bi <?= esc($child['icon']) ?>"></i>
                                        <span class="sidebar-label"><?= esc($child['label']) ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </li>

            <?php else: ?>
                <!-- Ítem simple -->
                <li class="sidebar-item <?= $isActive ? 'active' : '' ?>">
                    <a href="<?= base_url($item['url']) ?>"
                       class="sidebar-link <?= $isActive ? 'active' : '' ?>">
                        <i class="bi <?= esc($item['icon']) ?> sidebar-icon"></i>
                        <span class="sidebar-label"><?= esc($item['label']) ?></span>
                        <?php if (!empty($item['badge'])): ?>
                            <span class="sidebar-badge sidebar-label"><?= esc($item['badge']) ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endif; ?>

            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Footer del sidebar ───────────────────────────── -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar"><?= strtoupper(substr(session('user_name') ?? 'U', 0, 1)) ?></div>
            <div class="sidebar-user-info sidebar-label">
                <span class="sidebar-user-name"><?= esc(session('user_name')) ?></span>
                <span class="sidebar-user-role"><?= esc($userRole) ?></span>
            </div>
        </div>
        <a href="<?= base_url('/logout') ?>" class="sidebar-logout" title="Cerrar sesión">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>

</aside>

<!-- Overlay para móvil -->
<div id="sidebar-overlay" class="sidebar-overlay"></div>