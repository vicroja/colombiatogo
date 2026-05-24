<?php

namespace App\Controllers;

use App\Models\TenantModel;
use App\Services\TourDashboardService;
use App\Services\HotelDashboardService;

class DashboardController extends BaseController
{
    public function index()
    {
        $tenantId = (int) session('active_tenant_id');
        $tenantModel = new TenantModel();
        $tenant = $tenantModel->find($tenantId);

        // Parseamos el settings_json para saber qué módulos están activos
        $settings = json_decode($tenant['settings_json'] ?? '{}', true) ?: [];
        $features = [
            'has_tours'         => (bool) ($settings['has_tours']         ?? false),
            'has_accommodation' => (bool) ($settings['has_accommodation'] ?? false),
        ];

        // Si por error no tiene ninguno activo, asumimos hotel (legacy)
        if (!$features['has_tours'] && !$features['has_accommodation']) {
            $features['has_accommodation'] = true;
        }

        $widgets = [];

        // ─── MÓDULO TOURS ───────────────────────────────────────────────
        if ($features['has_tours']) {
            $tourService = new TourDashboardService($tenantId);

            $widgets['tour_alerts']         = $tourService->getOperationalAlerts();
            $widgets['todays_departures']   = $tourService->getTodaysDepartures();
            $widgets['upcoming_departures'] = $tourService->getUpcomingDepartures(7);
            $widgets['tour_kpis']           = $tourService->getKpis();
            $widgets['recent_reservations'] = $tourService->getRecentReservations(8);
            $widgets['top_tours_month']     = $tourService->getTopToursOfMonth(5);
            $widgets['guides_summary']      = $tourService->getGuidesSummary();
        }

        // ─── MÓDULO ALOJAMIENTO ────────────────────────────────────────
        if ($features['has_accommodation']) {
            $hotelService = new HotelDashboardService($tenantId);

            $widgets['hotel_kpis']          = $hotelService->getKpis();
            $widgets['arrivals_today']      = $hotelService->getArrivalsToday();
            $widgets['departures_today']    = $hotelService->getDeparturesToday();
            $widgets['units_status']        = $hotelService->getUnitsStatus();
        }

        $data = [
            'title'      => $this->buildTitle($features),
            'tenant'     => $tenant,
            'hotelName'  => $tenant['name'],
            'userName'   => session('user_name'),
            'role'       => session('user_role'),
            'features'   => $features,
            'widgets'    => $widgets,
            'currency'   => $tenant['currency_symbol'] ?? '$',
            'today'      => date('l, d \d\e F \d\e Y'),
        ];

        return view('dashboard/index', $data);
    }

    /**
     * El título de la página se adapta al tipo de negocio.
     */
    private function buildTitle(array $features): string
    {
        if ($features['has_tours'] && $features['has_accommodation']) {
            return 'Operaciones';
        }
        if ($features['has_tours']) {
            return 'Operaciones de Tours';
        }
        return 'Recepción';
    }
}
