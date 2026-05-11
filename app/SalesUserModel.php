<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * SalesUserModel
 * Vendedores y gerentes de la fuerza comercial de MAVILUSA.
 * NO extiende BaseMultiTenantModel porque no pertenecen a un tenant.
 */
class SalesUserModel extends Model
{
    protected $table            = 'sales_users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields    = [
        'name', 'email', 'password_hash', 'phone', 'role',
        'commission_rate', 'max_active_leads', 'accepts_inbound',
        'is_active', 'last_login_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Vendedores activos que pueden recibir leads inbound (para round-robin)
     */
    public function getInboundEligible(): array
    {
        return $this->where('is_active', 1)
                    ->where('accepts_inbound', 1)
                    ->where('role', 'seller')
                    ->orderBy('id', 'ASC')
                    ->findAll();
    }

    /**
     * Cuenta leads activos (no won/lost) asignados a un vendedor
     */
    public function countActiveLeads(int $salesUserId): int
    {
        $db = \Config\Database::connect();
        return $db->table('leads l')
            ->join('lead_stages s', 's.id = l.stage_id')
            ->where('l.assigned_to', $salesUserId)
            ->where('s.is_won', 0)
            ->where('s.is_lost', 0)
            ->countAllResults();
    }
}
