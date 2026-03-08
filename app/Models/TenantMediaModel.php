<?php
namespace App\Models;

class TenantMediaModel extends BaseMultiTenantModel
{
    protected $table         = 'tenant_media';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'entity_type', 'entity_id', 'file_path', 'file_type', 'is_main', 'sort_order', 'description',];
}