<?php

namespace App\Controllers;

use App\Models\PurchaseModel;
use App\Models\SupplierModel;
use App\Models\ProductModel;
use App\Models\PurchaseItemModel;
use App\Models\PurchasePaymentModel;
use App\Services\PurchaseService;

class PurchaseController extends BaseController
{
    // Listado de compras
    public function index()
    {
        $purchaseModel = new PurchaseModel();
        $supplierModel = new SupplierModel();

        $purchases = $purchaseModel->select('purchases.*, suppliers.name as supplier_name')
            ->join('suppliers', 'suppliers.id = purchases.supplier_id', 'left')
            ->orderBy('purchases.purchase_date', 'DESC')
            ->findAll();

        $suppliers = $supplierModel->where('is_active', 1)->findAll();

        return view('purchases/index', [
            'purchases' => $purchases,
            'suppliers' => $suppliers
        ]);
    }

    // Crear la "cabecera" de la factura
    public function store()
    {
        $purchaseModel = new PurchaseModel();

        $purchaseId = $purchaseModel->insert([
            'tenant_id'        => session('active_tenant_id'),
            'supplier_id'      => $this->request->getPost('supplier_id'),
            'reference_number' => $this->request->getPost('reference_number'),
            'purchase_date'    => $this->request->getPost('purchase_date'),
            'status'           => 'draft',
            'created_by'       => session('user_id')
        ]);

        return redirect()->to("/purchases/show/{$purchaseId}")->with('success', 'Factura creada. Ahora agrega los productos.');
    }

    // Mostrar detalle, ítems y pagos
    public function show($id)
    {
        $purchaseModel = new PurchaseModel();
        $itemModel = new PurchaseItemModel();
        $paymentModel = new PurchasePaymentModel();
        $productModel = new ProductModel();

        $purchase = $purchaseModel->select('purchases.*, suppliers.name as supplier_name, suppliers.tax_id')
            ->join('suppliers', 'suppliers.id = purchases.supplier_id', 'left')
            ->find($id);

        if (!$purchase) return redirect()->to('/purchases')->with('error', 'Compra no encontrada.');

        $items = $itemModel->where('purchase_id', $id)->findAll();
        $payments = $paymentModel->where('purchase_id', $id)->findAll();
        $products = $productModel->where('is_active', 1)->findAll();

        return view('purchases/show', [
            'purchase' => $purchase,
            'items'    => $items,
            'payments' => $payments,
            'products' => $products
        ]);
    }

    // Agregar un producto/gasto a la factura
    public function addItem($id)
    {
        $itemModel = new PurchaseItemModel();
        $productModel = new ProductModel();
        $purchaseService = new PurchaseService();

        $productId = $this->request->getPost('product_id');
        $quantity = $this->request->getPost('quantity');
        $unitCost = $this->request->getPost('unit_cost');

        $product = $productId ? $productModel->find($productId) : null;
        $description = $product ? $product['name'] : $this->request->getPost('description');
        $taxRate = $product ? $product['tax_rate'] : 0; // Para MVP simplificamos impuestos

        $subtotal = $quantity * $unitCost;
        $taxAmount = $subtotal * ($taxRate / 100);

        $itemModel->insert([
            'purchase_id' => $id,
            'product_id'  => $productId ?: null,
            'description' => $description,
            'quantity'    => $quantity,
            'unit_cost'   => $unitCost,
            'tax_rate'    => $taxRate,
            'tax_amount'  => $taxAmount,
            'subtotal'    => $subtotal
        ]);

        $purchaseService->recalculateTotals($id);

        return redirect()->back()->with('success', 'Ítem agregado a la compra.');
    }

    // Eliminar ítem
    public function deleteItem($id, $itemId)
    {
        $itemModel = new PurchaseItemModel();
        $purchaseService = new PurchaseService();

        $itemModel->delete($itemId);
        $purchaseService->recalculateTotals($id);

        return redirect()->back()->with('success', 'Ítem eliminado.');
    }

    // Registrar pago
    public function addPayment($id)
    {
        $paymentModel = new PurchasePaymentModel();
        $purchaseService = new PurchaseService();

        $paymentModel->insert([
            'purchase_id'    => $id,
            'amount'         => $this->request->getPost('amount'),
            'payment_method' => $this->request->getPost('payment_method'),
            'payment_date'   => $this->request->getPost('payment_date'),
            'reference'      => $this->request->getPost('reference'),
            'created_by'     => session('user_id')
        ]);

        $purchaseService->recalculateTotals($id);

        return redirect()->back()->with('success', 'Pago registrado correctamente.');
    }
}