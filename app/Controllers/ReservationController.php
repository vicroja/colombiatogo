<?php

namespace App\Controllers;

use App\Models\ReservationModel;
use App\Models\GuestModel;
use App\Models\AccommodationUnitModel;
use App\Services\ReservationStateMachineService;

class ReservationController extends BaseController
{

    public function calculatePrice()
    {
        $unitId = $this->request->getGet('unit_id');
        $ratePlanId = $this->request->getGet('rate_plan_id');
        $checkIn = $this->request->getGet('check_in');
        $checkOut = $this->request->getGet('check_out');
        $promoCode = $this->request->getGet('promo_code'); // NUEVO: Recibimos el código

        if (!$unitId || !$ratePlanId || !$checkIn || !$checkOut) {
            return $this->response->setJSON(['success' => false, 'message' => 'Faltan datos']);
        }

        $datetime1 = new \DateTime($checkIn);
        $datetime2 = new \DateTime($checkOut);
        $interval = $datetime1->diff($datetime2);
        $nights = $interval->days;

        if ($nights <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Fechas inválidas']);
        }

        // Lógica base de precio (Simplificada para el MVP)
// Lógica base de precio (A prueba de balas)
        $rateModel = new \App\Models\UnitRateModel();
        $rate = $rateModel->where('unit_id', $unitId)
            ->where('rate_plan_id', $ratePlanId)
            ->first();

        // 1. Intentar sacar el precio del plan de tarifas (probando los nombres de columna más comunes)
        if ($rate) {
            $basePricePerNight = $rate['rate'] ?? $rate['base_rate'] ?? $rate['price'] ?? 0;
        } else {
            // 2. Si no hay plan de tarifas asignado, usamos el precio estándar de la cabaña
            $unitModel = new \App\Models\AccommodationUnitModel();
            $unitInfo = $unitModel->find($unitId);
            $basePricePerNight = $unitInfo['base_price'] ?? 0;
        }
        $totalPrice = $basePricePerNight * $nights;
        $originalPrice = $totalPrice;
        $discountAmount = 0;
        $promoId = null;

        // NUEVO: Lógica de Promociones
        if (!empty($promoCode) && $totalPrice > 0) {
            $promoModel = new \App\Models\PromotionModel();
            $promo = $promoModel->where('code', strtoupper($promoCode))
                ->where('tenant_id', session('active_tenant_id'))
                ->where('is_active', 1)
                ->where('valid_from <=', date('Y-m-d'))
                ->where('valid_until >=', date('Y-m-d'))
                ->first();

            // Si el cupón existe y tiene usos disponibles
            if ($promo && ($promo['max_uses'] == 0 || $promo['current_uses'] < $promo['max_uses'])) {
                if ($promo['discount_type'] == 'percentage') {
                    $discountAmount = $totalPrice * ($promo['discount_value'] / 100);
                } else {
                    $discountAmount = $promo['discount_value'];
                }

                $totalPrice = max(0, $totalPrice - $discountAmount);
                $promoId = $promo['id'];
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Cupón inválido o agotado']);
            }
        }

        return $this->response->setJSON([
            'success'         => true,
            'nights'          => $nights,
            'original_price'  => number_format($originalPrice, 2, '.', ''),
            'discount_amount' => number_format($discountAmount, 2, '.', ''),
            'total_price'     => number_format($totalPrice, 2, '.', ''),
            'promo_applied'   => $discountAmount > 0,
            'promo_id'        => $promoId
        ]);
    }
    public function create()
    {
        $unitModel = new AccommodationUnitModel();
        $planModel = new \App\Models\RatePlanModel();
        $sourceModel = new \App\Models\ReservationSourceModel(); // NUEVO
        $agentModel = new \App\Models\CommissionAgentModel(); // NUEVO

        // Si no hay fuentes, creamos las básicas para ayudar al hotel
        if ($sourceModel->countAllResults() == 0) {
            $sourceModel->createForTenant(['name' => 'Directa (Recepción)', 'color' => '#198754']);
            $sourceModel->createForTenant(['name' => 'Booking.com', 'color' => '#003580']);
            $sourceModel->createForTenant(['name' => 'Airbnb', 'color' => '#FF5A5F']);
        }

        $unitModel = new \App\Models\AccommodationUnitModel();



        $units = $unitModel->select('accommodation_units.*, accommodation_types.name as type_name')
            ->join('accommodation_types', 'accommodation_types.id = accommodation_units.type_id', 'left')
            ->where('accommodation_units.status !=', 'maintenance')
            ->findAll();


        $ratePlanModel = new \App\Models\RatePlanModel();



        $plans = $planModel->where('is_active', 1)->findAll();
        $sources = $sourceModel->where('is_active', 1)->findAll(); // NUEVO
        $tenantId = session('active_tenant_id');
        $agents = $agentModel->where('tenant_id', $tenantId)->where('is_active', 1)->findAll();

        return view('reservations/create', [
            'units' => $units,
            'plans' => $plans,
            'agents' => $agents, // NUEVO
            'sources' => $sources,
            'rate_plans' => $ratePlanModel->findAll(),
        ]);
    }



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
        $reservationId = $resModel->createForTenant([
            'guest_id'              => $guestId,
            'source_id'             => $this->request->getPost('source_id'),
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
// Registrar el uso del cupón si se aplicó
        $promoId = $this->request->getPost('promo_id');
        if ($promoId) {
            $promoModel = new \App\Models\PromotionModel();
            $promoModel->where('id', $promoId)->set('current_uses', 'current_uses+1', false)->update();
        }

        // --- PROCESAR ACOMPAÑANTES ---
        $additionalGuests = $this->request->getPost('additional_guests');
        if (!empty($additionalGuests) && is_array($additionalGuests)) {
            $resGuestModel = new \App\Models\ReservationGuestModel();
            $insertedCount = 0;

            foreach ($additionalGuests as $guest) {
                // Solo insertamos si al menos el nombre está presente
                if (!empty($guest['first_name'])) {
                    $resGuestModel->insert([
                        'reservation_id' => $reservationId,
                        'first_name'     => $guest['first_name'],
                        'last_name'      => $guest['last_name'],
                        'doc_number'     => $guest['doc_number'] ?? null,
                        'created_at'     => date('Y-m-d H:i:s')
                    ]);
                    $insertedCount++;
                }
            }
            log_message('info', "Acompañantes registrados: {$insertedCount} para la reserva #{$reservationId}");
        }

        // ==========================================
        // LÓGICA DE COMISIONISTAS Y AGENCIAS
        // ==========================================
        $agentId = $this->request->getPost('agent_id');

        if (!empty($agentId)) {
            $agentModel = new \App\Models\CommissionAgentModel();
            $commissionModel = new \App\Models\CommissionModel();

            $agent = $agentModel->where('tenant_id', session('active_tenant_id'))->find($agentId);

            if ($agent) {
                // Capturamos el total directamente del formulario
                $precioAlojamiento = (float) $this->request->getPost('total_price');

                // La comisión se calcula SOLO sobre el alojamiento, no sobre minibar o extras
                $commissionAmount = 0;

                if ($agent['commission_type'] == 'percentage') {
                    $commissionAmount = $precioAlojamiento * ($agent['commission_value'] / 100);
                } else {
                    $commissionAmount = $agent['commission_value']; // Monto fijo
                }

                // Guardar la deuda como "Pendiente"
                $commissionModel->insert([
                    'tenant_id'      => session('active_tenant_id'),
                    'reservation_id' => $reservationId,
                    'agent_id'       => $agentId,
                    'amount'         => $commissionAmount,
                    'status'         => 'pending'
                ]);
            }
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


    // Muestra el Folio (Detalle, Pagos y Consumos)
    public function show($id)
    {
        $resModel = new ReservationModel();
        $paymentModel = new \App\Models\PaymentModel();
        $consumptionModel = new \App\Models\ReservationConsumptionModel();
        $productModel = new \App\Models\ProductModel();

        $reservation = $resModel->select('reservations.*, guests.full_name, guests.document, guests.phone, accommodation_units.name as unit_name')
            ->join('guests', 'guests.id = reservations.guest_id')
            ->join('accommodation_units', 'accommodation_units.id = reservations.accommodation_unit_id')
            ->find($id);

        if (!$reservation) return redirect()->to('/reservations')->with('error', 'Reserva no encontrada.');

        $payments = $paymentModel->where('reservation_id', $id)->orderBy('created_at', 'DESC')->findAll();
        $consumptions = $consumptionModel->where('reservation_id', $id)->orderBy('created_at', 'DESC')->findAll();
        $products = $productModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();

        //Traer acompañantes
        $guestModel = new \App\Models\ReservationGuestModel();
        $companions = $guestModel->where('reservation_id', $id)->findAll();
        // Finanzas Actualizadas
        $totalPaid = array_sum(array_column($payments, 'amount'));
        $totalConsumptions = array_sum(array_column($consumptions, 'subtotal'));

        $grandTotal = $reservation['total_price'] + $totalConsumptions;
        $balance = $grandTotal - $totalPaid;

        return view('reservations/show', [
            'reservation'       => $reservation,
            'payments'          => $payments,
            'consumptions'      => $consumptions,
            'products'          => $products,
            'companions'        => $companions, // NUEVO
            'totalPaid'         => $totalPaid,
            'totalConsumptions' => $totalConsumptions,
            'grandTotal'        => $grandTotal,
            'balance'           => $balance
        ]);
    }

    // Procesar un nuevo consumo (POS)
    public function addConsumption($id)
    {
        $productModel = new \App\Models\ProductModel();
        $consumptionModel = new \App\Models\ReservationConsumptionModel();

        $productId = $this->request->getPost('product_id');
        $quantity = $this->request->getPost('quantity');

        $product = $productModel->find($productId);
        if(!$product) return redirect()->back()->with('error', 'Producto no válido.');

        $subtotal = $product['unit_price'] * $quantity;

        $consumptionModel->createForTenant([
            'reservation_id' => $id,
            'product_id'     => $product['id'],
            'description'    => $product['name'],
            'quantity'       => $quantity,
            'unit_price'     => $product['unit_price'],
            'subtotal'       => $subtotal
        ]);

        return redirect()->to("/reservations/show/{$id}")->with('success', 'Consumo agregado a la cuenta.');
    }

    // Eliminar un consumo (si el recepcionista se equivocó)
    public function deleteConsumption($id, $consumptionId)
    {
        $consumptionModel = new \App\Models\ReservationConsumptionModel();
        $consumptionModel->delete($consumptionId);
        return redirect()->to("/reservations/show/{$id}")->with('success', 'Consumo eliminado.');
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
    public function addCompanion($id)
    {
        $guestModel = new \App\Models\ReservationGuestModel();
        $guestModel->insert([
            'reservation_id' => $id,
            'first_name'     => $this->request->getPost('first_name'),
            'last_name'      => $this->request->getPost('last_name'),
            'doc_type'       => $this->request->getPost('doc_type'),
            'doc_number'     => $this->request->getPost('doc_number'),
            'relationship'   => $this->request->getPost('relationship')
        ]);
        return redirect()->to("/reservations/show/{$id}")->with('success', 'Acompañante registrado (SIRE).');
    }

    public function deleteCompanion($id, $companionId)
    {
        $guestModel = new \App\Models\ReservationGuestModel();
        $guestModel->delete($companionId);
        return redirect()->to("/reservations/show/{$id}")->with('success', 'Acompañante eliminado.');
    }
    // Vista de Cierre de Cuenta (Factura Final)
    public function closure($id)
    {
        $resModel = new ReservationModel();
        $paymentModel = new \App\Models\PaymentModel();
        $consumptionModel = new \App\Models\ReservationConsumptionModel();

        // Traemos los datos
        $reservation = $resModel->select('reservations.*, guests.full_name, guests.document, accommodation_units.name as unit_name')
            ->join('guests', 'guests.id = reservations.guest_id')
            ->join('accommodation_units', 'accommodation_units.id = reservations.accommodation_unit_id')
            ->find($id);

        if (!$reservation) return redirect()->to('/reservations')->with('error', 'Reserva no encontrada.');

        $payments = $paymentModel->where('reservation_id', $id)->orderBy('created_at', 'ASC')->findAll();
        $consumptions = $consumptionModel->where('reservation_id', $id)->orderBy('created_at', 'ASC')->findAll();

        // Cálculos financieros
        $totalPaid = array_sum(array_column($payments, 'amount'));
        $totalConsumptions = array_sum(array_column($consumptions, 'subtotal'));

        $grandTotal = $reservation['total_price'] + $totalConsumptions;
        $balance = $grandTotal - $totalPaid;

        return view('reservations/account_closure', [
            'reservation'       => $reservation,
            'payments'          => $payments,
            'consumptions'      => $consumptions,
            'totalPaid'         => $totalPaid,
            'totalConsumptions' => $totalConsumptions,
            'grandTotal'        => $grandTotal,
            'balance'           => $balance
        ]);
    }

    // Procesar el Check-out Definitivo
    public function processCheckout($id)
    {
        $resModel = new ReservationModel();
        $paymentModel = new \App\Models\PaymentModel();
        $consumptionModel = new \App\Models\ReservationConsumptionModel();

        $reservation = $resModel->find($id);
        if (!$reservation) return redirect()->to('/reservations')->with('error', 'Reserva no encontrada.');

        // 1. Verificación Estricta: ¿Debe dinero?
        $totalPaid = array_sum(array_column($paymentModel->where('reservation_id', $id)->findAll(), 'amount'));
        $totalConsumptions = array_sum(array_column($consumptionModel->where('reservation_id', $id)->findAll(), 'subtotal'));

        $balance = ($reservation['total_price'] + $totalConsumptions) - $totalPaid;

        if ($balance > 0) {
            return redirect()->back()->with('error', 'No se puede hacer Check-out. El huésped aún debe $'.number_format($balance, 2));
        }

        // 2. Transición de Estado usando nuestra Máquina de Estados (FSM)
        $fsm = new \App\Services\ReservationStateMachineService();
        $result = $fsm->transitionState($id, 'checked_out');

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->to('/reservations')->with('success', '¡Check-out completado! La habitación está libre nuevamente.');
    }

    // Generar Factura en PDF (mPDF)
    public function invoice($id)
    {
        $resModel = new ReservationModel();
        $paymentModel = new \App\Models\PaymentModel();
        $consumptionModel = new \App\Models\ReservationConsumptionModel();

        $reservation = $resModel->select('reservations.*, guests.full_name, guests.document, accommodation_units.name as unit_name')
            ->join('guests', 'guests.id = reservations.guest_id')
            ->join('accommodation_units', 'accommodation_units.id = reservations.accommodation_unit_id')
            ->find($id);

        if (!$reservation) return redirect()->to('/reservations')->with('error', 'Reserva no encontrada.');

        $payments = $paymentModel->where('reservation_id', $id)->orderBy('created_at', 'ASC')->findAll();
        $consumptions = $consumptionModel->where('reservation_id', $id)->orderBy('created_at', 'ASC')->findAll();

        $totalPaid = array_sum(array_column($payments, 'amount'));
        $totalConsumptions = array_sum(array_column($consumptions, 'subtotal'));
        $grandTotal = $reservation['total_price'] + $totalConsumptions;
        $balance = $grandTotal - $totalPaid;

        // Preparamos los datos para la vista del PDF
        $data = [
            'reservation'       => $reservation,
            'payments'          => $payments,
            'consumptions'      => $consumptions,
            'totalPaid'         => $totalPaid,
            'totalConsumptions' => $totalConsumptions,
            'grandTotal'        => $grandTotal,
            'balance'           => $balance,
            'tenant_name'       => session('tenant_name') ?: 'Hotel Casa Lucerito',
            'tenant_logo'       => session('tenant_logo'), // Si subiste logo en configuración
            'currency'          => session('currency_symbol') ?: '$'
        ];

        // 1. Capturamos el HTML de la vista
        $html = view('reservations/invoice_pdf', $data);

        // 2. Instanciamos mPDF
        // 'format' => 'Letter' (Carta) o 'A4'
        $mpdf = new \Mpdf\Mpdf([
            'mode'   => 'utf-8',
            'format' => 'Letter',
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_left' => 15,
            'margin_right' => 15,
        ]);

        // Opcional: Agregar pie de página
        $mpdf->SetFooter('Generado el ' . date('d/m/Y H:i') . '||Pág. {PAGENO} de {nbpg}');
        // 3. Escribimos el HTML en el PDF
        $mpdf->WriteHTML($html);

        // 4. Salida al navegador. 'I' abre en el navegador, 'D' fuerza la descarga directa.
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("Factura_Reserva_00{$id}.pdf", 'I');

        // Evitamos que CodeIgniter intente renderizar algo más
        exit();
    }
}