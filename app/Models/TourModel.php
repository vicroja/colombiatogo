<?php
// app/Models/TourModel.php

namespace App\Models;

class TourModel extends BaseMultiTenantModel
{
    protected $table         = 'tours';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tenant_id', 'category_id', 'name', 'description',
        'duration_minutes', 'meeting_point', 'min_pax',
        'price_adult', 'price_child', 'cancellation_policy',
        'difficulty_level', 'included_json', 'excluded_json',
        'media_json', 'is_active',
    ];

    /**
     * Devuelve todos los tours activos del tenant con su categoría.
     * Usado en listados y en el selector de reserva.
     */
    public function getActiveTours(int $tenantId): array
    {
        return $this->select('tours.*, product_categories.name AS category_name')
            ->join('product_categories', 'product_categories.id = tours.category_id', 'left')
            ->where('tours.tenant_id', $tenantId)
            ->where('tours.is_active', 1)
            ->orderBy('tours.name', 'ASC')
            ->findAll();
    }
}