<?php

namespace App\Models;

class PaymentModel extends BaseMultiTenantModel
{
    protected $table         = 'payments';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'tenant_id', 'reservation_id', 'entity_type',  // ← agregar entity_type
        'amount', 'payment_method', 'reference',
        'bank_name', 'receipt_date', 'ocr_raw_data', 'attachment_path'
    ];

    /**
     * Obtiene pagos filtrando por entidad (reserva de hotel o tour).
     * Necesario porque reservation_id es ahora polimórfico.
     */
    public function getByEntity(int $entityId, string $entityType = 'reservation'): array
    {
        return $this->where('reservation_id', $entityId)
            ->where('entity_type', $entityType)
            ->findAll();
    }
}