<?php
// app/Helpers/whatsapp_context_helper.php

/**
 * Helper de Contexto para el Asistente IA (PMS Multi-Tenant)
 *
 * CAMBIO v2: Devuelve JSON estructurado en vez de texto plano.
 * Gemini procesa JSON más eficientemente y con mayor precisión.
 */

if (!function_exists('build_guest_context_data')) {

    /**
     * Construye el contexto completo del huésped como JSON estructurado.
     * Se inyecta en el system_instruction de Gemini en cada turno.
     *
     * @param object|null $guest       Objeto del huésped (tabla guests)
     * @param int         $tenantId    ID del tenant
     * @param string      $senderPhone Teléfono del remitente
     * @return string                  JSON string listo para concatenar al prompt
     */
    function build_guest_context_data($guest, int $tenantId, string $senderPhone): string
    {
        $db = \Config\Database::connect();

        // ── 1. DATOS DEL TENANT ───────────────────────────────────────────────
        $tenant   = $db->table('tenants')->where('id', $tenantId)->get()->getRow();
        $settings = json_decode($tenant->settings_json ?? '{}', true) ?? [];

        // Zona horaria del tenant para que Gemini maneje fechas correctamente
        $tz = $tenant->timezone ?? 'America/Bogota';
        $dt = new \DateTime('now', new \DateTimeZone($tz));

        $hasAccommodation = (bool)($settings['has_accommodation'] ?? true);
        $hasTours         = (bool)($settings['has_tours']         ?? false);

        // ── 2. CATÁLOGO DE ALOJAMIENTO ────────────────────────────────────────
        $catalogoAlojamiento = [];
        if ($hasAccommodation) {
            $unidades = $db->query("
                SELECT
                    au.id, au.name, au.description, au.max_occupancy,
                    au.base_occupancy, au.beds_info,
                    at.base_capacity,
                    (SELECT price_per_night FROM unit_rates
                     WHERE unit_id = au.id AND is_active = 1
                     ORDER BY id ASC LIMIT 1) as base_price,
                    (SELECT extra_person_price FROM unit_rates
                     WHERE unit_id = au.id AND is_active = 1
                     ORDER BY id ASC LIMIT 1) as extra_price
                FROM accommodation_units au
                JOIN accommodation_types at ON au.type_id = at.id
                WHERE au.tenant_id = ?
                  AND au.status != 'maintenance'
                  AND au.parent_id IS NULL
            ", [$tenantId])->getResult();

            foreach ($unidades as $u) {
                // Verificar si tiene fotos disponibles
                $fotoCount = $db->table('tenant_media')
                    ->where('tenant_id', $tenantId)
                    ->where('entity_type', 'unit')
                    ->where('entity_id', $u->id)
                    ->where('file_type', 'image')
                    ->countAllResults();

                $catalogoAlojamiento[] = [
                    'id'              => (int)$u->id,
                    'nombre'          => $u->name,
                    'descripcion'     => $u->description,
                    'capacidad_base'  => (int)$u->base_occupancy,
                    'capacidad_max'   => (int)$u->max_occupancy,
                    'camas'           => $u->beds_info,
                    'precio_desde'    => (float)($u->base_price ?? 0),
                    'precio_extra_persona' => (float)($u->extra_price ?? 0),
                    'tiene_fotos'     => $fotoCount > 0,
                    'num_fotos'       => (int)$fotoCount,
                    'nota'            => 'Precio "desde" — usar consultar_disponibilidad para precio exacto con temporadas y extras.',
                ];
            }
        }

        // ── 3. CATÁLOGO DE TOURS ──────────────────────────────────────────────
        $catalogoTours = [];
        if ($hasTours) {
            $tours = $db->query("
                SELECT
                    t.id, t.name, t.description, t.duration_minutes,
                    t.meeting_point, t.min_pax, t.price_adult, t.price_child,
                    t.difficulty_level,
                    COUNT(ts.id) as proximas_salidas
                FROM tours t
                LEFT JOIN tour_schedules ts
                    ON ts.tour_id = t.id
                    AND ts.status = 'scheduled'
                    AND ts.start_datetime >= NOW()
                    AND ts.current_pax < ts.max_pax
                WHERE t.tenant_id = ?
                  AND t.is_active = 1
                GROUP BY t.id
            ", [$tenantId])->getResult();

            foreach ($tours as $t) {
                $catalogoTours[] = [
                    'id'               => (int)$t->id,
                    'nombre'           => $t->name,
                    'descripcion'      => $t->description,
                    'duracion_minutos' => (int)$t->duration_minutes,
                    'punto_encuentro'  => $t->meeting_point,
                    'min_personas'     => (int)$t->min_pax,
                    'precio_adulto'    => (float)$t->price_adult,
                    'precio_nino'      => (float)$t->price_child,
                    'dificultad'       => $t->difficulty_level,
                    'salidas_disponibles' => (int)$t->proximas_salidas,
                    'nota'             => 'Usar consultar_tours_disponibles para ver fechas exactas y cupos.',
                ];
            }
        }

        // ── 4. DATOS DEL HUÉSPED Y SUS RESERVAS ──────────────────────────────
        $datosHuesped    = null;
        $reservasActivas = [];
        $toursReservados = [];

        if ($guest) {
            $datosHuesped = [
                'id'        => (int)$guest->id,
                'nombre'    => $guest->full_name,
                'telefono'  => $senderPhone,
                'documento' => $guest->document ?: null,
                'es_nuevo'  => false,
            ];

            // Reservas de alojamiento activas
            if ($hasAccommodation) {
                $reservas = $db->table('reservations r')
                    ->select('r.*, u.name as unit_name')
                    ->join('accommodation_units u', 'u.id = r.accommodation_unit_id')
                    ->where('r.guest_id', $guest->id)
                    ->where('r.tenant_id', $tenantId)
                    ->whereIn('r.status', ['pending', 'confirmed', 'checked_in'])
                    ->orderBy('r.check_in_date', 'ASC')
                    ->get()->getResult();

                foreach ($reservas as $res) {
                    // Calcular saldo pendiente
                    $pagado = (float)($db->table('payments')
                        ->selectSum('amount')
                        ->where('reservation_id', $res->id)
                        ->where('entity_type', 'reservation')
                        ->get()->getRow()->amount ?? 0);

                    $saldo = (float)$res->total_price - $pagado;

                    $resData = [
                        'id'          => (int)$res->id,
                        'unidad'      => $res->unit_name,
                        'estado'      => $res->status,
                        'check_in'    => $res->check_in_date,
                        'check_out'   => $res->check_out_date,
                        'total'       => (float)$res->total_price,
                        'pagado'      => $pagado,
                        'saldo'       => $saldo,
                        'tiene_saldo' => $saldo > 0,
                    ];

                    // Si está hospedado, agregar consumos
                    if ($res->status === 'checked_in') {
                        $consumos = $db->table('reservation_consumptions')
                            ->where('reservation_id', $res->id)
                            ->get()->getResult();

                        $resData['consumos'] = array_map(fn($c) => [
                            'descripcion' => $c->description,
                            'cantidad'    => (int)$c->quantity,
                            'subtotal'    => (float)$c->subtotal,
                        ], $consumos);
                    }

                    $reservasActivas[] = $resData;
                }
            }

            // Tours reservados activos
            if ($hasTours) {
                $toursRes = $db->query("
                    SELECT
                        tr.id, tr.status, tr.num_adults, tr.num_children,
                        tr.total_price, tr.pickup_location,
                        t.name as tour_name, t.meeting_point,
                        ts.start_datetime,
                        tg.name as guide_name
                    FROM tour_reservations tr
                    JOIN tour_schedules ts ON ts.id = tr.schedule_id
                    JOIN tours t ON t.id = ts.tour_id
                    LEFT JOIN tour_guides tg ON tg.id = ts.guide_id
                    WHERE tr.guest_id = ?
                      AND tr.tenant_id = ?
                      AND tr.status IN ('pending', 'confirmed')
                      AND ts.start_datetime >= NOW()
                    ORDER BY ts.start_datetime ASC
                ", [$guest->id, $tenantId])->getResult();

                foreach ($toursRes as $tr) {
                    $pagadoTour = (float)($db->table('payments')
                        ->selectSum('amount')
                        ->where('reservation_id', $tr->id)
                        ->where('entity_type', 'tour_reservation')
                        ->get()->getRow()->amount ?? 0);

                    $toursReservados[] = [
                        'id'             => (int)$tr->id,
                        'tour'           => $tr->tour_name,
                        'estado'         => $tr->status,
                        'fecha_salida'   => $tr->start_datetime,
                        'adultos'        => (int)$tr->num_adults,
                        'ninos'          => (int)$tr->num_children,
                        'total'          => (float)$tr->total_price,
                        'pagado'         => $pagadoTour,
                        'saldo'          => (float)$tr->total_price - $pagadoTour,
                        'punto_encuentro'=> $tr->pickup_location ?? $tr->meeting_point,
                        'guia'           => $tr->guide_name,
                    ];
                }
            }
        } else {
            // Cliente nuevo — no está en la BD aún
            $datosHuesped = [
                'nombre'   => null,
                'telefono' => $senderPhone,
                'es_nuevo' => true,
            ];
        }

        // ── 5. ESTADO DE CONVERSACIÓN (funnel y contexto persistido) ─────────
        $funnelStage = 'cold';
        $contextoPrevia = [];

        if ($guest) {
            $funnelStage    = $guest->funnel_stage ?? 'cold';
            $contextoPrevia = json_decode($guest->conversation_context_json ?? '{}', true) ?? [];
        }

        // ── 6. CONSTRUIR EL JSON FINAL ────────────────────────────────────────
        $contexto = [
            'fecha_hora' => $dt->format('Y-m-d H:i:s'),
            'dia_semana' => translate_day_to_spanish($dt->format('l')),
            'establecimiento' => [
                'nombre'   => $tenant->name,
                'ciudad'   => $tenant->city,
                'checkin'  => $tenant->checkin_time,
                'checkout' => $tenant->checkout_time,
                'moneda'   => $tenant->currency_code,
                'simbolo'  => $tenant->currency_symbol,
            ],
            'huesped'            => $datosHuesped,
            'reservas_activas'   => $reservasActivas,
            'tours_reservados'   => $toursReservados,
            'catalogo_alojamiento' => $catalogoAlojamiento,
            'catalogo_tours'     => $catalogoTours,
            'estado_conversacion' => array_merge([
                'funnel_stage'               => $funnelStage,
                'precio_revelado'            => false,
                'disponibilidad_consultada'  => false,
                'objeciones_detectadas'      => [],
                'ultima_unidad_consultada'   => null,
                'ultimo_tour_consultado'     => null,
            ], $contextoPrevia),
        ];

        $jsonContexto = json_encode($contexto, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        log_message('info', "[ContextHelper] Contexto generado para tenant {$tenantId}, guest: " .
            ($guest ? $guest->full_name : 'NUEVO') .
            ", funnel: {$funnelStage}");

        return $jsonContexto;
    }
}

// ── FUNCIONES DE APOYO ────────────────────────────────────────────────────────

if (!function_exists('translate_day_to_spanish')) {
    function translate_day_to_spanish(string $english_day): string
    {
        return [
            'Monday'    => 'Lunes',
            'Tuesday'   => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday'  => 'Jueves',
            'Friday'    => 'Viernes',
            'Saturday'  => 'Sábado',
            'Sunday'    => 'Domingo',
        ][$english_day] ?? $english_day;
    }
}

if (!function_exists('translate_reservation_status')) {
    function translate_reservation_status(string $status): string
    {
        return [
            'pending'    => 'PENDIENTE DE PAGO',
            'confirmed'  => 'CONFIRMADA',
            'checked_in' => 'HOSPEDADO',
            'checked_out'=> 'FINALIZADA',
            'cancelled'  => 'CANCELADA',
        ][$status] ?? strtoupper($status);
    }
}