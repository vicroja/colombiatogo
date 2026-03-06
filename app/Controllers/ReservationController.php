<?php

namespace App\Controllers;

use App\Models\ReservationModel;
use App\Models\GuestModel;
use App\Models\AccommodationUnitModel;
use App\Services\ReservationStateMachineService;

class ReservationController extends BaseController
{
    public function index()
    {
        $resModel = new ReservationModel();

        // Traemos las reservas con los nombres de los huéspedes y habitaciones
        $reservations = $resModel->select('reservations.*, guests.full_name, accommodation_units.name as unit_name')
            ->join('guests', 'guests.id = reservations.guest_id')
            ->join('accommodation_units', 'accommodation_units.id = reservations.accommodation_unit_id')
            ->orderBy('check_in_date', 'ASC')
            ->findAll();

        return view('reservations/index', ['reservations' => $reservations]);
    }

    public function create()
    {
        $unitModel = new AccommodationUnitModel();
        // Solo mostramos habitaciones que no estén bloqueadas o en mantenimiento
        $units = $unitModel->whereIn('status', ['available', 'occupied'])->findAll();

        return view('reservations/create', ['units' => $units]);
    }

    public function store()
    {
        $guestModel = new GuestModel();
        $resModel = new ReservationModel();

        $guestModel->db->transStart();

        // 1. Creamos al huésped al vuelo para agilizar el proceso de recepción
        $guestId = $guestModel->createForTenant([
            'full_name' => $this->request->getPost('guest_name'),
            'document'  => $this->request->getPost('guest_document'),
            'phone'     => $this->request->getPost('guest_phone'),
            'email'     => $this->request->getPost('guest_email'),
        ]);

        // 2. Creamos la reserva inicial (siempre nace 'pending')
        $resModel->createForTenant([
            'guest_id'              => $guestId,
            'accommodation_unit_id' => $this->request->getPost('unit_id'),
            'check_in_date'         => $this->request->getPost('check_in'),
            'check_out_date'        => $this->request->getPost('check_out'),
            'total_price'           => $this->request->getPost('total_price'),
            'status'                => 'pending'
        ]);

        $guestModel->db->transComplete();

        if ($guestModel->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Error al crear la reserva.');
        }

        return redirect()->to('/reservations')->with('success', 'Reserva creada exitosamente.');
    }

    public function updateStatus($id)
    {
        $newStatus = $this->request->getPost('new_status');
        $fsm = new ReservationStateMachineService();

        $result = $fsm->transitionState($id, $newStatus);

        if (!$result['success']) {
            return redirect()->to('/reservations')->with('error', $result['message']);
        }

        return redirect()->to('/reservations')->with('success', $result['message']);
    }

    // ... métodos anteriores (index, create, store, updateStatus) ...

    // ENDPOINT 1: Devuelve las habitaciones para la columna izquierda del calendario
    public function getResources()
    {
        $unitModel = new \App\Models\AccommodationUnitModel();
        // Traemos solo las unidades del hotel activo (BaseMultiTenantModel se encarga del filtro)
        $units = $unitModel->findAll();

        $resources = [];
        foreach ($units as $u) {
            $resources[] = [
                'id'    => $u['id'],
                'title' => $u['name']
            ];
        }

        return $this->response->setJSON($resources);
    }

    // ENDPOINT 2: Devuelve las reservas para pintarlas en la grilla
    public function getEvents()
    {
        $resModel = new \App\Models\ReservationModel();

        // Obtenemos los rangos de fechas que FullCalendar pide por la URL (start y end)
        $start = $this->request->getGet('start');
        $end   = $this->request->getGet('end');

        // Filtramos las reservas que caen dentro del mes que el usuario está mirando
        $resModel->select('reservations.*, guests.full_name')
            ->join('guests', 'guests.id = reservations.guest_id')
            ->groupStart()
            ->where("check_in_date >=", $start)
            ->orWhere("check_out_date <=", $end)
            ->groupEnd();

        $reservations = $resModel->findAll();

        $events = [];
        // Colores según el motor de estados (FSM)
        $statusColors = [
            'pending'     => '#ffc107', // Amarillo
            'confirmed'   => '#0dcaf0', // Celeste
            'checked_in'  => '#198754', // Verde
            'checked_out' => '#6c757d', // Gris
            'cancelled'   => '#dc3545'  // Rojo
        ];

        foreach ($reservations as $r) {
            $events[] = [
                'id'         => $r['id'],
                'resourceId' => $r['accommodation_unit_id'], // Vincula la reserva a su fila
                'title'      => $r['full_name'],
                'start'      => $r['check_in_date'] . 'T14:00:00', // Asumimos Check-in a las 2 PM
                'end'        => $r['check_out_date'] . 'T11:00:00', // Asumimos Check-out a las 11 AM
                'color'      => $statusColors[$r['status']] ?? '#007bff',
                'url'        => base_url('/reservations') // Si hacen clic, los lleva a la lista
            ];
        }

        return $this->response->setJSON($events);
    }

    // Muestra el Folio (Detalle completo de la reserva y pagos)
    public function show($id)
    {
        $resModel = new ReservationModel();
        $paymentModel = new \App\Models\PaymentModel();

        // 1. Obtener la reserva con sus relaciones
        $reservation = $resModel->select('reservations.*, guests.full_name, guests.document, guests.phone, accommodation_units.name as unit_name')
            ->join('guests', 'guests.id = reservations.guest_id')
            ->join('accommodation_units', 'accommodation_units.id = reservations.accommodation_unit_id')
            ->find($id);

        if (!$reservation) {
            return redirect()->to('/reservations')->with('error', 'Reserva no encontrada.');
        }

        // 2. Obtener el historial de pagos de esta reserva
        $payments = $paymentModel->where('reservation_id', $id)->orderBy('created_at', 'DESC')->findAll();

        // 3. Calcular finanzas
        $totalPaid = 0;
        foreach ($payments as $p) {
            $totalPaid += $p['amount'];
        }
        $balance = $reservation['total_price'] - $totalPaid;

        $data = [
            'reservation' => $reservation,
            'payments'    => $payments,
            'totalPaid'   => $totalPaid,
            'balance'     => $balance
        ];

        return view('reservations/show', $data);
    }

    // Procesa un nuevo pago/abono
    public function addPayment($id)
    {
        $resModel = new ReservationModel();
        $reservation = $resModel->find($id);

        if (!$reservation) return redirect()->to('/reservations')->with('error', 'Reserva no encontrada.');

        $amount = $this->request->getPost('amount');

        // Validación básica para no cobrar montos negativos
        if ($amount <= 0) {
            return redirect()->back()->with('error', 'El monto debe ser mayor a cero.');
        }

        $paymentModel = new \App\Models\PaymentModel();
        $paymentModel->createForTenant([
            'reservation_id' => $id,
            'amount'         => $amount,
            'payment_method' => $this->request->getPost('payment_method'),
            'reference'      => $this->request->getPost('reference')
        ]);

        return redirect()->to("/reservations/show/{$id}")->with('success', 'Pago registrado exitosamente.');
    }
}