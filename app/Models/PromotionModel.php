<?php
namespace App\Models;

class PromotionModel extends BaseMultiTenantModel
{
    protected $table         = 'promotions';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'code', 'name', 'discount_type', 'discount_value', 'valid_from', 'valid_until', 'max_uses', 'current_uses', 'is_active'];
}