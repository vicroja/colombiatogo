<?php

namespace App\Models;

use CodeIgniter\Model;

class BedTypeModel extends Model
{
    protected $table            = 'bed_types';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields    = ['tenant_id', 'name', 'base_capacity'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}