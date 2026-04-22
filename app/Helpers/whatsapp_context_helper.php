<?php

/**
 * Helper de Contexto para el Asistente IA (PMS Multi-Tenant)
 * Extrae y formatea toda la información relevante de un huésped en el hotel/cabaña.
 */

if (!function_exists('build_guest_context_data')) {

    /**
     * Construye el contexto completo del huésped para inyectarlo en Gemini.
     *
     * @param object $guest El objeto del huésped (tabla guests).
     * @param int $tenantId El ID del inquilino (Hotel/Cabaña actual).
     * @param string $senderPhone El teléfono con el que se está comunicando.
     * @return string Texto formateado listo para concatenar al system_instruction.
     */
    function build_guest_context_data($guest, int $tenantId, string $senderPhone): string
    {
        $db = \Config\Database::connect();

        // 1. OBTENER DATOS DEL ESTABLECIMIENTO (TENANT)
        $tenant = $db->table('tenants')->where('id', $tenantId)->get()->getRow();

        // Ajustar la zona horaria a la del tenant (Vital para que Gemini agende bien)
        $tz = $tenant->timezone ?? 'America/Bogota';
        $dt = new \DateTime('now', new \DateTimeZone($tz));
        $fechaActual = $dt->format('Y-m-d H:i:s');
        $diaSemana = translate_day_to_spanish($dt->format('l'));

        // --- INICIO DEL CONTEXTO ---
        $contexto = "========================================\n";
        $contexto .= "[CONTEXTO DINÁMICO DEL SISTEMA Y HUÉSPED]\n";
        $contexto .= "========================================\n";
        $contexto .= "- Fecha y hora actual servidor: {$fechaActual} ({$diaSemana})\n";
        $contexto .= "- Nombre del Establecimiento: {$tenant->name}\n";
        $contexto .= "- Horarios Oficiales -> Check-in: {$tenant->checkin_time} | Check-out: {$tenant->checkout_time}\n";
        $contexto .= "- Moneda Local: {$tenant->currency_code} ({$tenant->currency_symbol})\n\n";


// --- NUEVO: CATÁLOGO DE ALOJAMIENTOS ---
        $contexto .= "[CATÁLOGO DE ALOJAMIENTOS DISPONIBLES EN EL SISTEMA]\n";
        $contexto .= "Nota para la IA: Puedes ofrecer estas opciones al cliente libremente. Los precios aquí son 'Desde', el precio final depende de la temporada y cantidad de personas.\n";

        $unidadesQuery = $db->query("
            SELECT au.id, au.name, au.description, au.max_occupancy, au.beds_info, at.base_capacity,
                   (SELECT price_per_night FROM unit_rates WHERE unit_id = au.id AND is_active = 1 ORDER BY id ASC LIMIT 1) as base_price,
                   (SELECT extra_person_price FROM unit_rates WHERE unit_id = au.id AND is_active = 1 ORDER BY id ASC LIMIT 1) as extra_price
            FROM accommodation_units au
            JOIN accommodation_types at ON au.type_id = at.id
            WHERE au.tenant_id = ? AND au.status != 'maintenance'
        ", [$tenantId])->getResult();


        $baseUrl = rtrim(config('App')->baseURL, '/');

        foreach ($unidadesQuery as $u) {
            $basePriceF  = number_format((float)$u->base_price,  0, ',', '.');
            $extraPriceF = number_format((float)$u->extra_price, 0, ',', '.');

            $contexto .= "🏠 *{$u->name}* (ID: {$u->id} | Capacidad Base: {$u->base_capacity} | Max: {$u->max_occupancy} personas).\n";
            $contexto .= "  - Descripción: {$u->description}\n";
            $contexto .= "  - Camas: {$u->beds_info}\n";
            $contexto .= "  - Precio Base: {$tenant->currency_symbol}{$basePriceF}/noche. (Persona extra: {$tenant->currency_symbol}{$extraPriceF}/noche).\n";

            // Consultar fotos de esta unidad
            $fotos = $db->table('tenant_media')
                ->where('tenant_id', $tenantId)
                ->where('entity_type', 'unit')
                ->where('entity_id', $u->id)
                ->where('file_type', 'image')
                ->orderBy('is_main', 'DESC')
                ->orderBy('sort_order', 'ASC')
                ->get()->getResult();

            if (!empty($fotos)) {
                $contexto .= "  - 📸 Fotos disponibles: SÍ ({$count} foto(s)). Puedes llamar a enviar_fotos_cabana con unit_id: {$u->id}\n";
            } else {
                $contexto .= "  - 📸 Fotos disponibles: NO. No llames a enviar_fotos_cabana para esta unidad.\n";
            }
        }

        $contexto .= "\n";


        // 2. DATOS DEL HUÉSPED
        if ($guest) {
            $contexto .= "[PERFIL DEL HUÉSPED]\n";
            $contexto .= "- Nombre: {$guest->full_name}\n";
            $contexto .= "- Teléfono: {$senderPhone}\n";
            $contexto .= "- Documento: " . ($guest->document ?: 'No registrado') . "\n\n";

            // 3. RESERVAS ACTIVAS O FUTURAS (Ignoramos canceladas o ya finalizadas)
            $reservas = $db->table('reservations r')
                ->select('r.*, u.name as unit_name')
                ->join('accommodation_units u', 'u.id = r.accommodation_unit_id')
                ->where('r.guest_id', $guest->id)
                ->where('r.tenant_id', $tenantId)
                ->whereIn('r.status', ['pending', 'confirmed', 'checked_in'])
                ->orderBy('r.check_in_date', 'ASC')
                ->get()
                ->getResult();

            if (count($reservas) > 0) {
                $contexto .= "[RESERVAS DEL HUÉSPED]\n";
                foreach ($reservas as $res) {
                    $estadoTraducido = translate_reservation_status($res->status);

                    $contexto .= "-> Reserva ID: {$res->id} | Estado: {$estadoTraducido}\n";
                    $contexto .= "   Alojamiento: {$res->unit_name}\n";
                    $contexto .= "   Fechas: Check-in {$res->check_in_date} al Check-out {$res->check_out_date}\n";

                    // Calcular Pagos para obtener el Saldo Pendiente
                    // Sumamos los montos de la tabla payments asociados a esta reserva
                    $pagos = $db->table('payments')
                        ->selectSum('amount')
                        ->where('reservation_id', $res->id)
                        ->get()
                        ->getRow()->amount ?? 0;

                    $saldo = $res->total_price - $pagos;

                    // Formatear números
                    $totalFormat = number_format($res->total_price, 0, ',', '.');
                    $pagosFormat = number_format($pagos, 0, ',', '.');
                    $saldoFormat = number_format($saldo, 0, ',', '.');

                    $contexto .= "   Finanzas: Total {$tenant->currency_symbol}{$totalFormat} | Abonado: {$tenant->currency_symbol}{$pagosFormat} | SALDO PENDIENTE: {$tenant->currency_symbol}{$saldoFormat}\n";

                    // Si el huésped ya está en el hotel, le mostramos a la IA los consumos que ha hecho
                    if ($res->status === 'checked_in') {
                        $consumos = $db->table('reservation_consumptions')
                            ->where('reservation_id', $res->id)
                            ->get()
                            ->getResult();

                        if (count($consumos) > 0) {
                            $contexto .= "   Consumos Extra (Restaurante/Bar/Servicios):\n";
                            foreach ($consumos as $c) {
                                $subtotalF = number_format($c->subtotal, 0, ',', '.');
                                $contexto .= "    * {$c->quantity}x {$c->description} - {$tenant->currency_symbol}{$subtotalF}\n";
                            }
                        } else {
                            $contexto .= "   Consumos Extra: Ninguno registrado aún.\n";
                        }
                    }
                    $contexto .= "\n";
                }
            } else {
                $contexto .= "[RESERVAS]\n- El cliente no tiene reservas activas ni futuras en este momento.\n\n";
            }

        } else {
            $contexto .= "[PERFIL DEL HUÉSPED]\n";
            $contexto .= "- ¡ATENCIÓN! Este es un cliente NUEVO o prospecto. Aún no está registrado en la base de datos.\n";
            $contexto .= "- Teléfono de contacto: {$senderPhone}\n\n";
        }

log_message('info', "Contexto Final: {$contexto}");
        return $contexto;
    }
}

// --- FUNCIONES DE APOYO ---

if (!function_exists('translate_day_to_spanish')) {
    function translate_day_to_spanish($english_day) {
        $days = [
            'Monday'    => 'Lunes',
            'Tuesday'   => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday'  => 'Jueves',
            'Friday'    => 'Viernes',
            'Saturday'  => 'Sábado',
            'Sunday'    => 'Domingo'
        ];
        return $days[$english_day] ?? $english_day;
    }
}

if (!function_exists('translate_reservation_status')) {
    function translate_reservation_status($status) {
        $statuses = [
            'pending'      => 'PENDIENTE DE PAGO/CONFIRMACIÓN',
            'confirmed'    => 'CONFIRMADA',
            'checked_in'   => 'HOSPEDADO (EN EL HOTEL)',
            'checked_out'  => 'FINALIZADA',
            'cancelled'    => 'CANCELADA'
        ];
        return $statuses[$status] ?? strtoupper($status);
    }
}