<?php
namespace App\Models;

class AccommodationUnitModel extends BaseMultiTenantModel
{
    protected $table            = 'accommodation_units';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['tenant_id', 'type_id', 'name', 'status'];
    protected $useTimestamps    = true;
}