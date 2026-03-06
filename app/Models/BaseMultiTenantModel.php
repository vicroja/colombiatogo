<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo Base Multi-Tenant
 * Aplica automáticamente el filtro de tenant_id a todas las consultas.
 */
abstract class BaseMultiTenantModel extends Model
{
    protected $useTimestamps = true;

    /**
     * Este método se ejecuta automáticamente al inicializar el modelo.
     * Inyecta la condición WHERE tenant_id = X en cada query.
     */
    protected function initialize()
    {
        // Obtenemos el ID del tenant activo desde la sesión
        $tenantId = session('active_tenant_id');

        if ($tenantId) {
            // Aplicamos el filtro usando el nombre de la tabla actual para evitar ambigüedades en JOINs
            $this->where($this->table . '.tenant_id', $tenantId);
        }
    }

    /**
     * Método auxiliar para insertar datos asegurando que se asigne al tenant correcto.
     */
    public function createForTenant(array $data)
    {
        $data['tenant_id'] = session('active_tenant_id');
        return $this->insert($data);
    }
}

