<?php
namespace App\Models;

class DashboardKpiCacheModel extends BaseMultiTenantModel
{
    protected $table         = 'dashboard_kpi_cache';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'kpi_key', 'value_json', 'period_start', 'period_end', 'calculated_at', 'expires_at'];
}