<?php
namespace App\Models;

class ReservationConsumptionModel extends BaseMultiTenantModel
{
    protected $table         = 'reservation_consumptions';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'reservation_id', 'product_id', 'description', 'quantity', 'unit_price', 'subtotal'];
}