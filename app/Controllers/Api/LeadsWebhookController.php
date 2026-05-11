<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\LeadModel;
use App\Models\LeadStageModel;
use App\Models\LeadSourceModel;
use App\Models\LeadActivityModel;
use App\Services\LeadAssignmentService;
use App\Services\LeadNotificationService;

/**
 * LeadsWebhookController
 * Endpoint público para recibir leads desde:
 *  - Formulario web embebido
 *  - Facebook Ads (vía webhook)
 *  - Zapier / Make
 *
 * URL: POST /api/leads/intake
 * Auth: token simple en header X-Intake-Token (configura en .env)
 */
class LeadsWebhookController extends BaseController
{
    public function intake()
    {
        // 1. Validación de token
        $token = $this->request->getHeaderLine('X-Intake-Token');
        $expected = getenv('LEADS_INTAKE_TOKEN') ?: 'cambia-este-token';

        if ($token !== $expected) {
            return $this->response->setStatusCode(401)->setJSON(['ok'=>false,'msg'=>'No autorizado']);
        }

        // 2. Datos (acepta JSON o form)
        $data = $this->request->getJSON(true) ?: $this->request->getPost();

        // Mapeo flexible de nombres comunes
        $lead = [
            'contact_name'     => $data['name']         ?? $data['contact_name'] ?? null,
            'contact_email'    => $data['email']        ?? $data['contact_email'] ?? null,
            'contact_phone'    => $data['phone']        ?? $data['contact_phone'] ?? null,
            'property_name'    => $data['hotel_name']   ?? $data['property_name'] ?? ($data['company'] ?? 'Sin nombre'),
            'property_city'    => $data['city']         ?? null,
            'property_country' => $data['country']      ?? null,
            'rooms_count'      => isset($data['rooms']) ? (int)$data['rooms'] : null,
            'current_pms'      => $data['current_pms']  ?? null,
            'notes'            => $data['message']      ?? $data['notes'] ?? null,
        ];

        if (empty($lead['contact_name']) || (empty($lead['contact_email']) && empty($lead['contact_phone']))) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok'=>false, 'msg'=>'contact_name y (email o phone) son obligatorios'
            ]);
        }

        // 3. Etapa inicial y fuente
        $stages  = new LeadStageModel();
        $sources = new LeadSourceModel();

        $firstStage = $stages->where('is_won',0)->where('is_lost',0)
            ->orderBy('order_position','ASC')->first();
        $lead['stage_id'] = $firstStage['id'];

        $sourceName = $data['source'] ?? 'Formulario web';
        $src = $sources->where('name', $sourceName)->first();
        $lead['source_id'] = $src['id'] ?? null;
        $lead['last_activity_at'] = date('Y-m-d H:i:s');

        // 4. Check duplicado
        $leadModel = new LeadModel();
        $dupes = $leadModel->findDuplicates($lead);
        if (!empty($dupes)) {
            log_message('info', '[LeadIntake] Lead duplicado detectado: '.json_encode($dupes));
            return $this->response->setJSON([
                'ok'=>true, 'duplicated'=>true, 'existing_id'=>$dupes[0]['id'],
                'msg'=>'Ya existe un lead similar'
            ]);
        }

        // 5. Insertar
        $id = $leadModel->insert($lead);
        if (!$id) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok'=>false,'msg'=>'Error guardando lead','errors'=>$leadModel->errors()
            ]);
        }

        // 6. Auto-asignar por round robin
        $svc = new LeadAssignmentService();
        $sellerId = $svc->autoAssignInbound((int)$id);

        // 7. Notificar al vendedor asignado
        if ($sellerId) {
            (new LeadNotificationService())->notifyNewAssignment((int)$id, $sellerId);
        }

        // 8. Registrar actividad inicial
        (new LeadActivityModel())->log((int)$id, 'system',
            'Lead recibido vía webhook ('.$sourceName.')', null, 'Captura inbound');

        return $this->response->setJSON([
            'ok'=>true, 'lead_id'=>$id, 'assigned_to'=>$sellerId
        ]);
    }
}
