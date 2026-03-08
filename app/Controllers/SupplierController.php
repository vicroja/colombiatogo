<?php

namespace App\Controllers;

use App\Models\SupplierModel;

class SupplierController extends BaseController
{
    public function index()
    {
        $supplierModel = new SupplierModel();
        $suppliers = $supplierModel->orderBy('name', 'ASC')->findAll();

        return view('suppliers/index', [
            'suppliers' => $suppliers
        ]);
    }

    public function store()
    {
        $supplierModel = new SupplierModel();

        $supplierModel->createForTenant([
            'name'         => $this->request->getPost('name'),
            'tax_id'       => $this->request->getPost('tax_id'),
            'contact_name' => $this->request->getPost('contact_name'),
            'phone'        => $this->request->getPost('phone'),
            'email'        => $this->request->getPost('email'),
            'is_active'    => 1
        ]);

        return redirect()->to('/suppliers')->with('success', 'Proveedor registrado exitosamente.');
    }
}