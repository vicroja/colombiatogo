<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * SalesUserModel — V2 con jerarquía y override
 * REEMPLAZA el archivo existente en app/Models/SalesUserModel.php
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
        'manager_id', 'commission_rate', 'override_rate',
        'max_active_leads', 'accepts_inbound',
        'is_active', 'last_login_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Vendedores activos elegibles para round-robin (excluye managers).
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
     * Cuenta leads activos asignados a un vendedor.
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

    /**
     * Lista de gerentes activos (para el select del formulario de vendedor).
     */
    public function getActiveManagers(): array
    {
        return $this->where('is_active', 1)
                    ->where('role', 'manager')
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }

    /**
     * Vendedores que reportan a un gerente dado.
     */
    public function getTeamOf(int $managerId): array
    {
        return $this->where('manager_id', $managerId)
                    ->where('is_active', 1)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }

    /**
     * Devuelve el gerente de un vendedor (o null).
     */
    public function getManagerFor(int $salesUserId): ?array
    {
        $u = $this->find($salesUserId);
        if (!$u || empty($u['manager_id'])) return null;
        return $this->find($u['manager_id']);
    }
}
