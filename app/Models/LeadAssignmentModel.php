<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadAssignmentModel extends Model
{
    protected $table         = 'lead_assignments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['lead_id','sales_user_id','assigned_by','reason','assigned_at','unassigned_at'];
    protected $useTimestamps = false;

    /**
     * Cierra la asignación activa (si existe) y crea una nueva.
     * Devuelve el id de la nueva fila.
     */
    public function reassign(int $leadId, ?int $newSalesUserId, string $reason, ?int $assignedBy = null): int
    {
        $now = date('Y-m-d H:i:s');

        // Cerramos la asignación abierta
        $this->where('lead_id', $leadId)
             ->where('unassigned_at IS NULL')
             ->set('unassigned_at', $now)
             ->update();

        // Creamos nueva si hay vendedor (puede ser null si liberamos)
        return $this->insert([
            'lead_id'       => $leadId,
            'sales_user_id' => $newSalesUserId,
            'assigned_by'   => $assignedBy,
            'reason'        => $reason,
            'assigned_at'   => $now,
        ]);
    }
}
