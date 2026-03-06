<?php

namespace App\Models;

class PaymentModel extends BaseMultiTenantModel
{
    protected $table         = 'payments';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'reservation_id', 'amount', 'payment_method', 'reference'];
}