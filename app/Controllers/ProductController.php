<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\ProductCategoryModel;

class ProductController extends BaseController
{
    public function index()
    {
        $catModel = new ProductCategoryModel();
        $prodModel = new ProductModel();

        // Si no hay categorías, creamos un par por defecto para ayudar al usuario
        if ($catModel->countAllResults() == 0) {
            $catModel->createForTenant(['name' => 'Minibar & Snacks', 'type' => 'product']);
            $catModel->createForTenant(['name' => 'Servicios Adicionales', 'type' => 'service']);
        }

        $categories = $catModel->findAll();

        // Traemos los productos con el nombre de su categoría usando un JOIN manual simple
        $products = $prodModel->select('products.*, product_categories.name as category_name')
            ->join('product_categories', 'product_categories.id = products.category_id')
            ->orderBy('category_name', 'ASC')
            ->findAll();

        return view('products/index', [
            'categories' => $categories,
            'products'   => $products
        ]);
    }

    public function storeCategory()
    {
        $catModel = new ProductCategoryModel();
        $catModel->createForTenant([
            'name' => $this->request->getPost('name'),
            'type' => $this->request->getPost('type')
        ]);
        return redirect()->to('/products')->with('success', 'Categoría creada.');
    }

    public function storeProduct()
    {
        $prodModel = new ProductModel();
        $prodModel->createForTenant([
            'category_id'             => $this->request->getPost('category_id'),
            'name'                    => $this->request->getPost('name'),
            'unit_price'              => $this->request->getPost('unit_price'),
            'sku'                     => $this->request->getPost('sku'),
            'is_available_for_guests' => 1
        ]);
        return redirect()->to('/products')->with('success', 'Producto/Servicio agregado al catálogo.');
    }
}