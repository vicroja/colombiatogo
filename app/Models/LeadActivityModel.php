<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadActivityModel extends Model
{
    protected $table         = 'lead_activities';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['lead_id','sales_user_id','type','subject','body','metadata_json','occurred_at'];
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = null; // las actividades son inmutables

    /**
     * Timeline de un lead, más reciente primero
     */
    public function getTimeline(int $leadId): array
    {
        return $this->db->table($this->table . ' a')
            ->select('a.*, u.name as user_name, u.email as user_email')
            ->join('sales_users u', 'u.id = a.sales_user_id', 'left')
            ->where('a.lead_id', $leadId)
            ->orderBy('a.occurred_at', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Registra rápido una actividad
     */
    public function log(int $leadId, string $type, ?string $body = null, ?int $userId = null, ?string $subject = null, ?array $metadata = null): int|false
    {
        return $this->insert([
            'lead_id'       => $leadId,
            'sales_user_id' => $userId ?? session('sales_user_id'),
            'type'          => $type,
            'subject'       => $subject,
            'body'          => $body,
            'metadata_json' => $metadata ? json_encode($metadata) : null,
            'occurred_at'   => date('Y-m-d H:i:s'),
        ]);
    }
}
