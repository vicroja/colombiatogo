<?php
// app/Services/ToolsSchemaBuilder.php

namespace App\Services;

/**
 * Construye dinámicamente el JSON Schema de herramientas disponibles
 * según el perfil del tenant (has_accommodation, has_tours).
 *
 * El schema resultante se inyecta en el system_instruction de Gemini
 * y también se guarda en ai_prompts.tools_schema_json al configurar el tenant.
 */
class ToolsSchemaBuilder
{
    /**
     * Genera el array de tools disponibles para un tenant.
     *
     * @param bool $hasAccommodation  El tenant gestiona alojamiento
     * @param bool $hasTours          El tenant gestiona tours
     * @return array                  Array listo para json_encode()
     */
    public static function build(bool $hasAccommodation = true, bool $hasTours = false): array
    {
        $tools = [];

        // ── TOOLS COMUNES (siempre disponibles) ──────────────────────────────

        $tools[] = [
            'name'        => 'notificar_administrador',
            'description' => 'Escala la conversación a un humano cuando el cliente lo pide explícitamente, cuando hay un problema técnico grave, o cuando después de 3 intentos no se logra resolver la solicitud del cliente. Al llamar esta herramienta, la IA se desactiva automáticamente.',
            'parameters'  => [
                'type'       => 'object',
                'required'   => ['mensaje'],
                'properties' => [
                    'mensaje' => [
                        'type'        => 'string',
                        'description' => 'Resumen breve del motivo de escalamiento para que el administrador entienda el contexto sin leer toda la conversación.',
                    ],
                ],
            ],
        ];

        $tools[] = [
            'name'        => 'enviar_fotos_cabana',
            'description' => 'Envía fotos del establecimiento o de una unidad/cabaña específica al cliente por WhatsApp. Usar cuando el cliente pida ver fotos, imágenes o quiera conocer visualmente el lugar.',
            'parameters'  => [
                'type'       => 'object',
                'required'   => ['entity_type'],
                'properties' => [
                    'entity_type' => [
                        'type'        => 'string',
                        'enum'        => ['tenant', 'unit'],
                        'description' => 'tenant = fotos generales del establecimiento. unit = fotos de una unidad/cabaña específica.',
                    ],
                    'unit_id' => [
                        'type'        => 'integer',
                        'description' => 'ID de la unidad (solo requerido si entity_type es "unit"). Usar el id_unidad del catálogo de alojamientos del contexto.',
                    ],
                ],
            ],
        ];

        // ── TOOLS DE ALOJAMIENTO ─────────────────────────────────────────────
        if ($hasAccommodation) {
            $tools[] = [
                'name'        => 'consultar_disponibilidad',
                'description' => 'Consulta qué unidades/cabañas/habitaciones están disponibles para unas fechas y cantidad de personas dadas. Devuelve precios exactos calculados con temporadas y extras. SIEMPRE llamar esta herramienta antes de dar precios o crear reservas — nunca usar los precios del catálogo directamente.',
                'parameters'  => [
                    'type'       => 'object',
                    'required'   => ['check_in_date', 'check_out_date', 'num_adults'],
                    'properties' => [
                        'check_in_date'  => [
                            'type'        => 'string',
                            'description' => 'Fecha de llegada en formato YYYY-MM-DD.',
                        ],
                        'check_out_date' => [
                            'type'        => 'string',
                            'description' => 'Fecha de salida en formato YYYY-MM-DD.',
                        ],
                        'num_adults' => [
                            'type'        => 'integer',
                            'description' => 'Número de adultos.',
                        ],
                        'num_children' => [
                            'type'        => 'integer',
                            'description' => 'Número de niños. Omitir o enviar 0 si no hay niños.',
                        ],
                    ],
                ],
            ];

            $tools[] = [
                'name'        => 'crear_reserva',
                'description' => 'Crea una reserva bloqueando la unidad seleccionada en estado pendiente. SOLO llamar después de que el cliente haya confirmado explícitamente la unidad, las fechas y el precio. Nunca crear una reserva sin confirmación explícita del cliente.',
                'parameters'  => [
                    'type'       => 'object',
                    'required'   => ['accommodation_unit_id', 'check_in_date', 'check_out_date', 'precio_total_acordado', 'nombre_cliente'],
                    'properties' => [
                        'accommodation_unit_id' => [
                            'type'        => 'integer',
                            'description' => 'ID numérico de la unidad a reservar (del catálogo en el contexto).',
                        ],
                        'check_in_date' => [
                            'type'        => 'string',
                            'description' => 'Fecha de llegada en formato YYYY-MM-DD.',
                        ],
                        'check_out_date' => [
                            'type'        => 'string',
                            'description' => 'Fecha de salida en formato YYYY-MM-DD.',
                        ],
                        'precio_total_acordado' => [
                            'type'        => 'number',
                            'description' => 'Precio total exacto devuelto por consultar_disponibilidad. Nunca inventar este valor.',
                        ],
                        'nombre_cliente' => [
                            'type'        => 'string',
                            'description' => 'Nombre completo del cliente para el registro.',
                        ],
                    ],
                ],
            ];
        }

        // ── TOOLS DE TOURS ───────────────────────────────────────────────────
        if ($hasTours) {
            $tools[] = [
                'name'        => 'consultar_tours_disponibles',
                'description' => 'Consulta qué tours tienen salidas disponibles con cupos libres. Usar cuando el cliente pregunte por tours, actividades, experiencias o planes. Devuelve tours con fechas, precios y cupos disponibles.',
                'parameters'  => [
                    'type'       => 'object',
                    'required'   => [],
                    'properties' => [
                        'fecha_desde' => [
                            'type'        => 'string',
                            'description' => 'Fecha desde la que buscar salidas en formato YYYY-MM-DD. Si el cliente no especifica, usar la fecha actual.',
                        ],
                        'fecha_hasta' => [
                            'type'        => 'string',
                            'description' => 'Fecha hasta la que buscar salidas en formato YYYY-MM-DD. Opcional.',
                        ],
                        'num_personas' => [
                            'type'        => 'integer',
                            'description' => 'Número total de personas (adultos + niños). Opcional, para filtrar por cupos disponibles.',
                        ],
                    ],
                ],
            ];

            $tools[] = [
                'name'        => 'reservar_tour',
                'description' => 'Crea una reserva de tour para el cliente. SOLO llamar después de que el cliente haya confirmado explícitamente el tour, la fecha de salida y el precio.',
                'parameters'  => [
                    'type'       => 'object',
                    'required'   => ['schedule_id', 'num_adults', 'precio_total_acordado', 'nombre_cliente'],
                    'properties' => [
                        'schedule_id' => [
                            'type'        => 'integer',
                            'description' => 'ID de la salida específica del tour (devuelto por consultar_tours_disponibles).',
                        ],
                        'num_adults' => [
                            'type'        => 'integer',
                            'description' => 'Número de adultos.',
                        ],
                        'num_children' => [
                            'type'        => 'integer',
                            'description' => 'Número de niños. Omitir o enviar 0 si no hay niños.',
                        ],
                        'precio_total_acordado' => [
                            'type'        => 'number',
                            'description' => 'Precio total exacto devuelto por consultar_tours_disponibles.',
                        ],
                        'nombre_cliente' => [
                            'type'        => 'string',
                            'description' => 'Nombre completo del cliente.',
                        ],
                        'pickup_location' => [
                            'type'        => 'string',
                            'description' => 'Punto de recogida si el cliente indica uno diferente al punto de encuentro del tour. Opcional.',
                        ],
                    ],
                ],
            ];
        }

        return $tools;
    }

    /**
     * Genera el JSON string listo para guardar en ai_prompts.tools_schema_json
     * o para inyectar en el system_instruction.
     */
    public static function buildJson(bool $hasAccommodation = true, bool $hasTours = false): string
    {
        return json_encode(self::build($hasAccommodation, $hasTours), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}