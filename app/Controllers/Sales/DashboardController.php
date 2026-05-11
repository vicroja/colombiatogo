<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $myId    = session('sales_user_id');
        $isMgr   = session('sales_user_role') === 'manager';

        // Si es manager, muestra todo; si no, solo lo suyo.
        $filter = $isMgr ? '' : "AND l.assigned_to = {$myId}";

        $row = $db->query("
            SELECT
              SUM(CASE WHEN s.is_won=0 AND s.is_lost=0 THEN 1 ELSE 0 END) AS open_leads,
              SUM(CASE WHEN s.is_won=1 AND MONTH(l.won_at)=MONTH(CURRENT_DATE) THEN 1 ELSE 0 END) AS won_this_month,
              SUM(CASE WHEN s.is_lost=1 AND MONTH(l.lost_at)=MONTH(CURRENT_DATE) THEN 1 ELSE 0 END) AS lost_this_month,
              SUM(CASE WHEN l.is_cold=1 AND s.is_won=0 AND s.is_lost=0 THEN 1 ELSE 0 END) AS cold_leads,
              SUM(CASE WHEN l.next_action_at <= NOW() AND l.next_action_at IS NOT NULL THEN 1 ELSE 0 END) AS overdue_actions
            FROM leads l
            JOIN lead_stages s ON s.id = l.stage_id
            WHERE 1=1 {$filter}
        ")->getRowArray();

        return view('sales/dashboard/index', [
            'title'   => 'Dashboard',
            'metrics' => $row,
            'isManager' => $isMgr,
        ]);
    }
}
