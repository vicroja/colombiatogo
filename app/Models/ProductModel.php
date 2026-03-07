<?php
namespace App\Models;

class ProductModel extends BaseMultiTenantModel
{
    protected $table         = 'products';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'category_id', 'name', 'description', 'sku', 'unit_price', 'is_available_for_guests', 'is_active'];
}