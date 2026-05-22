<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo Base Multi-Tenant - Versión endurecida.
 *
 * Aplica el filtro tenant_id automáticamente en CADA operación
 * usando los callbacks de CI4. Es fail-closed: si no hay sesión
 * de tenant, las queries devuelven vacío en vez de exponer datos
 * de otros tenants.
 *
 * Para saltar el filtro en contextos legítimos (cron, super-admin,
 * jobs en cola), usa el método ->withoutTenantScope() explícitamente.
 */
abstract class BaseMultiTenantModel extends Model
{
    protected $useTimestamps = true;

    /**
     * Flag para saltar el filtro en operaciones administrativas.
     * Se activa con ->withoutTenantScope() y se resetea tras la query.
     */
    protected bool $skipTenantScope = false;

    /**
     * Callbacks registrados en CI4.
     */
    protected $beforeFind   = ['injectTenantWhere'];
    protected $beforeInsert = ['injectTenantOnInsert'];
    protected $beforeUpdate = ['injectTenantOnUpdate'];
    protected $beforeDelete = ['injectTenantOnDelete'];

    /**
     * Inyecta WHERE tenant_id = X en cada SELECT.
     */
    protected function injectTenantWhere(array $data)
    {
        if ($this->skipTenantScope) {
            $this->skipTenantScope = false;
            return $data;
        }

        $tenantId = $this->getActiveTenantId();

        if ($tenantId === null) {
            // FAIL-CLOSED: forzamos un WHERE imposible para que no devuelva nada
            $this->builder()->where('1 = 0');
            return $data;
        }

        $this->builder()->where($this->table . '.tenant_id', $tenantId);
        return $data;
    }

    /**
     * Inyecta tenant_id automáticamente en cada INSERT.
     */
    protected function injectTenantOnInsert(array $data)
    {
        if ($this->skipTenantScope) {
            $this->skipTenantScope = false;
            return $data;
        }

        $tenantId = $this->getActiveTenantId();

        // Si ya viene tenant_id en el insert, lo validamos contra la sesión
        if (isset($data['data']['tenant_id'])) {
            if ($tenantId !== null && (int)$data['data']['tenant_id'] !== (int)$tenantId) {
                throw new \RuntimeException(
                    'Intento de insertar registro con tenant_id diferente al de la sesión.'
                );
            }
            return $data;
        }

        if ($tenantId === null) {
            throw new \RuntimeException(
                'No se puede insertar en ' . $this->table . ' sin un tenant activo en sesión.'
            );
        }

        $data['data']['tenant_id'] = $tenantId;
        return $data;
    }

    /**
     * Restringe los UPDATE al tenant actual.
     */
    protected function injectTenantOnUpdate(array $data)
    {
        if ($this->skipTenantScope) {
            $this->skipTenantScope = false;
            return $data;
        }

        $tenantId = $this->getActiveTenantId();

        if ($tenantId === null) {
            $this->builder()->where('1 = 0');
            return $data;
        }

        $this->builder()->where($this->table . '.tenant_id', $tenantId);

        // Si vienen tenant_id en el set, no permitimos cambiarlo
        if (isset($data['data']['tenant_id'])) {
            unset($data['data']['tenant_id']);
        }

        return $data;
    }

    /**
     * Restringe los DELETE al tenant actual.
     */
    protected function injectTenantOnDelete(array $data)
    {
        if ($this->skipTenantScope) {
            $this->skipTenantScope = false;
            return $data;
        }

        $tenantId = $this->getActiveTenantId();

        if ($tenantId === null) {
            $this->builder()->where('1 = 0');
            return $data;
        }

        $this->builder()->where($this->table . '.tenant_id', $tenantId);
        return $data;
    }

    /**
     * Permite ejecutar UNA operación sin el filtro de tenant.
     * Uso: $model->withoutTenantScope()->findAll();
     *
     * ¡Cuidado! Solo debe usarse en contextos confiables:
     * - Comandos CLI / cron
     * - Controladores de super-admin
     * - Jobs en cola
     */
    public function withoutTenantScope(): self
    {
        $this->skipTenantScope = true;
        return $this;
    }

    /**
     * Resuelve el tenant activo. Por defecto desde sesión,
     * pero permite override para CLI / jobs.
     */
    protected function getActiveTenantId(): ?int
    {
        // En CLI no hay sesión, retornamos null (fail-closed automático)
        if (PHP_SAPI === 'cli') {
            return null;
        }

        $id = session('active_tenant_id');
        return $id ? (int)$id : null;
    }

    /**
     * Helper legacy para compatibilidad. Ahora es redundante porque
     * el insert ya inyecta tenant_id, pero lo mantenemos para no romper código.
     */
    public function createForTenant(array $data)
    {
        return $this->insert($data);
    }
}