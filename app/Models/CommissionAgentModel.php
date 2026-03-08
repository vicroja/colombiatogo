<?php
namespace App\Models;

class CommissionAgentModel extends BaseMultiTenantModel
{
    protected $table         = 'commission_agents';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'name', 'contact_info', 'bank_details', 'commission_type', 'commission_value', 'tracking_code', 'is_active'];
}