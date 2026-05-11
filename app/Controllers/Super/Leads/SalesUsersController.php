<?php

namespace App\Controllers\Super\Leads;

use App\Controllers\BaseController;
use App\Models\SalesUserModel;

/**
 * Gestión de vendedores desde /super
 * Solo accesible para super_admins.
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
        return view('super/leads/sales_users/index', [
            'title' => 'Equipo comercial',
            'users' => $this->model->orderBy('is_active','DESC')->orderBy('name','ASC')->findAll(),
        ]);
    }

    public function create()
    {
        return view('super/leads/sales_users/create', ['title'=>'Nuevo vendedor']);
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

        $this->model->insert([
            'name'             => $data['name'],
            'email'            => $data['email'],
            'phone'            => $data['phone'] ?? null,
            'password_hash'    => password_hash($data['password'], PASSWORD_BCRYPT),
            'role'             => $data['role'] ?? 'seller',
            'commission_rate'  => $data['commission_rate'] ?? 0,
            'max_active_leads' => $data['max_active_leads'] ?? 50,
            'accepts_inbound'  => isset($data['accepts_inbound']) ? 1 : 0,
            'is_active'        => 1,
        ]);

        return redirect()->to('/super/sales-users')->with('success','Vendedor creado');
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
