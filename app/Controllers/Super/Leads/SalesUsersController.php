<?php

namespace App\Controllers\Super\Leads;

use App\Controllers\BaseController;
use App\Models\SalesUserModel;

/**
 * SalesUsersController — V2 con jerarquía manager/seller y override.
 * REEMPLAZA app/Controllers/Super/Leads/SalesUsersController.php
 */
class SalesUsersController extends BaseController
{
    protected SalesUserModel $model;

    public function __construct()
    {
        $this->model = new SalesUserModel();
    }

    public function index()
    {
        // Trae cada usuario con el nombre de su gerente si tiene
        $db = \Config\Database::connect();
        $users = $db->table('sales_users u')
            ->select('u.*, m.name as manager_name')
            ->join('sales_users m', 'm.id = u.manager_id', 'left')
            ->orderBy('u.is_active', 'DESC')
            ->orderBy('u.role', 'ASC')
            ->orderBy('u.name', 'ASC')
            ->get()->getResultArray();

        return view('super/leads/sales_users/index', [
            'title' => 'Equipo comercial',
            'users' => $users,
        ]);
    }

    public function create()
    {
        return view('super/leads/sales_users/create', [
            'title'    => 'Nuevo vendedor',
            'managers' => $this->model->getActiveManagers(),
        ]);
    }

    public function store()
    {
        $data = $this->request->getPost();

        if (empty($data['password']) || strlen($data['password']) < 6) {
            return redirect()->back()->withInput()->with('error','Password mínimo 6 caracteres');
        }
        if ($this->model->where('email', $data['email'])->first()) {
            return redirect()->back()->withInput()->with('error','Email ya registrado');
        }

        // Validación: si es seller con manager_id, verificar que existe y es manager
        $managerId = !empty($data['manager_id']) ? (int)$data['manager_id'] : null;
        if ($data['role'] === 'seller' && $managerId) {
            $mgr = $this->model->find($managerId);
            if (!$mgr || $mgr['role'] !== 'manager') {
                return redirect()->back()->withInput()->with('error','El gerente seleccionado no es válido');
            }
        }
        // Un manager NO puede tener manager_id
        if ($data['role'] === 'manager') {
            $managerId = null;
        }

        $this->model->insert([
            'name'             => $data['name'],
            'email'            => $data['email'],
            'phone'            => $data['phone'] ?? null,
            'password_hash'    => password_hash($data['password'], PASSWORD_BCRYPT),
            'role'             => $data['role'] ?? 'seller',
            'manager_id'       => $managerId,
            'commission_rate'  => $data['commission_rate'] ?? 0,
            'override_rate'    => ($data['role'] === 'manager') ? ($data['override_rate'] ?? 0) : 0,
            'max_active_leads' => $data['max_active_leads'] ?? 50,
            'accepts_inbound'  => isset($data['accepts_inbound']) ? 1 : 0,
            'is_active'        => 1,
        ]);

        return redirect()->to('/super/sales-users')->with('success','Vendedor creado');
    }

    public function edit(int $id)
    {
        $user = $this->model->find($id);
        if (!$user) return redirect()->to('/super/sales-users')->with('error','No encontrado');

        return view('super/leads/sales_users/edit', [
            'title'    => 'Editar: '.$user['name'],
            'user'     => $user,
            'managers' => $this->model->getActiveManagers(),
        ]);
    }

    public function update(int $id)
    {
        $user = $this->model->find($id);
        if (!$user) return redirect()->to('/super/sales-users')->with('error','No encontrado');

        $data = $this->request->getPost();
        $managerId = !empty($data['manager_id']) ? (int)$data['manager_id'] : null;

        // Evitar auto-referencia y que un manager tenga manager
        if ($data['role'] === 'manager') $managerId = null;
        if ($managerId === $id) $managerId = null;

        $update = [
            'name'             => $data['name'],
            'phone'            => $data['phone'] ?? null,
            'role'             => $data['role'],
            'manager_id'       => $managerId,
            'commission_rate'  => $data['commission_rate'] ?? 0,
            'override_rate'    => ($data['role'] === 'manager') ? ($data['override_rate'] ?? 0) : 0,
            'max_active_leads' => $data['max_active_leads'] ?? 50,
            'accepts_inbound'  => isset($data['accepts_inbound']) ? 1 : 0,
        ];

        // Solo cambia password si lo escriben
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                return redirect()->back()->withInput()->with('error','Password mínimo 6 caracteres');
            }
            $update['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $this->model->update($id, $update);
        return redirect()->to('/super/sales-users')->with('success','Vendedor actualizado');
    }

    public function toggle(int $id)
    {
        $u = $this->model->find($id);
        if ($u) {
            $this->model->update($id, ['is_active' => $u['is_active'] ? 0 : 1]);
        }
        return redirect()->back();
    }
}
