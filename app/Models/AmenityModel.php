<?php

namespace App\Models;

use CodeIgniter\Model;

class AmenityModel extends Model
{
    protected $table            = 'amenities';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    // Campos permitidos para inserción/actualización
    protected $allowedFields    = ['tenant_id', 'name', 'category', 'icon'];

    // Timestamps automáticos
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Retorna amenidades globales + las del tenant activo.
     */
    public function getForTenant(int $tenantId): array
    {
        return $this->where('tenant_id', 0)
            ->orWhere('tenant_id', $tenantId)
            ->orderBy('category', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }
}