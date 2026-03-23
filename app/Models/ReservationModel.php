<?php
namespace App\Models;

class ReservationModel extends BaseMultiTenantModel
{
    protected $table         = 'reservations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    // Añadimos 'num_guests' a los allowedFields
    protected $allowedFields    = [
        'tenant_id',
        'guest_id',
        'source_id',
        'accommodation_unit_id',
        'num_guests', // <- NUEVO CAMPO AÑADIDO
        'check_in_date',
        'check_out_date',
        'status',
        'total_price'
    ];

    // Dates
    protected $useTimestamps = true;
}