<?php

namespace App\Controllers\Super;

use App\Controllers\BaseController;
use App\Models\TenantModel;
use App\Models\TenantSubscriptionModel;
use App\Models\SubscriptionPlanModel;
use App\Models\SaasPaymentModel;

class BillingController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $builder = $db->table('tenants');
        $builder->select('tenants.id, tenants.name, tenants.is_suspended,
                          tenant_subscriptions.id as subscription_id,
                          tenant_subscriptions.current_period_end,
                          tenant_subscriptions.grace_period_days,
                          tenant_subscriptions.status as sub_status,
                          subscription_plans.name as plan_name,
                          subscription_plans.price,
                          subscription_plans.currency,
                          subscription_plans.color');
        $builder->join('tenant_subscriptions', 'tenant_subscriptions.tenant_id = tenants.id');
        $builder->join('subscription_plans', 'subscription_plans.id = tenant_subscriptions.plan_id');
        $builder->orderBy('tenant_subscriptions.current_period_end', 'ASC');

        $tenants = $builder->get()->getResultArray();

        // KPIs en vivo
        $paymentModel = new SaasPaymentModel();
        $kpis = [
            'mrr'                => $paymentModel->getMrr(),
            'total_tenants'      => count($tenants),
            'suspended'          => 0,
            'expiring_this_week' => 0,
        ];

        $today = strtotime(date('Y-m-d'));
        foreach ($tenants as $t) {
            if ($t['is_suspended']) $kpis['suspended']++;
            $daysLeft = round((strtotime($t['current_period_end']) - $today) / 86400);
            if ($daysLeft >= 0 && $daysLeft <= 7) $kpis['expiring_this_week']++;
        }

        $data = [
            'title'   => 'Facturación SaaS - MAVILUSA',
            'tenants' => $tenants,
            'kpis'    => $kpis,
        ];

        return view('super/billing/index', $data);
    }

    /**
     * Renueva la suscripción y registra el pago.
     */
    public function renew($tenantId)
    {
        $subModel     = new TenantSubscriptionModel();
        $tenantModel  = new TenantModel();
        $planModel    = new SubscriptionPlanModel();
        $paymentModel = new SaasPaymentModel();

        $subscription = $subModel->where('tenant_id', $tenantId)->first();

        if (!$subscription) {
            return redirect()->back()->with('error', 'No se encontró la suscripción.');
        }

        $plan = $planModel->find($subscription['plan_id']);
        if (!$plan) {
            return redirect()->back()->with('error', 'Plan no encontrado.');
        }

        // Lectura de datos del formulario (opcional, con defaults sensatos)
        $paymentMethod = $this->request->getPost('payment_method') ?: 'transfer';
        $reference     = $this->request->getPost('reference') ?: null;
        $notes         = $this->request->getPost('notes') ?: null;
        $amount        = $this->request->getPost('amount') ?: $plan['price'];

        $subModel->db->transStart();

        // 1. Calcular nueva fecha de corte
        $currentEndDate = $subscription['current_period_end'];
        if (strtotime($currentEndDate) < time()) {
            // Vencido: nuevo periodo empieza hoy
            $newPeriodStart = date('Y-m-d');
            $newPeriodEnd   = date('Y-m-d', strtotime('+1 month'));
        } else {
            // Vigente: sumamos al final actual
            $newPeriodStart = date('Y-m-d', strtotime($currentEndDate . ' +1 day'));
            $newPeriodEnd   = date('Y-m-d', strtotime($currentEndDate . ' +1 month'));
        }

        // 2. Actualizar suscripción
        $subModel->update($subscription['id'], [
            'current_period_start' => $newPeriodStart,
            'current_period_end'   => $newPeriodEnd,
            'status'               => 'active',
            'suspended_at'         => null,
        ]);

        // 3. Reactivar tenant si estaba suspendido
        $tenantModel->update($tenantId, [
            'is_suspended'     => 0,
            'suspended_reason' => null
        ]);

        // 4. Registrar el pago en saas_payments
        $paymentModel->insert([
            'tenant_id'       => $tenantId,
            'subscription_id' => $subscription['id'],
            'plan_id'         => $plan['id'],
            'amount'          => $amount,
            'currency'        => $plan['currency'],
            'payment_method'  => $paymentMethod,
            'reference'       => $reference,
            'period_start'    => $newPeriodStart,
            'period_end'      => $newPeriodEnd,
            'notes'           => $notes,
            'recorded_by'     => session('superadmin_id'),
        ]);

        $subModel->db->transComplete();

        if ($subModel->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Error al procesar la renovación.');
        }

        return redirect()->back()->with('success',
            "Pago registrado por \${$amount} {$plan['currency']}. Nueva fecha de corte: " . date('d/m/Y', strtotime($newPeriodEnd))
        );
    }

    /**
     * Historial de pagos de un tenant.
     */
    public function history($tenantId)
    {
        $tenantModel  = new TenantModel();
        $paymentModel = new SaasPaymentModel();

        $tenant = $tenantModel->find($tenantId);
        if (!$tenant) {
            return redirect()->to('/super/billing')->with('error', 'Tenant no encontrado.');
        }

        // Pagos con join para mostrar plan y admin
        $db = \Config\Database::connect();
        $payments = $db->table('saas_payments sp')
            ->select('sp.*, sub.id as sub_id, p.name as plan_name, p.color as plan_color, sa.name as admin_name')
            ->join('tenant_subscriptions sub', 'sub.id = sp.subscription_id', 'left')
            ->join('subscription_plans p', 'p.id = sp.plan_id', 'left')
            ->join('super_admins sa', 'sa.id = sp.recorded_by', 'left')
            ->where('sp.tenant_id', $tenantId)
            ->orderBy('sp.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $totalPaid = array_sum(array_column($payments, 'amount'));

        $data = [
            'title'      => 'Historial de Pagos - ' . $tenant['name'],
            'tenant'     => $tenant,
            'payments'   => $payments,
            'totalPaid'  => $totalPaid,
        ];

        return view('super/billing/history', $data);
    }
}