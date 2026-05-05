<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * TenantMediaModel
 *
 * Gestiona los archivos/documentos adjuntos a un tenant.
 * Usada por SettingsController y (futura Etapa 2) por el asistente virtual.
 */
class TenantMediaModel extends Model
{
    protected $table            = 'tenant_media';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'tenant_id',
        'entity_type',
        'entity_id',
        'file_path',
        'file_type',    // 'image' | 'pdf'
        'tag',          // 'rut' | 'cuenta_bancaria' | 'seguro' | 'contrato' | 'otro'
        'description',
        'is_main',
        'sort_order',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // ── Etiquetas legibles para la UI y el asistente ───────────────
    public static array $tags = [
        'rut'             => 'RUT',
        'cuenta_bancaria' => 'Cuenta Bancaria',
        'seguro'          => 'Seguro',
        'contrato'        => 'Contrato',
        'otro'            => 'Otro',
    ];

    /**
     * Devuelve todos los documentos de un tenant (entity_type = 'tenant').
     */
    public function getByTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)
            ->where('entity_type', 'tenant')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Busca documentos de un tenant por tag específico.
     * Útil para el asistente virtual en Etapa 2.
     *
     * Ejemplo: $model->getByTag($tenantId, 'rut')
     */
    public function getByTag(int $tenantId, string $tag): array
    {
        return $this->where('tenant_id', $tenantId)
            ->where('entity_type', 'tenant')
            ->where('tag', $tag)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}