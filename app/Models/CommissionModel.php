<?php
namespace App\Models;

class CommissionModel extends BaseMultiTenantModel
{
    protected $table         = 'commissions';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'reservation_id', 'agent_id', 'amount', 'status', 'paid_at'];
}