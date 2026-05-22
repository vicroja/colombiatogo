<?php

namespace App\Controllers\Super;

use App\Controllers\BaseController;
use App\Models\TenantModel;
use App\Models\TenantSubscriptionModel;
use App\Models\SaasPaymentModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $tenantModel  = new TenantModel();
        $subModel     = new TenantSubscriptionModel();
        $paymentModel = new SaasPaymentModel();

        $totalTenants     = $tenantModel->countAllResults(false);
        $activeTenants    = $tenantModel->where('is_suspended', 0)->countAllResults(false);
        $suspendedTenants = $tenantModel->where('is_suspended', 1)->countAllResults();

        $trialSubs    = $subModel->where('status', 'trial')->countAllResults(false);
        $activeSubs   = $subModel->where('status', 'active')->countAllResults(false);
        $pastDueSubs  = $subModel->where('status', 'past_due')->countAllResults();

        // Pagos del mes actual
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd   = date('Y-m-t 23:59:59');
        $revenueThisMonth = $paymentModel->getRevenueBetween($monthStart, $monthEnd);
        $mrr = $paymentModel->getMrr();

        // Vencimientos próximos (7 días)
        $upcoming = $subModel
            ->select('tenant_subscriptions.*, tenants.name as tenant_name')
            ->join('tenants', 'tenants.id = tenant_subscriptions.tenant_id')
            ->where('current_period_end >=', date('Y-m-d'))
            ->where('current_period_end <=', date('Y-m-d', strtotime('+7 days')))
            ->orderBy('current_period_end', 'ASC')
            ->findAll(10);

        $data = [
            'title'            => 'Dashboard SuperAdmin',
            'adminName'        => session()->get('superadmin_name'),
            'totalTenants'     => $totalTenants,
            'activeTenants'    => $activeTenants,
            'suspendedTenants' => $suspendedTenants,
            'trialSubs'        => $trialSubs,
            'activeSubs'       => $activeSubs,
            'pastDueSubs'      => $pastDueSubs,
            'revenueThisMonth' => $revenueThisMonth,
            'mrr'              => $mrr,
            'upcoming'         => $upcoming,
        ];

        return view('super/dashboard/index', $data);
    }
}