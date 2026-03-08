<?php

namespace App\Controllers;

use App\Models\PromotionModel;

class PromotionController extends BaseController
{
    public function index()
    {
        $promoModel = new PromotionModel();
        $promotions = $promoModel->orderBy('created_at', 'DESC')->findAll();

        return view('promotions/index', [
            'promotions' => $promotions
        ]);
    }

    public function store()
    {
        $promoModel = new PromotionModel();

        $code = strtoupper(trim($this->request->getPost('code')));

        // Evitar códigos duplicados en el mismo hotel
        $exists = $promoModel->where('code', $code)->first();
        if ($exists) {
            return redirect()->back()->with('error', 'Ese código de descuento ya existe.');
        }

        $promoModel->createForTenant([
            'code'           => $code,
            'name'           => $this->request->getPost('name'),
            'discount_type'  => $this->request->getPost('discount_type'),
            'discount_value' => $this->request->getPost('discount_value'),
            'valid_from'     => $this->request->getPost('valid_from'),
            'valid_until'    => $this->request->getPost('valid_until'),
            'max_uses'       => $this->request->getPost('max_uses') ?: 0,
            'is_active'      => 1
        ]);

        return redirect()->to('/promotions')->with('success', 'Cupón promocional creado con éxito.');
    }

    public function delete($id)
    {
        $promoModel = new PromotionModel();
        $promoModel->delete($id);
        return redirect()->to('/promotions')->with('success', 'Cupón eliminado.');
    }
}