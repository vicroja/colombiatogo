<?php
namespace App\Models;

class AccommodationTypeModel extends BaseMultiTenantModel
{
    protected $table            = 'accommodation_types';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['tenant_id', 'name', 'description', 'base_capacity', 'max_capacity'];
    protected $useTimestamps    = true;
}