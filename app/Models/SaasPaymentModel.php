<?php

namespace App\Models;

use CodeIgniter\Model;

class SaasPaymentModel extends Model
{
    protected $table            = 'saas_payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'tenant_id', 'subscription_id', 'plan_id',
        'amount', 'currency', 'payment_method', 'reference',
        'period_start', 'period_end', 'notes', 'recorded_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Pagos de un tenant ordenados del más reciente al más antiguo.
     */
    public function getByTenant(int $tenantId): array
    {
        return $this->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Suma total ingresada por SaaS en un rango de fechas.
     */
    public function getRevenueBetween(string $from, string $to): float
    {
        $row = $this->selectSum('amount', 'total')
            ->where('created_at >=', $from)
            ->where('created_at <=', $to)
            ->first();
        return (float)($row['total'] ?? 0);
    }

    /**
     * MRR aproximado: suma de pagos del último mes.
     */
    public function getMrr(): float
    {
        $from = date('Y-m-d 00:00:00', strtotime('-30 days'));
        $to   = date('Y-m-d 23:59:59');
        return $this->getRevenueBetween($from, $to);
    }
}