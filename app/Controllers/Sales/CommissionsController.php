<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Models\CommissionModel;

/**
 * Permite que el vendedor / gerente vea sus propias comisiones generadas.
 */
class CommissionsController extends BaseController
{
    public function index()
    {
        $model  = new CommissionModel();
        $userId = (int) session('sales_user_id');

        $earnings    = $model->getMyEarnings($userId);
        $commissions = $model->listWithDetails(['user_id' => $userId]);

        return view('sales/commissions/index', [
            'title'       => 'Mis comisiones',
            'earnings'    => $earnings,
            'commissions' => $commissions,
        ]);
    }
}
