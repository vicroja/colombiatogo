<?php

namespace App\Controllers;

use App\Models\TenantModel;

class SettingsController extends BaseController
{
    public function index()
    {
        $tenantModel = new TenantModel();
        // Buscamos los datos actuales de la propiedad logueada
        $tenant = $tenantModel->find(session('active_tenant_id'));

        return view('settings/index', ['tenant' => $tenant]);
    }

    public function update()
    {
        $tenantModel = new TenantModel();
        $tenantId = session('active_tenant_id');

        // Recopilamos los datos del formulario
        $data = [
            'name'            => $this->request->getPost('name'),
            'email'           => $this->request->getPost('email'),
            'phone'           => $this->request->getPost('phone'),
            'currency_code'   => strtoupper($this->request->getPost('currency_code')),
            'currency_symbol' => $this->request->getPost('currency_symbol'),
            'timezone'        => $this->request->getPost('timezone')
        ];

        // Actualizamos en la base de datos
        $tenantModel->update($tenantId, $data);

        // Actualizamos las variables de sesión por si cambió el nombre o la moneda
        session()->set('tenant_name', $data['name']);
        session()->set('currency_symbol', $data['currency_symbol']);

        return redirect()->to('/settings')->with('success', 'Configuración de la propiedad actualizada correctamente.');
    }
}