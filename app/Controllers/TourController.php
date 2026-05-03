<?php
// app/Controllers/TourController.php

namespace App\Controllers;

use App\Models\TourModel;
use App\Models\TourScheduleModel;
use App\Models\TourGuideModel;
use App\Models\TourReservationModel;
use App\Models\GuestModel;
use App\Models\PaymentModel;
use App\Models\CommissionModel;
use App\Models\ReservationConsumptionModel;
use App\Services\TourPriceCalculatorService;

class TourController extends BaseController
{
    // Reemplazar por esto al inicio de la clase:
    private int   $tenantId         = 0;
    private bool  $hasAccommodation = true;
    private bool  $hasTours         = false;
    private array $viewData         = [];

    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface            $logger
    ): void {
        parent::initController($request, $response, $logger);

        // La sesión solo está disponible DESPUÉS de parent::initController()
        // active_tenant_id es la clave correcta confirmada en el log
        $this->tenantId = (int) session('active_tenant_id');

        if ($this->tenantId > 0) {
            $tenantModel = new \App\Models\TenantModel();
            $tenant      = $tenantModel->find($this->tenantId);
            $settings    = json_decode($tenant['settings_json'] ?? '{}', true) ?? [];

            $this->hasAccommodation = (bool)($settings['has_accommodation'] ?? true);
            $this->hasTours         = (bool)($settings['has_tours']         ?? false);

            $this->viewData = [
                'has_accommodation' => $this->hasAccommodation,
                'has_tours'         => $this->hasTours,
                'tenant'            => $tenant,
            ];

            log_message('info', "[TourController] tenant={$this->tenantId} " .
                "has_accommodation=" . ($this->hasAccommodation ? 'si' : 'no') . " " .
                "has_tours="         . ($this->hasTours         ? 'si' : 'no'));
        } else {
            log_message('warning', '[TourController] active_tenant_id no encontrado en sesión.');
        }
    }

    // =========================================================================
    // GESTIÓN DE TOURS (CRUD)
    // =========================================================================

    /**
     * Lista todos los tours del tenant.
     * Para operadores puros (has_accommodation = false) esta es su vista principal.
     */
    public function index(): string
    {
        $tourModel = new TourModel();
        log_message('debug', '[Tours::index] sesión completa: ' . json_encode(session()->get()));

        // DEBUG TEMPORAL — eliminar después de confirmar
        log_message('debug', '[Tours::index] tenant_id sesión: ' . $this->tenantId);
        log_message('debug', '[Tours::index] has_tours: ' . ($this->hasTours ? 'true' : 'false'));

        $tours = $tourModel->getActiveTours($this->tenantId);

        log_message('debug', '[Tours::index] tours encontrados: ' . count($tours));

        return view('tours/index', array_merge($this->viewData, [
            'tours' => $tours,
        ]));
    }

    /**
     * Formulario para crear un tour nuevo.
     */
    public function create(): string
    {
        $guideModel = new TourGuideModel();

        return view('tours/create', [
            'guides' => $guideModel->getActiveGuides($this->tenantId),
        ]);
    }

    /**
     * Guarda un tour nuevo en BD.
     */
    public function store()
    {
        $tourModel = new TourModel();

        $data = [
            'tenant_id'           => $this->tenantId,
            'name'                => $this->request->getPost('name'),
            'description'         => $this->request->getPost('description'),
            'duration_minutes'    => (int) $this->request->getPost('duration_minutes'),
            'meeting_point'       => $this->request->getPost('meeting_point'),
            'min_pax'             => (int) $this->request->getPost('min_pax'),
            'price_adult'         => (float) $this->request->getPost('price_adult'),
            'price_child'         => (float) $this->request->getPost('price_child'),
            'cancellation_policy' => $this->request->getPost('cancellation_policy'),
            'difficulty_level'    => $this->request->getPost('difficulty_level'),
            // included/excluded llegan como arrays del form y se serializan
            'included_json'       => json_encode($this->request->getPost('included') ?? []),
            'excluded_json'       => json_encode($this->request->getPost('excluded') ?? []),
            'is_active'           => 1,
        ];
        $tourId=$tourModel->insert($data);
        if (!$tourId) {
            log_message('error', '[TourController::store] Error al insertar tour: ' . json_encode($tourModel->errors()));
            return redirect()->back()->withInput()->with('error', 'Error al guardar el tour.');
        }

        log_message('info', "[TourController::store] Tour '{$data['name']}' creado para tenant {$this->tenantId}.");
        return redirect()->to("/tours/{$tourId}/edit")->with('success', 'Tour creado. Ahora puedes añadir fotos y videos.');
    }

    /**
     * Formulario de edición de un tour.
     */
    public function edit(int $id): string
    {
        $tourModel  = new TourModel();
        $guideModel = new TourGuideModel();

        $tour = $tourModel->where('tenant_id', $this->tenantId)->find($id);
        if (!$tour) {
            return redirect()->to('/tours')->with('error', 'Tour no encontrado.');
        }

        return view('tours/edit', [
            'tour'   => $tour,
            'guides' => $guideModel->getActiveGuides($this->tenantId),
        ]);
    }

    /**
     * Actualiza los datos de un tour.
     */
    public function update(int $id)
    {
        $tourModel = new TourModel();

        $tour = $tourModel->where('tenant_id', $this->tenantId)->find($id);
        if (!$tour) {
            return redirect()->to('/tours')->with('error', 'Tour no encontrado.');
        }

        $data = [
            'name'                => $this->request->getPost('name'),
            'description'         => $this->request->getPost('description'),
            'duration_minutes'    => (int) $this->request->getPost('duration_minutes'),
            'meeting_point'       => $this->request->getPost('meeting_point'),
            'min_pax'             => (int) $this->request->getPost('min_pax'),
            'price_adult'         => (float) $this->request->getPost('price_adult'),
            'price_child'         => (float) $this->request->getPost('price_child'),
            'cancellation_policy' => $this->request->getPost('cancellation_policy'),
            'difficulty_level'    => $this->request->getPost('difficulty_level'),
            'included_json'       => json_encode($this->request->getPost('included') ?? []),
            'excluded_json'       => json_encode($this->request->getPost('excluded') ?? []),
        ];

        $tourModel->update($id, $data);

        log_message('info', "[TourController::update] Tour ID {$id} actualizado por tenant {$this->tenantId}.");
        return redirect()->to('/tours')->with('success', 'Tour actualizado.');
    }

    // =========================================================================
    // GESTIÓN DE SCHEDULES (SALIDAS)
    // =========================================================================

    /**
     * Lista las próximas salidas de un tour específico.
     */
    public function schedules(int $tourId): string
    {
        $tourModel     = new TourModel();
        $scheduleModel = new TourScheduleModel();
        $guideModel    = new TourGuideModel();

        $tour = $tourModel->where('tenant_id', $this->tenantId)->find($tourId);
        if (!$tour) {
            return redirect()->to('/tours')->with('error', 'Tour no encontrado.');
        }

        return view('tours/schedules', [
            'tour'      => $tour,
            'schedules' => $scheduleModel->getUpcomingByTour($tourId),
            'guides'    => $guideModel->getActiveGuides($this->tenantId),
        ]);
    }

    /**
     * Guarda una nueva salida para un tour.
     */
    public function storeSchedule(int $tourId)
    {
        $scheduleModel = new TourScheduleModel();
        $tourModel     = new TourModel();

        $tour = $tourModel->where('tenant_id', $this->tenantId)->find($tourId);
        if (!$tour) {
            return redirect()->to('/tours')->with('error', 'Tour no encontrado.');
        }

        // Override de precio: solo guardar si se ingresó un valor distinto al base
        $priceAdultOverride = $this->request->getPost('price_adult_override');
        $priceChildOverride = $this->request->getPost('price_child_override');

        $data = [
            'tour_id'              => $tourId,
            'guide_id'             => $this->request->getPost('guide_id') ?: null,
            'start_datetime'       => $this->request->getPost('start_datetime'),
            'max_pax'              => (int) $this->request->getPost('max_pax'),
            'current_pax'          => 0,
            'price_adult_override' => $priceAdultOverride !== '' ? (float)$priceAdultOverride : null,
            'price_child_override' => $priceChildOverride !== '' ? (float)$priceChildOverride : null,
            'status'               => 'scheduled',
            'notes'                => $this->request->getPost('notes'),
        ];

        if (!$scheduleModel->insert($data)) {
            log_message('error', "[TourController::storeSchedule] Error: " . json_encode($scheduleModel->errors()));
            return redirect()->back()->withInput()->with('error', 'Error al guardar la salida.');
        }

        log_message('info', "[TourController::storeSchedule] Nueva salida para tour {$tourId} el {$data['start_datetime']}.");
        return redirect()->to("/tours/{$tourId}/schedules")->with('success', 'Salida programada correctamente.');
    }

    // =========================================================================
    // RESERVAS DE TOURS
    // =========================================================================

    /**
     * Formulario para reservar un tour.
     * Recibe schedule_id opcional para pre-seleccionar la salida.
     */

    // Busca el método createReservation() y reemplázalo completo por esto:

    public function createReservation(int $tourId): string
    {
        $tourModel     = new TourModel();
        $scheduleModel = new TourScheduleModel();
        $guestModel    = model('App\Models\GuestModel');
        $agentModel    = new \App\Models\CommissionAgentModel();

        $tour = $tourModel->where('tenant_id', $this->tenantId)->find($tourId);

        if (!$tour) {
            return redirect()->to('/tours')->with('error', 'Tour no encontrado.');
        }

        // Obtener schedules disponibles del tour
        $schedules = $scheduleModel
            ->select('tour_schedules.*')
            ->where('tour_schedules.tour_id', $tourId)
            ->where('tour_schedules.status', 'scheduled')
            ->where('tour_schedules.start_datetime >=', date('Y-m-d H:i:s'))
            ->orderBy('tour_schedules.start_datetime', 'ASC')
            ->findAll();

        // Obtener guests del tenant
        $guests = $guestModel->where('tenant_id', $this->tenantId)->findAll();

        // Obtener agentes comisionistas
        $agents = $agentModel->where('tenant_id', $this->tenantId)->where('is_active', 1)->findAll();

        // ← NUEVA LÍNEA: leer el schedule_id de la URL
        $preselectedScheduleId = (int) $this->request->getGet('schedule_id');

        return view('tours/reservation_create', array_merge($this->viewData, [
            'tour'                  => $tour,
            'schedules'             => $schedules,
            'guests'                => $guests,
            'agents'                => $agents,
            'preselectedScheduleId' => $preselectedScheduleId, // ← NUEVA LÍNEA
        ]));
    }

    /**
     * Procesa y guarda la reserva de un tour.
     *
     * Flujo:
     *  1. Validar disponibilidad de cupos
     *  2. Calcular precio
     *  3. Insertar tour_reservation dentro de transacción
     *  4. Ajustar current_pax del schedule
     *  5. Si hay parent_reservation_id → generar consumption en el folio del hotel
     *  6. Si hay agent_id → registrar comisión
     *  7. Si hay abono inicial → registrar pago
     */
    public function storeReservation()
    {
        $scheduleModel    = new TourScheduleModel();
        $tourResModel     = new TourReservationModel();
        $paymentModel     = new PaymentModel();
        $commissionModel  = new CommissionModel();
        $consumptionModel = new ReservationConsumptionModel();
        $calculator       = new TourPriceCalculatorService();

        $scheduleId         = (int) $this->request->getPost('schedule_id');
        $guestId            = (int) $this->request->getPost('guest_id');
        $numAdults          = (int) $this->request->getPost('num_adults');
        $numChildren        = (int) $this->request->getPost('num_children');
        $parentResId        = $this->request->getPost('parent_reservation_id') ?: null;
        $agentId            = $this->request->getPost('agent_id') ?: null;
        $pickupLocation     = $this->request->getPost('pickup_location');
        $notes              = $this->request->getPost('notes');
        $initialPayment     = (float) ($this->request->getPost('initial_payment') ?? 0);
        $paymentMethod      = $this->request->getPost('payment_method') ?? 'cash';

        $totalPax = $numAdults + $numChildren;

        // 1. Verificar disponibilidad
        if (!$scheduleModel->checkAvailability($scheduleId, $totalPax)) {
            return redirect()->back()->withInput()->with('error', 'No hay cupos suficientes para esta salida.');
        }

        // 2. Calcular precio
        $priceData = $calculator->calculate($scheduleId, $numAdults, $numChildren);

        if ($priceData['price_source'] === 'error') {
            return redirect()->back()->withInput()->with('error', 'Error al calcular el precio. Revise la configuración del tour.');
        }

        // 3. Iniciar transacción
        $db = \Config\Database::connect();
        $db->transStart();

        // Insertar la reserva de tour
        $tourResId = $tourResModel->insert([
            'tenant_id'             => $this->tenantId,
            'schedule_id'           => $scheduleId,
            'guest_id'              => $guestId,
            'parent_reservation_id' => $parentResId,
            'agent_id'              => $agentId,
            'num_adults'            => $numAdults,
            'num_children'          => $numChildren,
            'total_price'           => $priceData['total_price'],
            'pickup_location'       => $pickupLocation,
            'status'                => 'confirmed',
            'price_snapshot_json'   => json_encode($priceData),
            'notes'                 => $notes,
        ]);

        // 4. Actualizar cupos del schedule
        $scheduleModel->adjustPax($scheduleId, $totalPax);

        // 5. Si hay reserva de hotel padre → agregar al folio como consumption
        if ($parentResId) {
            $schedule = $scheduleModel->find($scheduleId);
            $tour     = (new TourModel())->find($schedule['tour_id']);

            $consumptionModel->insert([
                'tenant_id'      => $this->tenantId,
                'reservation_id' => $parentResId,
                'product_id'     => null,  // no es un product del catálogo
                'description'    => "Tour: {$tour['name']} ({$priceData['departure_date']})",
                'quantity'       => 1,
                'unit_price'     => $priceData['total_price'],
                'subtotal'       => $priceData['total_price'],
            ]);

            log_message('info', "[TourController::storeReservation] Tour agregado al folio de reserva hotel #{$parentResId}.");
        }

        // 6. Registrar comisión si hay agente
        if ($agentId) {
            $agentModel = new \App\Models\CommissionAgentModel();
            $agent      = $agentModel->find($agentId);

            if ($agent) {
                $commissionAmount = $agent['commission_type'] === 'percentage'
                    ? round($priceData['total_price'] * ($agent['commission_value'] / 100), 2)
                    : (float) $agent['commission_value'];

                $commissionModel->insert([
                    'tenant_id'      => $this->tenantId,
                    'reservation_id' => $tourResId,   // reutilizamos el campo
                    'entity_type'    => 'tour_reservation',
                    'agent_id'       => $agentId,
                    'amount'         => $commissionAmount,
                    'status'         => 'pending',
                ]);

                log_message('info', "[TourController::storeReservation] Comisión de $" . number_format($commissionAmount, 2) . " registrada para agente {$agentId}.");
            }
        }

        // 7. Registrar abono inicial si existe
        if ($initialPayment > 0) {
            $paymentModel->insert([
                'tenant_id'      => $this->tenantId,
                'reservation_id' => $tourResId,
                'entity_type'    => 'tour_reservation',   // ← faltaba
                'amount'         => $initialPayment,
                'payment_method' => $paymentMethod,
                'reference'      => 'Abono inicial tour',
            ]);

            log_message('info', "[TourController::storeReservation] Pago inicial de $" . number_format($initialPayment, 2) . " registrado para tour_reservation #{$tourResId}.");
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            log_message('error', "[TourController::storeReservation] Transacción fallida para schedule {$scheduleId}, guest {$guestId}.");
            return redirect()->back()->withInput()->with('error', 'Error en la base de datos. Intente de nuevo.');
        }

        return redirect()->to("/tours/reservation/{$tourResId}")->with('success', 'Reserva de tour confirmada.');
    }

    /**
     * Vista de detalle de una reserva de tour.
     * Muestra desglose de precio, pagos y estado.
     */
    public function showReservation(int $id)
    {
        $tourResModel = new TourReservationModel();
        $paymentModel = new PaymentModel();

        $reservation = $tourResModel->getFullReservation($id);
        if (!$reservation || $reservation['tenant_id'] !== $this->tenantId) {
            return redirect()->to('/tours')->with('error', 'Reserva no encontrada.');
        }

        // Reutilizamos PaymentModel: los pagos de tours usan el mismo campo reservation_id
        $payments   = $paymentModel->where('reservation_id', $id)->findAll();
        $totalPaid  = array_sum(array_column($payments, 'amount'));
        $balance    = round($reservation['total_price'] - $totalPaid, 2);

        return view('tours/reservation_show', [
            'reservation' => $reservation,
            'payments'    => $payments,
            'totalPaid'   => $totalPaid,
            'balance'     => $balance,
            'priceSnapshot' => json_decode($reservation['price_snapshot_json'] ?? '{}', true),
        ]);
    }

    /**
     * Cambia el estado de una reserva de tour.
     * Estados válidos: confirmed → completed | no_show | cancelled
     *                  pending  → confirmed | cancelled
     */
    public function updateReservationStatus(int $id)
    {
        $tourResModel  = new TourReservationModel();
        $scheduleModel = new TourScheduleModel();

        $reservation = $tourResModel->where('tenant_id', $this->tenantId)->find($id);
        if (!$reservation) {
            return redirect()->back()->with('error', 'Reserva no encontrada.');
        }

        $newStatus  = $this->request->getPost('new_status');
        $totalPax   = (int)$reservation['num_adults'] + (int)$reservation['num_children'];

        $allowed = [
            'pending'   => ['confirmed', 'cancelled'],
            'confirmed' => ['completed', 'no_show', 'cancelled'],
            'no_show'   => [],
            'completed' => [],
            'cancelled' => [],
            'refunded'  => [],
        ];

        $currentStatus = $reservation['status'];

        if (!in_array($newStatus, $allowed[$currentStatus] ?? [])) {
            return redirect()->back()->with('error', "Transición no válida: {$currentStatus} → {$newStatus}.");
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $tourResModel->update($id, ['status' => $newStatus]);

        // Si se cancela, devolvemos los cupos al schedule
        if (in_array($newStatus, ['cancelled', 'refunded'])) {
            $scheduleModel->adjustPax($reservation['schedule_id'], -$totalPax);
            log_message('info', "[TourController::updateReservationStatus] Reserva #{$id} cancelada. Se liberaron {$totalPax} cupos del schedule {$reservation['schedule_id']}.");
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            log_message('error', "[TourController::updateReservationStatus] Error al cambiar estado reserva #{$id} a {$newStatus}.");
            return redirect()->back()->with('error', 'Error en la base de datos.');
        }

        return redirect()->back()->with('success', 'Estado actualizado correctamente.');
    }

    /**
     * Manifiesto de carga para el guía.
     * Lista todos los pasajeros confirmados de una salida específica.
     * TODO: Paso siguiente → generar versión PDF con la skill de PDF.
     */
    public function manifest(int $scheduleId): string
    {
        $scheduleModel = new TourScheduleModel();
        $tourResModel  = new TourReservationModel();

        // Verificar que el schedule pertenece al tenant
        $schedule = $scheduleModel
            ->select('tour_schedules.*, tours.name AS tour_name, tours.meeting_point, tours.tenant_id, tour_guides.name AS guide_name, tour_guides.phone AS guide_phone')
            ->join('tours',       'tours.id = tour_schedules.tour_id')
            ->join('tour_guides', 'tour_guides.id = tour_schedules.guide_id', 'left')
            ->where('tour_schedules.id', $scheduleId)
            ->first();

        if (!$schedule || (int)$schedule['tenant_id'] !== $this->tenantId) {
            return redirect()->to('/tours')->with('error', 'Salida no encontrada.');
        }

        $passengers = $tourResModel->getManifestBySchedule($scheduleId);

        // Total de personas en esta salida
        $totalAdults   = array_sum(array_column($passengers, 'num_adults'));
        $totalChildren = array_sum(array_column($passengers, 'num_children'));

        return view('tours/manifest', [
            'schedule'      => $schedule,
            'passengers'    => $passengers,
            'totalAdults'   => $totalAdults,
            'totalChildren' => $totalChildren,
            'totalPax'      => $totalAdults + $totalChildren,
        ]);
    }

    /**
     * Agrega un pago a una reserva de tour existente.
     * Reutiliza PaymentModel igual que ReservationController::addPayment.
     */
    public function addPayment(int $id)
    {
        $tourResModel = new TourReservationModel();
        $paymentModel = new PaymentModel();

        $reservation = $tourResModel->where('tenant_id', $this->tenantId)->find($id);
        if (!$reservation) {
            return redirect()->back()->with('error', 'Reserva no encontrada.');
        }

        $amount = (float) $this->request->getPost('amount');
        if ($amount <= 0) {
            return redirect()->back()->with('error', 'El monto debe ser mayor a 0.');
        }

        $paymentModel->insert([
            'tenant_id'      => $this->tenantId,
            'reservation_id' => $id,
            'entity_type'    => 'tour_reservation',   // ← faltaba
            'amount'         => $amount,
            'payment_method' => $this->request->getPost('payment_method'),
            'reference'      => $this->request->getPost('reference'),
        ]);

        log_message('info', "[TourController::addPayment] Pago de $" . number_format($amount, 2) . " registrado para tour_reservation #{$id}.");
        return redirect()->back()->with('success', 'Pago registrado correctamente.');
    }


    /**
     * Cambia el estado de un schedule.
     * Al pasar a 'completed' dispara el cálculo automático del pago al guía.
     */
    public function updateScheduleStatus(int $scheduleId)
    {
        $scheduleModel = new TourScheduleModel();
        $calculator    = new \App\Services\GuidePaymentCalculatorService();

        $schedule = $scheduleModel
            ->select('tour_schedules.*, tours.tenant_id')
            ->join('tours', 'tours.id = tour_schedules.tour_id')
            ->where('tour_schedules.id', $scheduleId)
            ->first();

        if (!$schedule || (int)$schedule['tenant_id'] !== $this->tenantId) {
            return redirect()->back()->with('error', 'Salida no encontrada.');
        }

        $newStatus = $this->request->getPost('new_status');

        $allowed = [
            'scheduled'   => ['in_progress', 'cancelled'],
            'in_progress' => ['completed',   'cancelled'],
            'completed'   => [],
            'cancelled'   => [],
        ];

        if (!in_array($newStatus, $allowed[$schedule['status']] ?? [])) {
            return redirect()->back()->with('error', "Transición no válida: {$schedule['status']} → {$newStatus}.");
        }

        $scheduleModel->update($scheduleId, ['status' => $newStatus]);

        // Al completar: calcular pago al guía automáticamente
        if ($newStatus === 'completed') {
            $result = $calculator->calculateAndStore($scheduleId, $this->tenantId);

            if ($result['success'] && $result['amount'] > 0) {
                log_message('info', "[TourController] Pago al guía generado: $" .
                    number_format($result['amount'], 2) . " para schedule #{$scheduleId}.");

                return redirect()->back()->with('success',
                    'Salida completada. Pago al guía de $' .
                    number_format($result['amount'], 2) . ' generado automáticamente.');
            }
        }

        return redirect()->back()->with('success', 'Estado de la salida actualizado.');
    }

    // Agregar al final de TourController, después de los otros métodos

    /**
     * AJAX endpoint: crea un guest rápidamente desde el modal.
     * Devuelve JSON con el guest creado para agregarlo al select sin recargar.
     */
    public function quickCreateGuest()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Solo AJAX']);
        }

        $guestModel = model('App\Models\GuestModel');

        $data = [
            'tenant_id' => $this->tenantId,
            'full_name' => $this->request->getPost('full_name'),
            'document'  => $this->request->getPost('document'),
            'email'     => $this->request->getPost('email'),
            'phone'     => $this->request->getPost('phone'),
            'ai_active' => 0, // Creado manualmente desde el panel
            'chat_state'=> 'CLOSED',
        ];

        if (!$guestModel->insert($data)) {
            return $this->response->setStatusCode(400)->setJSON([
                'error'  => 'Error de validación',
                'fields' => $guestModel->errors(),
            ]);
        }

        $guestId = $guestModel->getInsertID();
        $guest   = $guestModel->find($guestId);

        return $this->response->setJSON([
            'success' => true,
            'guest'   => $guest,
        ]);
    }

    /**
     * AJAX — Sube un archivo de media (foto o video) para un tour.
     * Guarda el archivo en writable/uploads/tours/{tenant_id}/
     * y actualiza media_json del tour.
     *
     * POST /tours/{id}/media/upload
     * Body (multipart): file, description
     */
    public function uploadMedia(int $tourId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Solo AJAX']);
        }

        $tourModel = new TourModel();
        $tour = $tourModel->where('tenant_id', $this->tenantId)->find($tourId);

        if (!$tour) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Tour no encontrado.']);
        }

        $file = $this->request->getFile('file');

        if (!$file || !$file->isValid()) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Archivo inválido: ' . ($file ? $file->getErrorString() : 'no recibido'),
            ]);
        }

// ✅ Leer MIME y tamaño ANTES de cualquier move()
        $mimeType     = $file->getClientMimeType();   // usa lo que reporta el cliente
        $fileSize     = $file->getSize();
        $originalName = $file->getClientName();

        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/webp', 'image/gif',
            'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/webm',
        ];

        if (!in_array($mimeType, $allowedMimes)) {
            return $this->response->setStatusCode(422)->setJSON([
                'error' => 'Tipo de archivo no permitido. Solo imágenes (JPG, PNG, WEBP) y videos (MP4, MOV, AVI, WEBM).',
            ]);
        }

        if ($fileSize > 50 * 1024 * 1024) {
            return $this->response->setStatusCode(422)->setJSON([
                'error' => 'El archivo supera el límite de 50 MB.',
            ]);
        }

// Directorio de destino
        $uploadDir = FCPATH . "uploads/tours/{$this->tenantId}";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newName = $file->getRandomName();

        if (!$file->move($uploadDir, $newName)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Error al guardar el archivo en disco.']);
        }

// ✅ Detectar tipo DESPUÉS del move usando el MIME ya leído
        $isVideo = str_starts_with($mimeType, 'video/');

        $newItem = [
            'id'          => uniqid('media_', true),
            'type'        => $isVideo ? 'video' : 'image',
            'filename'    => $newName,
            'original'    => $originalName,
            'mime'        => $mimeType,
            'size'        => $fileSize,
            'path' => "uploads/tours/{$this->tenantId}/{$newName}",
            'description' => $this->request->getPost('description') ?? '',
            'uploaded_at' => date('Y-m-d H:i:s'),
        ];

        // Actualizar media_json del tour
        $currentMedia = json_decode($tour['media_json'] ?? '[]', true) ?? [];
        $currentMedia[] = $newItem;

        $tourModel->update($tourId, ['media_json' => json_encode($currentMedia)]);

        log_message('info', "[TourController::uploadMedia] Archivo '{$newItem['original']}' subido para tour {$tourId}.");

        return $this->response->setJSON([
            'success'    => true,
            'item'       => $newItem,
            'csrf_token' => csrf_hash(),
        ]);
    }

    /**
     * AJAX — Actualiza la descripción de un item de media.
     *
     * POST /tours/{id}/media/{mediaId}/description
     * Body: description
     */
    public function updateMediaDescription(int $tourId, string $mediaId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Solo AJAX']);
        }

        $tourModel = new TourModel();
        $tour = $tourModel->where('tenant_id', $this->tenantId)->find($tourId);

        if (!$tour) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Tour no encontrado.']);
        }

        $media = json_decode($tour['media_json'] ?? '[]', true) ?? [];
        $found = false;

        foreach ($media as &$item) {
            if ($item['id'] === $mediaId) {
                $item['description'] = $this->request->getPost('description') ?? '';
                $found = true;
                break;
            }
        }
        unset($item);

        if (!$found) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Media no encontrado.']);
        }

        $tourModel->update($tourId, ['media_json' => json_encode($media)]);

        return $this->response->setJSON(['success' => true, 'csrf_token' => csrf_hash()]);
    }

    /**
     * AJAX — Elimina un archivo de media del tour.
     * Borra el archivo físico y actualiza media_json.
     *
     * POST /tours/{id}/media/{mediaId}/delete
     */
    public function deleteMedia(int $tourId, string $mediaId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Solo AJAX']);
        }

        $tourModel = new TourModel();
        $tour = $tourModel->where('tenant_id', $this->tenantId)->find($tourId);

        if (!$tour) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Tour no encontrado.']);
        }

        $media   = json_decode($tour['media_json'] ?? '[]', true) ?? [];
        $toDelete = null;

        $filtered = array_filter($media, function ($item) use ($mediaId, &$toDelete) {
            if ($item['id'] === $mediaId) {
                $toDelete = $item;
                return false; // excluir del array resultante
            }
            return true;
        });

        if (!$toDelete) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Media no encontrado.']);
        }

        // Borrar archivo físico (se guardó en FCPATH, no en WRITEPATH)
        $filePath = FCPATH . $toDelete['path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $tourModel->update($tourId, ['media_json' => json_encode(array_values($filtered))]);

        log_message('info', "[TourController::deleteMedia] Media '{$toDelete['filename']}' eliminado del tour {$tourId}.");

        return $this->response->setJSON(['success' => true, 'csrf_token' => csrf_hash()]);
    }

    /**
     * AJAX — Reordena los items de media del tour.
     *
     * POST /tours/{id}/media/reorder
     * Body JSON: { "order": ["media_id1", "media_id2", ...] }
     */
    public function reorderMedia(int $tourId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Solo AJAX']);
        }

        $tourModel = new TourModel();
        $tour = $tourModel->where('tenant_id', $this->tenantId)->find($tourId);

        if (!$tour) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Tour no encontrado.']);
        }

        $input    = $this->request->getJSON(true) ?? json_decode($this->request->getBody(), true) ?? [];
        $newOrder = $input['order'] ?? [];
        $media    = json_decode($tour['media_json'] ?? '[]', true) ?? [];

        // Indexar por ID para reordenar
        $indexed = [];
        foreach ($media as $item) {
            $indexed[$item['id']] = $item;
        }

        $reordered = [];
        foreach ($newOrder as $id) {
            if (isset($indexed[$id])) {
                $reordered[] = $indexed[$id];
            }
        }

        $tourModel->update($tourId, ['media_json' => json_encode($reordered)]);

        return $this->response->setJSON(['success' => true, 'csrf_token' => csrf_hash()]);
    }


}