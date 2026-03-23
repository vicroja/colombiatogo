<?php

namespace App\Controllers;
use App\Services\DashboardService;

class DashboardController extends BaseController
{
    public function index()
    {
        $dashboardService = new DashboardService();
        $metrics = $dashboardService->getTodaysMetrics();
        $data = [
            'title'      => 'Recepción',
            'hotelName'  => session('tenant_name'),
            'userName'   => session('user_name'),
            'role'       => session('user_role'),
            'metrics' => $metrics,
            'income_yesterday' => 20000,   // para el badge %
            'units_status'     => [],
        ];

        return view('dashboard/index', $data);
    }
}