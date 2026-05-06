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
 *   badge    → (opcional) texto del badge, p.ej. 'Nuevo'
 *   children → (opcional) array de sub-ítems (un nivel de profundidad)
 *   divider  → si true, renderiza un separador ANTES del ítem
 */
class Menu extends BaseConfig
{
    public array $items = [

        // ── Operaciones principales ───────────────────────────
        [
            'label' => 'Recepción',
            'url'   => '/dashboard',
            'icon'  => 'bi-house-door',
            'roles' => [],
        ],
        [
            'label' => 'Reservas',
            'url'   => '/reservations',
            'icon'  => 'bi-calendar-check',
            'roles' => [],
        ],
        [
            'label' => 'Inventario',
            'url'   => '/inventory',
            'icon'  => 'bi-building',
            'roles' => [],
        ],
        [
            'label' => 'Mantenimiento',
            'url'   => '/maintenance',
            'icon'  => 'bi-tools',
            'roles' => [],
        ],

        // ── Comercial ─────────────────────────────────────────
        [
            'label'   => 'Tarifas',
            'icon'    => 'bi-currency-dollar',
            'roles'   => [],
            'divider' => true,
            'children' => [
                ['label' => 'Planes Tarifarios', 'url' => '/rate-plans',        'icon' => 'bi-tags'],
                ['label' => 'Matriz de Precios',  'url' => '/rate-plans/matrix', 'icon' => 'bi-grid-3x3'],
                ['label' => 'Temporadas Altas',   'url' => '/seasonal-rates',    'icon' => 'bi-calendar-event'],
            ],
        ],
        [
            'label' => 'Promociones',
            'url'   => '/promotions',
            'icon'  => 'bi-percent',
            'roles' => [],
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

        // ── Punto de Venta & Compras ──────────────────────────
        [
            'label'   => 'POS & Compras',
            'icon'    => 'bi-cart3',
            'roles'   => [],
            'divider' => true,
            'children' => [
                ['label' => 'Catálogo POS',  'url' => '/products',  'icon' => 'bi-box-seam'],
                ['label' => 'Proveedores',   'url' => '/suppliers', 'icon' => 'bi-truck'],
                ['label' => 'Compras',       'url' => '/purchases', 'icon' => 'bi-receipt'],
            ],
        ],

        // ── CRM & Comunicaciones ──────────────────────────────
        [
            'label'   => 'CRM & Canales',
            'icon'    => 'bi-people',
            'roles'   => [],
            'divider' => true,
            'children' => [
                ['label' => 'CRM Huéspedes',     'url' => '/crm',                  'icon' => 'bi-person-lines-fill'],
                ['label' => 'WhatsApp Simulator', 'url' => '/whatsapp/simulator',   'icon' => 'bi-robot'],
                ['label' => 'Live Chat',          'url' => '/whatsapp/chat',        'icon' => 'bi-chat-dots'],
                ['label' => 'Tunel',          'url' => '/crm/pipeline',        'icon' => 'bi-chat-dots'],
            ],
        ],

        // ── Tours & Guías ─────────────────────────────────────
        [
            'label'   => 'Tours & Guías',
            'icon'    => 'bi-compass',
            'roles'   => [],
            'divider' => true,
            'children' => [
                ['label' => 'Tours y Actividades', 'url' => '/tours',  'icon' => 'bi-map'],
                ['label' => 'Guías',               'url' => '/guides', 'icon' => 'bi-person-badge'],
            ],
        ],

        // ── Análisis & Web ────────────────────────────────────
        [
            'label' => 'Reportes',
            'url'   => '/reports',
            'icon'  => 'bi-bar-chart-line',
            'roles' => [],
            'divider' => true,
        ],
        [
            'label' => 'Mi Sitio Web',
            'url'   => '/website',
            'icon'  => 'bi-globe',
            'roles' => [],
            'badge' => 'Web',
        ],

        // ── Admin ─────────────────────────────────────────────
        [
            'label' => 'Usuarios',
            'url'   => '/users',
            'icon'  => 'bi-people-fill',
            'roles' => ['admin'],
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