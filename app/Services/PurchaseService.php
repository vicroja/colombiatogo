<?php

namespace App\Services;

use App\Models\PurchaseModel;
use App\Models\PurchaseItemModel;
use App\Models\PurchasePaymentModel;

class PurchaseService
{
    /**
     * Recalcula los subtotales, impuestos, total y pagos de una compra
     */
    public function recalculateTotals($purchaseId)
    {
        $purchaseModel = new PurchaseModel();
        $itemModel = new PurchaseItemModel();
        $paymentModel = new PurchasePaymentModel();

        // Sumar todos los items
        $items = $itemModel->where('purchase_id', $purchaseId)->findAll();
        $subtotal = 0;
        $taxAmount = 0;

        foreach ($items as $item) {
            $subtotal += ($item['quantity'] * $item['unit_cost']);
            $taxAmount += $item['tax_amount'];
        }

        $total = $subtotal + $taxAmount;

        // Sumar todos los pagos
        $payments = $paymentModel->where('purchase_id', $purchaseId)->findAll();
        $amountPaid = array_sum(array_column($payments, 'amount'));

        // Determinar el nuevo estado
        $status = 'draft';
        if (count($items) > 0) {
            if ($amountPaid >= $total && $total > 0) {
                $status = 'paid';
            } elseif ($amountPaid > 0) {
                $status = 'partial';
            } else {
                $status = 'pending';
            }
        }

        // Actualizar la compra
        $purchaseModel->update($purchaseId, [
            'subtotal'    => $subtotal,
            'tax_amount'  => $taxAmount,
            'total'       => $total,
            'amount_paid' => $amountPaid,
            'status'      => $status
        ]);
    }
}