<?php

namespace App\Models;

use CodeIgniter\Model;

class SubscriptionPlanModel extends Model
{
    protected $table            = 'subscription_plans';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    // Todos los campos de la tabla de planes
    protected $allowedFields    = [
        'name', 'slug', 'description', 'price', 'currency',
        'billing_cycle', 'trial_days', 'limits_json',
        'is_public', 'is_active', 'sort_order', 'color'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}