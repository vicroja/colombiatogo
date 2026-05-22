<?php

namespace App\Models;

class ImportStagingModel extends BaseMultiTenantModel
{
    protected $table         = 'import_staging';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected $allowedFields = [
        'tenant_id', 'created_by',
        'source_type', 'source_reference', 'status',
        'detected_vertical',
        'extracted_json', 'edited_json',
        'tokens_used', 'cost_usd', 'error_message',
        'imported_at', 'import_summary_json',
    ];

    public function pendingForTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'extracted', 'reviewed'])
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function latestForTenant(int $tenantId): ?array
    {
        $r = $this->where('tenant_id', $tenantId)
            ->orderBy('id', 'DESC')
            ->first();
        return $r ?: null;
    }
}