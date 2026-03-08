<?php

namespace App\Controllers;

use App\Models\CommissionAgentModel;

class AgentController extends BaseController
{
    public function index()
    {
        $agentModel = new CommissionAgentModel();
        // Traemos todos los agentes del hotel activo
        $agents = $agentModel->where('tenant_id', session('active_tenant_id'))
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('agents/index', [
            'agents' => $agents
        ]);
    }

    public function store()
    {
        $agentModel = new CommissionAgentModel();

        $trackingCode = strtoupper(trim($this->request->getPost('tracking_code')));

        // Validar que el código no exista ya en este hotel
        $exists = $agentModel->where('tenant_id', session('active_tenant_id'))
            ->where('tracking_code', $trackingCode)
            ->first();

        if ($exists) {
            return redirect()->back()->with('error', 'Ese Código de Rastreo ya está en uso por otro agente.');
        }

        $agentModel->insert([
            'tenant_id'        => session('active_tenant_id'),
            'name'             => $this->request->getPost('name'),
            'contact_info'     => $this->request->getPost('contact_info'),
            'bank_details'     => $this->request->getPost('bank_details'),
            'commission_type'  => $this->request->getPost('commission_type'),
            'commission_value' => $this->request->getPost('commission_value'),
            'tracking_code'    => $trackingCode,
            'is_active'        => 1
        ]);

        return redirect()->to('/agents')->with('success', 'Comisionista registrado con éxito.');
    }

    public function delete($id)
    {
        $agentModel = new CommissionAgentModel();
        // Idealmente en un sistema real haríamos un "soft delete" (is_active = 0)
        // para no perder el historial contable, pero para el MVP lo borramos directo si no tiene reservas.
        try {
            $agentModel->where('tenant_id', session('active_tenant_id'))->delete($id);
            return redirect()->to('/agents')->with('success', 'Comisionista eliminado.');
        } catch (\Exception $e) {
            return redirect()->to('/agents')->with('error', 'No se puede eliminar porque ya tiene comisiones registradas.');
        }
    }
}