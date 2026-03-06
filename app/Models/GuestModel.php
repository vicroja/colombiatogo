<?php
namespace App\Models;

class GuestModel extends BaseMultiTenantModel
{
    protected $table         = 'guests';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'full_name', 'document', 'email', 'phone'];
}