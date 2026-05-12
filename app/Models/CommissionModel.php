<?php

namespace App\Models;

use CodeIgniter\Model;

class CommissionModel extends Model
{
    protected $table         = 'commissions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'lead_id','tenant_id','sales_user_id','source_sales_user_id',
        'type','base_amount','rate','amount','status',
        'earned_at','approved_at','approved_by','paid_at','paid_by',
        'payment_method','payment_reference','notes'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Trae todas las comisiones con datos de vendedor y lead.
     */
    public function listWithDetails(array $filters = []): array
    {
        $builder = $this->db->table($this->table.' c')
            ->select('c.*,
                      u.name as user_name, u.email as user_email,
                      src.name as source_name,
                      l.property_name, l.contact_name')
            ->join('sales_users u',   'u.id = c.sales_user_id', 'left')
            ->join('sales_users src', 'src.id = c.source_sales_user_id', 'left')
            ->join('leads l',         'l.id = c.lead_id', 'left');

        if (!empty($filters['user_id'])) {
            $builder->where('c.sales_user_id', $filters['user_id']);
        }
        if (!empty($filters['status'])) {
            $builder->where('c.status', $filters['status']);
        }
        if (!empty($filters['type'])) {
            $builder->where('c.type', $filters['type']);
        }
        if (!empty($filters['from'])) {
            $builder->where('c.earned_at >=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $builder->where('c.earned_at <=', $filters['to']);
        }

        return $builder->orderBy('c.earned_at', 'DESC')->get()->getResultArray();
    }

    /**
     * Totales por vendedor y status (resumen para liquidación).
     */
    public function getSummaryByUser(): array
    {
        return $this->db->table($this->table.' c')
            ->select('u.id, u.name, u.email,
                      SUM(CASE WHEN c.status="pending"  THEN c.amount ELSE 0 END) as total_pending,
                      SUM(CASE WHEN c.status="approved" THEN c.amount ELSE 0 END) as total_approved,
                      SUM(CASE WHEN c.status="paid"     THEN c.amount ELSE 0 END) as total_paid,
                      COUNT(c.id) as total_records')
            ->join('sales_users u', 'u.id = c.sales_user_id')
            ->where('c.status !=', 'cancelled')
            ->groupBy('u.id')
            ->orderBy('total_pending', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Totales por vendedor (para que vea su propia liquidación).
     */
    public function getMyEarnings(int $userId): array
    {
        $row = $this->db->table($this->table)
            ->selectSum('amount', 'total')
            ->select("SUM(CASE WHEN status='pending'  THEN amount ELSE 0 END) AS pending")
            ->select("SUM(CASE WHEN status='approved' THEN amount ELSE 0 END) AS approved")
            ->select("SUM(CASE WHEN status='paid'     THEN amount ELSE 0 END) AS paid")
            ->where('sales_user_id', $userId)
            ->where('status !=', 'cancelled')
            ->get()->getRowArray();

        return [
            'total'    => (float)($row['total'] ?? 0),
            'pending'  => (float)($row['pending'] ?? 0),
            'approved' => (float)($row['approved'] ?? 0),
            'paid'     => (float)($row['paid'] ?? 0),
        ];
    }

    /**
     * Cambia el status (approved/paid/cancelled) registrando quién y cuándo.
     */
    public function changeStatus(int $id, string $newStatus, ?int $by = null, ?array $extra = []): bool
    {
        $data = ['status' => $newStatus];

        if ($newStatus === 'approved') {
            $data['approved_at'] = date('Y-m-d H:i:s');
            $data['approved_by'] = $by;
        }
        if ($newStatus === 'paid') {
            $data['paid_at']           = date('Y-m-d H:i:s');
            $data['paid_by']           = $by;
            $data['payment_method']    = $extra['payment_method']    ?? null;
            $data['payment_reference'] = $extra['payment_reference'] ?? null;
        }
        if (!empty($extra['notes'])) {
            $data['notes'] = $extra['notes'];
        }

        return $this->update($id, $data);
    }
}
