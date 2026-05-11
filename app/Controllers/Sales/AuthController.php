<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Models\SalesUserModel;

/**
 * Auth de vendedores (URL: /sales/login)
 */
class AuthController extends BaseController
{
    public function login()
    {
        if (session()->has('sales_user_id')) {
            return redirect()->to('/sales/dashboard');
        }
        return view('sales/auth/login');
    }

    public function authenticate()
    {
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $model    = new SalesUserModel();

        $user = $model->where('email', $email)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'Credenciales incorrectas.');
        }
        if (!$user['is_active']) {
            return redirect()->back()->with('error', 'Tu usuario está inactivo. Habla con tu gerente.');
        }
        if (!password_verify($password, $user['password_hash'])) {
            return redirect()->back()->with('error', 'Credenciales incorrectas.');
        }

        session()->set([
            'sales_user_id'    => $user['id'],
            'sales_user_name'  => $user['name'],
            'sales_user_role'  => $user['role'],
            'sales_user_email' => $user['email'],
            'sales_logged_in'  => true,
        ]);

        $model->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        return redirect()->to('/sales/dashboard');
    }

    public function logout()
    {
        // Limpiamos solo las claves de la sesión de sales para no afectar otras
        $keys = ['sales_user_id','sales_user_name','sales_user_role','sales_user_email','sales_logged_in'];
        foreach ($keys as $k) session()->remove($k);
        return redirect()->to('/sales/login');
    }
}
