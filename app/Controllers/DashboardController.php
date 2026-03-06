<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function index()
    {
        $data = [
            'title'      => 'Recepción',
            'hotelName'  => session('tenant_name'),
            'userName'   => session('user_name'),
            'role'       => session('user_role')
        ];

        return view('dashboard/index', $data);
    }
}