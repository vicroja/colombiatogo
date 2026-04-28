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
    // tenant_id del usuario autenticado, disponible en todos los métodos
    private int $tenantId;

    public function __construct()
    {
        // Asumimos que el tenant_id viene de la sesión,
        // igual que en ReservationController
        $this->tenantId = (int) session()->get('tenant_id');
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

        $tours = $tourModel->getActiveTours($this->tenantId);

        return view('tours/index', [
            'tours' => $tours,
        ]);
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

        if (!$tourModel->insert($data)) {
            log_message('error', '[TourController::store] Error al insertar tour: ' . json_encode($tourModel->errors()));
            return redirect()->back()->withInput()->with('error', 'Error al guardar el tour.');
        }

        log_message('info', "[TourController::store] Tour '{$data['name']}' creado para tenant {$this->tenantId}.");
        return redirect()->to('/tours')->with('success', 'Tour creado correctamente.');
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
    public function createReservation(int $tourId): string
    {
        $tourModel     = new TourModel();
        $scheduleModel = new TourScheduleModel();
        $guestModel    = new GuestModel();
        $agentModel = new \App\Models\CommissionAgentModel();

        $tour = $tourModel->where('tenant_id', $this->tenantId)->find($tourId);
        if (!$tour) {
            return redirect()->to('/tours')->with('error', 'Tour no encontrado.');
        }

        return view('tours/reservation_create', [
            'tour'      => $tour,
            'schedules' => $scheduleModel->getUpcomingByTour($tourId),
            'guests'    => $guestModel->where('tenant_id', $this->tenantId)->findAll(),
            'agents'    => $agentModel->where('tenant_id', $this->tenantId)  // ← agregar
            ->where('is_active', 1)
                ->findAll(),
        ]);
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
    public function showReservation(int $id): string
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
}