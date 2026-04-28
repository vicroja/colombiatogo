<?php
// app/Models/TourGuideModel.php

namespace App\Models;

class TourGuideModel extends BaseMultiTenantModel
{
    protected $table         = 'tour_guides';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tenant_id', 'user_id', 'name', 'phone',
        'document', 'specialty', 'languages', 'is_active',
    ];

    /**
     * Devuelve guías activos del tenant para poblar selects.
     */
    public function getActiveGuides(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }
}