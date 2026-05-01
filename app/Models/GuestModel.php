<?php
// app/Models/GuestModel.php

namespace App\Models;

class GuestModel extends BaseMultiTenantModel
{
    protected $table         = 'guests';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tenant_id', 'full_name', 'document', 'email', 'phone',
        'ai_active', 'chat_state', 'funnel_stage', 'conversation_context_json',
    ];

    protected $validationRules = [
        'full_name' => 'required|min_length[3]|max_length[150]',
        'phone'     => 'permit_empty|max_length[50]',
        'email'     => 'permit_empty|valid_email|max_length[150]',
        'document'  => 'permit_empty|max_length[50]',
    ];
}