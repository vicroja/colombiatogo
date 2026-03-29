<?php
namespace App\Models;

class UnitRateModel extends BaseMultiTenantModel
{
    protected $table         = 'unit_rates';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'unit_id', 'rate_plan_id', 'price_per_night', 'extra_person_price', 'extra_child_price', 'min_nights', 'is_active'];
}