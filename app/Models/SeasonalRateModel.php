<?php
namespace App\Models;

class SeasonalRateModel extends BaseMultiTenantModel
{
    protected $table         = 'seasonal_rates';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'unit_id', 'rate_plan_id', 'name', 'start_date', 'end_date', 'modifier_type', 'modifier_value', 'priority', 'is_active'];
}