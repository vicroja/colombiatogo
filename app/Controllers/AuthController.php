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
}