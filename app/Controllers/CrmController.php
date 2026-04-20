<?php

namespace App\Controllers;

use App\Models\GuestModel;
use App\Models\ReservationModel;
use App\Models\ReservationConsumptionModel;
use App\Models\AccommodationUnitModel;
use App\Models\GeminiModel;

/**
 * CrmController
 *
 * Módulo CRM de huéspedes con scoring RFM, perfiles individuales,
 * notas del personal y mensajes generados por IA.
 */
class CrmController extends BaseController
{
    private GuestModel               $guestModel;
    private ReservationModel         $resModel;
    private AccommodationUnitModel   $unitModel;
    private GeminiModel              $geminiModel;
    private \CodeIgniter\Database\BaseConnection $db;
    private int   $tenantId;
    private array $tenant;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface            $logger
    ): void {
        parent::initController($request, $response, $logger);

        $this->tenantId    = session('active_tenant_id');
        $this->guestModel  = new GuestModel();
        $this->resModel    = new ReservationModel();
        $this->unitModel   = new AccommodationUnitModel();
        $this->geminiModel = new GeminiModel();
        $this->db          = \Config\Database::connect();
        $this->tenant      = (new \App\Models\TenantModel())->find($this->tenantId) ?? [];
    }

    // =========================================================================
    // INDEX — Lista de huéspedes con scoring RFM
    // =========================================================================
    public function index(): string
    {
        $segment  = $this->request->getGet('segment') ?? '';
        $search   = $this->request->getGet('q')       ?? '';
        $sort     = $this->request->getGet('sort')    ?? 'score';

        // Calcular RFM para todos los huéspedes del tenant
        $guests = $this->buildGuestList($segment, $search, $sort);

        // Stats globales del CRM
        $stats = $this->buildCrmStats($guests);

        return view('crm/index', [
            'guests'  => $guests,
            'stats'   => $stats,
            'segment' => $segment,
            'search'  => $search,
            'sort'    => $sort,
            'tenant'  => $this->tenant,
        ]);
    }

    // =========================================================================
    // SHOW — Perfil individual del huésped
    // =========================================================================
    public function show(int $guestId): string
    {
        $guest = $this->guestModel->find($guestId);

        if (!$guest) {
            return redirect()->to('/crm')->with('error', 'Huésped no encontrado.');
        }

        // Historial completo de reservaciones
        $reservations = $this->db->table('reservations r')
            ->select('r.*, au.name as unit_name,
                      DATEDIFF(r.check_out_date, r.check_in_date) as nights')
            ->join('accommodation_units au', 'au.id = r.accommodation_unit_id', 'left')
            ->where('r.guest_id', $guestId)
            ->where('r.tenant_id', $this->tenantId)
            ->orderBy('r.check_in_date', 'DESC')
            ->get()->getResultArray();

        // Consumos por reservación
        foreach ($reservations as &$res) {
            $res['consumptions'] = $this->db->table('reservation_consumptions')
                ->where('reservation_id', $res['id'])
                ->get()->getResultArray();
            $res['total_consumptions'] = array_sum(
                array_column($res['consumptions'], 'subtotal')
            );
        }
        unset($res);

        // Score RFM del huésped
        $rfm = $this->calculateRfm($guest, $reservations);

        // Notas del personal
        $notes = $this->db->table('guest_notes')
            ->select('guest_notes.*, users.name as author_name')
            ->join('users', 'users.id = guest_notes.created_by', 'left')
            ->where('guest_notes.guest_id', $guestId)
            ->where('guest_notes.tenant_id', $this->tenantId)
            ->orderBy('guest_notes.created_at', 'DESC')
            ->get()->getResultArray();

        // Historial de mensajes CRM enviados
        $messages = $this->db->table('crm_messages')
            ->where('guest_id', $guestId)
            ->where('tenant_id', $this->tenantId)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        // Preferencias detectadas automáticamente
        $preferences = $this->detectPreferences($reservations);

        return view('crm/show', [
            'guest'       => $guest,
            'reservations'=> $reservations,
            'rfm'         => $rfm,
            'notes'       => $notes,
            'messages'    => $messages,
            'preferences' => $preferences,
            'tenant'      => $this->tenant,
        ]);
    }

    // =========================================================================
    // ADD NOTE — Agregar nota manual
    // =========================================================================
    public function addNote(int $guestId): \CodeIgniter\HTTP\RedirectResponse
    {
        $note = trim($this->request->getPost('note') ?? '');

        if (!empty($note)) {
            $this->db->table('guest_notes')->insert([
                'tenant_id'  => $this->tenantId,
                'guest_id'   => $guestId,
                'note'       => $note,
                'created_by' => session('user_id'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to('/crm/guest/' . $guestId . '#notas')
            ->with('success', 'Nota agregada.');
    }

    // =========================================================================
    // SEND MESSAGE — Registrar mensaje enviado
    // =========================================================================
    public function sendMessage(int $guestId): \CodeIgniter\HTTP\RedirectResponse
    {
        $body    = trim($this->request->getPost('message_body') ?? '');
        $channel = $this->request->getPost('channel') ?? 'whatsapp';
        $aiGen   = $this->request->getPost('ai_generated') ? 1 : 0;

        if (!empty($body)) {
            $this->db->table('crm_messages')->insert([
                'tenant_id'    => $this->tenantId,
                'guest_id'     => $guestId,
                'channel'      => $channel,
                'message_body' => $body,
                'ai_generated' => $aiGen,
                'status'       => 'sent',
                'sent_at'      => date('Y-m-d H:i:s'),
                'created_at'   => date('Y-m-d H:i:s'),
            ]);

            log_message('info', "[CRM] Mensaje registrado para huésped #{$guestId}");
        }

        return redirect()->to('/crm/guest/' . $guestId . '#mensajes')
            ->with('success', 'Mensaje registrado correctamente.');
    }

    // =========================================================================
    // AI MESSAGE — Generar mensaje con Gemini (AJAX)
    // =========================================================================
    public function aiMessage(): \CodeIgniter\HTTP\ResponseInterface
    {
        $input   = $this->request->getJSON(true);
        $guestId = (int) ($input['guest_id'] ?? 0);
        $goal    = trim($input['goal'] ?? 'reactivar');
        $promo   = trim($input['promo'] ?? '');

        $guest = $this->guestModel->find($guestId);
        if (!$guest) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Huésped no encontrado.'
            ]);
        }

        // Recopilar contexto del huésped
        $reservations = $this->db->table('reservations r')
            ->select('r.*, au.name as unit_name,
                      DATEDIFF(r.check_out_date, r.check_in_date) as nights')
            ->join('accommodation_units au', 'au.id = r.accommodation_unit_id', 'left')
            ->where('r.guest_id', $guestId)
            ->where('r.tenant_id', $this->tenantId)
            ->where('r.status', 'checked_out')
            ->orderBy('r.check_in_date', 'DESC')
            ->get()->getResultArray();

        $rfm         = $this->calculateRfm($guest, $reservations);
        $preferences = $this->detectPreferences($reservations);
        $hotelName   = $this->tenant['name'] ?? '';

        // Construir contexto para el prompt
        $lastVisit   = !empty($reservations)
            ? date('d/m/Y', strtotime($reservations[0]['check_in_date']))
            : null;
        $favUnit     = $preferences['favorite_unit'] ?? null;
        $totalVisits = count($reservations);
        $totalSpent  = array_sum(array_column($reservations, 'total_price'));

        $goalPrompts = [
            'reactivar'  => 'invitarlo a volver después de un tiempo sin visitar',
            'fidelizar'  => 'agradecer su lealtad y ofrecerle un beneficio especial',
            'promocion'  => 'comunicarle una promoción o descuento disponible',
            'bienvenida' => 'darle la bienvenida por su primera visita y motivar una segunda',
            'cumpleanos' => 'felicitarlo y ofrecerle algo especial por su cumpleaños',
        ];

        $goalDesc = $goalPrompts[$goal] ?? $goal;

        $prompt = "Eres el asistente de comunicaciones del hotel '{$hotelName}'. " .
            "Escribe un mensaje de WhatsApp CORTO, cálido y personal para el huésped " .
            "llamado {$guest['full_name']}. " .
            ($lastVisit ? "Su última visita fue el {$lastVisit}. " : '') .
            ($favUnit ? "Su unidad favorita es '{$favUnit}'. " : '') .
            ($totalVisits > 1 ? "Ha visitado el hotel {$totalVisits} veces. " : '') .
            "Segmento del cliente: {$rfm['segment_label']}. " .
            "El objetivo del mensaje es: {$goalDesc}. " .
            ($promo ? "Incluye esta promoción o detalle especial: {$promo}. " : '') .
            "El mensaje debe sonar como escrito por el dueño del hotel, no como marketing masivo. " .
            "Máximo 3 oraciones. Solo el mensaje, sin explicaciones ni saludos de cierre genéricos.";

        $result = $this->geminiModel->generateText($prompt, 300, 0.85);

        if ($result['success']) {
            log_message('info', "[CRM/AI] Mensaje generado para huésped #{$guestId}");
            return $this->response->setJSON([
                'success' => true,
                'message' => trim($result['text']),
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => $result['message'] ?? 'Error generando mensaje.'
        ]);
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    /**
     * Construye la lista de huéspedes con score RFM calculado
     */
    private function buildGuestList(
        string $segment,
        string $search,
        string $sort
    ): array {
        // Traer todos los huéspedes con sus stats de reservaciones
        $query = $this->db->table('guests g')
            ->select('
                g.*,
                COUNT(CASE WHEN r.status = "checked_out" THEN 1 END) as completed_reservations,
 COUNT(r.id) as total_reservations,
                SUM(CASE WHEN r.status = "checked_out"
                    THEN r.total_price ELSE 0 END)       as total_spent,
                MAX(r.check_in_date)                     as last_visit,
                MIN(r.check_in_date)                     as first_visit,
                SUM(CASE WHEN r.status = "cancelled"
                    THEN 1 ELSE 0 END)                   as cancellations,
            
            ')
            ->join('reservations r',
                'r.guest_id = g.id AND r.tenant_id = ' . $this->tenantId,
                'left')
            ->join('accommodation_units au',
                'au.id = r.accommodation_unit_id', 'left')
            ->where('g.tenant_id', $this->tenantId)
            ->groupBy('g.id');

        if (!empty($search)) {
            $query->groupStart()
                ->like('g.full_name', $search)
                ->orLike('g.email',   $search)
                ->orLike('g.phone',   $search)
                ->groupEnd();
        }

        $guests = $query->get()->getResultArray();

        // Calcular RFM para cada huésped
        foreach ($guests as &$guest) {
            $reservations = $this->db->table('reservations')
                ->where('guest_id',  $guest['id'])
                ->where('tenant_id', $this->tenantId)
                ->where('status',    'checked_out')
                ->get()->getResultArray();

            $rfm            = $this->calculateRfm($guest, $reservations);
            $guest['rfm']   = $rfm;
            $guest['score'] = $rfm['score'];
            $guest['segment']       = $rfm['segment'];
            $guest['segment_label'] = $rfm['segment_label'];
            $guest['segment_color'] = $rfm['segment_color'];
        }
        unset($guest);

        // Filtrar por segmento
        if (!empty($segment)) {
            $guests = array_filter($guests,
                fn($g) => $g['segment'] === $segment);
        }

        // Ordenar
        usort($guests, function ($a, $b) use ($sort) {
            return match($sort) {
                'name'       => strcmp($a['full_name'], $b['full_name']),
                'spent'      => $b['total_spent'] <=> $a['total_spent'],
                'visits'     => $b['total_reservations'] <=> $a['total_reservations'],
                'last_visit' => strcmp($b['last_visit'] ?? '', $a['last_visit'] ?? ''),
                default      => $b['score'] <=> $a['score'],
            };
        });

        return array_values($guests);
    }

    /**
     * Calcula el score RFM de un huésped
     */
    private function calculateRfm(array $guest, array $reservations): array
    {
        $completed = array_filter($reservations,
            fn($r) => $r['status'] === 'checked_out');
        $completed = array_values($completed);

        $totalVisits = count($completed);
        $totalSpent  = array_sum(array_column($completed, 'total_price'));

        // Recency — días desde última visita
        $lastVisitDate = !empty($completed)
            ? $completed[0]['check_in_date']
            : null;
        $daysSince = $lastVisitDate
            ? (int) ceil((time() - strtotime($lastVisitDate)) / 86400)
            : 999;

        // Scores individuales (1-5)
        $rScore = match(true) {
            $daysSince <= 30   => 5,
            $daysSince <= 90   => 4,
            $daysSince <= 180  => 3,
            $daysSince <= 365  => 2,
            default            => 1,
        };

        $fScore = match(true) {
            $totalVisits >= 5  => 5,
            $totalVisits >= 3  => 4,
            $totalVisits >= 2  => 3,
            $totalVisits === 1 => 2,
            default            => 1,
        };

        $mScore = match(true) {
            $totalSpent >= 2000000 => 5,
            $totalSpent >= 800000  => 4,
            $totalSpent >= 300000  => 3,
            $totalSpent >= 100000  => 2,
            default                => 1,
        };

        // Score compuesto ponderado (R=30%, F=35%, M=35%)
        $score = round(($rScore * 0.30) + ($fScore * 0.35) + ($mScore * 0.35), 1);

        // Segmento basado en patrones RFM
        [$segment, $label, $color, $desc] = match(true) {
            $rScore >= 4 && $fScore >= 4 && $mScore >= 3
            => ['champion',   'Champion',       '#7c3aed', 'Cliente frecuente y reciente con alto gasto'],
            $fScore >= 4
            => ['loyal',      'Leal',           '#2563eb', 'Regresa consistentemente'],
            $rScore <= 2 && $fScore >= 3
            => ['at_risk',    'En riesgo',      '#dc2626', 'Buen cliente que no ha vuelto'],
            $rScore >= 4 && $fScore <= 2 && $mScore >= 4
            => ['potential',  'Alto potencial', '#059669', 'Gasta bien, hay que fidelizarlo'],
            $rScore >= 4 && $fScore <= 2
            => ['new',        'Nuevo',          '#0891b2', 'Primera o segunda visita reciente'],
            $rScore <= 1 && $fScore <= 2
            => ['lost',       'Perdido',        '#94a3b8', 'Hace mucho que no visita'],
            default
            => ['regular',    'Regular',        '#64748b', 'Cliente ocasional'],
        };

        return [
            'r_score'       => $rScore,
            'f_score'       => $fScore,
            'm_score'       => $mScore,
            'score'         => $score,
            'segment'       => $segment,
            'segment_label' => $label,
            'segment_color' => $color,
            'segment_desc'  => $desc,
            'total_visits'  => $totalVisits,
            'total_spent'   => $totalSpent,
            'days_since'    => $daysSince,
            'last_visit'    => $lastVisitDate,
        ];
    }

    /**
     * Detecta preferencias automáticas del huésped
     */
    private function detectPreferences(array $reservations): array
    {
        if (empty($reservations)) return [];

        // Unidad favorita
        $unitCounts = array_count_values(
            array_column($reservations, 'unit_name')
        );
        arsort($unitCounts);
        $favoriteUnit = array_key_first($unitCounts);

        // Mes favorito de viaje
        $months = array_map(
            fn($r) => date('n', strtotime($r['check_in_date'])),
            $reservations
        );
        $monthCounts = array_count_values($months);
        arsort($monthCounts);
        $favoriteMonth = array_key_first($monthCounts);

        // Tamaño de grupo promedio
        $avgAdults = !empty($reservations)
            ? round(array_sum(array_column($reservations, 'num_adults'))
                / count($reservations))
            : 2;

        // Duración promedio de estadía
        $nights = array_filter(array_column($reservations, 'nights'));
        $avgNights = !empty($nights)
            ? round(array_sum($nights) / count($nights), 1)
            : 1;

        $monthNames = [
            1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
            5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
            9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'
        ];

        return [
            'favorite_unit'  => $favoriteUnit,
            'favorite_month' => $monthNames[$favoriteMonth] ?? null,
            'avg_adults'     => $avgAdults,
            'avg_nights'     => $avgNights,
        ];
    }

    /**
     * Stats globales del CRM
     */
    private function buildCrmStats(array $guests): array
    {
        $segments = array_count_values(array_column($guests, 'segment'));

        return [
            'total'      => count($guests),
            'champions'  => $segments['champion']  ?? 0,
            'at_risk'    => $segments['at_risk']    ?? 0,
            'loyal'      => $segments['loyal']      ?? 0,
            'new'        => $segments['new']        ?? 0,
            'lost'       => $segments['lost']       ?? 0,
            'repeat_pct' => count($guests) > 0
                ? round(count(array_filter($guests,
                        fn($g) => ($g['total_reservations'] ?? 0) > 1))
                    / count($guests) * 100)
                : 0,
            'avg_score'  => count($guests) > 0
                ? round(array_sum(array_column($guests, 'score'))
                    / count($guests), 1)
                : 0,
            'total_revenue' => array_sum(array_column($guests, 'total_spent')),
        ];
    }
}