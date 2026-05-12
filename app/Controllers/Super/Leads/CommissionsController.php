<?php

namespace App\Controllers\Super\Leads;

use App\Controllers\BaseController;
use App\Models\CommissionModel;
use App\Models\SalesUserModel;

/**
 * Gestión y liquidación de comisiones desde /super.
 */
class CommissionsController extends BaseController
{
    protected CommissionModel $model;

    public function __construct()
    {
        $this->model = new CommissionModel();
    }

    /**
     * Vista resumen: totales por vendedor.
     */
    public function index()
    {
        return view('super/leads/commissions/index', [
            'title'   => 'Liquidación de comisiones',
            'summary' => $this->model->getSummaryByUser(),
        ]);
    }

    /**
     * Listado detallado con filtros.
     */
    public function detail()
    {
        $filters = [
            'user_id' => $this->request->getGet('user_id'),
            'status'  => $this->request->getGet('status'),
            'type'    => $this->request->getGet('type'),
            'from'    => $this->request->getGet('from'),
            'to'      => $this->request->getGet('to'),
        ];
        $filters = array_filter($filters); // limpia vacíos

        $users = (new SalesUserModel())->orderBy('name','ASC')->findAll();

        return view('super/leads/commissions/detail', [
            'title'       => 'Detalle de comisiones',
            'commissions' => $this->model->listWithDetails($filters),
            'users'       => $users,
            'filters'     => $filters,
        ]);
    }

    /**
     * Aprueba una comisión pendiente.
     */
    public function approve(int $id)
    {
        $by = session('superadmin_id');
        $this->model->changeStatus($id, 'approved', $by);
        return redirect()->back()->with('success', 'Comisión aprobada');
    }

    /**
     * Aprueba todas las pendientes de un vendedor en bloque.
     */
    public function approveAll()
    {
        $userId = (int)$this->request->getPost('user_id');
        if (!$userId) {
            return redirect()->back()->with('error', 'Vendedor inválido');
        }
        $db = \Config\Database::connect();
        $by = session('superadmin_id');
        $db->table('commissions')
           ->where('sales_user_id', $userId)
           ->where('status', 'pending')
           ->update([
               'status'      => 'approved',
               'approved_at' => date('Y-m-d H:i:s'),
               'approved_by' => $by,
           ]);
        $n = $db->affectedRows();
        return redirect()->back()->with('success', "{$n} comisiones aprobadas");
    }

    /**
     * Marca como pagada (con método y referencia).
     */
    public function pay(int $id)
    {
        $by = session('superadmin_id');
        $extra = [
            'payment_method'    => $this->request->getPost('payment_method'),
            'payment_reference' => $this->request->getPost('payment_reference'),
            'notes'             => $this->request->getPost('notes'),
        ];
        $this->model->changeStatus($id, 'paid', $by, $extra);
        return redirect()->back()->with('success', 'Comisión marcada como pagada');
    }

    /**
     * Cancela una comisión (ej: lead que se canceló después).
     */
    public function cancel(int $id)
    {
        $notes = $this->request->getPost('notes') ?: 'Cancelada manualmente';
        $this->model->changeStatus($id, 'cancelled', session('superadmin_id'), ['notes'=>$notes]);
        return redirect()->back()->with('success', 'Comisión cancelada');
    }
}
