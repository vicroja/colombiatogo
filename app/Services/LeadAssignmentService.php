<?php

namespace App\Services;

use App\Models\SalesUserModel;
use App\Models\LeadModel;
use App\Models\LeadAssignmentModel;
use App\Models\LeadActivityModel;
use App\Models\LeadSettingsModel;

/**
 * LeadAssignmentService
 * Encapsula toda la lógica de asignación, round-robin y reasignación de leads.
 */
class LeadAssignmentService
{
    protected SalesUserModel $salesUsers;
    protected LeadModel $leads;
    protected LeadAssignmentModel $assignments;
    protected LeadActivityModel $activities;
    protected LeadSettingsModel $settings;

    public function __construct()
    {
        $this->salesUsers  = new SalesUserModel();
        $this->leads       = new LeadModel();
        $this->assignments = new LeadAssignmentModel();
        $this->activities  = new LeadActivityModel();
        $this->settings    = new LeadSettingsModel();
    }

    /**
     * Round-robin: devuelve el id del siguiente vendedor disponible.
     * Respeta el tope max_active_leads.
     */
    public function pickNextSeller(): ?int
    {
        $eligible = $this->salesUsers->getInboundEligible();
        if (empty($eligible)) return null;

        $pointer = (int) $this->settings->getValue('round_robin_pointer', 0);
        $count   = count($eligible);

        // Recorremos hasta encontrar uno bajo su tope
        for ($i = 0; $i < $count; $i++) {
            $idx    = ($pointer + $i) % $count;
            $seller = $eligible[$idx];
            $active = $this->salesUsers->countActiveLeads((int)$seller['id']);

            if ($active < (int)$seller['max_active_leads']) {
                // Avanzamos el puntero
                $this->settings->setValue('round_robin_pointer', ($idx + 1) % $count);
                return (int)$seller['id'];
            }
        }
        // Todos saturados: devolvemos el siguiente igual (que el gerente decida)
        $idx = $pointer % $count;
        $this->settings->setValue('round_robin_pointer', ($idx + 1) % $count);
        return (int)$eligible[$idx]['id'];
    }

    /**
     * Asigna un lead inbound recién creado mediante round-robin.
     */
    public function autoAssignInbound(int $leadId): ?int
    {
        $sellerId = $this->pickNextSeller();
        if (!$sellerId) return null;

        $this->leads->update($leadId, [
            'assigned_to'      => $sellerId,
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        $this->assignments->reassign($leadId, $sellerId, 'round_robin');
        $this->activities->log($leadId, 'assignment', "Asignación automática (round-robin) a vendedor #{$sellerId}", null, 'Asignación inicial');

        return $sellerId;
    }

    /**
     * Reasigna manualmente un lead a otro vendedor (acción del gerente o transferencia).
     */
    public function manualAssign(int $leadId, ?int $sellerId, string $reason = 'manual', ?int $by = null): bool
    {
        $by = $by ?? session('sales_user_id');

        $this->leads->update($leadId, ['assigned_to' => $sellerId]);
        $this->assignments->reassign($leadId, $sellerId, $reason, $by);
        $this->activities->log(
            $leadId,
            'assignment',
            $sellerId ? "Reasignado a vendedor #{$sellerId} ({$reason})" : "Lead liberado al pool",
            $by,
            'Reasignación'
        );
        return true;
    }

    /**
     * Worker: reasigna leads inactivos según horas configuradas.
     * Devuelve número de leads reasignados.
     */
    public function reassignInactiveLeads(): int
    {
        $hours = (int) $this->settings->getValue('auto_reassign_hours', 48);
        $inactive = $this->leads->getInactiveLeads($hours);
        $count = 0;

        foreach ($inactive as $lead) {
            $newSeller = $this->pickNextSeller();
            if (!$newSeller || $newSeller == $lead['assigned_to']) continue;

            $this->manualAssign((int)$lead['id'], $newSeller, 'reassignment_inactive');
            $count++;
        }
        return $count;
    }

    /**
     * Worker: marca leads como fríos según días configurados.
     */
    public function detectColdLeads(): int
    {
        $days   = (int) $this->settings->getValue('cold_lead_days', 7);
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $db = \Config\Database::connect();
        $affected = $db->table('leads l')
            ->join('lead_stages s', 's.id = l.stage_id')
            ->where('s.is_won', 0)->where('s.is_lost', 0)
            ->where('l.is_cold', 0)
            ->groupStart()
                ->where('l.last_activity_at <', $cutoff)
                ->orWhere('l.last_activity_at IS NULL')
            ->groupEnd()
            ->update(['l.is_cold' => 1]);

        return (int)$db->affectedRows();
    }
}
