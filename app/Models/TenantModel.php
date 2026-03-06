<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantModel extends Model
{
    protected $table            = 'tenants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    // Campos que permitimos guardar desde el formulario o el código
    protected $allowedFields    = [
        'name', 'slug', 'logo_path', 'address', 'city', 'country',
        'phone', 'email', 'website', 'timezone', 'currency_code',
        'currency_symbol', 'checkin_time', 'checkout_time', 'settings_json',
        'is_active', 'onboarding_status', 'is_suspended',
        'suspended_reason', 'trial_ends_at', 'current_plan_slug'
    ];

    // Manejo automático de created_at y updated_at
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}