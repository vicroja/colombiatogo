<?php
namespace App\Models;

class ReservationModel extends BaseMultiTenantModel
{
    protected $table         = 'reservations';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'guest_id', 'accommodation_unit_id', 'check_in_date', 'check_out_date', 'status', 'total_price'];
}