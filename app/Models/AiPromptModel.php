<?php

namespace App\Models;

/**
 * AiPromptModel
 *
 * Gestiona los prompts del asistente IA por tenant.
 * Extiende BaseMultiTenantModel para filtrado automático por tenant_id.
 * Tabla: ai_prompts
 */
class AiPromptModel extends BaseMultiTenantModel
{
    protected $table          = 'ai_prompts';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields  = [
        'tenant_id',
        'profile_role',
        'model_version',
        'system_instruction',
        'tools_schema_json',
    ];

    protected $useTimestamps  = true;
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';

    protected $validationRules = [
        'tenant_id'          => 'required|integer',
        'profile_role'       => 'required|max_length[50]',
        'system_instruction' => 'required',
        'model_version'      => 'required|max_length[50]',
    ];
}