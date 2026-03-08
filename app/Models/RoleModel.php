<?php
namespace App\Models;

class RoleModel extends BaseMultiTenantModel
{
    protected $table         = 'roles';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'name', 'slug', 'permissions_json', 'is_system'];
}