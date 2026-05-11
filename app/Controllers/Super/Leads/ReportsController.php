<?php

namespace App\Controllers\Super\Leads;

use App\Controllers\BaseController;

/**
 * Reportes de leads para superadmin: embudo, conversión, razones de pérdida.
 */
class ReportsController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // 1. Embudo: conteo por etapa
        $funnel = $db->query("
            SELECT s.name, s.color, s.order_position, COUNT(l.id) AS total,
                   COALESCE(SUM(l.estimated_value),0) AS value_sum
            FROM lead_stages s
            LEFT JOIN leads l ON l.stage_id = s.id
            WHERE s.is_active = 1
            GROUP BY s.id
            ORDER BY s.order_position ASC
        ")->getResultArray();

        // 2. Ranking de vendedores (último 90 días)
        $ranking = $db->query("
            SELECT u.id, u.name,
                   COUNT(l.id) AS total_leads,
                   SUM(CASE WHEN s.is_won=1 THEN 1 ELSE 0 END) AS won,
                   SUM(CASE WHEN s.is_lost=1 THEN 1 ELSE 0 END) AS lost,
                   COALESCE(SUM(CASE WHEN s.is_won=1 THEN l.estimated_value ELSE 0 END),0) AS revenue
            FROM sales_users u
            LEFT JOIN leads l ON l.assigned_to = u.id AND l.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
            LEFT JOIN lead_stages s ON s.id = l.stage_id
            WHERE u.is_active = 1
            GROUP BY u.id
            ORDER BY won DESC, total_leads DESC
        ")->getResultArray();

        // 3. Razones de pérdida (últimos 90 días)
        $lossReasons = $db->query("
            SELECT r.name, COUNT(l.id) AS total
            FROM lead_loss_reasons r
            LEFT JOIN leads l ON l.loss_reason_id = r.id
              AND l.lost_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
            GROUP BY r.id
            ORDER BY total DESC
        ")->getResultArray();

        // 4. Inbound vs Outbound (conversión)
        $sourceComparison = $db->query("
            SELECT src.type,
                   COUNT(l.id) AS total,
                   SUM(CASE WHEN s.is_won=1 THEN 1 ELSE 0 END) AS won,
                   SUM(CASE WHEN s.is_won=1 THEN l.estimated_value ELSE 0 END) AS revenue
            FROM lead_sources src
            LEFT JOIN leads l ON l.source_id = src.id
            LEFT JOIN lead_stages s ON s.id = l.stage_id
            GROUP BY src.type
        ")->getResultArray();

        // 5. Forecast del mes (suma de oportunidades abiertas × probabilidad)
        $forecast = $db->query("
            SELECT COALESCE(SUM(l.estimated_value * (s.probability/100)),0) AS expected
            FROM leads l
            JOIN lead_stages s ON s.id = l.stage_id
            WHERE s.is_won=0 AND s.is_lost=0
              AND l.expected_close_date BETWEEN DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') AND LAST_DAY(CURRENT_DATE)
        ")->getRowArray();

        return view('super/leads/reports/index', [
            'title'            => 'Reportes de ventas',
            'funnel'           => $funnel,
            'ranking'          => $ranking,
            'lossReasons'      => $lossReasons,
            'sourceComparison' => $sourceComparison,
            'forecast'         => $forecast['expected'] ?? 0,
        ]);
    }
}
