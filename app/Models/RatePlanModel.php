<?php
namespace App\Models;

class RatePlanModel extends BaseMultiTenantModel
{
    protected $table         = 'rate_plans';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'name', 'description', 'includes_breakfast', 'is_default', 'is_active'];
}