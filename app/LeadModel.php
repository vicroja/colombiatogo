<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * LeadModel
 * NO extiende BaseMultiTenantModel: los leads son de MAVILUSA, no de un tenant.
 */
class LeadModel extends Model
{
    protected $table            = 'leads';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields    = [
        'contact_name', 'contact_email', 'contact_phone', 'contact_position',
        'property_name', 'property_type', 'property_city', 'property_country',
        'property_website', 'rooms_count', 'current_pms', 'channels_used',
        'source_id', 'stage_id', 'assigned_to', 'estimated_value', 'expected_close_date',
        'won_at', 'lost_at', 'loss_reason_id', 'loss_notes', 'converted_tenant_id',
        'last_activity_at', 'next_action_at', 'next_action_note', 'is_cold',
        'notes', 'created_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validación básica
    protected $validationRules = [
        'contact_name'  => 'required|min_length[2]|max_length[120]',
        'property_name' => 'required|min_length[2]|max_length[150]',
        'contact_email' => 'permit_empty|valid_email',
        'stage_id'      => 'required|is_natural_no_zero',
    ];

    /**
     * Trae todos los leads con datos relacionados (para listados y Kanban)
     */
    public function getWithRelations(array $filters = []): array
    {
        $builder = $this->db->table($this->table . ' l')
            ->select('l.*, s.name as stage_name, s.slug as stage_slug, s.color as stage_color, s.order_position, s.is_won, s.is_lost,
                     u.name as seller_name, u.email as seller_email,
                     src.name as source_name, src.type as source_type')
            ->join('lead_stages s', 's.id = l.stage_id', 'left')
            ->join('sales_users u', 'u.id = l.assigned_to', 'left')
            ->join('lead_sources src', 'src.id = l.source_id', 'left');

        if (!empty($filters['assigned_to'])) {
            $builder->where('l.assigned_to', $filters['assigned_to']);
        }
        if (!empty($filters['stage_id'])) {
            $builder->where('l.stage_id', $filters['stage_id']);
        }
        if (!empty($filters['source_id'])) {
            $builder->where('l.source_id', $filters['source_id']);
        }
        if (isset($filters['only_open']) && $filters['only_open']) {
            $builder->where('s.is_won', 0)->where('s.is_lost', 0);
        }
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $builder->groupStart()
                ->like('l.contact_name', $s)
                ->orLike('l.property_name', $s)
                ->orLike('l.contact_email', $s)
                ->orLike('l.contact_phone', $s)
                ->groupEnd();
        }

        return $builder->orderBy('s.order_position', 'ASC')
                       ->orderBy('l.updated_at', 'DESC')
                       ->get()->getResultArray();
    }

    /**
     * Busca posibles duplicados por email, teléfono o nombre de propiedad
     */
    public function findDuplicates(array $data): array
    {
        $builder = $this->db->table($this->table)
            ->select('id, contact_name, contact_email, contact_phone, property_name, assigned_to')
            ->groupStart();

        $hasCondition = false;
        if (!empty($data['contact_email'])) {
            $builder->orWhere('contact_email', $data['contact_email']);
            $hasCondition = true;
        }
        if (!empty($data['contact_phone'])) {
            $builder->orWhere('contact_phone', $data['contact_phone']);
            $hasCondition = true;
        }
        if (!empty($data['property_name'])) {
            $builder->orLike('property_name', $data['property_name']);
            $hasCondition = true;
        }
        $builder->groupEnd();

        if (!$hasCondition) return [];

        return $builder->limit(5)->get()->getResultArray();
    }

    /**
     * Leads con next_action_at vencido (para recordatorios)
     */
    public function getDueReminders(): array
    {
        $now = date('Y-m-d H:i:s');
        return $this->db->table($this->table . ' l')
            ->select('l.*, u.email as seller_email, u.name as seller_name, u.phone as seller_phone')
            ->join('sales_users u', 'u.id = l.assigned_to', 'left')
            ->join('lead_stages s', 's.id = l.stage_id')
            ->where('l.next_action_at <=', $now)
            ->where('l.next_action_at IS NOT NULL')
            ->where('s.is_won', 0)
            ->where('s.is_lost', 0)
            ->get()->getResultArray();
    }

    /**
     * Leads inactivos según horas configuradas (para reasignación)
     */
    public function getInactiveLeads(int $hours): array
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        return $this->db->table($this->table . ' l')
            ->select('l.*')
            ->join('lead_stages s', 's.id = l.stage_id')
            ->where('s.is_won', 0)
            ->where('s.is_lost', 0)
            ->where('l.assigned_to IS NOT NULL')
            ->groupStart()
                ->where('l.last_activity_at <', $cutoff)
                ->orWhere('l.last_activity_at IS NULL')
            ->groupEnd()
            ->get()->getResultArray();
    }
}
