<?php

namespace App\Controllers;

use App\Models\CommissionModel;

class CommissionController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $tenantId = session('active_tenant_id');

        // Cruzamos Comisiones + Agentes + Reservas + Huéspedes
        $builder = $db->table('commissions c');
        $builder->select('c.*, a.name as agent_name, a.bank_details, r.check_in_date, r.check_out_date, r.total_price, r.status as reservation_status, g.full_name as guest_name');
        $builder->join('commission_agents a', 'a.id = c.agent_id');
        $builder->join('reservations r', 'r.id = c.reservation_id');
        $builder->join('guests g', 'g.id = r.guest_id');
        $builder->where('c.tenant_id', $tenantId);
        $builder->orderBy('c.created_at', 'DESC');

        $commissions = $builder->get()->getResultArray();

        // Calculamos los totales rápidos para las tarjetas de resumen
        $totalPending = 0;
        $totalPaid = 0;
        foreach ($commissions as $c) {
            if ($c['status'] == 'pending' || $c['status'] == 'approved') $totalPending += $c['amount'];
            if ($c['status'] == 'paid') $totalPaid += $c['amount'];
        }

        return view('commissions/index', [
            'commissions'  => $commissions,
            'totalPending' => $totalPending,
            'totalPaid'    => $totalPaid
        ]);
    }

    // Método para marcar como transferido/pagado
    public function pay($id)
    {
        $model = new CommissionModel();
        $commission = $model->where('tenant_id', session('active_tenant_id'))->find($id);

        if ($commission && $commission['status'] != 'paid') {
            $model->update($id, [
                'status'  => 'paid',
                'paid_at' => date('Y-m-d H:i:s')
            ]);
            return redirect()->to('/commissions')->with('success', '¡Comisión marcada como pagada exitosamente!');
        }

        return redirect()->to('/commissions')->with('error', 'No se pudo procesar el pago.');
    }
}