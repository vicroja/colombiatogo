<?php

namespace App\Controllers\Onboarding;

use App\Controllers\BaseController;
use App\Models\ImportStagingModel;
use App\Models\TenantModel;
use App\Services\Onboarding\HotelExtractorService;
use App\Services\Onboarding\HotelImportApplierService;

/**
 * ImportController — flujo de importación automática del PMS.
 *
 * Flujo:
 *   1. form()    → muestra el formulario (URL / archivo / texto)
 *   2. extract() → procesa la entrada, llama a Gemini, redirige a review
 *   3. review()  → pantalla editable con tabs por sección
 *   4. confirm() → recibe el POST, hace MERGE correcto sobre el JSON
 *                  original (sin perder datos), aplica con el Applier
 *                  y marca los pasos del wizard como completados.
 */
class ImportController extends BaseController
{
    private function tenantId(): int
    {
        return (int)(session('active_tenant_id') ?: session('tenant_id'));
    }

    /**
     * Lee el perfil del tenant (has_accommodation / has_tours) desde settings_json.
     */
    private function profile(): array
    {
        $tenant   = (new TenantModel())->find($this->tenantId());
        $settings = json_decode($tenant['settings_json'] ?? '{}', true) ?? [];

        return [
            'has_accommodation' => (bool)($settings['has_accommodation'] ?? true),
            'has_tours'         => (bool)($settings['has_tours']         ?? false),
        ];
    }

    // ──────────────────────────────────────────────────────────────────
    // 1. Formulario
    // ──────────────────────────────────────────────────────────────────

    public function form()
    {
        $tenantId = $this->tenantId();
        $latest   = (new ImportStagingModel())->latestForTenant($tenantId);

        return view('onboarding/import/extract_form', [
            'latest'  => $latest,
            'profile' => $this->profile(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // 2. Extracción
    // ──────────────────────────────────────────────────────────────────

    public function extract()
    {
        $tenantId  = $this->tenantId();
        $userId    = (int)session('user_id');
        $profile   = $this->profile();
        $extractor = new HotelExtractorService();

        $mode = $this->request->getPost('mode');

        if ($mode === 'urls') {
            $raw    = trim($this->request->getPost('urls') ?? '');
            $urls   = array_filter(array_map('trim', explode("\n", $raw)));
            $result = $extractor->extractFromUrls($tenantId, $urls, $profile, $userId);
        } elseif ($mode === 'text') {
            $text   = trim($this->request->getPost('text') ?? '');
            $result = $extractor->extractFromText($tenantId, $text, $profile, $userId);
        } elseif ($mode === 'file') {
            $file = $this->request->getFile('file');
            if (!$file || !$file->isValid()) {
                return redirect()->back()->with('error', 'Archivo inválido.');
            }
            $mimeType = $file->getMimeType();
            $allowed  = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mimeType, $allowed, true)) {
                return redirect()->back()->with('error', 'Solo PDF o imágenes JPG/PNG/WebP.');
            }

            $destDir = WRITEPATH . 'uploads/imports/' . $tenantId;
            if (!is_dir($destDir)) mkdir($destDir, 0777, true);
            $newName = $file->getRandomName();
            $file->move($destDir, $newName);
            $fullPath = $destDir . '/' . $newName;

            $result = $extractor->extractFromFile($tenantId, $fullPath, $mimeType, $profile, $userId);
        } else {
            return redirect()->back()->with('error', 'Modo de importación inválido.');
        }

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->to('/onboarding/import/review/' . $result['staging_id']);
    }

    // ──────────────────────────────────────────────────────────────────
    // 3. Review
    // ──────────────────────────────────────────────────────────────────

    public function review(int $stagingId)
    {
        $tenantId = $this->tenantId();
        $staging  = (new ImportStagingModel())
            ->where('import_staging.tenant_id', $tenantId)
            ->find($stagingId);

        if (!$staging) {
            return redirect()->to('/onboarding/import')->with('error', 'No encontrado.');
        }

        $data = json_decode($staging['edited_json'] ?: $staging['extracted_json'] ?: '{}', true) ?: [];

        return view('onboarding/import/review', [
            'staging' => $staging,
            'data'    => $data,
            'profile' => $this->profile(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // 4. Confirmación → aplicar
    // ──────────────────────────────────────────────────────────────────

    public function confirm(int $stagingId)
    {
        $tenantId     = $this->tenantId();
        $stagingModel = new ImportStagingModel();
        $staging      = $stagingModel
            ->where('import_staging.tenant_id', $tenantId)
            ->find($stagingId);

        if (!$staging) {
            return redirect()->to('/onboarding/import')->with('error', 'No encontrado.');
        }

        // MERGE correcto: tomar el JSON original como base y sobrescribir
        // las secciones editables con lo que vino del POST.
        // Esto NO pierde claves como extraction_notes, _scraped_images_pool, etc.
        $original = json_decode($staging['extracted_json'] ?? '{}', true) ?: [];
        $edited   = $this->buildEditedJsonFromPost($original);

        $stagingModel->update($stagingId, [
            'edited_json' => json_encode($edited, JSON_UNESCAPED_UNICODE),
            'status'      => 'reviewed',
        ]);

        $applier = new HotelImportApplierService();
        $result  = $applier->apply($stagingId);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['error']);
        }

        // Marcar pasos del wizard como completados según lo que se importó
        $this->markWizardStepsCompleted($tenantId, $edited, $result['summary'], $stagingId);

        return view('onboarding/import/imported', [
            'summary'        => $result['summary'],
            'data'           => $edited,
            'back_to_wizard' => true,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Helpers privados
    // ──────────────────────────────────────────────────────────────────

    /**
     * Construye el JSON editado haciendo MERGE sobre el original.
     *
     * El POST envía formularios planos por sección. Aquí los reconvertimos
     * a la misma estructura del extracted_json y reemplazamos solo las
     * secciones editadas — todo lo demás (extraction_notes,
     * _scraped_images_pool, detected_vertical, etc.) se conserva.
     */
    private function buildEditedJsonFromPost(array $original): array
    {
        $payload = $original; // base

        // ── Business summary ──────────────────────────────────────────
        $bs = $this->request->getPost('business_summary');
        if (is_array($bs)) {
            $payload['business_summary'] = array_merge(
                $payload['business_summary'] ?? [],
                $bs
            );
        }

        // ── Secciones de array con `include` ─────────────────────────
        $arraySections = [
            'accommodation_types',
            'accommodation_units',
            'rate_plans',
            'unit_rates',
            'tours',
            'tour_schedules',
            'products',
        ];

        foreach ($arraySections as $section) {
            $rows = $this->request->getPost($section);
            if (!is_array($rows)) continue;

            $clean = [];
            foreach ($rows as $row) {
                if (!is_array($row)) continue;

                // Normalizar tipos
                $row['include'] = !empty($row['include']);

                // Numéricos comunes
                foreach ([
                    'base_capacity', 'max_capacity',
                    'base_occupancy', 'max_occupancy',
                    'duration_minutes', 'min_pax', 'min_nights',
                    'min_nights_default', 'max_pax',
                ] as $intField) {
                    if (isset($row[$intField]) && $row[$intField] !== '') {
                        $row[$intField] = (int)$row[$intField];
                    }
                }

                foreach ([
                    'bathrooms', 'price_per_night',
                    'extra_person_price', 'extra_child_price',
                    'price_adult', 'price_child', 'unit_price',
                ] as $floatField) {
                    if (isset($row[$floatField]) && $row[$floatField] !== '') {
                        $row[$floatField] = (float)$row[$floatField];
                    }
                }

                // Arrays planos que vinieron como string CSV
                foreach (['beds', 'amenities', 'included', 'excluded'] as $arrField) {
                    if (isset($row[$arrField]) && is_string($row[$arrField])) {
                        // Permitir formato CSV simple: "Cama Queen, Cama Sencilla"
                        $row[$arrField] = array_values(array_filter(array_map('trim', explode(',', $row[$arrField]))));
                    }
                }

                $clean[] = $row;
            }

            $payload[$section] = $clean;
        }

        // Vertical confirmado por el usuario (si vino)
        $vertical = $this->request->getPost('confirmed_vertical');
        if ($vertical) {
            $payload['detected_vertical'] = $vertical;
        }

        return $payload;
    }

    /**
     * Marca como completados los pasos del wizard según lo que se importó.
     * Esto evita que el usuario tenga que repetir manualmente lo ya creado.
     *
     * NOTA: los números de paso dependen del buildSteps() del WizardController.
     * Aquí asumimos el esquema actual del PMS:
     *   1=identity, 2=profile, [3=import opcional, NUEVO],
     *   4=media, 5=unit, 6=rates, 7=tour_basic, 8=tour_schedule,
     *   9=ai_prompt, 10=product, 11=whatsapp, 12=preview.
     *
     * Si el WizardController usa otra numeración tras la integración, basta
     * con cambiar este mapeo.
     */
    private function markWizardStepsCompleted(int $tenantId, array $edited, array $summary, int $stagingId): void
    {
        $tenantModel = new TenantModel();
        $tenant      = $tenantModel->find($tenantId);
        $settings    = json_decode($tenant['settings_json'] ?? '{}', true) ?? [];

        $completed = $settings['onboarding_completed_steps'] ?? [];

        // Paso de import siempre se marca completado
        $importStep = 3;
        $completed[] = $importStep;

        // Pasos posteriores que se pueden saltar si se importaron datos
        $skipMap = [
            5 => ($summary['units_created']           ?? 0) > 0, // primera unidad
            6 => ($summary['unit_rates_created']      ?? 0) > 0, // plan tarifario
            7 => ($summary['tours_created']           ?? 0) > 0, // primer tour
            8 => ($summary['tour_schedules_created']  ?? 0) > 0, // primera salida
            10 => ($summary['products_created']       ?? 0) > 0, // producto
        ];

        foreach ($skipMap as $stepNum => $shouldComplete) {
            if ($shouldComplete) $completed[] = $stepNum;
        }

        $completed = array_values(array_unique($completed));

        $settings['onboarding_completed_steps'] = $completed;
        $settings['onboarding_import_staging_id'] = $stagingId;
        $settings['onboarding_import_decision'] = 'completed';

        $tenantModel->update($tenantId, [
            'settings_json' => json_encode($settings, JSON_UNESCAPED_UNICODE),
        ]);

        log_message('info', "[ImportController] Tenant {$tenantId} → pasos completados tras import: " . implode(',', $completed));
    }
}
