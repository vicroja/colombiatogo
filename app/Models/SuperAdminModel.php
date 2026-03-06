<?php

namespace App\Models;

use CodeIgniter\Model;

class SuperAdminModel extends Model
{
    protected $table            = 'super_admins';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    // Campos permitidos para interactuar con la base de datos
    protected $allowedFields    = [
        'name',
        'email',
        'password_hash',
        'is_active',
        'last_login_at'
    ];

    // Manejo automático de fechas de creación y actualización
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}