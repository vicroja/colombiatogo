<?php

namespace App\Services;

use App\Models\LeadModel;
use App\Models\LeadActivityModel;

/**
 * LeadNotificationService
 * Envía recordatorios y alertas a vendedores.
 * Aprovecha el WhatsappModel ya existente si tienes un número saas configurado.
 */
class LeadNotificationService
{
    /**
     * Notifica al vendedor que tiene un nuevo lead asignado.
     */
    public function notifyNewAssignment(int $leadId, int $sellerId): void
    {
        $lead = (new LeadModel())->find($leadId);
        $db = \Config\Database::connect();
        $seller = $db->table('sales_users')->where('id', $sellerId)->get()->getRowArray();
        if (!$lead || !$seller) return;

        $msg = "Tienes un lead nuevo asignado: {$lead['property_name']} ({$lead['contact_name']}). "
             . "Tienes 24h para hacer el primer contacto.";

        $this->sendEmail($seller['email'], 'Nuevo lead asignado', $msg);
        if (!empty($seller['phone'])) {
            $this->sendWhatsapp($seller['phone'], $msg);
        }
    }

    /**
     * Worker: recorre leads con next_action_at vencido y avisa al vendedor.
     */
    public function processDueReminders(): int
    {
        $leadModel = new LeadModel();
        $activity  = new LeadActivityModel();
        $due       = $leadModel->getDueReminders();
        $sent      = 0;

        foreach ($due as $l) {
            if (empty($l['seller_email'])) continue;

            $msg = "Recordatorio: tienes una acción pendiente con {$l['property_name']}. "
                 . "Nota: {$l['next_action_note']}";

            $this->sendEmail($l['seller_email'], 'Acción pendiente con lead', $msg);
            if (!empty($l['seller_phone'])) {
                $this->sendWhatsapp($l['seller_phone'], $msg);
            }

            // Limpiamos next_action_at para no repetir
            $leadModel->update($l['id'], ['next_action_at' => null]);
            $activity->log((int)$l['id'], 'system', 'Recordatorio enviado al vendedor', null, 'Recordatorio');
            $sent++;
        }
        return $sent;
    }

    protected function sendEmail(string $to, string $subject, string $body): bool
    {
        try {
            $email = \Config\Services::email();
            $email->setTo($to);
            $email->setSubject($subject);
            $email->setMessage(nl2br($body));
            return $email->send();
        } catch (\Throwable $e) {
            log_message('error', "[LeadNotif] Email fail: ".$e->getMessage());
            return false;
        }
    }

    /**
     * Reutiliza el WhatsappModel existente.
     * Asume que tienes un número SAAS configurado (is_saas=true).
     */
    protected function sendWhatsapp(string $phone, string $message): bool
    {
        try {
            if (!class_exists('App\Models\WhatsappModel')) return false;
            $wa = model('App\Models\WhatsappModel');
            // tenant_id=null porque es notificación interna de MAVILUSA
            $resp = $wa->sendTextApi($phone, $message, true, null);
            return isset($resp['messages'][0]['id']);
        } catch (\Throwable $e) {
            log_message('error', "[LeadNotif] WA fail: ".$e->getMessage());
            return false;
        }
    }
}
