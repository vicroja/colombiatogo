<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configuración del menú lateral del PMS.
 *
 * Estructura de cada ítem:
 *   label    → texto visible
 *   url      → ruta relativa (base_url se aplica en la vista)
 *   icon     → clase Bootstrap Icons (bi-*)
 *   roles    → array de roles permitidos; vacío = todos
 *   features → array de features del tenant requeridas (cualquiera basta).
 *              Valores válidos: 'tours', 'accommodation'.
 *              Vacío o ausente = visible para todos los tenants.
 *   badge    → (opcional) texto del badge, p.ej. 'Nuevo'
 *   children → (opcional) array de sub-ítems (un nivel de profundidad)
 *   divider  → si true, renderiza un separador ANTES del ítem
 */
class Menu extends BaseConfig
{
    public array $items = [

        // ── Dashboard (siempre visible) ───────────────────────
        [
            'label' => 'Inicio',
            'url'   => '/dashboard',
            'icon'  => 'bi-house-door',
            'roles' => [],
        ],

        // ── ALOJAMIENTO ───────────────────────────────────────
        [
            'label'    => 'Reservas',
            'url'      => '/reservations',
            'icon'     => 'bi-calendar-check',
            'roles'    => [],
            'features' => ['accommodation'],
        ],
        [
            'label'    => 'Inventario',
            'url'      => '/inventory',
            'icon'     => 'bi-building',
            'roles'    => [],
            'features' => ['accommodation'],
        ],
        [
            'label'    => 'Mantenimiento',
            'url'      => '/maintenance',
            'icon'     => 'bi-tools',
            'roles'    => [],
            'features' => ['accommodation'],
        ],

        // ── TOURS & GUÍAS ─────────────────────────────────────
        [
            'label'    => 'Tours & Guías',
            'icon'     => 'bi-compass',
            'roles'    => [],
            'features' => ['tours'],
            'divider'  => true,
            'children' => [
                ['label' => 'Tours y Actividades', 'url' => '/tours',                  'icon' => 'bi-map'],
                ['label' => 'Salidas Programadas', 'url' => '/tours/schedules',        'icon' => 'bi-calendar3'],
                ['label' => 'Reservas de Tours',   'url' => '/tours/reservations',     'icon' => 'bi-bookmark-check'],
                ['label' => 'Guías',               'url' => '/guides',                 'icon' => 'bi-person-badge'],
                ['label' => 'Pagos a Guías',       'url' => '/guides/payments/pending','icon' => 'bi-cash-stack'],
            ],
        ],

        // ── COMERCIAL (mixto: aplica a ambos) ──────────────────
        [
            'label'    => 'Tarifas',
            'icon'     => 'bi-currency-dollar',
            'roles'    => [],
            'features' => ['accommodation'], // por ahora solo aplica a alojamiento
            'divider'  => true,
            'children' => [
                ['label' => 'Planes Tarifarios', 'url' => '/rate-plans',        'icon' => 'bi-tags'],
                ['label' => 'Matriz de Precios', 'url' => '/rate-plans/matrix', 'icon' => 'bi-grid-3x3'],
                ['label' => 'Temporadas Altas',  'url' => '/seasonal-rates',    'icon' => 'bi-calendar-event'],
            ],
        ],
        [
            'label' => 'Promociones',
            'url'   => '/promotions',
            'icon'  => 'bi-percent',
            'roles' => [],
            // sin features → visible siempre (sirve para tours y hotel)
        ],
        [
            'label' => 'Comisionistas',
            'url'   => '/agents',
            'icon'  => 'bi-briefcase',
            'roles' => [],
        ],
        [
            'label' => 'Liquidar Comisiones',
            'url'   => '/commissions',
            'icon'  => 'bi-cash-coin',
            'roles' => [],
        ],

        // ── POS & COMPRAS (típicamente del hotel/restaurante) ──
        [
            'label'    => 'POS & Compras',
            'icon'     => 'bi-cart3',
            'roles'    => [],
            'features' => ['accommodation'],
            'divider'  => true,
            'children' => [
                ['label' => 'Catálogo POS', 'url' => '/products',  'icon' => 'bi-box-seam'],
                ['label' => 'Proveedores',  'url' => '/suppliers', 'icon' => 'bi-truck'],
                ['label' => 'Compras',      'url' => '/purchases', 'icon' => 'bi-receipt'],
            ],
        ],

        // ── CRM & COMUNICACIONES (siempre visible) ─────────────
        [
            'label'   => 'CRM & Canales',
            'icon'    => 'bi-people',
            'roles'   => [],
            'divider' => true,
            'children' => [
                ['label' => 'CRM Huéspedes',      'url' => '/crm',                'icon' => 'bi-person-lines-fill'],
                ['label' => 'WhatsApp Simulator', 'url' => '/whatsapp/simulator', 'icon' => 'bi-robot'],
                ['label' => 'Live Chat',          'url' => '/whatsapp/chat',      'icon' => 'bi-chat-dots'],
                ['label' => 'Embudo',             'url' => '/crm/pipeline',       'icon' => 'bi-funnel'],
            ],
        ],

        // ── ANÁLISIS & WEB ────────────────────────────────────
        [
            'label'   => 'Reportes',
            'url'     => '/reports',
            'icon'    => 'bi-bar-chart-line',
            'roles'   => [],
            'divider' => true,
        ],
        [
            'label' => 'Mi Sitio Web',
            'url'   => '/website',
            'icon'  => 'bi-globe',
            'roles' => [],
            'badge' => 'Web',
        ],

        // ── ADMIN ─────────────────────────────────────────────
        [
            'label'   => 'Usuarios',
            'url'     => '/users',
            'icon'    => 'bi-people-fill',
            'roles'   => ['admin'],
            'divider' => true,
        ],
        [
            'label' => 'Configuración',
            'url'   => '/settings',
            'icon'  => 'bi-gear',
            'roles' => ['admin'],
        ],
    ];
}