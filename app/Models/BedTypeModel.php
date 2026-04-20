<?php

namespace App\Models;

use CodeIgniter\Model;

class BedTypeModel extends Model
{
    protected $table            = 'bed_types';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields    = ['tenant_id', 'name', 'base_capacity'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    /**
     * Retorna tipos de cama globales (tenant_id=0)
     * más los personalizados del tenant activo.
     * Globales primero, luego propios ordenados por nombre.
     */
    public function getForTenant(int $tenantId): array
    {
        return $this->where('tenant_id', 0)
            ->orWhere('tenant_id', $tenantId)
            ->orderBy('tenant_id', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }
}