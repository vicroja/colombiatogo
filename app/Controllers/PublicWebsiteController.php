<?php

namespace App\Controllers;

use App\Models\TenantModel;
use App\Models\TenantWebsiteModel;
use App\Models\TenantMediaModel;
use App\Models\AccommodationUnitModel;
use App\Models\GuestModel;
use App\Models\ReservationModel;

class PublicWebsiteController extends BaseController
{
    // Carga la página de aterrizaje (Landing Page)
    public function index($slug)
    {
        $tenantModel = new TenantModel();
        $websiteModel = new TenantWebsiteModel();
        $mediaModel = new TenantMediaModel();
        $unitModel = new AccommodationUnitModel();

        // 1. Buscar el hotel por su slug
        $tenant = $tenantModel->where('slug', $slug)->where('is_active', 1)->first();
        if (!$tenant) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        // 2. Buscar la configuración web
        $website = $websiteModel->where('tenant_id', $tenant['id'])->where('is_published', 1)->first();
        if (!$website) return "Este hotel aún no ha publicado su sitio web.";

        // 3. Cargar la galería y las habitaciones disponibles
        $media = $mediaModel->where('tenant_id', $tenant['id'])->where('entity_type', 'tenant')->findAll();
        $units = $unitModel->where('tenant_id', $tenant['id'])->where('status !=', 'maintenance')->findAll();


        // 4. Determinar qué plantilla usar (Si está vacío, por defecto 'resort')
        $theme = !empty($website['theme_slug']) ? $website['theme_slug'] : 'resort';

        // 5. Cargar la vista dinámicamente según la plantilla elegida
        return view("public/themes/{$theme}/index", [
            'tenant'  => $tenant,
            'website' => $website,
            'media'   => $media,
            'units'   => $units
        ]);
    }

    // Procesa la reserva hecha por el cliente en la web
    public function confirm($slug)
    {
        $tenantModel = new TenantModel();
        $tenant = $tenantModel->where('slug', $slug)->first();
        if (!$tenant) return redirect()->back();

        $checkIn = $this->request->getPost('check_in_date');
        $checkOut = $this->request->getPost('check_out_date');
        $unitId = $this->request->getPost('unit_id');

        // (Aquí en una versión Pro iría la validación de disponibilidad exacta cruzando fechas)
        // Para el MVP, creamos la reserva en estado 'pending' para que Recepción la confirme

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Registrar o buscar al huésped
        $guestModel = new GuestModel();
        $guest = $guestModel->where('tenant_id', $tenant['id'])
            ->where('document', $this->request->getPost('document'))
            ->first();

        if (!$guest) {
            $guestId = $guestModel->insert([
                'tenant_id' => $tenant['id'],
                'full_name' => $this->request->getPost('full_name'),
                'document'  => $this->request->getPost('document'),
                'email'     => $this->request->getPost('email'),
                'phone'     => $this->request->getPost('phone'),
                'country'   => 'Colombia' // Por defecto para MVP
            ]);
        } else {
            $guestId = $guest['id'];
        }

        // 2. Crear la reserva
        $resModel = new ReservationModel();

        // Calcular noches
        $datetime1 = new \DateTime($checkIn);
        $datetime2 = new \DateTime($checkOut);
        $nights = $datetime1->diff($datetime2)->days;

        // Calcular un precio estimado (usando la tarifa base de la habitación como ejemplo)
        $unitModel = new AccommodationUnitModel();


        $unit        = $unitModel->find($unitId);
        $unitRateModel = new \App\Models\UnitRateModel();

// Buscar tarifa por defecto o la primera activa
        $ratePlanModel = new \App\Models\RatePlanModel();
        $defaultPlan   = $ratePlanModel
            ->where('tenant_id', $tenant['id'])
            ->where('is_default', 1)
            ->first();

        $rate = null;
        if ($defaultPlan) {
            $rate = $unitRateModel
                ->where('unit_id', $unitId)
                ->where('rate_plan_id', $defaultPlan['id'])
                ->first();
        }
        if (!$rate) {
            $rate = $unitRateModel
                ->where('unit_id', $unitId)
                ->where('is_active', 1)
                ->first();
        }

        $pricePerNight  = $rate['price_per_night'] ?? 0;
        $estimatedTotal = $pricePerNight * $nights;


        $reservationId = $resModel->insert([
            'tenant_id'             => $tenant['id'],
            'guest_id'              => $guestId,
            'accommodation_unit_id' => $unitId,
            'check_in_date'         => $checkIn,
            'check_out_date'        => $checkOut,
            'nights'                => $nights,
            'adults'                => $this->request->getPost('adults'),
            'total_price'           => $estimatedTotal,
            'status'                => 'pending' // Pendiente de pago/confirmación
        ]);

        // ==========================================
        // NUEVO: RASTREO DE COMISIÓN DESDE LA WEB
        // ==========================================
        $refCode = $this->request->getPost('agent_ref');

        if (!empty($refCode)) {
            $agentModel = new \App\Models\CommissionAgentModel();

            // Buscamos si existe un agente activo con ese código en este hotel
            $agent = $agentModel->where('tenant_id', $tenant['id'])
                ->where('tracking_code', strtoupper(trim($refCode)))
                ->where('is_active', 1)
                ->first();

            if ($agent) {
                $commissionModel = new \App\Models\CommissionModel();

                // Calculamos cuánto le toca
                $commissionAmount = 0;
                if ($agent['commission_type'] == 'percentage') {
                    $commissionAmount = $estimatedTotal * ($agent['commission_value'] / 100);
                } else {
                    $commissionAmount = $agent['commission_value'];
                }

                // Anotamos la deuda en el libro contable
                $commissionModel->insert([
                    'tenant_id'      => $tenant['id'],
                    'reservation_id' => $reservationId, // Usamos el ID de la reserva que acabamos de crear
                    'agent_id'       => $agent['id'],
                    'amount'         => $commissionAmount,
                    'status'         => 'pending'
                ]);
            }
        }


        $db->transComplete();

        return redirect()->to("/book/{$slug}/success");
    }

    public function success($slug)
    {
        $tenantModel = new TenantModel();
        $websiteModel = new TenantWebsiteModel();

        $tenant = $tenantModel->where('slug', $slug)->first();
        $website = $websiteModel->where('tenant_id', $tenant['id'])->first();

        return view('public/success', [
            'tenant' => $tenant,
            'website' => $website
        ]);
    }
}