<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;

class UserController extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $roleModel = new RoleModel();

        // 1. Crear roles por defecto si no existen
        if ($roleModel->countAllResults() == 0) {
            $roleModel->createForTenant(['name' => 'Administrador', 'slug' => 'admin', 'is_system' => 1]);
            $roleModel->createForTenant(['name' => 'Recepcionista', 'slug' => 'receptionist', 'is_system' => 1]);
            $roleModel->createForTenant(['name' => 'Mantenimiento', 'slug' => 'maintenance', 'is_system' => 1]);
        }

        $roles = $roleModel->findAll();

        // 2. Traer a todos los empleados del hotel actual (tenant_id)
        $users = $userModel->select('users.*, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->where('users.tenant_id', session('active_tenant_id'))
            ->findAll();

        return view('users/index', [
            'users' => $users,
            'roles' => $roles
        ]);
    }

    public function store()
    {
        $userModel = new UserModel();

        // Validación básica de email único
        $existing = $userModel->where('email', $this->request->getPost('email'))->first();
        if ($existing) {
            return redirect()->back()->with('error', 'El correo electrónico ya está registrado.');
        }

        $userModel->insert([
            'tenant_id'     => session('active_tenant_id'),
            'role_id'       => $this->request->getPost('role_id'),
            'name'          => $this->request->getPost('name'),
            'email'         => $this->request->getPost('email'),
            // Ciframos la contraseña por seguridad
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'is_active'     => 1
        ]);

        return redirect()->to('/users')->with('success', 'Cuenta de empleado creada exitosamente.');
    }

    public function delete($id)
    {
        $userModel = new UserModel();

        // Evitar que el administrador se borre a sí mismo
        if ($id == session('user_id')) {
            return redirect()->back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $userModel->delete($id);
        return redirect()->to('/users')->with('success', 'Empleado eliminado.');
    }
}