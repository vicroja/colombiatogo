<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantSubscriptionModel extends Model
{
    protected $table            = 'tenant_subscriptions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    // Campos para controlar la suscripción activa
    protected $allowedFields    = [
        'tenant_id', 'plan_id', 'status', 'started_at', 'trial_ends_at',
        'current_period_start', 'current_period_end', 'grace_period_days',
        'suspended_at', 'cancelled_at', 'cancellation_reason', 'notes', 'created_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

