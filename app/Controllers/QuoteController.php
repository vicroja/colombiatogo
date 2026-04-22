<?php

namespace App\Controllers;

use App\Models\AccommodationUnitModel;
use App\Models\RatePlanModel;
use App\Models\ReservationModel;
use App\Services\PriceCalculatorService;

class QuoteController extends BaseController
{
    // =========================================================================
    // VISTA PRINCIPAL
    // =========================================================================
    public function index()
    {
        $planModel = new RatePlanModel();

        return view('reservations/quote', [
            'plans' => $planModel->where('is_active', 1)->findAll(),
        ]);
    }

    // =========================================================================
    // ENDPOINT AJAX: Calcula opciones para todas las unidades disponibles
    // GET /reservations/quote/search
    // =========================================================================
    public function search()
    {
        $tenantId   = session('active_tenant_id');
        $checkIn    = $this->request->getGet('check_in');
        $checkOut   = $this->request->getGet('check_out');
        $numAdults  = (int)($this->request->getGet('adults')   ?? 1);
        $numChildren= (int)($this->request->getGet('children') ?? 0);
        $currency   = session('currency_symbol') ?: '$';

        // Validación básica
        if (!$checkIn || !$checkOut || strtotime($checkIn) >= strtotime($checkOut)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Fechas inválidas.'
            ]);
        }

        $unitModel  = new AccommodationUnitModel();
        $planModel  = new RatePlanModel();
        $resModel   = new ReservationModel();
        $calculator = new PriceCalculatorService();

        // 1. Traer solo unidades padre (sin sub-habitaciones) que no estén en mantenimiento
        $units = $unitModel
            ->select('accommodation_units.*, accommodation_types.name as type_name')
            ->join('accommodation_types', 'accommodation_types.id = accommodation_units.type_id', 'left')
            ->where('accommodation_units.tenant_id', $tenantId)
            ->where('accommodation_units.status !=', 'maintenance')
            ->where('accommodation_units.parent_id IS NULL', null, false)
            ->findAll();

        // 2. Unidades ocupadas en esas fechas
        $occupiedIds = $this->getOccupiedUnitIds($tenantId, $checkIn, $checkOut, $resModel);

        // 3. Planes activos
        $plans = $planModel->where('is_active', 1)->findAll();

        // 4. Ocupación actual para contexto de IA
        $totalUnits    = count($units);
        $occupiedCount = count($occupiedIds);
        $occupancyRate = $totalUnits > 0 ? round(($occupiedCount / $totalUnits) * 100) : 0;

        // 5. Calcular precios para cada unidad × cada plan
        $results = [];

        foreach ($units as $unit) {
            $isAvailable = !in_array($unit['id'], $occupiedIds);

            // Amenidades desde JSON
            $amenities = [];
            if (!empty($unit['amenities'])) {
                $decoded = is_string($unit['amenities'])
                    ? json_decode($unit['amenities'], true)
                    : $unit['amenities'];
                if (is_array($decoded)) {
                    $amenities = array_keys(array_filter($decoded));
                }
            }

            // Calcular para cada plan
            $planOptions = [];
            $lowestTotal = PHP_INT_MAX;
            $lowestPlanId = null;

            foreach ($plans as $plan) {
                try {
                    $calc = $calculator->calculateStay(
                        $unit['id'],
                        $plan['id'],
                        $checkIn,
                        $checkOut,
                        $numAdults,
                        $numChildren
                    );

                    // Si no hay tarifa configurada (precio = 0), omitir este plan
                    if ($calc['total_price'] <= 0) continue;

                    // Decodificar amenities del plan
                    $planAmenities = [];
                    if (!empty($plan['amenities_json'])) {
                        $pa = is_string($plan['amenities_json'])
                            ? json_decode($plan['amenities_json'], true)
                            : $plan['amenities_json'];
                        if (is_array($pa)) {
                            $planAmenities = array_keys(array_filter($pa));
                        }
                    }

                    // Detectar si hay temporada aplicada
                    $seasonsApplied = array_unique(array_filter(
                        array_column($calc['daily_details'], 'season'),
                        fn($s) => $s !== 'Tarifa Base'
                    ));

                    $planOptions[] = [
                        'plan_id'         => $plan['id'],
                        'plan_name'       => $plan['name'],
                        'plan_amenities'  => $planAmenities,
                        'cancellation'    => $plan['cancellation_policy'] ?? 'flexible',
                        'min_nights'      => $plan['min_nights_default'] ?? 1,
                        'nights'          => $calc['nights'],
                        'base_capacity'   => $calc['base_capacity'],
                        'room_total'      => $calc['room_total'],
                        'extra_total'     => $calc['extra_total'],
                        'extra_adults'    => $calc['extra_adults'],
                        'extra_children'  => $calc['extra_children'],
                        'total_price'     => $calc['total_price'],
                        'price_per_night' => round($calc['total_price'] / max($calc['nights'], 1), 0),
                        'seasons_applied' => array_values($seasonsApplied),
                        'daily_details'   => $calc['daily_details'],
                    ];

                    if ($calc['total_price'] < $lowestTotal) {
                        $lowestTotal   = $calc['total_price'];
                        $lowestPlanId  = $plan['id'];
                    }
                } catch (\Exception $e) {
                    log_message('warning', "[QuoteController] Unidad {$unit['id']} plan {$plan['id']}: " . $e->getMessage());
                    continue;
                }
            }

            // No mostrar unidades sin ningún plan con tarifa
            if (empty($planOptions)) continue;

            // Ordenar planes: el más barato primero
            usort($planOptions, fn($a, $b) => $a['total_price'] <=> $b['total_price']);

            // Días desde la última reserva (para contexto IA)
            $lastReservation = $resModel
                ->where('accommodation_unit_id', $unit['id'])
                ->where('status !=', 'cancelled')
                ->orderBy('check_out_date', 'DESC')
                ->first();
            $daysSinceLastCheckout = null;
            if ($lastReservation) {
                $diff = (strtotime(date('Y-m-d')) - strtotime($lastReservation['check_out_date'])) / 86400;
                $daysSinceLastCheckout = max(0, (int)$diff);
            }

            $results[] = [
                'unit_id'                  => $unit['id'],
                'unit_name'                => $unit['name'],
                'unit_type'                => $unit['type_name'] ?? '',
                'base_occupancy'           => $unit['base_occupancy'] ?? 2,
                'max_occupancy'            => $unit['max_occupancy'] ?? 10,
                'beds_info'                => $unit['beds_info'] ?? '',
                'bathrooms'                => $unit['bathrooms'] ?? 1,
                'description'              => $unit['description'] ?? '',
                'amenities'                => $amenities,
                'is_available'             => $isAvailable,
                'days_since_last_checkout' => $daysSinceLastCheckout,
                'plan_options'             => $planOptions,
                'lowest_plan_id'           => $lowestPlanId,
                'lowest_total'             => $lowestTotal === PHP_INT_MAX ? 0 : $lowestTotal,
            ];
        }

        // Ordenar: disponibles primero, luego por precio
        usort($results, function ($a, $b) {
            if ($a['is_available'] !== $b['is_available']) {
                return $a['is_available'] ? -1 : 1;
            }
            return $a['lowest_total'] <=> $b['lowest_total'];
        });

        $nights = (int)(
            (strtotime($checkOut) - strtotime($checkIn)) / 86400
        );

        return $this->response->setJSON([
            'success'        => true,
            'results'        => $results,
            'meta'           => [
                'check_in'       => $checkIn,
                'check_out'      => $checkOut,
                'nights'         => $nights,
                'adults'         => $numAdults,
                'children'       => $numChildren,
                'total_units'    => $totalUnits,
                'occupied_count' => $occupiedCount,
                'occupancy_rate' => $occupancyRate,
                'currency'       => $currency,
            ],
        ]);
    }

    // =========================================================================
    // ENDPOINT AJAX: Recomendación IA
    // POST /reservations/quote/ai-suggest
    // =========================================================================
    public function aiSuggest()
    {
        $body        = $this->request->getJSON(true);
        $results     = $body['results']  ?? [];
        $meta        = $body['meta']     ?? [];
        $tenantName  = session('tenant_name') ?? 'el hotel';

        if (empty($results)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sin opciones para analizar.'
            ]);
        }

        // Construir contexto compacto para Gemini (solo lo necesario)
        $available = array_filter($results, fn($r) => $r['is_available']);
        $contextUnits = array_map(function ($r) {
            $cheapestPlan = $r['plan_options'][0] ?? null;
            return [
                'id'              => $r['unit_id'],
                'name'            => $r['unit_name'],
                'type'            => $r['unit_type'],
                'base_capacity'   => $r['base_occupancy'],
                'max_capacity'    => $r['max_occupancy'],
                'amenities'       => array_slice($r['amenities'], 0, 6),
                'lowest_price'    => $r['lowest_total'],
                'days_vacant'     => $r['days_since_last_checkout'],
                'plans_count'     => count($r['plan_options']),
                'cheapest_plan'   => $cheapestPlan ? [
                    'name'  => $cheapestPlan['plan_name'],
                    'total' => $cheapestPlan['total_price'],
                    'extras'=> $cheapestPlan['extra_total'],
                ] : null,
            ];
        }, array_values($available));

        $prompt = "Eres un experto en gestión hotelera. El hotel '{$tenantName}' tiene una ocupación actual del {$meta['occupancy_rate']}% ({$meta['occupied_count']} de {$meta['total_units']} unidades ocupadas).

Una recepcionista está cotizando para: {$meta['adults']} adulto(s) y {$meta['children']} niño(s), del {$meta['check_in']} al {$meta['check_out']} ({$meta['nights']} noches).

Unidades disponibles con sus precios más bajos (en {$meta['currency']}):
" . json_encode($contextUnits, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "

Analiza y devuelve SOLO un objeto JSON válido con esta estructura exacta (sin texto adicional, sin markdown):
{
  \"best_for_guest\": {
    \"unit_id\": <número>,
    \"unit_name\": \"<nombre>\",
    \"reason\": \"<explicación corta en español, máximo 20 palabras, enfocada en beneficios para el huésped>\"
  },
  \"best_for_hotel\": {
    \"unit_id\": <número>,
    \"unit_name\": \"<nombre>\",
    \"reason\": \"<explicación corta en español, máximo 20 palabras, enfocada en optimización de ocupación>\"
  },
  \"upsell\": \"<sugerencia de upgrade o servicio adicional, máximo 15 palabras, o null si no aplica>\",
  \"occupancy_insight\": \"<observación breve sobre la situación de ocupación del hotel, máximo 15 palabras>\"
}";

        $gemini = new \App\Models\GeminiModel();
        $response = $gemini->generateText($prompt, 600, 0.3);

        if (!($response['success'] ?? false)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se pudo obtener la recomendación IA.'
            ]);
        }

        // Limpiar y parsear JSON
        $text  = $response['text'];
        $text  = preg_replace('/```json|```/i', '', $text);
        $start = strpos($text, '{');
        $end   = strrpos($text, '}');
        if ($start === false || $end === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Respuesta IA inválida.']);
        }
        $text    = substr($text, $start, $end - $start + 1);
        $decoded = json_decode($text, true);

        if (!$decoded) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se pudo parsear la respuesta IA.']);
        }

        return $this->response->setJSON([
            'success'    => true,
            'suggestion' => $decoded,
        ]);
    }

    // =========================================================================
    // HELPER: unidades ocupadas en un rango de fechas
    // =========================================================================
    private function getOccupiedUnitIds(
        int $tenantId,
        string $checkIn,
        string $checkOut,
        ReservationModel $resModel
    ): array {
        // Solapamiento: reservas que empiecen antes del checkout Y terminen después del checkin
        $db = \Config\Database::connect();
        $rows = $db->table('reservations')
            ->select('accommodation_unit_id')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where('check_in_date <', $checkOut)
            ->where('check_out_date >', $checkIn)
            ->get()
            ->getResultArray();

        return array_unique(array_column($rows, 'accommodation_unit_id'));
    }
}