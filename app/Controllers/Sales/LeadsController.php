<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Models\LeadModel;
use App\Models\LeadStageModel;
use App\Models\LeadSourceModel;
use App\Models\LeadLossReasonModel;
use App\Models\LeadActivityModel;
use App\Models\SalesUserModel;
use App\Services\LeadAssignmentService;
use App\Services\LeadConversionService;

/**
 * LeadsController para el vendedor.
 * - Listado / vista Kanban con filtro "mis leads" o "todos" (si es manager).
 * - Detalle del lead (timeline, edición, próximas acciones).
 * - Mover etapa (drag & drop) vía endpoint AJAX.
 * - Marcar ganado/perdido.
 */
class LeadsController extends BaseController
{
    protected LeadModel $leads;
    protected LeadStageModel $stages;
    protected LeadSourceModel $sources;
    protected LeadLossReasonModel $reasons;
    protected LeadActivityModel $activities;
    protected SalesUserModel $sellers;

    public function __construct()
    {
        $this->leads      = new LeadModel();
        $this->stages     = new LeadStageModel();
        $this->sources    = new LeadSourceModel();
        $this->reasons    = new LeadLossReasonModel();
        $this->activities = new LeadActivityModel();
        $this->sellers    = new SalesUserModel();
    }

    /**
     * Vista Kanban: el corazón del CRM
     */
    public function kanban()
    {
        $role     = session('sales_user_role');
        $myId     = session('sales_user_id');

        $filters = ['only_open' => true];
        // Vendedor solo ve los suyos; manager ve todos
        if ($role !== 'manager') {
            $filters['assigned_to'] = $myId;
        } elseif ($this->request->getGet('seller')) {
            $filters['assigned_to'] = (int)$this->request->getGet('seller');
        }

        if ($s = $this->request->getGet('q')) {
            $filters['search'] = $s;
        }

        return view('sales/leads/kanban', [
            'title'   => 'Pipeline de ventas',
            'stages'  => $this->stages->getActiveOrdered(),
            'leads'   => $this->leads->getWithRelations($filters),
            'sellers' => ($role === 'manager') ? $this->sellers->findAll() : [],
            'isManager' => $role === 'manager',
        ]);
    }

    /**
     * Endpoint AJAX para mover lead de etapa (drag & drop).
     * POST /sales/leads/move
     * Params: lead_id, stage_id
     */
    public function move()
    {
        $leadId  = (int)$this->request->getPost('lead_id');
        $stageId = (int)$this->request->getPost('stage_id');

        if (!$leadId || !$stageId) {
            return $this->response->setJSON(['ok'=>false, 'msg'=>'Parámetros incompletos']);
        }

        $lead = $this->leads->find($leadId);
        if (!$lead) {
            return $this->response->setJSON(['ok'=>false, 'msg'=>'Lead no encontrado']);
        }
        // Permisos: vendedor solo puede mover los suyos
        if (session('sales_user_role') !== 'manager' && $lead['assigned_to'] != session('sales_user_id')) {
            return $this->response->setJSON(['ok'=>false, 'msg'=>'No puedes mover este lead']);
        }

        $newStage = $this->stages->find($stageId);
        if (!$newStage) {
            return $this->response->setJSON(['ok'=>false, 'msg'=>'Etapa inválida']);
        }

        // Si la etapa es Ganado o Perdido, devolvemos flag para que el front pida datos extra
        if ($newStage['is_won']) {
            return $this->response->setJSON([
                'ok'=>true, 'needs_won_modal'=>true, 'lead_id'=>$leadId
            ]);
        }
        if ($newStage['is_lost']) {
            return $this->response->setJSON([
                'ok'=>true, 'needs_lost_modal'=>true, 'lead_id'=>$leadId,
                'reasons' => $this->reasons->getActive(),
            ]);
        }

        $this->leads->update($leadId, [
            'stage_id'         => $stageId,
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        $this->activities->log($leadId, 'stage_change',
            "Movido de etapa #{$lead['stage_id']} a #{$stageId}",
            session('sales_user_id'),
            "Cambio de etapa: {$newStage['name']}"
        );

        return $this->response->setJSON(['ok'=>true]);
    }

    /**
     * Marcar lead como perdido (requiere razón)
     */
    public function markLost()
    {
        $leadId   = (int)$this->request->getPost('lead_id');
        $reasonId = (int)$this->request->getPost('loss_reason_id');
        $notes    = $this->request->getPost('loss_notes');

        $lostStage = $this->stages->where('is_lost',1)->first();
        if (!$lostStage) return $this->response->setJSON(['ok'=>false,'msg'=>'No hay etapa Perdido']);

        $this->leads->update($leadId, [
            'stage_id'         => $lostStage['id'],
            'lost_at'          => date('Y-m-d H:i:s'),
            'loss_reason_id'   => $reasonId ?: null,
            'loss_notes'       => $notes,
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        $this->activities->log($leadId,'stage_change','Lead marcado como perdido', session('sales_user_id'), 'Perdido');
        return $this->response->setJSON(['ok'=>true]);
    }

    /**
     * Marca el lead como ganado y lo convierte en tenant.
     * Espera: lead_id, plan_id, admin_password (opcional).
     */
    public function markWon()
    {
        $leadId   = (int)$this->request->getPost('lead_id');
        $planId   = (int)$this->request->getPost('plan_id');
        $pass     = $this->request->getPost('admin_password');

        $wonStage = $this->stages->where('is_won',1)->first();
        if (!$wonStage) return $this->response->setJSON(['ok'=>false,'msg'=>'No hay etapa Ganado']);

        // Movemos el lead a Ganado
        $this->leads->update($leadId, [
            'stage_id'         => $wonStage['id'],
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        // Convertimos a tenant
        $svc = new LeadConversionService();
        $result = $svc->convert($leadId, [
            'plan_id'        => $planId,
            'admin_password' => $pass ?: bin2hex(random_bytes(6)),
        ]);

        return $this->response->setJSON([
            'ok'        => $result['success'],
            'msg'       => $result['message'],
            'tenant_id' => $result['tenant_id'] ?? null,
        ]);
    }

    /**
     * Detalle de un lead con timeline completo.
     */
    public function detail(int $id)
    {
        $lead = $this->leads->find($id);
        if (!$lead) return redirect()->to('/sales/leads')->with('error','Lead no encontrado');

        if (session('sales_user_role') !== 'manager'
            && $lead['assigned_to'] != session('sales_user_id')) {
            return redirect()->to('/sales/leads')->with('error','Este lead no es tuyo');
        }

        return view('sales/leads/detail', [
            'title'      => 'Lead: '.$lead['property_name'],
            'lead'       => $lead,
            'timeline'   => $this->activities->getTimeline($id),
            'stages'     => $this->stages->getActiveOrdered(),
            'sources'    => $this->sources->getActive(),
            'reasons'    => $this->reasons->getActive(),
        ]);
    }

    /**
     * Form para crear lead manualmente (outbound).
     */
    public function create()
    {
        return view('sales/leads/create', [
            'title'   => 'Nuevo lead',
            'sources' => $this->sources->where('type','outbound')->findAll(),
            'stages'  => $this->stages->getActiveOrdered(),
        ]);
    }

    public function store()
    {
        $data = $this->request->getPost([
            'contact_name','contact_email','contact_phone','contact_position',
            'property_name','property_type','property_city','property_country',
            'property_website','rooms_count','current_pms','source_id',
            'estimated_value','expected_close_date','notes'
        ]);

        $firstStage = $this->stages->where('is_won',0)->where('is_lost',0)
            ->orderBy('order_position','ASC')->first();

        $data['stage_id']         = $firstStage['id'];
        $data['assigned_to']      = session('sales_user_id'); // outbound se queda con quien lo carga
        $data['created_by']       = session('sales_user_id');
        $data['last_activity_at'] = date('Y-m-d H:i:s');

        // Check duplicados
        $dupes = $this->leads->findDuplicates($data);
        if (!empty($dupes) && !$this->request->getPost('force')) {
            return $this->response->setJSON([
                'ok'=>false,
                'duplicates'=>$dupes,
                'msg'=>'Posibles duplicados encontrados. Confirma para forzar el guardado.'
            ]);
        }

        if (!$this->leads->insert($data)) {
            return redirect()->back()->with('error', implode(' | ', $this->leads->errors()));
        }
        $leadId = $this->leads->getInsertID();
        $this->activities->log($leadId, 'system', 'Lead creado manualmente (outbound)', session('sales_user_id'), 'Creación');

        return redirect()->to('/sales/leads/detail/'.$leadId)->with('success','Lead creado');
    }

    /**
     * Endpoint para agregar nota/llamada/email rápida al timeline.
     */
    public function addActivity()
    {
        $leadId = (int)$this->request->getPost('lead_id');
        $type   = $this->request->getPost('type'); // note, call, email, whatsapp, meeting, demo
        $body   = $this->request->getPost('body');

        if (!in_array($type, ['note','call','email','whatsapp','meeting','demo','task_done'])) {
            return $this->response->setJSON(['ok'=>false,'msg'=>'Tipo inválido']);
        }

        $this->activities->log($leadId, $type, $body, session('sales_user_id'));
        $this->leads->update($leadId, ['last_activity_at'=>date('Y-m-d H:i:s'), 'is_cold'=>0]);

        return $this->response->setJSON(['ok'=>true]);
    }

    /**
     * Programar próxima acción.
     */
    public function setNextAction()
    {
        $leadId = (int)$this->request->getPost('lead_id');
        $at     = $this->request->getPost('next_action_at');
        $note   = $this->request->getPost('next_action_note');

        $this->leads->update($leadId, [
            'next_action_at'   => $at,
            'next_action_note' => $note,
        ]);
        $this->activities->log($leadId,'system',"Próxima acción: {$note} ({$at})", session('sales_user_id'), 'Acción programada');

        return $this->response->setJSON(['ok'=>true]);
    }
}
