<?php

namespace App\Controllers;

use App\Controllers\BaseController;
// Usamos query builder directo porque para el login aún no tenemos el active_tenant_id en sesión
class AuthController extends BaseController
{
    public function login()
    {
        if (session()->has('user_id')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login');
    }

    public function authenticate()
    {
        $session = session();
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Buscamos al usuario en TODA la base de datos (por eso no usamos el UserModel aquí)
        $db = \Config\Database::connect();
        $user = $db->table('users')->where('email', $email)->get()->getRowArray();

        if ($user) {
            if ($user['is_active'] != 1) {
                return redirect()->back()->with('error', 'Su usuario está inactivo. Hable con su gerente.');
            }

            if (password_verify($password, $user['password_hash'])) {
                // Obtenemos los datos del hotel para guardarlos en sesión
                $tenant = $db->table('tenants')->where('id', $user['tenant_id'])->get()->getRowArray();

                // Verificamos si la propiedad no está bloqueada ANTES de dejarlo entrar
                if ($tenant['is_suspended'] == 1) {
                    return redirect()->back()->with('error', 'La cuenta de este establecimiento está suspendida.');
                }

                // Creamos el contexto Multi-Tenant
                $ses_data = [
                    'user_id'          => $user['id'],
                    'user_name'        => $user['name'],
                    'user_role'        => $user['role'],
                    'active_tenant_id' => $tenant['id'],
                    'tenant_name'      => $tenant['name'],
                    'currency_symbol'  => $tenant['currency_symbol'], // Útil para las vistas financieras
                    'is_logged_in'     => true
                ];
                $session->set($ses_data);

                // Actualizamos último login
                $db->table('users')->where('id', $user['id'])->update(['last_login_at' => date('Y-m-d H:i:s')]);

                return redirect()->to('/dashboard');
            }
        }

        return redirect()->back()->with('error', 'Credenciales incorrectas.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }

    // Agrega este método en app/Controllers/AuthController.php
    public function processRegister(): \CodeIgniter\HTTP\ResponseInterface
    {
        $db          = \Config\Database::connect();
        $tenantModel = new \App\Models\TenantModel();
        $userModel   = new \App\Models\UserModel();

        $name       = trim($this->request->getPost('name'));
        $hotelName  = trim($this->request->getPost('hotel_name'));
        $email      = trim($this->request->getPost('email'));
        $phone      = trim($this->request->getPost('phone'));
        $city       = trim($this->request->getPost('city'));
        $country    = trim($this->request->getPost('country'));
        $password   = $this->request->getPost('password');

        // Validar email único
        if ($userModel->where('email', $email)->first()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ya existe una cuenta con ese email.'
            ]);
        }

        // Generar slug único para el tenant
        $slug = url_title(strtolower($hotelName), '-', true);
        $base = $slug;
        $i    = 1;
        while ($tenantModel->where('slug', $slug)->first()) {
            $slug = $base . '-' . $i++;
        }

        // Crear tenant
        $tenantId = $tenantModel->insert([
            'name'              => $hotelName,
            'slug'              => $slug,
            'phone'             => $phone,
            'city'              => $city,
            'country'           => $country,
            'timezone'          => 'America/Bogota',
            'currency_code'     => 'COP',
            'currency_symbol'   => '$',
            'checkin_time'      => '15:00:00',
            'checkout_time'     => '12:00:00',
            'is_active'         => 1,
            'onboarding_status' => 'pending',
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        if (!$tenantId) {
            log_message('error', "[Register] Error creando tenant para: {$hotelName}");
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error creando el hotel. Intenta de nuevo.'
            ]);
        }

        // Crear usuario admin
        $userId = $db->table('users')->insert([
            'tenant_id'     => $tenantId,
            'name'          => $name,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'role'          => 'admin',
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        if (!$userId) {
            log_message('error', "[Register] Error creando usuario para tenant {$tenantId}");
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error creando el usuario. Intenta de nuevo.'
            ]);
        }

        // Crear sesión activa — igual que hace tu AuthController::authenticate
        $tenant = $tenantModel->find($tenantId);
        session()->set([
            'active_tenant_id'   => $tenantId,
            'active_tenant_slug' => $slug,
            'tenant_name'        => $hotelName,
            'currency_symbol'    => '$',
            'timezone'           => 'America/Bogota',
            'user_id'            => $db->insertID(),
            'user_name'          => $name,
            'user_role'          => 'admin',
            'tenant_logo'        => null,
        ]);

        log_message('info', "[Register] Nuevo tenant #{$tenantId} ({$hotelName}) registrado.");

        return $this->response->setJSON([
            'success'  => true,
            'message'  => '¡Cuenta creada!',
            'redirect' => '/onboarding',  // directo al wizard
        ]);
    }
}