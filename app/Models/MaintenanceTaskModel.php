<?php
namespace App\Models;

class MaintenanceTaskModel extends BaseMultiTenantModel
{
    protected $table         = 'maintenance_tasks';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tenant_id', 'unit_id', 'title', 'description', 'priority', 'status', 'blocks_unit', 'scheduled_date', 'reminder_sent_at'];
}