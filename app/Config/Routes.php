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

    $routes->get('reservations/calculate-price', 'ReservationController::calculatePrice');

    $routes->get('products', 'ProductController::index');
    $routes->post('products/store-category', 'ProductController::storeCategory');
    $routes->post('products/store-product', 'ProductController::storeProduct');

    //Motor de Tarifas
    $routes->get('rate-plans', 'RatePlanController::index');
    $routes->post('rate-plans/store', 'RatePlanController::store');
    $routes->get('rate-plans/matrix', 'RatePlanController::matrix');
    $routes->post('rate-plans/update-matrix', 'RatePlanController::updateMatrix');
    $routes->get('seasonal-rates', 'SeasonalRateController::index');
    $routes->post('seasonal-rates/store', 'SeasonalRateController::store');
    $routes->get('seasonal-rates/delete/(:num)', 'SeasonalRateController::delete/$1');

    // Ruta de prueba para ver la magia de la calculadora en acción
    $routes->get('seasonal-rates/test-calculator', 'SeasonalRateController::testCalculator');

    //  ESTAS LÍNEAS: Punto de Venta (Consumos)
    $routes->post('reservations/add-consumption/(:num)', 'ReservationController::addConsumption/$1');
    $routes->get('reservations/delete-consumption/(:num)/(:num)', 'ReservationController::deleteConsumption/$1/$2');

    $routes->post('reservations/add-companion/(:num)', 'ReservationController::addCompanion/$1');
    $routes->get('reservations/delete-companion/(:num)/(:num)', 'ReservationController::deleteCompanion/$1/$2');

    $routes->get('reservations/closure/(:num)', 'ReservationController::closure/$1');
    $routes->post('reservations/process-checkout/(:num)', 'ReservationController::processCheckout/$1');

    // AGREGA ESTAS LÍNEAS: Gestión de Empleados
    $routes->get('users', 'UserController::index');
    $routes->post('users/store', 'UserController::store');
    $routes->get('users/delete/(:num)', 'UserController::delete/$1');

    $routes->get('maintenance', 'MaintenanceController::index');
    $routes->post('maintenance/store', 'MaintenanceController::store');
    $routes->post('maintenance/update-status/(:num)', 'MaintenanceController::updateStatus/$1');
    $routes->get('maintenance/delete/(:num)', 'MaintenanceController::delete/$1');

    //Proveedores
        $routes->get('suppliers', 'SupplierController::index');
        $routes->post('suppliers/store', 'SupplierController::store');

    //Módulo de Compras
    $routes->get('purchases', 'PurchaseController::index');
    $routes->post('purchases/store', 'PurchaseController::store');
    $routes->get('purchases/show/(:num)', 'PurchaseController::show/$1');
    $routes->post('purchases/add-item/(:num)', 'PurchaseController::addItem/$1');
    $routes->get('purchases/delete-item/(:num)/(:num)', 'PurchaseController::deleteItem/$1/$2');
    $routes->post('purchases/add-payment/(:num)', 'PurchaseController::addPayment/$1');

    //Configuración del Hotel
        $routes->get('settings', 'SettingsController::index');
        $routes->post('settings/update', 'SettingsController::update');
        //Centro de Reportes
            $routes->get('reports', 'ReportController::index');
            $routes->post('reports/export', 'ReportController::export');

    //Promociones
        $routes->get('promotions', 'PromotionController::index');
        $routes->post('promotions/store', 'PromotionController::store');
        $routes->get('promotions/delete/(:num)', 'PromotionController::delete/$1');

    $routes->get('reservations/invoice/(:num)', 'ReservationController::invoice/$1');

    // AGREGA ESTAS LÍNEAS: Constructor del Sitio Web y Galería
    $routes->get('website', 'WebsiteController::index');
    $routes->post('website/update', 'WebsiteController::update');
    $routes->post('website/upload-media', 'WebsiteController::uploadMedia');
    $routes->get('website/delete-media/(:num)', 'WebsiteController::deleteMedia/$1');


    //Rutas de Reservas
    $routes->get('reservations', 'ReservationController::index');
    $routes->get('reservations/create', 'ReservationController::create');
    $routes->post('reservations/store', 'ReservationController::store');
// Ruta para cambiar el estado de la reserva (FSM)
    $routes->post('reservations/update-status/(:num)', 'ReservationController::updateStatus/$1');

    $routes->get('inventory/edit-unit/(:num)', 'InventoryController::editUnit/$1');
    $routes->post('inventory/update-unit/(:num)', 'InventoryController::updateUnit/$1');
    $routes->post('inventory/upload-unit-media', 'InventoryController::uploadUnitMedia');
    $routes->get('inventory/delete-unit-media/(:num)', 'InventoryController::deleteUnitMedia/$1');

    $routes->get('inventory/unit/edit/(:num)', 'InventoryController::editUnit/$1');
    $routes->post('inventory/unit/update/(:num)', 'InventoryController::updateUnit/$1');
    $routes->get('inventory/unit/media/delete/(:num)', 'InventoryController::deleteUnitMedia/$1');

    // RUTAS PARA COMISIONISTAS Y AGENCIAS
    $routes->get('agents', 'AgentController::index');
    $routes->post('agents/store', 'AgentController::store');
    $routes->get('agents/delete/(:num)', 'AgentController::delete/$1');

    // LIQUIDACIÓN DE COMISIONES
    $routes->get('commissions', 'CommissionController::index');
    $routes->get('commissions/pay/(:num)', 'CommissionController::pay/$1');

});

$routes->match(['get', 'post'], 'whatsapp/webhook', 'Whatsapp::webhook');

// ====================================================================
// MOTOR DE RESERVAS DIRECTAS (PÁGINA WEB PÚBLICA DEL HOTEL)
// ====================================================================
// La URL será: misaas.com/book/casa-lucerito
$routes->get('book/(:segment)', 'PublicWebsiteController::index/$1');
$routes->post('book/(:segment)/confirm', 'PublicWebsiteController::confirm/$1');
$routes->get('book/(:segment)/success', 'PublicWebsiteController::success/$1');

$routes->get('admin/logs', 'Admin\LogViewer::index');






