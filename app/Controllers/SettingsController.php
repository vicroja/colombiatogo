<?php

namespace App\Controllers;

use App\Models\TenantModel;

class SettingsController extends BaseController
{
    public function index()
    {
        $tenantModel = new TenantModel();
        // Cargamos la información del hotel actualmente logueado
        $tenant = $tenantModel->find(session('active_tenant_id'));

        return view('settings/general', [
            'tenant' => $tenant
        ]);
    }

    public function update()
    {
        $tenantModel = new TenantModel();
        $tenantId = session('active_tenant_id');

        $dataToUpdate = [
            'name'            => $this->request->getPost('name'),
            'email'           => $this->request->getPost('email'),
            'phone'           => $this->request->getPost('phone'),
            'address'         => $this->request->getPost('address'),
            'city'            => $this->request->getPost('city'),
            'country'         => $this->request->getPost('country'),
            'currency_code'   => $this->request->getPost('currency_code'),
            'currency_symbol' => $this->request->getPost('currency_symbol'),
            'timezone'        => $this->request->getPost('timezone'),
            'checkin_time'    => $this->request->getPost('checkin_time'),
            'checkout_time'   => $this->request->getPost('checkout_time'),
        ];

        // Procesar subida de logo si existe
        $logoFile = $this->request->getFile('logo');
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            // Validar que sea imagen
            if (strpos($logoFile->getMimeType(), 'image') === 0) {
                $newName = $logoFile->getRandomName();
                $logoFile->move(FCPATH . 'uploads/logos', $newName);
                $dataToUpdate['logo_path'] = 'uploads/logos/' . $newName;

                // Actualizar la variable de sesión para que el logo cambie de inmediato
                session()->set('tenant_logo', $dataToUpdate['logo_path']);
            }
        }

        // Actualizamos la base de datos
        $tenantModel->update($tenantId, $dataToUpdate);

        // Actualizamos las variables de sesión clave para que impacten todo el sistema
        session()->set([
            'tenant_name'     => $dataToUpdate['name'],
            'currency_symbol' => $dataToUpdate['currency_symbol'],
            'timezone'        => $dataToUpdate['timezone']
        ]);

        return redirect()->to('/settings')->with('success', 'Configuración de la propiedad actualizada correctamente.');
    }
}