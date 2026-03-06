<?php

namespace App\Controllers\Super;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        // Pasamos datos básicos a la vista
        $data = [
            'title' => 'Dashboard SuperAdmin',
            'adminName' => session()->get('superadmin_name')
        ];

        return view('super/dashboard/index', $data);
    }
}