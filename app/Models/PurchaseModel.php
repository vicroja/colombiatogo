<?php
namespace App\Models;

class PurchaseModel extends BaseMultiTenantModel
{
    protected $table         = 'purchases';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'supplier_id', 'reference_number', 'purchase_date', 'subtotal', 'tax_amount', 'total', 'amount_paid', 'status', 'notes', 'created_by'];
}