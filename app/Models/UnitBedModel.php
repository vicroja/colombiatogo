<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitBedModel extends Model
{
    protected $table            = 'unit_beds';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['unit_id', 'bed_type_id', 'quantity'];
    protected $useTimestamps    = false;
}