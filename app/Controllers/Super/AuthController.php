<?php

namespace App\Controllers\Super;

use App\Controllers\BaseController;
use App\Models\SuperAdminModel; // <- ESTA LÍNEA ES VITAL PARA QUE ENCUENTRE EL MODELO
class AuthController extends BaseController
{
    // Muestra el formulario de login
    public function login()
    {
        // Si ya tiene sesión, lo enviamos directo al dashboard
        if (session()->has('superadmin_id')) {
            return redirect()->to('/super/dashboard');
        }

        return view('super/auth/login');
    }

    // Procesa los datos del formulario
    public function authenticate()
    {
        $session = session();
        $model = new SuperAdminModel();

        // Obtenemos los datos del formulario
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Buscamos al super admin por su correo
        $admin = $model->where('email', $email)->first();

        if ($admin) {
            // Verificamos si la cuenta está activa
            if ($admin['is_active'] != 1) {
                return redirect()->back()->with('error', 'Esta cuenta está desactivada.');
            }

            // Verificamos el hash de la contraseña
            if (password_verify($password, $admin['password_hash'])) {
                // Credenciales correctas: Creamos la sesión aislada
                $ses_data = [
                    'superadmin_id'    => $admin['id'],
                    'superadmin_name'  => $admin['name'],
                    'superadmin_email' => $admin['email'],
                    'is_impersonating' => false, // Para uso futuro en soporte técnico
                    'logged_in'        => true
                ];
                $session->set($ses_data);

                // Actualizamos la fecha de último login
                $model->update($admin['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

                return redirect()->to('/super/dashboard');
            }
        }

        // Si llegamos aquí, el usuario o la clave son incorrectos
        return redirect()->back()->with('error', 'Credenciales incorrectas.');
    }

    // Destruye la sesión
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/super/login');
    }
}

