<?php

namespace App\Controllers\Onboarding;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * WizardController
 *
 * Maneja el flujo completo del onboarding wizard para nuevos tenants.
 * Guarda el progreso en tenants.settings_json -> onboarding_step.
 *
 * FIXES APLICADOS:
 * - Sesión: active_tenant_id (consistente con todo el PMS)
 * - Filtro de ruta: tenant_auth
 * - FCPATH para uploads públicos
 * - $settings pasado a vistas
 * - Sesión sincronizada tras paso 1
 * - onboarding_status marcado in_progress al entrar
 * - checkRequiredSteps retorno corregido
 * - Redundancias de tenant_id eliminadas (BaseMultiTenantModel lo maneja)
 */
class WizardController extends BaseController
{
    // ── Definición de pasos ───────────────────────────────────────────────


    private int   $tenantId;
    private array $tenant;
    private array $settings;

    private array $steps = [];

    // ── Modelos ───────────────────────────────────────────────────────────
    private \App\Models\TenantModel            $tenantModel;
    private \App\Models\AccommodationTypeModel $typeModel;
    private \App\Models\AccommodationUnitModel $unitModel;
    private \App\Models\RatePlanModel          $ratePlanModel;
    private \App\Models\UnitRateModel          $unitRateModel;
    private \App\Models\ProductCategoryModel   $catModel;
    private \App\Models\ProductModel           $productModel;
    private \App\Models\AiPromptModel          $aiPromptModel;
    private \App\Models\TenantMediaModel       $mediaModel;
    private \App\Models\TenantWebsiteModel     $websiteModel;
    private \App\Models\BedTypeModel           $bedTypeModel;
    private \App\Models\AmenityModel           $amenityModel;
    private \App\Models\UnitAmenityModel       $unitAmenityModel;
    private \App\Models\UnitBedModel           $unitBedModel;

    private \App\Models\GeminiModel $geminiModel;


    // ─────────────────────────────────────────────────────────────────────

    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface            $logger,

    ): void {
        parent::initController($request, $response, $logger);

        // FIX #1: usar active_tenant_id consistente con todo el PMS
        $this->tenantId = session('active_tenant_id');

        $this->tenantModel      = model('TenantModel');
        $this->typeModel        = model('AccommodationTypeModel');
        $this->unitModel        = model('AccommodationUnitModel');
        $this->ratePlanModel    = model('RatePlanModel');
        $this->unitRateModel    = model('UnitRateModel');
        $this->catModel         = model('ProductCategoryModel');
        $this->productModel     = model('ProductModel');
        $this->aiPromptModel    = model('AiPromptModel');
        $this->mediaModel       = model('TenantMediaModel');
        $this->websiteModel     = model('TenantWebsiteModel');
        $this->bedTypeModel     = model('BedTypeModel');
        $this->amenityModel     = model('AmenityModel');
        $this->unitAmenityModel = model('UnitAmenityModel');
        $this->unitBedModel     = model('UnitBedModel');
        $this->geminiModel = new \App\Models\GeminiModel();


        $this->tenant   = $this->tenantModel->find($this->tenantId) ?? [];
        $this->settings = json_decode($this->tenant['settings_json'] ?? '{}', true) ?? [];
        $this->steps = $this->buildSteps();

    }

    // =========================================================================
    // INDEX — Redirige al paso actual
    // =========================================================================
    public function index(): RedirectResponse
    {
        if (($this->tenant['onboarding_status'] ?? 'pending') === 'complete') {
            return redirect()->to('/dashboard');
        }

        $currentStep = $this->settings['onboarding_step'] ?? 1;
        log_message('info', "[Onboarding] Tenant {$this->tenantId} → paso actual: {$currentStep}");

        return redirect()->to("/onboarding/step/{$currentStep}");
    }

    // =========================================================================
    // STEP — Muestra un paso
    // =========================================================================
    public function step(int $stepNumber): string|RedirectResponse
    {
        if (!isset($this->steps[$stepNumber])) {
            return redirect()->to('/onboarding');
        }

        // FIX #12: marcar in_progress la primera vez que entra
        if (($this->tenant['onboarding_status'] ?? 'pending') === 'pending') {
            $this->tenantModel->update($this->tenantId, [
                'onboarding_status' => 'in_progress',
            ]);
        }

        // FIX #9: retorno corregido
        $redirect = $this->checkRequiredSteps($stepNumber);
        if ($redirect !== null) {
            return $redirect;
        }

        $data = [
            'steps'       => $this->steps,
            'currentStep' => $stepNumber,
            'tenant'      => $this->tenant,
            'settings'    => $this->settings,  // FIX #5: disponible en todas las vistas
            'completed'   => $this->settings['onboarding_completed_steps'] ?? [],
            'stepData'    => $this->getStepData($stepNumber),
        ];

        return view('onboarding/layout', $data);
    }

    // =========================================================================
    // SAVE STEP — Procesa el POST de cada paso
    // =========================================================================
    public function saveStep(int $stepNumber): RedirectResponse
    {
        if (!isset($this->steps[$stepNumber])) {
            return redirect()->to('/onboarding');
        }

        log_message('info', "[Onboarding] Guardando paso {$stepNumber} para tenant {$this->tenantId}");

        $result = match($stepNumber) {
            1  => $this->saveStep1(),
            2  => $this->saveStep2Profile(),   // nuevo paso de perfil
            3  => $this->saveStep3Media(),     // era paso 2
            4  => $this->saveStep4Unit(),      // era paso 3
            5  => $this->saveStep5Rates(),     // era paso 4
            6  => $this->saveStep6Tour(),      // nuevo
            7  => $this->saveStep7TourSchedule(), // nuevo
            8  => $this->saveStep8AiPrompt(),  // era paso 5
            9  => $this->saveStep9Product(),   // era paso 6
            10 => $this->saveStep10Whatsapp(), // era paso 7
            11 => $this->saveStep11Preview(),  // era paso 8
            default => ['success' => false, 'message' => 'Paso inválido']
        };

        if (!$result['success']) {
            log_message('error', "[Onboarding] Error en paso {$stepNumber}: " . ($result['message'] ?? 'desconocido'));
            return redirect()->back()
                ->withInput()
                ->with('error', $result['message'] ?? 'Error al guardar.');
        }

        $this->markStepCompleted($stepNumber);
        $nextStep = $stepNumber + 1;

        if ($nextStep > count($this->steps)) {
            return redirect()->to('/onboarding/complete');
        }

        return redirect()->to("/onboarding/step/{$nextStep}")
            ->with('success', '✅ ' . $this->steps[$stepNumber]['title'] . ' guardado correctamente.');
    }

    // =========================================================================
    // AI GENERATE — Endpoint AJAX para llamadas a Gemini
    // =========================================================================
    public function aiGenerate(): \CodeIgniter\HTTP\ResponseInterface
    {
        $action = $this->request->getJSON(true)['action'] ?? '';
        log_message('info', "[Onboarding/AI] Acción: {$action} — tenant {$this->tenantId}");

        $result = match($action) {
            'generate_description' => $this->aiGenerateDescription(),
            'generate_prompt'      => $this->aiGeneratePrompt(),
            'generate_hero'        => $this->aiGenerateHero(),
            'generate_logo'        => $this->aiGenerateLogo(),   // ← nuevo

            default                => ['success' => false, 'message' => 'Acción no reconocida']
        };

        return $this->response->setJSON($result);
    }

    // =========================================================================
    // COMPLETE — Pantalla final
    // =========================================================================
    public function complete(): string
    {
        $this->tenantModel->update($this->tenantId, [
            'onboarding_status' => 'complete',
        ]);

        log_message('info', "[Onboarding] Tenant {$this->tenantId} completó el onboarding.");

        return view('onboarding/complete', [
            'tenant'  => $this->tenant,
            'steps'   => $this->steps,
            'summary' => $this->buildSummary(),
        ]);
    }

    // =========================================================================
    // SAVE STEPS — Implementación individual
    // =========================================================================

    /**
     * Paso 1: Identidad del hotel
     */
    private function saveStep1(): array
    {
        $rules = [
            'name'          => 'required|max_length[120]',
            'phone'         => 'required|max_length[30]',
            'city'          => 'required|max_length[100]',
            'country'       => 'required|max_length[80]',
            'checkin_time'  => 'required',
            'checkout_time' => 'required',
            'currency_code' => 'required|max_length[10]',
            'timezone'      => 'required|max_length[60]',
        ];

        if (!$this->validate($rules)) {
            return ['success' => false, 'message' => implode(' ', $this->validator->getErrors())];
        }

        $updated = $this->tenantModel->update($this->tenantId, [
            'name'            => $this->request->getPost('name'),
            'phone'           => $this->request->getPost('phone'),
            'address'         => $this->request->getPost('address'),
            'city'            => $this->request->getPost('city'),
            'country'         => $this->request->getPost('country'),
            'checkin_time'    => $this->request->getPost('checkin_time'),
            'checkout_time'   => $this->request->getPost('checkout_time'),
            'currency_code'   => $this->request->getPost('currency_code'),
            'currency_symbol' => $this->request->getPost('currency_symbol'),
            'timezone'        => $this->request->getPost('timezone'),
            'email'           => $this->request->getPost('email'),
        ]);

        if (!$updated) {
            return ['success' => false, 'message' => 'Error actualizando datos del hotel.'];
        }

        // FIX #7: sincronizar sesión igual que SettingsController
        session()->set([
            'tenant_name'     => $this->request->getPost('name'),
            'currency_symbol' => $this->request->getPost('currency_symbol'),
            'timezone'        => $this->request->getPost('timezone'),
        ]);

        return ['success' => true];
    }

    /**
     * Paso 2: Subida de logo y fotos
     */
    private function saveStep3Media(): array
    {
        $logo = $this->request->getFile('logo');

        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $newName = $logo->getRandomName();

            // FIX #6: FCPATH para que los archivos sean públicos
            $logo->move(FCPATH . 'uploads/tenants/' . $this->tenantId, $newName);
            $logoPath = 'uploads/tenants/' . $this->tenantId . '/' . $newName;

            $this->tenantModel->update($this->tenantId, ['logo_path' => $logoPath]);

            // Sincronizar logo en sesión igual que SettingsController
            session()->set('tenant_logo', $logoPath);

            log_message('info', "[Onboarding/Media] Logo guardado: {$logoPath}");
        }

        $photos = $this->request->getFiles('photos');
        if (!empty($photos['photos'])) {
            foreach ($photos['photos'] as $idx => $photo) {
                if ($photo->isValid() && !$photo->hasMoved()) {
                    $newName = $photo->getRandomName();

                    // FIX #6: FCPATH para archivos públicos
                    $photo->move(FCPATH . 'uploads/tenants/' . $this->tenantId, $newName);

                    // Usar createForTenant() del BaseMultiTenantModel
                    $this->mediaModel->createForTenant([
                        'entity_type' => 'tenant',
                        'entity_id'   => $this->tenantId,
                        'file_path'   => 'uploads/tenants/' . $this->tenantId . '/' . $newName,
                        'file_type'   => 'image',
                        'is_main'     => ($idx === 0) ? 1 : 0,
                        'sort_order'  => $idx,
                    ]);
                }
            }
        }

        return ['success' => true];
    }


    private function saveStep4Unit(): array
    {
        $rules = [
            'unit_name'      => 'required|max_length[50]',
            'type_name'      => 'required|max_length[100]',
            'base_occupancy' => 'required|integer|greater_than[0]',
            'max_occupancy'  => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return ['success' => false,
                'message' => implode(' ', $this->validator->getErrors())];
        }

        $mode = $this->request->getPost('unit_mode') ?? 'simple';

        // Crear o reutilizar el tipo de alojamiento
        $typeName = $this->request->getPost('type_name');
        $type     = $this->typeModel->where('name', $typeName)->first();

        if (!$type) {
            $typeId = $this->typeModel->createForTenant([
                'name'          => $typeName,
                'base_capacity' => $this->request->getPost('base_occupancy'),
                'max_capacity'  => $this->request->getPost('max_occupancy'),
            ]);
        } else {
            $typeId = $type['id'];
        }

        // Crear la unidad padre
        $unitId = $this->unitModel->createForTenant([
            'type_id'        => $typeId,
            'name'           => $this->request->getPost('unit_name'),
            'description'    => $this->request->getPost('description'),
            'base_occupancy' => $this->request->getPost('base_occupancy'),
            'max_occupancy'  => $this->request->getPost('max_occupancy'),
            'bathrooms'      => $this->request->getPost('bathrooms') ?? 1.0,
            'status'         => 'available',
            'parent_id'      => null,
        ]);

        if (!$unitId) {
            log_message('error', "[Onboarding/Paso3] Error creando unidad para tenant {$this->tenantId}");
            return ['success' => false, 'message' => 'Error creando la unidad.'];
        }

        if ($mode === 'simple') {
            // ── Modo simple: camas directas en la unidad ──────────────────
            $this->saveBeds($unitId, $this->request->getPost('beds') ?? []);

        } else {
            // ── Modo compound: crear cuartos hijos con sus camas ──────────
            $rooms = $this->request->getPost('rooms') ?? [];

            // Tipo genérico para las habitaciones hijas
            $roomType = $this->typeModel->where('name', 'Habitación')->first();
            if (!$roomType) {
                $roomTypeId = $this->typeModel->createForTenant([
                    'name'          => 'Habitación',
                    'base_capacity' => 2,
                    'max_capacity'  => 4,
                ]);
            } else {
                $roomTypeId = $roomType['id'];
            }

            foreach ($rooms as $idx => $room) {
                $roomName = !empty($room['name'])
                    ? trim($room['name'])
                    : 'Cuarto ' . ($idx + 1);

                $capacity = (int) ($room['capacity'] ?? 2);

                // Unidad hija vinculada al padre via parent_id
                $childId = $this->unitModel->createForTenant([
                    'type_id'        => $roomTypeId,
                    'parent_id'      => $unitId,
                    'name'           => $roomName,
                    'base_occupancy' => $capacity,
                    'max_occupancy'  => $capacity,
                    'bathrooms'      => 1.0,
                    'status'         => 'available',
                ]);

                if ($childId) {
                    $this->saveBeds($childId, $room['beds'] ?? []);
                    log_message('info', "[Onboarding/Paso3] Cuarto hijo #{$childId} '{$roomName}' bajo unidad #{$unitId}");
                }
            }
        }

        // Amenidades sobre la unidad padre
        $amenityIds = $this->request->getPost('amenity_ids') ?? [];
        foreach ($amenityIds as $amenityId) {
            $this->unitAmenityModel->insert([
                'unit_id'    => $unitId,
                'amenity_id' => (int) $amenityId,
            ]);
        }

        // Foto principal de la unidad padre
        $photo = $this->request->getFile('unit_photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $newName = $photo->getRandomName();
            $photo->move(FCPATH . 'uploads/units/' . $unitId, $newName);
            $this->mediaModel->createForTenant([
                'entity_type' => 'unit',
                'entity_id'   => $unitId,
                'file_path'   => 'uploads/units/' . $unitId . '/' . $newName,
                'file_type'   => 'image',
                'is_main'     => 1,
                'sort_order'  => 0,
            ]);
        }

        $this->updateSettings(['onboarding_unit_id' => $unitId]);

        log_message('info', "[Onboarding/Paso3] Unidad #{$unitId} modo '{$mode}' creada para tenant {$this->tenantId}");
        return ['success' => true];
    }

    /**
     * Helper: guarda las camas para una unidad dada.
     * Usado tanto por el modo simple como por cada cuarto del modo compound.
     *
     * @param int   $unitId
     * @param array $beds   array de ['type_id' => x, 'qty' => y]
     */
    private function saveBeds(int $unitId, array $beds): void
    {
        foreach ($beds as $bed) {
            $typeId = (int) ($bed['type_id'] ?? 0);
            $qty    = (int) ($bed['qty']     ?? 1);

            if ($typeId > 0 && $qty > 0) {
                $this->unitBedModel->insert([
                    'unit_id'     => $unitId,
                    'bed_type_id' => $typeId,
                    'quantity'    => $qty,
                ]);
            }
        }
    }

    /**
     * Paso 4: Plan tarifario base
     */
    private function saveStep5Rates(): array
    {
        $rules = [
            'plan_name'       => 'required|max_length[100]',
            'price_per_night' => 'required|decimal|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return ['success' => false, 'message' => implode(' ', $this->validator->getErrors())];
        }

        $planId = $this->ratePlanModel->createForTenant([
            'name'               => $this->request->getPost('plan_name'),
            'description'        => $this->request->getPost('plan_description'),
            'includes_breakfast' => $this->request->getPost('includes_breakfast') ? 1 : 0,
            'is_default'         => 1,
            'is_active'          => 1,
        ]);

        if (!$planId) {
            return ['success' => false, 'message' => 'Error creando el plan tarifario.'];
        }

        $unitId = $this->settings['onboarding_unit_id'] ?? null;

        if ($unitId) {
            $this->unitRateModel->createForTenant([
                'unit_id'            => $unitId,
                'rate_plan_id'       => $planId,
                'price_per_night'    => $this->request->getPost('price_per_night'),
                'extra_person_price' => $this->request->getPost('extra_person_price') ?? 0,
                'extra_child_price'  => $this->request->getPost('extra_child_price')  ?? 0,
                'min_nights'         => $this->request->getPost('min_nights')         ?? 1,
                'is_active'          => 1,
            ]);
        } else {
            log_message('warning', "[Onboarding/Paso4] No hay onboarding_unit_id en settings — tarifa sin unidad asignada.");
        }

        log_message('info', "[Onboarding/Paso4] Plan tarifario #{$planId} creado para tenant {$this->tenantId}");
        return ['success' => true];
    }


    /**
     * Paso 5: Prompt del asistente IA
     *
     * Guarda system_instruction (personalizable) + tools_schema_json (estándar PMS).
     * El tools_schema_json define las 3 herramientas que el asistente puede invocar:
     *   1. consultar_disponibilidad — busca cabañas libres y devuelve precio exacto
     *   2. crear_reserva            — bloquea la cabaña y crea el registro
     *   3. notificar_administrador  — escala la conversación a un humano
     */
    private function saveStep8AiPrompt(): array
    {
        $systemInstruction = trim($this->request->getPost('system_instruction') ?? '');

        if (empty($systemInstruction)) {
            return ['success' => false, 'message' => 'El prompt del asistente no puede estar vacío.'];
        }

        // ── Tools schema estándar del PMS ─────────────────────────────────────
        // Estas herramientas son las mismas que usa WhatsappWebhookService / WhatsappToolExecutor.
        // Son obligatorias para que la IA pueda operar con el PMS.
        $toolsSchema = [
            [
                'name'        => 'consultar_disponibilidad',
                'description' => 'Busca disponibilidad y devuelve el precio exacto calculado con temporadas.',
                'parameters'  => [
                    'type'       => 'object',
                    'required'   => ['check_in_date', 'check_out_date', 'numero_personas'],
                    'properties' => [
                        'check_in_date'   => [
                            'type'        => 'string',
                            'description' => 'Fecha de llegada en formato YYYY-MM-DD',
                        ],
                        'check_out_date'  => [
                            'type'        => 'string',
                            'description' => 'Fecha de salida en formato YYYY-MM-DD',
                        ],
                        'numero_personas' => [
                            'type'        => 'integer',
                            'description' => 'Cantidad total de huéspedes (adultos + niños)',
                        ],
                    ],
                ],
            ],
            [
                'name'        => 'crear_reserva',
                'description' => 'Crea una reserva bloqueando la cabaña.',
                'parameters'  => [
                    'type'       => 'object',
                    'required'   => [
                        'accommodation_unit_id',
                        'check_in_date',
                        'check_out_date',
                        'precio_total_acordado',
                        'nombre_cliente',
                    ],
                    'properties' => [
                        'accommodation_unit_id' => [
                            'type'        => 'integer',
                            'description' => 'ID numérico de la unidad.',
                        ],
                        'check_in_date'         => [
                            'type'        => 'string',
                            'description' => 'Fecha de llegada en formato YYYY-MM-DD.',
                        ],
                        'check_out_date'        => [
                            'type'        => 'string',
                            'description' => 'Fecha de salida en formato YYYY-MM-DD.',
                        ],
                        'precio_total_acordado' => [
                            'type'        => 'number',
                            'description' => 'Precio total exacto devuelto por la herramienta de disponibilidad.',
                        ],
                        'nombre_cliente'        => [
                            'type'        => 'string',
                            'description' => 'Nombre y apellido del cliente para el registro.',
                        ],
                    ],
                ],
            ],
            [
                'name'        => 'notificar_administrador',
                'description' => 'Llama a esta función para escalar a un humano.',
                'parameters'  => [
                    'type'       => 'object',
                    'required'   => ['mensaje'],
                    'properties' => [
                        'mensaje' => [
                            'type'        => 'string',
                            'description' => 'Breve resumen del problema.',
                        ],
                    ],
                ],
            ],
        ];

        $toolsSchemaJson = json_encode($toolsSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        // ── Buscar si ya existe un prompt para este tenant ────────────────────
        // BaseMultiTenantModel filtra por tenant_id automáticamente
        $existing = $this->aiPromptModel
            ->where('profile_role', 'assistant')
            ->first();

        if ($existing) {
            // Actualizar — siempre refrescar tools_schema_json al guardar
            $this->aiPromptModel->update($existing['id'], [
                'system_instruction' => $systemInstruction,
                'model_version'      => 'gemini-2.5-flash',
                'tools_schema_json'  => $toolsSchemaJson,   // ← NUEVO
            ]);

            log_message('info', "[Onboarding/Paso5] Prompt IA actualizado (ID: {$existing['id']}) para tenant {$this->tenantId}. tools_schema_json guardado.");
        } else {
            // Crear nuevo registro completo
            $newId = $this->aiPromptModel->createForTenant([
                'profile_role'       => 'assistant',
                'model_version'      => 'gemini-2.5-flash',
                'system_instruction' => $systemInstruction,
                'tools_schema_json'  => $toolsSchemaJson,   // ← NUEVO
            ]);

            log_message('info', "[Onboarding/Paso5] Prompt IA creado (ID: {$newId}) para tenant {$this->tenantId}. tools_schema_json guardado.");
        }

        return ['success' => true];
    }

    /**
     * Paso 6: Primer producto o servicio
     */
    private function saveStep9Product(): array
    {
        $rules = [
            'product_name'  => 'required|max_length[150]',
            'unit_price'    => 'required|decimal|greater_than_equal_to[0]',
            'category_name' => 'required|max_length[80]',
        ];

        if (!$this->validate($rules)) {
            return ['success' => false, 'message' => implode(' ', $this->validator->getErrors())];
        }

        $catId = $this->catModel->createForTenant([
            'name'      => $this->request->getPost('category_name'),
            'type'      => $this->request->getPost('category_type') ?? 'service',
            'is_active' => 1,
        ]);

        $this->productModel->createForTenant([
            'category_id'             => $catId,
            'name'                    => $this->request->getPost('product_name'),
            'description'             => $this->request->getPost('product_description'),
            'unit_price'              => $this->request->getPost('unit_price'),
            'is_available_for_guests' => 1,
            'is_active'               => 1,
        ]);

        log_message('info', "[Onboarding/Paso6] Producto creado para tenant {$this->tenantId}");
        return ['success' => true];
    }

    /**
     * Paso 7: WhatsApp — manejado por JS + /whatsapp/save_config
     */
    private function saveStep10Whatsapp(): array
    {
        return ['success' => true];
    }

    /**
     * Paso 8: Vista previa y publicación del sitio web
     */
    private function saveStep11Preview(): array
    {
        $existing = $this->websiteModel->first();

        $websiteData = [
            'hero_title'    => $this->request->getPost('hero_title'),
            'hero_subtitle' => $this->request->getPost('hero_subtitle'),
            'about_text'    => $this->request->getPost('about_text'),
            'is_published'  => $this->request->getPost('publish') ? 1 : 0,
        ];

        if ($existing) {
            $this->websiteModel->update($existing['id'], $websiteData);
        } else {
            $this->websiteModel->createForTenant($websiteData);
        }

        return ['success' => true];
    }

    /**
     * Paso 2: Perfil del negocio.
     * Guarda has_accommodation y has_tours en settings_json.
     * Esto determina qué pasos se mostrarán en el resto del wizard.
     */
    private function saveStep2Profile(): array
    {
        $hasAccommodation = $this->request->getPost('has_accommodation') === '1';
        $hasTours         = $this->request->getPost('has_tours')         === '1';

        // Validar que al menos un perfil esté seleccionado
        if (!$hasAccommodation && !$hasTours) {
            return [
                'success' => false,
                'message' => 'Debes seleccionar al menos un perfil: Alojamiento o Tours.',
            ];
        }

        // Guardar en settings_json — buildSteps() los leerá en el próximo request
        $this->updateSettings([
            'has_accommodation' => $hasAccommodation,
            'has_tours'         => $hasTours,
        ]);

        // Invalidar caché de tenant_settings en sesión para que el menú
        // se reconstruya con los nuevos flags en el próximo request
        session()->remove('tenant_settings');

        log_message('info', "[Onboarding/Paso2] Tenant {$this->tenantId} — " .
            "has_accommodation: " . ($hasAccommodation ? 'true' : 'false') . ", " .
            "has_tours: " . ($hasTours ? 'true' : 'false'));

        return ['success' => true];
    }

    /**
     * Paso 6: Primer tour (solo si has_tours = true).
     * Crea el registro base del tour usando TourModel.
     */
    private function saveStep6Tour(): array
    {
        // Si el tenant no tiene tours, saltar silenciosamente
        if (!($this->settings['has_tours'] ?? false)) {
            return ['success' => true];
        }

        $rules = [
            'tour_name'    => 'required|max_length[150]',
            'price_adult'  => 'required|decimal|greater_than[0]',
            'duration_minutes' => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return ['success' => false, 'message' => implode(' ', $this->validator->getErrors())];
        }

        $tourModel = new \App\Models\TourModel();

        $tourId = $tourModel->insert([
            'tenant_id'        => $this->tenantId,
            'name'             => $this->request->getPost('tour_name'),
            'description'      => $this->request->getPost('tour_description'),
            'duration_minutes' => (int) $this->request->getPost('duration_minutes'),
            'meeting_point'    => $this->request->getPost('meeting_point'),
            'min_pax'          => (int) ($this->request->getPost('min_pax') ?? 1),
            'price_adult'      => (float) $this->request->getPost('price_adult'),
            'price_child'      => (float) ($this->request->getPost('price_child') ?? 0),
            'difficulty_level' => $this->request->getPost('difficulty_level') ?? 'easy',
            'is_active'        => 1,
        ]);

        if (!$tourId) {
            log_message('error', "[Onboarding/Paso6] Error creando tour para tenant {$this->tenantId}");
            return ['success' => false, 'message' => 'Error al crear el tour.'];
        }

        // Guardar el tour_id en settings para usarlo en el paso 7
        $this->updateSettings(['onboarding_tour_id' => $tourId]);

        log_message('info', "[Onboarding/Paso6] Tour #{$tourId} creado para tenant {$this->tenantId}");
        return ['success' => true];
    }

    /**
     * Paso 7: Primera salida del tour (solo si has_tours = true).
     */
    private function saveStep7TourSchedule(): array
    {
        if (!($this->settings['has_tours'] ?? false)) {
            return ['success' => true];
        }

        $tourId = $this->settings['onboarding_tour_id'] ?? null;

        if (!$tourId) {
            return ['success' => false, 'message' => 'No se encontró el tour creado en el paso anterior.'];
        }

        $rules = [
            'start_datetime' => 'required',
            'max_pax'        => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return ['success' => false, 'message' => implode(' ', $this->validator->getErrors())];
        }

        $scheduleModel = new \App\Models\TourScheduleModel();

        $scheduleId = $scheduleModel->insert([
            'tour_id'        => $tourId,
            'start_datetime' => $this->request->getPost('start_datetime'),
            'max_pax'        => (int) $this->request->getPost('max_pax'),
            'current_pax'    => 0,
            'status'         => 'scheduled',
            'notes'          => $this->request->getPost('notes'),
        ]);

        if (!$scheduleId) {
            log_message('error', "[Onboarding/Paso7] Error creando schedule para tour {$tourId}");
            return ['success' => false, 'message' => 'Error al crear la salida del tour.'];
        }

        log_message('info', "[Onboarding/Paso7] Schedule #{$scheduleId} creado para tour {$tourId}");
        return ['success' => true];
    }

    // =========================================================================
    // AI HELPERS — Llamadas a Gemini
    // =========================================================================

    private function aiGenerateDescription(): array
    {
        $input = $this->request->getJSON(true);
        $text  = trim($input['text'] ?? '');

        if (empty($text)) {
            return ['success' => false, 'message' => 'Texto vacío'];
        }

        $prompt = "Eres un experto en marketing hotelero. " .
            "El hotel se llama '{$this->tenant['name']}' ubicado en " .
            ($this->tenant['city'] ?? 'Colombia') . ". " .
            "El usuario describió su habitación así: '{$text}'. " .
            "Genera una descripción atractiva, cálida y profesional de máximo 3 oraciones " .
            "para mostrar a huéspedes potenciales. " .
            "Solo responde con la descripción, sin títulos ni explicaciones adicionales.";

        return $this->callGemini($prompt);
    }

    private function aiGeneratePrompt(): array
    {
        $input      = $this->request->getJSON(true);
        $style      = trim($input['style']             ?? '');
        $chats      = trim($input['chats']             ?? '');
        $hotelDesc  = trim($input['hotel_description'] ?? '');

        $context = "Hotel: {$this->tenant['name']}, ubicado en " .
            ($this->tenant['city'] ?? '') . ".";

        if ($this->tenant['phone'] ?? '') {
            $context .= " Teléfono: {$this->tenant['phone']}.";
        }
        if ($hotelDesc) {
            $context .= " Descripción: {$hotelDesc}.";
        }

        $styleBlock = '';
        if ($style) $styleBlock .= "Estilo deseado: {$style}. ";
        if ($chats)  $styleBlock .= "Conversaciones reales del hotel:\n{$chats}\n";

        $prompt = "Eres un experto en hospitalidad y asistentes conversacionales de WhatsApp. " .
            "{$context} {$styleBlock}" .
            "Genera un system prompt completo en español para un asistente de WhatsApp de este hotel. " .
            "Incluye: tono y estilo, cómo saludar, cómo manejar consultas de disponibilidad, " .
            "cómo despedirse, y características especiales deducidas del contexto. " .
            "Escribe solo el system prompt, listo para usar, sin explicaciones adicionales.";

        log_message('info', "[Onboarding/AI] Generando prompt para tenant {$this->tenantId}");
        return $this->callGemini($prompt, 1500);
    }

    /**
     * Genera 3 opciones de logo con Gemini Image
     * usando el contexto del hotel recopilado hasta el paso 2.
     */
    private function aiGenerateLogo(): array
    {
        $input = $this->request->getJSON(true);
        $style = $input['style'] ?? 'both'; // 'wordmark' | 'icon' | 'both'

        // Leer el nombre de unidad si ya se configuró en paso 3
        // (en paso 2 aún no existe, usamos solo datos del tenant)
        $result = $this->geminiModel->generateLogoOptions(
            hotelName  : $this->tenant['name']    ?? '',
            city       : $this->tenant['city']    ?? '',
            type       : $this->settings['onboarding_unit_type'] ?? '',
            description: $this->tenant['address'] ?? '',
            style      : $style,
            count      : 3
        );

        if ($result['success']) {
            log_message('info', "[Onboarding/AI] {$this->tenantId} logos generados: " . count($result['logos']));
        }

        return $result;
    }

    private function aiGenerateHero(): array
    {
        $about = trim($this->request->getJSON(true)['about'] ?? '');

        $prompt = "Hotel: '{$this->tenant['name']}' en " .
            ($this->tenant['city'] ?? '') . ". " .
            "Descripción: '{$about}'. " .
            "Genera en JSON con las claves 'hero_title' (máximo 8 palabras, evocador) y " .
            "'hero_subtitle' (máximo 15 palabras, invita a reservar). " .
            "Solo el JSON puro, sin markdown ni backticks.";

        $result = $this->callGemini($prompt);

        if ($result['success']) {
            // Limpiar posibles backticks que Gemini a veces agrega
            $clean   = preg_replace('/```json|```/', '', $result['text']);
            $decoded = json_decode(trim($clean), true);

            if ($decoded && isset($decoded['hero_title'])) {
                return ['success' => true, 'data' => $decoded];
            }

            log_message('warning', "[Onboarding/AI] Gemini no retornó JSON válido para hero: " . $result['text']);
        }

        return $result;
    }

    /**
     * Llamada genérica a la API de Gemini
     */

    // Reemplaza callGemini() completo en WizardController

    private function callGemini(string $prompt, int $maxTokens = 800): array
    {
        $result = $this->geminiModel->generateText($prompt, $maxTokens, 0.7);

        if ($result['success'] ?? false) {
            return ['success' => true, 'text' => trim($result['text'])];
        }

        log_message('error', '[Onboarding/AI] Error: ' . ($result['message'] ?? 'desconocido'));
        return ['success' => false, 'message' => $result['message'] ?? 'Error en el servicio de IA.'];
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================


    // Reemplazar getStepData() completo:
    private function getStepData(int $step): array
    {
        // Obtenemos la vista del paso actual para saber qué datos cargar
        $view = $this->steps[$step]['view'] ?? '';

        return match($view) {
            'identity' => [
                'tenant' => $this->tenant,
            ],
            'profile' => [
                'has_accommodation' => (bool)($this->settings['has_accommodation'] ?? true),
                'has_tours'         => (bool)($this->settings['has_tours']         ?? false),
            ],
            'media' => [
                'logo'   => $this->tenant['logo_path'] ?? null,
                'photos' => $this->mediaModel->where('entity_type', 'tenant')->findAll(),
            ],
            'unit' => [
                'bed_types' => $this->bedTypeModel->getForTenant($this->tenantId),
                'amenities' => $this->amenityModel->getForTenant($this->tenantId),
            ],
            'rates' => [
                'unit_name' => $this->unitModel
                        ->find($this->settings['onboarding_unit_id'] ?? 0)['name'] ?? 'tu unidad',
                'tenant'    => $this->tenant,
            ],
            'tour_basic' => [
                'tenant' => $this->tenant,
            ],
            'tour_schedule' => [
                'tour' => (new \App\Models\TourModel())
                    ->find($this->settings['onboarding_tour_id'] ?? 0),
            ],
            'ai_prompt' => [
                'existing_prompt' => $this->aiPromptModel
                    ->where('profile_role', 'assistant')->first(),
            ],
            'product' => [
                'tenant' => $this->tenant,
            ],
            'whatsapp' => [
                'whatsapp_configured' => !empty($this->settings['whatsapp_phone_number_id']),
            ],
            'preview' => [
                'website'  => $this->websiteModel->first(),
                'unit'     => $this->unitModel->find($this->settings['onboarding_unit_id'] ?? 0),
                'settings' => $this->settings,
                'tenant'   => $this->tenant,
            ],
            default => []
        };
    }

    /**
     * FIX #9: retorna null si no hay bloqueo (nunca retorna false)
     */
    private function checkRequiredSteps(int $requestedStep): ?RedirectResponse
    {
        $completed = $this->settings['onboarding_completed_steps'] ?? [];

        for ($i = 1; $i < $requestedStep; $i++) {
            if ($this->steps[$i]['required'] && !in_array($i, $completed)) {
                return redirect()->to("/onboarding/step/{$i}")
                    ->with('warning', 'Por favor completa este paso antes de continuar.');
            }
        }

        return null;
    }

    private function markStepCompleted(int $step): void
    {
        $completed = $this->settings['onboarding_completed_steps'] ?? [];

        if (!in_array($step, $completed)) {
            $completed[] = $step;
        }

        $this->updateSettings([
            'onboarding_completed_steps' => $completed,
            'onboarding_step'            => $step + 1,
        ]);
    }

    private function updateSettings(array $newValues): void
    {
        $merged = array_merge($this->settings, $newValues);

        $this->tenantModel->update($this->tenantId, [
            'settings_json'     => json_encode($merged),
            'onboarding_status' => 'in_progress',
        ]);

        $this->settings = $merged;
    }

    private function buildSummary(): array
    {
        $unitId = $this->settings['onboarding_unit_id'] ?? null;

        return [
            'unit'     => $unitId ? $this->unitModel->find($unitId) : null,
            'plans'    => $this->ratePlanModel->findAll(),
            'products' => $this->productModel->findAll(),
            'has_ai'   => (bool) $this->aiPromptModel
                ->where('profile_role', 'assistant')
                ->first(),
            'has_wa'   => !empty($this->settings['whatsapp_phone_number_id']),
        ];
    }

    /**
     * Construye dinámicamente el array de pasos según el perfil del tenant.
     * has_accommodation y has_tours vienen de settings_json.
     * Se llama una sola vez en initController().
     */
    private function buildSteps(): array
    {
        $hasAccommodation = (bool)($this->settings['has_accommodation'] ?? true);
        $hasTours         = (bool)($this->settings['has_tours']         ?? false);

        $steps = [];

        // Paso 1: siempre presente
        $steps[1] = [
            'title'    => 'Identidad',
            'icon'     => 'bi-building',
            'required' => true,
            'view'     => 'identity',
        ];

        // Paso 2: perfil del negocio — siempre presente, define el resto
        $steps[2] = [
            'title'    => 'Perfil del Negocio',
            'icon'     => 'bi-toggles',
            'required' => true,
            'view'     => 'profile',
        ];

        // Paso 3: fotos — siempre presente, opcional
        $steps[3] = [
            'title'    => 'Fotos',
            'icon'     => 'bi-images',
            'required' => false,
            'view'     => 'media',
        ];

        // Pasos exclusivos de alojamiento
        if ($hasAccommodation) {
            $steps[4] = [
                'title'    => 'Primera Habitación',
                'icon'     => 'bi-door-open',
                'required' => true,
                'view'     => 'unit',
            ];
            $steps[5] = [
                'title'    => 'Plan Tarifario',
                'icon'     => 'bi-currency-dollar',
                'required' => true,
                'view'     => 'rates',
            ];
        }

        // Pasos exclusivos de tours
        if ($hasTours) {
            $steps[6] = [
                'title'    => 'Primer Tour',
                'icon'     => 'bi-compass',
                'required' => true,
                'view'     => 'tour_basic',
            ];
            $steps[7] = [
                'title'    => 'Primera Salida',
                'icon'     => 'bi-calendar-event',
                'required' => true,
                'view'     => 'tour_schedule',
            ];
        }

        // Pasos finales: siempre presentes
        $steps[8] = [
            'title'    => 'Asistente IA',
            'icon'     => 'bi-robot',
            'required' => false,
            'view'     => 'ai_prompt',
        ];
        $steps[9] = [
            'title'    => 'Producto / Servicio',
            'icon'     => 'bi-box-seam',
            'required' => false,
            'view'     => 'product',
        ];
        $steps[10] = [
            'title'    => 'WhatsApp Business',
            'icon'     => 'bi-whatsapp',
            'required' => false,
            'view'     => 'whatsapp',
        ];
        $steps[11] = [
            'title'    => 'Vista Previa',
            'icon'     => 'bi-eye',
            'required' => false,
            'view'     => 'preview',
        ];

        // Ordenar por clave para garantizar secuencia correcta
        ksort($steps);

        return $steps;
    }
}