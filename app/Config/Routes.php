<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// ====================================================================
// RUTAS DEL MÓDULO SUPERADMIN
// ====================================================================

// Rutas públicas (Login)
$routes->get('/super/login', 'Super\AuthController::login');
$routes->post('/super/login', 'Super\AuthController::authenticate');
$routes->get('/super/logout', 'Super\AuthController::logout');

// Rutas protegidas (Requieren sesión de SuperAdmin)
$routes->group('super', ['filter' => 'superadmin_auth'], static function ($routes) {
    // Dashboard principal
    $routes->get('dashboard', 'Super\DashboardController::index');
    $routes->get('tenants', 'Super\TenantController::index');
    $routes->get('tenants/create', 'Super\TenantController::create');
    $routes->post('tenants/store', 'Super\TenantController::store');

    $routes->get('billing', 'Super\BillingController::index');
    $routes->post('billing/renew/(:num)', 'Super\BillingController::renew/$1');




});

// ====================================================================
// RUTAS DEL PMS OPERATIVO (Hoteles, Cabañas, etc.)
// ====================================================================

// Rutas públicas (Login del personal)
$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::authenticate');
$routes->get('/logout', 'AuthController::logout');

// Rutas protegidas (Requieren sesión de empleado y tenant activo)
$routes->group('/', ['filter' => 'tenant_auth'], static function ($routes) {
    $routes->get('dashboard', 'DashboardController::index');
    //Rutas de Inventario
    $routes->get('inventory', 'InventoryController::index');
    $routes->get('inventory/create', 'InventoryController::create');
    $routes->post('inventory/store', 'InventoryController::store');
    // ENDPOINTS JSON PARA FULLCALENDAR
    $routes->get('api/resources', 'ReservationController::getResources');
    $routes->get('api/events', 'ReservationController::getEvents');

    $routes->get('reservations', 'ReservationController::index');
    $routes->get('reservations/create', 'ReservationController::create');
    $routes->post('reservations/store', 'ReservationController::store');
    $routes->post('reservations/update-status/(:num)', 'ReservationController::updateStatus/$1');

    // AGREGA ESTAS LÍNEAS: Folio y Pagos
    $routes->get('reservations/show/(:num)', 'ReservationController::show/$1');
    $routes->post('reservations/add-payment/(:num)', 'ReservationController::addPayment/$1');
//Configuración del Hotel
        $routes->get('settings', 'SettingsController::index');
        $routes->post('settings/update', 'SettingsController::update');
});

//Rutas de Reservas
        $routes->get('reservations', 'ReservationController::index');
        $routes->get('reservations/create', 'ReservationController::create');
        $routes->post('reservations/store', 'ReservationController::store');
        // Ruta para cambiar el estado de la reserva (FSM)
        $routes->post('reservations/update-status/(:num)', 'ReservationController::updateStatus/$1');

