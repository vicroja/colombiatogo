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

$routes->get('/calculadora',          'Calculadora::index');
$routes->post('/calculadora/analizar', 'Calculadora::analizar');

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

    $routes->get('crm/pipeline', 'CrmController::pipeline');

    $routes->get('inventory/wizard/step/(:num)',  'InventoryController::wizardStep/$1');
    $routes->post('inventory/wizard/save/(:num)', 'InventoryController::wizardSave/$1');
    $routes->get('inventory/wizard/skip/(:num)',  'InventoryController::wizardSkip/$1');


    $routes->get ('reservations/quote',            'QuoteController::index');
    $routes->get ('reservations/quote/search',     'QuoteController::search');
    $routes->post('reservations/quote/ai-suggest', 'QuoteController::aiSuggest');



    // Simulador de asistente IA
    $routes->get('whatsapp/simulator',          'SimulatorController::index');
    $routes->post('whatsapp/simulator/save',    'SimulatorController::savePrompt');
    $routes->post('whatsapp/simulator/turn',    'SimulatorController::simulateTurn');


    $routes->post('whatsapp/simulator/clear',   'SimulatorController::clear');

    // Módulo de Live Chat (WhatsApp Manual y Handoff)
    $routes->get('whatsapp/chat',                         'ChatController::index');
    $routes->get('whatsapp/chat/(:segment)',              'ChatController::index/$1');
    $routes->post('whatsapp/chat/return_ai',              'ChatController::returnToAiAjax');
    $routes->post('whatsapp/chat/close_chat',             'ChatController::closeChatAjax');
    $routes->post('whatsapp/chat/get_new_messages',       'ChatController::getNewMessagesAjax');
    $routes->post('whatsapp/ajax_search_sidebar_contacts','ChatController::ajaxSearchSidebarContacts');
    $routes->post('whatsapp/send_custom_message',         'ChatController::sendCustomMessage');


    $routes->get('crm',                          'CrmController::index');
    $routes->get('crm/guest/(:num)',             'CrmController::show/$1');
    $routes->post('crm/guest/(:num)/note',       'CrmController::addNote/$1');
    $routes->post('crm/guest/(:num)/message',    'CrmController::sendMessage/$1');
    $routes->post('crm/ai/message',              'CrmController::aiMessage');

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
    $routes->post('whatsapp/save_config', 'Whatsapp::saveConfig');

    $routes->post('settings/documents/upload',       'SettingsController::uploadDocument');
    $routes->post('settings/documents/delete/(:num)', 'SettingsController::deleteDocument/$1');



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
    $routes->post('maintenance/delete/(:num)', 'MaintenanceController::delete/$1');

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
    $routes->post('reservations/calculate-price', 'ReservationController::calculatePrice');

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

    // Sitio web — agregar junto a las rutas existentes de website
    $routes->post('website/set-main-photo/(:num)', 'WebsiteController::setMainPhoto/$1');
    $routes->post('website/reorder-photos',        'WebsiteController::reorderPhotos');
    $routes->post('website/ai-generate',           'WebsiteController::aiGenerate');
    $routes->get('website/preview',                'WebsiteController::preview');


});

$routes->group('tours', ['filter' => 'tenant_auth'], function ($routes) {

    // --- CRUD de Tours ---
    $routes->get('/',                        'TourController::index');
    $routes->get('create',                   'TourController::create');
    $routes->post('store',                   'TourController::store');
    $routes->get('(:num)/edit',              'TourController::edit/$1');
    $routes->post('(:num)/update',           'TourController::update/$1');

    // --- Schedules (Salidas) ---
    $routes->get('(:num)/schedules',         'TourController::schedules/$1');
    $routes->post('(:num)/schedules/store',  'TourController::storeSchedule/$1');

    // --- Reservas de Tours ---
    $routes->get('(:num)/reserve',           'TourController::createReservation/$1');
    $routes->post('reservation/store',       'TourController::storeReservation');
    $routes->get('reservation/(:num)',       'TourController::showReservation/$1');
    $routes->post('reservation/(:num)/status',   'TourController::updateReservationStatus/$1');
    $routes->post('reservation/(:num)/payment',  'TourController::addPayment/$1');

    // --- Manifiesto ---
    $routes->get('manifest/(:num)',          'TourController::manifest/$1');

    $routes->post('schedule/(:num)/status', 'TourController::updateScheduleStatus/$1');

    $routes->post('guest/quick-create', 'TourController::quickCreateGuest');

    // --- Media de Tours (fotos y videos) ---
    $routes->post('(:num)/media/upload',                    'TourController::uploadMedia/$1');
    $routes->post('(:num)/media/(:segment)/description',    'TourController::updateMediaDescription/$1/$2');
    $routes->post('(:num)/media/(:segment)/delete',         'TourController::deleteMedia/$1/$2');
    $routes->post('(:num)/media/reorder',                   'TourController::reorderMedia/$1');

});

$routes->group('guides', ['filter' => 'tenant_auth'], function ($routes) {

    // CRUD
    $routes->get('/',                          'GuideController::index');
    $routes->get('create',                     'GuideController::create');
    $routes->post('store',                     'GuideController::store');
    $routes->get('(:num)/edit',                'GuideController::edit/$1');
    $routes->post('(:num)/update',             'GuideController::update/$1');
    $routes->post('(:num)/toggle-status',      'GuideController::toggleStatus/$1');

    // Historial y pagos por guía
    $routes->get('(:num)/history',             'GuideController::history/$1');
    $routes->get('(:num)/payments',            'GuideController::payments/$1');

    // Vista global de pagos pendientes
    $routes->get('payments',                   'GuideController::allPendingPayments');

    // Acciones sobre pagos
    $routes->post('payment/(:num)/mark-paid',  'GuideController::markPaid/$1');
    $routes->post('payment/(:num)/edit',       'GuideController::editPayment/$1');
});

// Por esto (Sintaxis limpia y moderna):
$routes->get('whatsapp/webhook', 'Whatsapp::webhook');
$routes->post('whatsapp/webhook', 'Whatsapp::webhook');

// ====================================================================
// MOTOR DE RESERVAS DIRECTAS (PÁGINA WEB PÚBLICA DEL HOTEL)
// ====================================================================
// La URL será: misaas.com/book/casa-lucerito
$routes->get('book/(:segment)', 'PublicWebsiteController::index/$1');
$routes->post('book/(:segment)/confirm', 'PublicWebsiteController::confirm/$1');
$routes->get('book/(:segment)/success', 'PublicWebsiteController::success/$1');

$routes->get('admin/logs', 'Admin\LogViewer::index');

// ====================================================================
// RUTAS DE TERMINAL (CLI) PARA LOS WORKERS Y CRONJOBS
// ====================================================================
$routes->cli('worker/start', 'Worker::start');
$routes->cli('worker/watchdog', 'Worker::watchdog');
$routes->cli('worker/processOutgoingQueue', 'Worker::processOutgoingQueue');
$routes->cli('worker/processFollowUps', 'Worker::processFollowUps');

// Por si alguna vez usas el Cli.php (Plan B)
$routes->cli('cli/processIncomingQueue', 'Cli::processIncomingQueue');

$routes->post('webhooks/matias', 'MatiasWebhookController::handle');

// ── Onboarding Wizard ─────────────────────────────────────────
$routes->group('onboarding', ['filter' => 'tenant_auth', 'namespace' => 'App\Controllers\Onboarding'], function($routes) {
    $routes->get('/',                'WizardController::index');
    $routes->get('step/(:num)',      'WizardController::step/$1');
    $routes->post('step/(:num)',     'WizardController::saveStep/$1');
    $routes->post('ai/generate',     'WizardController::aiGenerate');      // Gemini calls
    $routes->post('whatsapp/connect','WizardController::whatsappConnect'); // Meta signup
    $routes->get('complete',         'WizardController::complete');



});

$routes->group('onboarding/import', ['filter' => 'tenant_auth'], function($routes) {
    $routes->get('/',                    'Onboarding\ImportController::form');
    $routes->post('extract',             'Onboarding\ImportController::extract');
    $routes->get('review/(:num)',        'Onboarding\ImportController::review/$1');
    $routes->post('confirm/(:num)',      'Onboarding\ImportController::confirm/$1');
});

// Rutas públicas de registro
$routes->get('/register',  'AuthController::register');
$routes->post('/register', 'AuthController::processRegister');

// Login de vendedores (sin filtro)
$routes->get ('sales/login',        'Sales\AuthController::login');
$routes->post('sales/login',        'Sales\AuthController::authenticate');
$routes->get ('sales/logout',       'Sales\AuthController::logout');

// Webhook público de captura inbound
$routes->post('api/leads/intake',   'Api\LeadsWebhookController::intake');

// Área de vendedores (protegida con SalesAuthFilter)
$routes->group('sales', ['filter' => 'salesauth'], function ($routes) {
    $routes->get ('dashboard',          'Sales\DashboardController::index');

    // Pipeline / Kanban
    $routes->get ('leads',              'Sales\LeadsController::kanban');
    $routes->get ('leads/kanban',       'Sales\LeadsController::kanban');
    $routes->get ('leads/create',       'Sales\LeadsController::create');
    $routes->post('leads/store',        'Sales\LeadsController::store');
    $routes->get ('leads/detail/(:num)','Sales\LeadsController::detail/$1');

    // AJAX
    $routes->post('leads/move',         'Sales\LeadsController::move');
    $routes->post('leads/markLost',     'Sales\LeadsController::markLost');
    $routes->post('leads/markWon',      'Sales\LeadsController::markWon');
    $routes->post('leads/addActivity',  'Sales\LeadsController::addActivity');
    $routes->post('leads/setNextAction','Sales\LeadsController::setNextAction');

    $routes->get('commissions', 'Sales\CommissionsController::index');

});

// Área superadmin (protegida con tu filtro existente de super_admin)
$routes->group('super', ['filter' => 'superadmin_auth'], function ($routes) {
    // Vendedores
    $routes->get ('sales-users',                'Super\Leads\SalesUsersController::index');
    $routes->get ('sales-users/create',         'Super\Leads\SalesUsersController::create');
    $routes->post('sales-users/store',          'Super\Leads\SalesUsersController::store');
    $routes->get ('sales-users/toggle/(:num)',  'Super\Leads\SalesUsersController::toggle/$1');

    // Reportes
    $routes->get ('leads/reports',              'Super\Leads\ReportsController::index');

    $routes->get ('sales-users/edit/(:num)',     'Super\Leads\SalesUsersController::edit/$1');
    $routes->post('sales-users/update/(:num)',   'Super\Leads\SalesUsersController::update/$1');

    // Comisiones (NUEVO)
    $routes->get ('leads/commissions',               'Super\Leads\CommissionsController::index');
    $routes->get ('leads/commissions/detail',        'Super\Leads\CommissionsController::detail');
    $routes->get ('leads/commissions/approve/(:num)','Super\Leads\CommissionsController::approve/$1');
    $routes->post('leads/commissions/approve-all',   'Super\Leads\CommissionsController::approveAll');
    $routes->post('leads/commissions/pay/(:num)',    'Super\Leads\CommissionsController::pay/$1');
    $routes->post('leads/commissions/cancel/(:num)', 'Super\Leads\CommissionsController::cancel/$1');

    $routes->get('plans',              'Super\PlanController::index');
    $routes->get('plans/create',       'Super\PlanController::create');
    $routes->post('plans/store',       'Super\PlanController::store');
    $routes->get('plans/edit/(:num)',  'Super\PlanController::edit/$1');
    $routes->post('plans/update/(:num)', 'Super\PlanController::update/$1');
    $routes->post('plans/toggle/(:num)', 'Super\PlanController::toggle/$1');

    // Tenants (edición y acciones)
    $routes->get('tenants/edit/(:num)',           'Super\TenantController::edit/$1');
    $routes->post('tenants/update/(:num)',        'Super\TenantController::update/$1');
    $routes->post('tenants/change-plan/(:num)',   'Super\TenantController::changePlan/$1');
    $routes->post('tenants/toggle-suspend/(:num)','Super\TenantController::toggleSuspend/$1');

// Billing - historial
    $routes->get('billing/history/(:num)', 'Super\BillingController::history/$1');

});





// ========== DENTRO de tu grupo: $routes->group('sales', ['filter' => 'salesauth'], ...) ==========
// (AGREGAR estas líneas)

// Mis comisiones (NUEVO)
$routes->get('commissions', 'Sales\CommissionsController::index');

// CLI Worker
$routes->cli('leadworker/processReminders', 'LeadWorker::processReminders');
$routes->cli('leadworker/reassignInactive', 'LeadWorker::reassignInactive');
$routes->cli('leadworker/detectColdLeads',  'LeadWorker::detectColdLeads');








