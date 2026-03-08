<?php
namespace App\Models;

class SupplierModel extends BaseMultiTenantModel
{
    protected $table         = 'suppliers';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'name', 'trade_name', 'tax_id', 'contact_name', 'email', 'phone', 'address', 'city', 'country', 'payment_terms', 'notes', 'is_active'];
}