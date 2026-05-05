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
        // Query simplificada — solo datos del huésped
        $query = $this->db->table('guests g')
            ->select('g.*')
            ->where('g.tenant_id', $this->tenantId);

        if (!empty($search)) {
            $query->groupStart()
                ->like('g.full_name', $search)
                ->orLike('g.email',   $search)
                ->orLike('g.phone',   $search)
                ->groupEnd();
        }

        $guests = $query->get()->getResultArray();

        // Para cada huésped calcular stats y RFM por separado
        foreach ($guests as &$guest) {
            // Traer reservaciones completadas
            $reservations = $this->db->table('reservations r')
                ->select('r.*, DATEDIFF(r.check_out_date, r.check_in_date) as nights')
                ->where('r.guest_id',  $guest['id'])
                ->where('r.tenant_id', $this->tenantId)
                ->orderBy('r.check_in_date', 'DESC')
                ->get()->getResultArray();

            // Stats básicas
            $completed = array_filter($reservations,
                fn($r) => $r['status'] === 'checked_out');

            $guest['total_reservations']   = count($reservations);
            $guest['completed_reservations']= count($completed);
            $guest['total_spent']          = array_sum(
                array_column(array_values($completed), 'total_price')
            );
            $guest['last_visit'] = !empty($completed)
                ? array_values($completed)[0]['check_in_date']
                : null;

            // RFM
            $rfm            = $this->calculateRfm($guest, array_values($completed));
            $guest['rfm']   = $rfm;
            $guest['score'] = $rfm['score'];
            $guest['segment']       = $rfm['segment'];
            $guest['segment_label'] = $rfm['segment_label'];
            $guest['segment_color'] = $rfm['segment_color'];
        }
        unset($guest);

        // Filtrar por segmento
        if (!empty($segment)) {
            $guests = array_values(array_filter($guests,
                fn($g) => $g['segment'] === $segment));
        }

        // Ordenar
        usort($guests, function ($a, $b) use ($sort) {
            return match($sort) {
                'name'       => strcmp($a['full_name'], $b['full_name']),
                'spent'      => $b['total_spent'] <=> $a['total_spent'],
                'visits'     => $b['completed_reservations'] <=> $a['completed_reservations'],
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

    // Reemplaza buildCrmStats completo
    private function buildCrmStats(array $guests): array
    {
        $segments = array_count_values(array_column($guests, 'segment'));

        return [
            'total'     => count($guests),
            'champions' => $segments['champion']  ?? 0,
            'loyal'     => $segments['loyal']     ?? 0,
            'at_risk'   => $segments['at_risk']   ?? 0,
            'potential' => $segments['potential'] ?? 0,
            'new'       => $segments['new']       ?? 0,
            'lost'      => $segments['lost']      ?? 0,
            'regular'   => $segments['regular']   ?? 0,
            'repeat_pct'=> count($guests) > 0
                ? round(count(array_filter($guests,
                        fn($g) => ($g['completed_reservations'] ?? 0) > 1))
                    / count($guests) * 100)
                : 0,
            'avg_score' => count($guests) > 0
                ? round(array_sum(array_column($guests, 'score'))
                    / count($guests), 1)
                : 0,
            'total_revenue' => array_sum(array_column($guests, 'total_spent')),
        ];
    }
    // =========================================================================
// PIPELINE — Funnel de conversión en tiempo real (Kanban)
// =========================================================================
    public function pipeline(): string
    {
        // ── 1. Obtener todos los guests activos con su etapa del funnel ──────
        $guests = $this->db->table('guests g')
            ->select('
            g.id, g.full_name, g.phone, g.funnel_stage, g.chat_state,
            g.ai_active, g.conversation_context_json, g.updated_at,
            TIMESTAMPDIFF(MINUTE, g.updated_at, NOW()) as minutos_en_etapa
        ')
            ->where('g.tenant_id', $this->tenantId)
            ->whereIn('g.chat_state', ['ACTIVE', 'WAITING_USER', 'OMITTED'])
            ->orderBy('g.updated_at', 'DESC')
            ->get()
            ->getResultArray();

        // ── 2. Enriquecer cada guest con datos relevantes ───────────────────
        foreach ($guests as &$g) {
            // Contexto de conversación (objeciones, tour consultado, etc.)
            $g['contexto'] = json_decode($g['conversation_context_json'] ?? '{}', true) ?? [];

            // Último mensaje de la conversación (para saber si el bot o el cliente habló último)
            $ultimoMsg = $this->db->table('whatsapp_messages')
                ->select('direction, message_body, created_at')
                ->where('tenant_id', $this->tenantId)
                ->groupStart()
                ->where('sender_phone', $g['phone'])
                ->orWhere('recipient_phone', $g['phone'])
                ->groupEnd()
                ->where('message_body NOT LIKE', '[Consultando:%')
                ->where('message_body NOT LIKE', '[RESULTADO DE HERRAMIENTAS]%')
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->get()
                ->getRow();

            $g['ultimo_msg_direction'] = $ultimoMsg->direction ?? null;
            $g['ultimo_msg_time']     = $ultimoMsg->created_at ?? null;

            // Total de mensajes intercambiados (para medir engagement)
            $g['total_mensajes'] = $this->db->table('whatsapp_messages')
                ->where('tenant_id', $this->tenantId)
                ->groupStart()
                ->where('sender_phone', $g['phone'])
                ->orWhere('recipient_phone', $g['phone'])
                ->groupEnd()
                ->countAllResults();

            // Tiene reservas activas? (para detectar post_booking legítimos)
            $g['tiene_reserva'] = $this->db->table('reservations')
                    ->where('guest_id', $g['id'])
                    ->where('tenant_id', $this->tenantId)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->countAllResults() > 0;

            // Tiene tours reservados?
            $g['tiene_tour'] = $this->db->table('tour_reservations tr')
                    ->join('tour_schedules ts', 'ts.id = tr.schedule_id')
                    ->where('tr.guest_id', $g['id'])
                    ->where('tr.tenant_id', $this->tenantId)
                    ->whereIn('tr.status', ['pending', 'confirmed'])
                    ->where('ts.start_datetime >=', date('Y-m-d H:i:s'))
                    ->countAllResults() > 0;

            // Calcular nivel de urgencia
            $g['urgencia'] = $this->calcularUrgencia($g);
        }
        unset($g);

        // ── 3. Agrupar por funnel_stage ─────────────────────────────────────
        $stages = ['cold', 'interested', 'evaluating', 'objecting', 'ready_close', 'post_booking'];
        $pipeline = [];
        foreach ($stages as $stage) {
            $pipeline[$stage] = array_values(array_filter($guests, fn($g) => $g['funnel_stage'] === $stage));
        }

        // ── 4. Generar alertas de intervención ──────────────────────────────
        $alertas = $this->generarAlertas($guests);

        // ── 5. Calcular métricas del pipeline ───────────────────────────────
        $totalActivos = count($guests);
        $stats = [
            'total_activos'    => $totalActivos,
            'requieren_accion' => count(array_filter($guests, fn($g) => $g['urgencia'] === 'danger')),
            'ia_desactivada'   => count(array_filter($guests, fn($g) => $g['ai_active'] == 0)),
            'esperando_cliente'=> count(array_filter($guests, fn($g) => $g['ultimo_msg_direction'] === 'outgoing')),
            'por_etapa'        => array_map(fn($s) => count($pipeline[$s]), $stages),
        ];

        // Tasas de conversión entre etapas (últimos 30 días)
        $stats['conversiones'] = $this->calcularTasasConversion();

        return view('crm/pipeline', [
            'pipeline' => $pipeline,
            'alertas'  => $alertas,
            'stats'    => $stats,
            'stages'   => $stages,
            'tenant'   => $this->tenant,
        ]);
    }

// =========================================================================
// HELPERS PRIVADOS PARA PIPELINE
// =========================================================================

    /**
     * Calcula el nivel de urgencia de un lead según su etapa y tiempo estancado.
     */
    private function calcularUrgencia(array $guest): string
    {
        $minutos = (int) $guest['minutos_en_etapa'];
        $stage   = $guest['funnel_stage'];
        $ultimoDir = $guest['ultimo_msg_direction'];

        // Si la IA está desactivada y no hay humano respondiendo → siempre danger
        if ($guest['ai_active'] == 0 && $guest['chat_state'] === 'OMITTED') {
            return 'danger';
        }

        // Umbrales por etapa (en minutos)
        $umbrales = [
            'cold'        => ['warn' =>  360, 'danger' => 1440],  //  6h / 24h
            'interested'  => ['warn' =>  480, 'danger' => 1440],  //  8h / 24h
            'evaluating'  => ['warn' =>  720, 'danger' => 2880],  // 12h / 48h
            'objecting'   => ['warn' =>  360, 'danger' => 1440],  //  6h / 24h
            'ready_close' => ['warn' =>  120, 'danger' =>  480],  //  2h /  8h (urgente!)
            'post_booking'=> ['warn' => 2880, 'danger' => 10080], // 48h /  7d
        ];

        $u = $umbrales[$stage] ?? ['warn' => 720, 'danger' => 2880];

        // Si el último mensaje fue del bot y el cliente no responde, bajar umbrales
        if ($ultimoDir === 'outgoing') {
            $u['warn']   = (int)($u['warn']   * 0.7);
            $u['danger'] = (int)($u['danger'] * 0.7);
        }

        if ($minutos >= $u['danger']) return 'danger';
        if ($minutos >= $u['warn'])   return 'warn';
        return 'ok';
    }

    /**
     * Genera alertas priorizadas para intervención humana.
     */
    private function generarAlertas(array $guests): array
    {
        $alertas = [];

        foreach ($guests as $g) {
            if ($g['urgencia'] === 'ok') continue;

            $minutos  = (int) $g['minutos_en_etapa'];
            $contexto = $g['contexto'];
            $motivo   = '';
            $prioridad = $g['urgencia'] === 'danger' ? 1 : 2;

            // Construir motivo descriptivo
            switch ($g['funnel_stage']) {
                case 'cold':
                    $motivo = 'Escribió pero no se ha identificado su interés';
                    if ($g['ultimo_msg_direction'] === 'outgoing') {
                        $motivo = 'El bot respondió pero el cliente no contestó';
                    }
                    break;

                case 'interested':
                    $tour = $contexto['ultimo_tour_consultado'] ?? null;
                    $consultado = $contexto['disponibilidad_consultada'] ?? false;
                    $motivo = $tour
                        ? "Interesado en \"{$tour}\""
                        : 'Mostró interés pero no especificó qué quiere';
                    if (!$consultado) {
                        $motivo .= ' — aún no se consultó disponibilidad';
                    }
                    break;

                case 'evaluating':
                    $objeciones = $contexto['objeciones_detectadas'] ?? [];
                    $motivo = 'Ya conoce precios, está evaluando';
                    if (!empty($objeciones)) {
                        $objLabels = [
                            'precio'               => 'precio',
                            'politica_cancelacion'  => 'cancelación',
                            'politica_pago'         => 'forma de pago',
                            'fecha'                 => 'fechas',
                        ];
                        $objTexto = array_map(fn($o) => $objLabels[$o] ?? $o, $objeciones);
                        $motivo .= ' — dudas sobre: ' . implode(', ', $objTexto);
                    }
                    break;

                case 'objecting':
                    $objeciones = $contexto['objeciones_detectadas'] ?? [];
                    $motivo = 'Puso freno a la conversación';
                    if (!empty($objeciones)) {
                        $motivo .= ' (' . implode(', ', $objeciones) . ')';
                    }
                    if ($g['ultimo_msg_direction'] === 'outgoing') {
                        $motivo .= ' — no respondió al bot';
                    }
                    break;

                case 'ready_close':
                    $motivo = 'Listo para reservar pero no se completó';
                    if ($g['ai_active'] == 0) {
                        $motivo .= ' — IA desactivada, requiere atención manual';
                    }
                    break;

                case 'post_booking':
                    if (!$g['tiene_reserva'] && !$g['tiene_tour']) {
                        $motivo = 'Marcado post_booking pero sin reserva activa';
                    } else {
                        $motivo = 'Con reserva activa — posible duda de seguimiento';
                    }
                    break;
            }

            // Tiempo humanizado
            $tiempoTexto = $this->humanizarTiempo($minutos);

            $alertas[] = [
                'guest'      => $g,
                'motivo'     => $motivo,
                'tiempo'     => $tiempoTexto,
                'prioridad'  => $prioridad,
                'urgencia'   => $g['urgencia'],
            ];
        }

        // Ordenar por prioridad (danger primero) y luego por tiempo
        usort($alertas, function($a, $b) {
            if ($a['prioridad'] !== $b['prioridad']) {
                return $a['prioridad'] <=> $b['prioridad'];
            }
            return $b['guest']['minutos_en_etapa'] <=> $a['guest']['minutos_en_etapa'];
        });

        return $alertas;
    }

    /**
     * Calcula tasas de conversión entre etapas (últimos 30 días).
     * Usa un approach simplificado: cuenta guests que están o pasaron por cada etapa.
     */
    private function calcularTasasConversion(): array
    {
        $stages = ['cold', 'interested', 'evaluating', 'objecting', 'ready_close', 'post_booking'];
        $stageOrder = array_flip($stages);

        // Contar guests que alcanzaron cada etapa o la superaron
        $counts = [];
        $guests = $this->db->table('guests')
            ->select('funnel_stage')
            ->where('tenant_id', $this->tenantId)
            ->where('updated_at >=', date('Y-m-d', strtotime('-30 days')))
            ->get()
            ->getResultArray();

        foreach ($stages as $s) {
            $counts[$s] = 0;
        }

        foreach ($guests as $g) {
            $guestStageIdx = $stageOrder[$g['funnel_stage']] ?? 0;
            // Este guest "pasó" por todas las etapas hasta su etapa actual
            foreach ($stages as $s) {
                if ($stageOrder[$s] <= $guestStageIdx) {
                    $counts[$s]++;
                }
            }
        }

        // Calcular tasas entre etapas consecutivas
        $tasas = [];
        for ($i = 0; $i < count($stages) - 1; $i++) {
            $from = $stages[$i];
            $to   = $stages[$i + 1];
            $tasas["{$from}_to_{$to}"] = $counts[$from] > 0
                ? round(($counts[$to] / $counts[$from]) * 100)
                : 0;
        }

        // Tasa global cold → post_booking
        $tasas['global'] = $counts['cold'] > 0
            ? round(($counts['post_booking'] / $counts['cold']) * 100)
            : 0;

        return $tasas;
    }

    /**
     * Convierte minutos a texto humanizado.
     */
    private function humanizarTiempo(int $minutos): string
    {
        if ($minutos < 60) return "{$minutos}m";
        if ($minutos < 1440) return round($minutos / 60) . "h";
        return round($minutos / 1440) . "d";
    }
}