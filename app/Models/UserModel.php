<?php

namespace App\Models;

// Extendemos del modelo base para heredar el aislamiento de datos
class UserModel extends BaseMultiTenantModel
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields    = [
        'tenant_id', 'name', 'email', 'password_hash',
        'role', 'is_active', 'last_login_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}