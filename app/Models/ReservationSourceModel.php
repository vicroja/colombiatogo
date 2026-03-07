<?php
namespace App\Models;

class ReservationSourceModel extends BaseMultiTenantModel
{
    protected $table         = 'reservation_sources';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'name', 'color', 'is_active'];
}