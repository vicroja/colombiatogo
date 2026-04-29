<?php
// app/Controllers/GuideController.php

namespace App\Controllers;

use App\Models\TourGuideModel;
use App\Models\GuidePaymentModel;
use App\Services\GuidePaymentCalculatorService;

class GuideController extends BaseController
{
    private int   $tenantId = 0;
    private array $viewData = [];

    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface            $logger
    ): void {
        parent::initController($request, $response, $logger);

        $this->tenantId = (int) session('active_tenant_id');

        $this->viewData = [
            'has_accommodation' => true,
            'has_tours'         => true,
        ];
    }

    // =========================================================================
    // CRUD DE GUÍAS
    // =========================================================================

    /**
     * Listado de guías con estadísticas de tours y pagos pendientes.
     */
    public function index(): string
    {
        $guideModel = new TourGuideModel();

        return view('guides/index', array_merge($this->viewData, [
            'guides' => $guideModel->getGuidesWithStats($this->tenantId),
        ]));
    }

    /**
     * Formulario nuevo guía.
     */
    public function create(): string
    {
        return view('guides/create', $this->viewData);
    }

    /**
     * Guarda un guía nuevo.
     */
    public function store()
    {
        $guideModel = new TourGuideModel();

        $data = $this->buildGuideData();
        $data['tenant_id'] = $this->tenantId;
        $data['is_active'] = 1;

        if (!$guideModel->insert($data)) {
            log_message('error', '[GuideController::store] ' . json_encode($guideModel->errors()));
            return redirect()->back()->withInput()->with('error', 'Error al guardar el guía.');
        }

        log_message('info', "[GuideController::store] Guía '{$data['name']}' creado para tenant {$this->tenantId}.");
        return redirect()->to('/guides')->with('success', 'Guía creado correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(int $id): string
    {
        $guideModel = new TourGuideModel();
        $guide = $guideModel->where('tenant_id', $this->tenantId)->find($id);

        if (!$guide) {
            return redirect()->to('/guides')->with('error', 'Guía no encontrado.');
        }

        return view('guides/edit', array_merge($this->viewData, [
            'guide' => $guide,
        ]));
    }

    /**
     * Actualiza un guía.
     */
    public function update(int $id)
    {
        $guideModel = new TourGuideModel();
        $guide = $guideModel->where('tenant_id', $this->tenantId)->find($id);

        if (!$guide) {
            return redirect()->to('/guides')->with('error', 'Guía no encontrado.');
        }

        $guideModel->update($id, $this->buildGuideData());

        log_message('info', "[GuideController::update] Guía #{$id} actualizado.");
        return redirect()->to('/guides')->with('success', 'Guía actualizado correctamente.');
    }

    /**
     * Activa o desactiva un guía.
     */
    public function toggleStatus(int $id)
    {
        $guideModel = new TourGuideModel();
        $guide = $guideModel->where('tenant_id', $this->tenantId)->find($id);

        if (!$guide) {
            return redirect()->back()->with('error', 'Guía no encontrado.');
        }

        $newStatus = $guide['is_active'] ? 0 : 1;
        $guideModel->update($id, ['is_active' => $newStatus]);

        $msg = $newStatus ? 'Guía activado.' : 'Guía desactivado.';
        log_message('info', "[GuideController::toggleStatus] Guía #{$id} → is_active={$newStatus}.");
        return redirect()->back()->with('success', $msg);
    }

    // =========================================================================
    // HISTORIAL Y PAGOS
    // =========================================================================

    /**
     * Historial de tours conducidos por un guía.
     */
    public function history(int $id): string
    {
        $guideModel = new TourGuideModel();
        $guide = $guideModel->where('tenant_id', $this->tenantId)->find($id);

        if (!$guide) {
            return redirect()->to('/guides')->with('error', 'Guía no encontrado.');
        }

        return view('guides/history', array_merge($this->viewData, [
            'guide'   => $guide,
            'history' => $guideModel->getGuideHistory($id),
        ]));
    }

    /**
     * Pagos de un guía específico.
     */
    public function payments(int $id): string
    {
        $guideModel   = new TourGuideModel();
        $paymentModel = new GuidePaymentModel();

        $guide = $guideModel->where('tenant_id', $this->tenantId)->find($id);
        if (!$guide) {
            return redirect()->to('/guides')->with('error', 'Guía no encontrado.');
        }

        $payments     = $paymentModel->getByGuide($id);
        $totalPending = array_sum(array_column(
            array_filter($payments, fn($p) => $p['status'] === 'pending'),
            'amount'
        ));
        $totalPaid = array_sum(array_column(
            array_filter($payments, fn($p) => $p['status'] === 'paid'),
            'amount'
        ));

        return view('guides/payments', array_merge($this->viewData, [
            'guide'        => $guide,
            'payments'     => $payments,
            'totalPending' => $totalPending,
            'totalPaid'    => $totalPaid,
        ]));
    }

    /**
     * Vista global de todos los pagos pendientes del tenant.
     */
    public function allPendingPayments(): string
    {
        $paymentModel = new GuidePaymentModel();

        return view('guides/all_payments', array_merge($this->viewData, [
            'payments' => $paymentModel->getPendingPayments($this->tenantId),
        ]));
    }

    /**
     * Registra un pago como pagado.
     * Puede venir de la vista de pagos del guía o de la vista global.
     */
    public function markPaid(int $paymentId)
    {
        $paymentModel = new GuidePaymentModel();
        $payment = $paymentModel->where('tenant_id', $this->tenantId)->find($paymentId);

        if (!$payment) {
            return redirect()->back()->with('error', 'Pago no encontrado.');
        }

        if ($payment['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Este pago ya fue procesado.');
        }

        $paymentModel->update($paymentId, [
            'status'         => 'paid',
            'payment_date'   => $this->request->getPost('payment_date') ?? date('Y-m-d'),
            'payment_method' => $this->request->getPost('payment_method'),
            'reference'      => $this->request->getPost('reference'),
            'notes'          => $this->request->getPost('notes'),
        ]);

        log_message('info', "[GuideController::markPaid] Pago #{$paymentId} marcado como pagado.");
        return redirect()->back()->with('success', 'Pago registrado correctamente.');
    }

    /**
     * Edita el monto de un pago pendiente (ajuste manual).
     */
    public function editPayment(int $paymentId)
    {
        $paymentModel = new GuidePaymentModel();
        $payment = $paymentModel->where('tenant_id', $this->tenantId)->find($paymentId);

        if (!$payment || $payment['status'] !== 'pending') {
            return redirect()->back()->with('error', 'No se puede editar este pago.');
        }

        $newAmount = (float) $this->request->getPost('amount');
        if ($newAmount <= 0) {
            return redirect()->back()->with('error', 'El monto debe ser mayor a 0.');
        }

        // Guardamos el monto original en el detalle para trazabilidad
        $detail = json_decode($payment['calculation_detail_json'] ?? '{}', true);
        $detail['manual_adjustment']  = true;
        $detail['original_amount']    = $payment['amount'];
        $detail['adjusted_to']        = $newAmount;
        $detail['adjusted_at']        = date('Y-m-d H:i:s');

        $paymentModel->update($paymentId, [
            'amount'                  => $newAmount,
            'calculation_detail_json' => json_encode($detail),
            'notes'                   => $this->request->getPost('notes'),
        ]);

        log_message('info', "[GuideController::editPayment] Pago #{$paymentId} ajustado de " .
            $payment['amount'] . " a {$newAmount}.");
        return redirect()->back()->with('success', 'Pago ajustado correctamente.');
    }

    // =========================================================================
    // HELPER PRIVADO
    // =========================================================================

    /**
     * Construye el array de datos del guía desde el POST.
     * Usado en store() y update().
     */
    private function buildGuideData(): array
    {
        return [
            'name'              => $this->request->getPost('name'),
            'phone'             => $this->request->getPost('phone'),
            'document'          => $this->request->getPost('document'),
            'specialty'         => $this->request->getPost('specialty'),
            'languages'         => $this->request->getPost('languages'),
            'payment_model'     => $this->request->getPost('payment_model'),
            'rate_fixed'        => $this->request->getPost('rate_fixed')        ?: null,
            'rate_per_adult'    => $this->request->getPost('rate_per_adult')    ?: null,
            'rate_per_child'    => $this->request->getPost('rate_per_child')    ?: null,
            'commission_pct'    => $this->request->getPost('commission_pct')    ?: null,
            'min_pax_for_bonus' => $this->request->getPost('min_pax_for_bonus') ?: null,
            'notes'             => $this->request->getPost('notes'),
        ];
    }
}