<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitAmenityModel extends Model
{
    protected $table            = 'unit_amenities';
    // Tabla pivote, la llave primaria compuesta se maneja manualmente o omitiendo $primaryKey
    protected $protectFields    = true;
    protected $allowedFields    = ['unit_id', 'amenity_id'];
    protected $useTimestamps    = false; // Las tablas pivote por lo general no llevan timestamps
}