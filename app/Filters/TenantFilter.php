<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TenantModel;

class TenantFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // 1. Verificar si el usuario operativo está logueado
        if (!$session->has('user_id') || !$session->has('active_tenant_id')) {
            return redirect()->to('/login')->with('error', 'Por favor inicie sesión para acceder al sistema.');
        }

        // 2. Verificar el estado de la propiedad (Tenant)
        $tenantModel = new TenantModel();
        // Usamos find directamente porque TenantModel no hereda de BaseMultiTenantModel
        $tenant = $tenantModel->find($session->get('active_tenant_id'));

        if (!$tenant) {
            $session->destroy();
            return redirect()->to('/login')->with('error', 'Propiedad no encontrada.');
        }

        // 3. Regla de Negocio: Bloqueo por falta de pago o suspensión
        if ($tenant['is_suspended'] == 1) {
            $session->destroy();
            $reason = $tenant['suspended_reason'] ?? 'Falta de pago o incumplimiento de términos.';
            return redirect()->to('/login')->with('error', 'SU CUENTA HA SIDO SUSPENDIDA. Motivo: ' . $reason . ' - Contacte a soporte.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed
    }
}