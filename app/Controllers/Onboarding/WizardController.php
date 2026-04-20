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
    private const STEPS = [
        1 => ['title' => 'Identidad del Hotel',  'icon' => 'bi-building',        'required' => true],
        2 => ['title' => 'Fotos',                'icon' => 'bi-images',          'required' => false],
        3 => ['title' => 'Primera Habitación',   'icon' => 'bi-door-open',       'required' => true],
        4 => ['title' => 'Plan Tarifario',       'icon' => 'bi-currency-dollar', 'required' => true],
        5 => ['title' => 'Asistente IA',         'icon' => 'bi-robot',           'required' => false],
        6 => ['title' => 'Producto / Servicio',  'icon' => 'bi-box-seam',        'required' => false],
        7 => ['title' => 'WhatsApp Business',    'icon' => 'bi-whatsapp',        'required' => false],
        8 => ['title' => 'Vista Previa',         'icon' => 'bi-eye',             'required' => false],
    ];

    private int   $tenantId;
    private array $tenant;
    private array $settings;

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
        if (!isset(self::STEPS[$stepNumber])) {
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
            'steps'       => self::STEPS,
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
        if (!isset(self::STEPS[$stepNumber])) {
            return redirect()->to('/onboarding');
        }

        log_message('info', "[Onboarding] Guardando paso {$stepNumber} para tenant {$this->tenantId}");

        $result = match($stepNumber) {
            1 => $this->saveStep1(),
            2 => $this->saveStep2(),
            3 => $this->saveStep3(),
            4 => $this->saveStep4(),
            5 => $this->saveStep5(),
            6 => $this->saveStep6(),
            7 => $this->saveStep7(),
            8 => $this->saveStep8(),
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

        if ($nextStep > count(self::STEPS)) {
            return redirect()->to('/onboarding/complete');
        }

        return redirect()->to("/onboarding/step/{$nextStep}")
            ->with('success', '✅ ' . self::STEPS[$stepNumber]['title'] . ' guardado correctamente.');
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
            'steps'   => self::STEPS,
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
    private function saveStep2(): array
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

    /**
     * Paso 3: Primera unidad de alojamiento
     */
    private function saveStep3(): array
    {
        $rules = [
            'unit_name'      => 'required|max_length[50]',
            'type_name'      => 'required|max_length[100]',
            'base_occupancy' => 'required|integer|greater_than[0]',
            'max_occupancy'  => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return ['success' => false, 'message' => implode(' ', $this->validator->getErrors())];
        }

        // Crear o reutilizar tipo — BaseMultiTenantModel filtra por tenant automáticamente
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

        // Crear la unidad
        $unitId = $this->unitModel->createForTenant([
            'type_id'        => $typeId,
            'name'           => $this->request->getPost('unit_name'),
            'description'    => $this->request->getPost('description'),
            'base_occupancy' => $this->request->getPost('base_occupancy'),
            'max_occupancy'  => $this->request->getPost('max_occupancy'),
            'bathrooms'      => $this->request->getPost('bathrooms') ?? 1.0,
            'status'         => 'available',
        ]);

        if (!$unitId) {
            log_message('error', "[Onboarding/Paso3] Error creando unidad para tenant {$this->tenantId}");
            return ['success' => false, 'message' => 'Error creando la habitación.'];
        }

        // Guardar camas
        $bedTypes = $this->request->getPost('bed_type_id') ?? [];
        $bedQtys  = $this->request->getPost('bed_quantity') ?? [];

        foreach ($bedTypes as $i => $bedTypeId) {
            if (!empty($bedTypeId) && !empty($bedQtys[$i])) {
                // UnitBedModel no tiene tenant_id — insert directo
                $this->unitBedModel->insert([
                    'unit_id'     => $unitId,
                    'bed_type_id' => (int) $bedTypeId,
                    'quantity'    => (int) $bedQtys[$i],
                ]);
            }
        }

        // Guardar amenidades
        $amenityIds = $this->request->getPost('amenity_ids') ?? [];
        foreach ($amenityIds as $amenityId) {
            // UnitAmenityModel no tiene tenant_id — insert directo
            $this->unitAmenityModel->insert([
                'unit_id'    => $unitId,
                'amenity_id' => (int) $amenityId,
            ]);
        }

        // Subir foto de la unidad
        $photo = $this->request->getFile('unit_photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $newName = $photo->getRandomName();

            // FIX #6: FCPATH para archivos públicos
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

        log_message('info', "[Onboarding/Paso3] Unidad #{$unitId} creada para tenant {$this->tenantId}");
        return ['success' => true];
    }

    /**
     * Paso 4: Plan tarifario base
     */
    private function saveStep4(): array
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
     */
    private function saveStep5(): array
    {
        $systemInstruction = trim($this->request->getPost('system_instruction') ?? '');

        if (empty($systemInstruction)) {
            return ['success' => false, 'message' => 'El prompt del asistente no puede estar vacío.'];
        }

        // BaseMultiTenantModel filtra por tenant automáticamente
        $existing = $this->aiPromptModel
            ->where('profile_role', 'assistant')
            ->first();

        if ($existing) {
            $this->aiPromptModel->update($existing['id'], [
                'system_instruction' => $systemInstruction,
                'model_version'      => 'gemini-2.5-flash',
            ]);
        } else {
            $this->aiPromptModel->createForTenant([
                'profile_role'       => 'assistant',
                'model_version'      => 'gemini-2.5-flash',
                'system_instruction' => $systemInstruction,
            ]);
        }

        log_message('info', "[Onboarding/Paso5] Prompt IA guardado para tenant {$this->tenantId}");
        return ['success' => true];
    }

    /**
     * Paso 6: Primer producto o servicio
     */
    private function saveStep6(): array
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
    private function saveStep7(): array
    {
        return ['success' => true];
    }

    /**
     * Paso 8: Vista previa y publicación del sitio web
     */
    private function saveStep8(): array
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

    private function getStepData(int $step): array
    {
        return match($step) {
            1 => [
                'tenant' => $this->tenant,
            ],
            2 => [
                'logo'   => $this->tenant['logo_path'] ?? null,
                'photos' => $this->mediaModel
                    ->where('entity_type', 'tenant')
                    ->findAll(),
            ],
            3 => [
                'bed_types' => $this->bedTypeModel->findAll(),
                'amenities' => $this->amenityModel->findAll(),
            ],
            4 => [
                'unit_name' => $this->unitModel
                        ->find($this->settings['onboarding_unit_id'] ?? 0)['name'] ?? 'tu unidad',
                'tenant'    => $this->tenant,
            ],
            5 => [
                'existing_prompt' => $this->aiPromptModel
                    ->where('profile_role', 'assistant')
                    ->first(),
            ],
            6 => [
                'tenant' => $this->tenant,
            ],
            7 => [
                'whatsapp_configured' => !empty($this->settings['whatsapp_phone_number_id']),
            ],
            // FIX #5: $settings incluido para step8_preview.php
            8 => [
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
            if (self::STEPS[$i]['required'] && !in_array($i, $completed)) {
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
}