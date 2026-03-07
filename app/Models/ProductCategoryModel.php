<?php
namespace App\Models;

class ProductCategoryModel extends BaseMultiTenantModel
{
    protected $table         = 'product_categories';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'name', 'type', 'is_active'];
}