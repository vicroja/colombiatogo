<?php

namespace App\Services\Onboarding;

use App\Models\ImportStagingModel;
use App\Models\TenantModel;
use App\Models\TenantMediaModel;
use App\Models\BedTypeModel;
use App\Models\AmenityModel;
use App\Models\AccommodationTypeModel;
use App\Models\AccommodationUnitModel;
use App\Models\UnitBedModel;
use App\Models\UnitAmenityModel;
use App\Models\RatePlanModel;
use App\Models\UnitRateModel;
use App\Models\TourModel;
use App\Models\TourScheduleModel;
use App\Models\ProductModel;
use App\Models\ProductCategoryModel;

/**
 * HotelImportApplierService
 *
 * Toma el edited_json de un import_staging y crea las filas reales
 * en las tablas del PMS, respetando el orden de FKs.
 *
 * Características:
 *   - Transaccional: todo o nada.
 *   - Idempotente: dedup por (tenant_id, name) en cada tabla.
 *   - Descarga imágenes y las asocia vía tenant_media (entity_type = unit/tour/product/tenant).
 *   - Crea bed_types y amenities faltantes on-the-fly.
 *   - Resuelve unidades padre→hijo en 2 pasadas.
 */
class HotelImportApplierService
{
    private ImportStagingModel        $stagingModel;
    private TenantModel               $tenantModel;
    private TenantMediaModel          $mediaModel;
    private BedTypeModel              $bedTypeModel;
    private AmenityModel              $amenityModel;
    private AccommodationTypeModel    $typeModel;
    private AccommodationUnitModel    $unitModel;
    private UnitBedModel              $unitBedModel;
    private UnitAmenityModel          $unitAmenityModel;
    private RatePlanModel             $ratePlanModel;
    private UnitRateModel             $unitRateModel;
    private TourModel                 $tourModel;
    private TourScheduleModel         $scheduleModel;
    private ProductModel              $productModel;
    private ProductCategoryModel      $categoryModel;
    private WebScraperService         $scraper;

    public function __construct()
    {
        $this->stagingModel     = new ImportStagingModel();
        $this->tenantModel      = new TenantModel();
        $this->mediaModel       = new TenantMediaModel();
        $this->bedTypeModel     = new BedTypeModel();
        $this->amenityModel     = new AmenityModel();
        $this->typeModel        = new AccommodationTypeModel();
        $this->unitModel        = new AccommodationUnitModel();
        $this->unitBedModel     = new UnitBedModel();
        $this->unitAmenityModel = new UnitAmenityModel();
        $this->ratePlanModel    = new RatePlanModel();
        $this->unitRateModel    = new UnitRateModel();
        $this->tourModel        = new TourModel();
        $this->scheduleModel    = new TourScheduleModel();
        $this->productModel     = new ProductModel();
        $this->categoryModel    = new ProductCategoryModel();
        $this->scraper          = new WebScraperService();
    }

    public function apply(int $stagingId): array
    {
        $staging = $this->stagingModel->find($stagingId);
        if (!$staging) {
            return ['success' => false, 'error' => 'Staging no encontrado.'];
        }
        if ($staging['status'] === 'imported') {
            return ['success' => false, 'error' => 'Este staging ya fue importado.'];
        }

        $data     = json_decode($staging['edited_json'] ?: $staging['extracted_json'] ?: '{}', true) ?: [];
        $tenantId = (int)$staging['tenant_id'];

        $db = \Config\Database::connect();
        $db->transStart();

        $summary = [
            'business_summary_updated' => false,
            'logo_downloaded'          => false,
            'bed_types_created'        => 0,
            'amenities_created'        => 0,
            'accommodation_types_created' => 0,
            'units_created'            => 0,
            'unit_beds_created'        => 0,
            'unit_amenities_created'   => 0,
            'unit_images_downloaded'   => 0,
            'rate_plans_created'       => 0,
            'unit_rates_created'       => 0,
            'tours_created'            => 0,
            'tour_images_downloaded'   => 0,
            'tour_schedules_created'   => 0,
            'products_created'         => 0,
            'product_images_downloaded'=> 0,
        ];

        // 1. Resumen del negocio (tenant)
        $bs = $this->applyBusinessSummary($tenantId, $data['business_summary'] ?? []);
        $summary['business_summary_updated'] = $bs['updated'];
        $summary['logo_downloaded']          = $bs['logo_downloaded'];

        // 2. Catálogos base — bed_types y amenities
        $bedTypeMap = $this->ensureBedTypes($tenantId, $data['accommodation_units'] ?? [], $summary);
        $amenityMap = $this->ensureAmenities($tenantId, $data['accommodation_units'] ?? [], $summary);

        // 3. Tipos de alojamiento
        $typeMap = $this->applyAccommodationTypes($tenantId, $data['accommodation_types'] ?? [], $summary);

        // 4. Unidades (2 pasadas: padres primero, luego hijos)
        $unitMap = $this->applyAccommodationUnits(
            $tenantId,
            $data['accommodation_units'] ?? [],
            $typeMap,
            $bedTypeMap,
            $amenityMap,
            $summary
        );

        // 5. Rate plans
        $planMap = $this->applyRatePlans($tenantId, $data['rate_plans'] ?? [], $summary);

        // 6. Unit rates (matching por nombre)
        $this->applyUnitRates($tenantId, $data['unit_rates'] ?? [], $unitMap, $planMap, $summary);

        // 7. Tours
        $tourMap = $this->applyTours($tenantId, $data['tours'] ?? [], $summary);

        // 8. Tour schedules
        $this->applyTourSchedules($data['tour_schedules'] ?? [], $tourMap, $summary);

        // 9. Productos
        $this->applyProducts($tenantId, $data['products'] ?? [], $summary);

        // 10. Marcar staging como importado
        $this->stagingModel->update($stagingId, [
            'status'              => 'imported',
            'imported_at'         => date('Y-m-d H:i:s'),
            'import_summary_json' => json_encode($summary, JSON_UNESCAPED_UNICODE),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            log_message('error', "[HotelImportApplier] Transacción falló para staging {$stagingId}");
            return ['success' => false, 'error' => 'Error de transacción al aplicar.'];
        }

        return ['success' => true, 'summary' => $summary];
    }

    // ──────────────────────────────────────────────────────────────────
    // 1. Business summary → tenants
    // ──────────────────────────────────────────────────────────────────

    private function applyBusinessSummary(int $tenantId, array $bs): array
    {
        if (empty($bs)) {
            return ['updated' => false, 'logo_downloaded' => false];
        }

        $tenant = $this->tenantModel->find($tenantId);
        if (!$tenant) {
            return ['updated' => false, 'logo_downloaded' => false];
        }

        $update = [];

        // Solo actualizamos campos del tenant que estén vacíos
        $fieldMap = [
            'name'          => 'name',
            'address'       => 'address',
            'city'          => 'city',
            'country'       => 'country',
            'phone'         => 'phone',
            'email'         => 'email',
            'website'       => 'website',
            'checkin_time'  => 'checkin_time',
            'checkout_time' => 'checkout_time',
        ];

        foreach ($fieldMap as $srcKey => $dstKey) {
            if (!empty($bs[$srcKey]) && empty($tenant[$dstKey])) {
                $update[$dstKey] = $bs[$srcKey];
            }
        }

        // Logo: descargar si vino y el tenant no tiene logo
        $logoDownloaded = false;
        if (!empty($bs['logo_url']) && empty($tenant['logo_path'])) {
            $destDir  = FCPATH . 'uploads/tenants/' . $tenantId;
            $filename = $this->scraper->downloadImage($bs['logo_url'], $destDir, 'logo');
            if ($filename) {
                $update['logo_path'] = 'uploads/tenants/' . $tenantId . '/' . $filename;
                $logoDownloaded = true;
            }
        }

        if (!empty($update)) {
            $this->tenantModel->update($tenantId, $update);
        }

        return ['updated' => !empty($update), 'logo_downloaded' => $logoDownloaded];
    }

    // ──────────────────────────────────────────────────────────────────
    // 2. Catálogos — bed_types
    // ──────────────────────────────────────────────────────────────────

    /**
     * Asegura que todos los bed_types referenciados existan. Retorna
     * mapa: lowercase(name) → id.
     */
    private function ensureBedTypes(int $tenantId, array $units, array &$summary): array
    {
        // Recolectar todos los nombres únicos referenciados
        $referenced = [];
        foreach ($units as $u) {
            foreach ($u['beds'] ?? [] as $bed) {
                $name = trim($bed['bed_type_name'] ?? '');
                if ($name !== '') $referenced[$this->normKey($name)] = $name;
            }
        }

        if (empty($referenced)) return [];

        // Cargar existentes
        $existing = $this->bedTypeModel
            ->where('bed_types.tenant_id', $tenantId)
            ->findAll();

        $map = [];
        foreach ($existing as $row) {
            $map[$this->normKey($row['name'])] = (int)$row['id'];
        }

        // Crear los faltantes
        foreach ($referenced as $key => $name) {
            if (isset($map[$key])) continue;
            $id = $this->bedTypeModel->createForTenant([
                'name'          => $name,
                'base_capacity' => 2,
            ]);
            if ($id) {
                $map[$key] = (int)$id;
                $summary['bed_types_created']++;
                log_message('info', "[HotelImportApplier] Bed type creado: {$name}");
            }
        }

        return $map;
    }

    // ──────────────────────────────────────────────────────────────────
    // 2b. Catálogos — amenities
    // ──────────────────────────────────────────────────────────────────

    private function ensureAmenities(int $tenantId, array $units, array &$summary): array
    {
        $referenced = [];
        foreach ($units as $u) {
            foreach ($u['amenities'] ?? [] as $amenityName) {
                $name = trim($amenityName);
                if ($name !== '') $referenced[$this->normKey($name)] = $name;
            }
        }

        if (empty($referenced)) return [];

        $existing = $this->amenityModel
            ->where('amenities.tenant_id', $tenantId)
            ->findAll();

        $map = [];
        foreach ($existing as $row) {
            $map[$this->normKey($row['name'])] = (int)$row['id'];
        }

        foreach ($referenced as $key => $name) {
            if (isset($map[$key])) continue;
            $id = $this->amenityModel->createForTenant([
                'name'     => $name,
                'category' => 'General',
            ]);
            if ($id) {
                $map[$key] = (int)$id;
                $summary['amenities_created']++;
                log_message('info', "[HotelImportApplier] Amenity creada: {$name}");
            }
        }

        return $map;
    }

    // ──────────────────────────────────────────────────────────────────
    // 3. Accommodation types
    // ──────────────────────────────────────────────────────────────────

    private function applyAccommodationTypes(int $tenantId, array $types, array &$summary): array
    {
        $map = [];

        // Cargar existentes
        $existing = $this->typeModel
            ->where('accommodation_types.tenant_id', $tenantId)
            ->findAll();
        foreach ($existing as $row) {
            $map[$this->normKey($row['name'])] = (int)$row['id'];
        }

        foreach ($types as $t) {
            $name = trim($t['name'] ?? '');
            if ($name === '') continue;

            $key = $this->normKey($name);
            if (isset($map[$key])) continue;

            $id = $this->typeModel->createForTenant([
                'name'          => $name,
                'description'   => $t['description']   ?? null,
                'base_capacity' => (int)($t['base_capacity'] ?? 2),
                'max_capacity'  => (int)($t['max_capacity']  ?? 2),
            ]);
            if ($id) {
                $map[$key] = (int)$id;
                $summary['accommodation_types_created']++;
            }
        }

        return $map;
    }

    // ──────────────────────────────────────────────────────────────────
    // 4. Accommodation units (2 pasadas)
    // ──────────────────────────────────────────────────────────────────

    private function applyAccommodationUnits(
        int $tenantId,
        array $units,
        array $typeMap,
        array $bedTypeMap,
        array $amenityMap,
        array &$summary
    ): array {
        // Filtrar solo los incluidos
        $included = array_values(array_filter($units, fn($u) => !empty($u['include']) && !empty($u['name'])));
        if (empty($included)) return [];

        // Pasada 1: crear padres y simples
        $unitMap = []; // normKey(name) → id

        foreach ($included as $u) {
            $mode = $u['mode'] ?? 'simple';
            if ($mode === 'child') continue; // se hace en pasada 2

            $id = $this->createSingleUnit(
                $tenantId, $u, null, $typeMap, $bedTypeMap, $amenityMap, $summary
            );
            if ($id) {
                $unitMap[$this->normKey($u['name'])] = $id;
            }
        }

        // Pasada 2: crear hijos
        foreach ($included as $u) {
            if (($u['mode'] ?? '') !== 'child') continue;

            $parentName = trim($u['parent_name'] ?? '');
            if ($parentName === '') {
                log_message('warning', "[HotelImportApplier] Unidad child '{$u['name']}' sin parent_name — descartada.");
                continue;
            }

            $parentId = $unitMap[$this->normKey($parentName)] ?? null;
            if (!$parentId) {
                log_message('warning', "[HotelImportApplier] No se encontró padre '{$parentName}' para child '{$u['name']}'");
                continue;
            }

            $id = $this->createSingleUnit(
                $tenantId, $u, $parentId, $typeMap, $bedTypeMap, $amenityMap, $summary
            );
            if ($id) {
                $unitMap[$this->normKey($u['name'])] = $id;
            }
        }

        return $unitMap;
    }

    /**
     * Crea una sola unidad (padre, hijo o simple) con sus beds, amenities y foto.
     */
    private function createSingleUnit(
        int $tenantId,
        array $u,
        ?int $parentId,
        array $typeMap,
        array $bedTypeMap,
        array $amenityMap,
        array &$summary
    ): ?int {
        $name = trim($u['name']);

        // Dedup por (tenant_id, name) — hay UNIQUE en la tabla
        $existing = $this->unitModel
            ->where('accommodation_units.tenant_id', $tenantId)
            ->where('accommodation_units.name', $name)
            ->first();
        if ($existing) {
            return (int)$existing['id'];
        }

        // Resolver type_id
        $typeName = trim($u['type_name'] ?? '');
        $typeId   = $typeMap[$this->normKey($typeName)] ?? null;

        if (!$typeId) {
            // Crear type on-the-fly si no existe (fallback)
            $typeId = $this->typeModel->createForTenant([
                'name'          => $typeName !== '' ? $typeName : 'Habitación',
                'base_capacity' => (int)($u['base_occupancy'] ?? 2),
                'max_capacity'  => (int)($u['max_occupancy']  ?? 2),
            ]);
            if (!$typeId) {
                log_message('error', "[HotelImportApplier] No pude crear type para unidad {$name}");
                return null;
            }
            $summary['accommodation_types_created']++;
            $typeMap[$this->normKey($typeName)] = (int)$typeId;
        }

        $unitId = $this->unitModel->createForTenant([
            'type_id'        => (int)$typeId,
            'parent_id'      => $parentId,
            'name'           => $name,
            'description'    => $u['description'] ?? null,
            'base_occupancy' => (int)($u['base_occupancy'] ?? 2),
            'max_occupancy'  => (int)($u['max_occupancy']  ?? 2),
            'bathrooms'      => (float)($u['bathrooms'] ?? 1.0),
            'status'         => 'available',
        ]);

        if (!$unitId) {
            log_message('error', "[HotelImportApplier] Falló insert de unidad {$name}");
            return null;
        }

        $unitId = (int)$unitId;
        $summary['units_created']++;

        // Insertar beds
        foreach ($u['beds'] ?? [] as $bed) {
            $btName = trim($bed['bed_type_name'] ?? '');
            $btId   = $bedTypeMap[$this->normKey($btName)] ?? null;
            $qty    = (int)($bed['quantity'] ?? 1);
            if ($btId && $qty > 0) {
                $this->unitBedModel->insert([
                    'unit_id'     => $unitId,
                    'bed_type_id' => $btId,
                    'quantity'    => $qty,
                ]);
                $summary['unit_beds_created']++;
            }
        }

        // Insertar amenities
        foreach ($u['amenities'] ?? [] as $amenityName) {
            $aId = $amenityMap[$this->normKey($amenityName)] ?? null;
            if ($aId) {
                $this->unitAmenityModel->insert([
                    'unit_id'    => $unitId,
                    'amenity_id' => $aId,
                ]);
                $summary['unit_amenities_created']++;
            }
        }

        // Foto: descargar y registrar en tenant_media
        if (!empty($u['image_url'])) {
            $destDir  = FCPATH . 'uploads/units/' . $unitId;
            $filename = $this->scraper->downloadImage($u['image_url'], $destDir, 'unit');
            if ($filename) {
                $this->mediaModel->insert([
                    'tenant_id'   => $tenantId,
                    'entity_type' => 'unit',
                    'entity_id'   => $unitId,
                    'file_path'   => 'uploads/units/' . $unitId . '/' . $filename,
                    'file_type'   => 'image',
                    'tag'         => 'otro',
                    'is_main'     => 1,
                    'sort_order'  => 0,
                ]);
                $summary['unit_images_downloaded']++;
            }
        }

        return $unitId;
    }

    // ──────────────────────────────────────────────────────────────────
    // 5. Rate plans
    // ──────────────────────────────────────────────────────────────────

    private function applyRatePlans(int $tenantId, array $plans, array &$summary): array
    {
        $map = [];

        // Cargar existentes
        $existing = $this->ratePlanModel
            ->where('rate_plans.tenant_id', $tenantId)
            ->findAll();
        foreach ($existing as $row) {
            $map[$this->normKey($row['name'])] = (int)$row['id'];
        }

        // Validar keys de amenities contra el catálogo permitido
        $allowedAmenityKeys = array_keys(RatePlanModel::availableAmenities());

        foreach ($plans as $p) {
            if (empty($p['include']) || empty($p['name'])) continue;

            $name = trim($p['name']);
            $key  = $this->normKey($name);
            if (isset($map[$key])) continue;

            // Construir amenities_json como mapa {key: true}
            $amenitiesJson = [];
            foreach ($p['amenities'] ?? [] as $aKey) {
                if (in_array($aKey, $allowedAmenityKeys, true)) {
                    $amenitiesJson[$aKey] = true;
                }
            }

            $cancellation = $p['cancellation_policy'] ?? 'flexible';
            if (!in_array($cancellation, ['flexible', 'moderate', 'strict', 'non_refundable'], true)) {
                $cancellation = 'flexible';
            }

            $id = $this->ratePlanModel->createForTenant([
                'name'                => $name,
                'description'         => $p['description'] ?? null,
                'amenities_json'      => json_encode($amenitiesJson, JSON_UNESCAPED_UNICODE),
                'cancellation_policy' => $cancellation,
                'min_nights_default'  => (int)($p['min_nights_default'] ?? 1),
                'is_default'          => !empty($p['is_default']) ? 1 : 0,
                'is_active'           => 1,
            ]);

            if ($id) {
                $map[$key] = (int)$id;
                $summary['rate_plans_created']++;
            }
        }

        return $map;
    }

    // ──────────────────────────────────────────────────────────────────
    // 6. Unit rates (matching por nombre de unit + nombre de plan)
    // ──────────────────────────────────────────────────────────────────

    private function applyUnitRates(int $tenantId, array $rates, array $unitMap, array $planMap, array &$summary): void
    {
        foreach ($rates as $r) {
            if (empty($r['include'])) continue;

            $unitKey = $this->normKey($r['unit_name']      ?? '');
            $planKey = $this->normKey($r['rate_plan_name'] ?? '');

            $unitId = $unitMap[$unitKey] ?? null;
            $planId = $planMap[$planKey] ?? null;

            if (!$unitId || !$planId) {
                log_message('warning', "[HotelImportApplier] Rate descartado: unit='{$r['unit_name']}', plan='{$r['rate_plan_name']}' no encontrados.");
                continue;
            }

            // Dedup por (unit_id, rate_plan_id) — hay UNIQUE
            $existing = $this->unitRateModel
                ->where('unit_rates.unit_id', $unitId)
                ->where('unit_rates.rate_plan_id', $planId)
                ->first();
            if ($existing) continue;

            $this->unitRateModel->createForTenant([
                'unit_id'            => $unitId,
                'rate_plan_id'       => $planId,
                'price_per_night'    => (float)($r['price_per_night']    ?? 0),
                'extra_person_price' => (float)($r['extra_person_price'] ?? 0),
                'extra_child_price'  => (float)($r['extra_child_price']  ?? 0),
                'min_nights'         => (int)($r['min_nights'] ?? 1),
                'is_active'          => 1,
            ]);
            $summary['unit_rates_created']++;
        }
    }

    // ──────────────────────────────────────────────────────────────────
    // 7. Tours
    // ──────────────────────────────────────────────────────────────────

    private function applyTours(int $tenantId, array $tours, array &$summary): array
    {
        $map = []; // normKey(name) → id

        foreach ($tours as $t) {
            if (empty($t['include']) || empty($t['name'])) continue;

            $name = trim($t['name']);
            $key  = $this->normKey($name);

            // Dedup
            $existing = $this->tourModel
                ->where('tours.tenant_id', $tenantId)
                ->where('tours.name', $name)
                ->first();
            if ($existing) {
                $map[$key] = (int)$existing['id'];
                continue;
            }

            $difficulty = $t['difficulty_level'] ?? 'easy';
            if (!in_array($difficulty, ['easy', 'moderate', 'hard'], true)) $difficulty = 'easy';

            $cancellation = $t['cancellation_policy'] ?? 'flexible';
            if (!in_array($cancellation, ['flexible', 'moderate', 'strict', 'non_refundable'], true)) {
                $cancellation = 'flexible';
            }

            $tourId = $this->tourModel->createForTenant([
                'name'                => $name,
                'description'         => $t['description']       ?? null,
                'duration_minutes'    => (int)($t['duration_minutes'] ?? 60),
                'meeting_point'       => $t['meeting_point']     ?? null,
                'min_pax'             => (int)($t['min_pax']     ?? 1),
                'price_adult'         => (float)($t['price_adult'] ?? 0),
                'price_child'         => (float)($t['price_child'] ?? 0),
                'cancellation_policy' => $cancellation,
                'difficulty_level'    => $difficulty,
                'included_json'       => json_encode($t['included'] ?? [], JSON_UNESCAPED_UNICODE),
                'excluded_json'       => json_encode($t['excluded'] ?? [], JSON_UNESCAPED_UNICODE),
                'is_active'           => 1,
            ]);

            if (!$tourId) continue;

            $tourId = (int)$tourId;
            $map[$key] = $tourId;
            $summary['tours_created']++;

            // Foto del tour
            if (!empty($t['image_url'])) {
                $destDir  = FCPATH . 'uploads/tours/' . $tourId;
                $filename = $this->scraper->downloadImage($t['image_url'], $destDir, 'tour');
                if ($filename) {
                    $this->mediaModel->insert([
                        'tenant_id'   => $tenantId,
                        'entity_type' => 'tour',
                        'entity_id'   => $tourId,
                        'file_path'   => 'uploads/tours/' . $tourId . '/' . $filename,
                        'file_type'   => 'image',
                        'tag'         => 'otro',
                        'is_main'     => 1,
                        'sort_order'  => 0,
                    ]);
                    $summary['tour_images_downloaded']++;
                }
            }
        }

        return $map;
    }

    // ──────────────────────────────────────────────────────────────────
    // 8. Tour schedules
    // ──────────────────────────────────────────────────────────────────

    private function applyTourSchedules(array $schedules, array $tourMap, array &$summary): void
    {
        foreach ($schedules as $s) {
            if (empty($s['include']) || empty($s['tour_name']) || empty($s['start_datetime'])) continue;

            $tourId = $tourMap[$this->normKey($s['tour_name'])] ?? null;
            if (!$tourId) continue;

            $this->scheduleModel->insert([
                'tour_id'        => $tourId,
                'start_datetime' => $s['start_datetime'],
                'max_pax'        => (int)($s['max_pax']     ?? 10),
                'current_pax'    => 0,
                'status'         => 'scheduled',
                'notes'          => $s['notes']             ?? null,
            ]);
            $summary['tour_schedules_created']++;
        }
    }

    // ──────────────────────────────────────────────────────────────────
    // 9. Products
    // ──────────────────────────────────────────────────────────────────

    private function applyProducts(int $tenantId, array $products, array &$summary): void
    {
        if (empty($products)) return;

        $categoryCache = [];
        $productImagesDir = FCPATH . 'uploads/products/' . $tenantId;

        foreach ($products as $p) {
            if (empty($p['include']) || empty($p['name'])) continue;

            $name = trim($p['name']);

            // Dedup
            $existing = $this->productModel
                ->where('products.tenant_id', $tenantId)
                ->where('products.name', $name)
                ->first();
            if ($existing) continue;

            // Categoría
            $catName = trim($p['category'] ?? 'General');
            if (!isset($categoryCache[$catName])) {
                $categoryCache[$catName] = $this->ensureCategory($tenantId, $catName, 'product');
            }
            $catId = $categoryCache[$catName];
            if (!$catId) continue;

            $productId = $this->productModel->createForTenant([
                'category_id'             => $catId,
                'name'                    => $name,
                'description'             => $p['description'] ?? null,
                'unit_price'              => (float)($p['unit_price'] ?? 0),
                'is_available_for_guests' => 1,
                'is_active'               => 1,
            ]);

            if (!$productId) continue;

            $productId = (int)$productId;
            $summary['products_created']++;

            // Foto del producto
            if (!empty($p['image_url'])) {
                $filename = $this->scraper->downloadImage($p['image_url'], $productImagesDir, 'prod');
                if ($filename) {
                    $this->mediaModel->insert([
                        'tenant_id'   => $tenantId,
                        'entity_type' => 'product',
                        'entity_id'   => $productId,
                        'file_path'   => 'uploads/products/' . $tenantId . '/' . $filename,
                        'file_type'   => 'image',
                        'tag'         => 'otro',
                        'is_main'     => 1,
                        'sort_order'  => 0,
                    ]);
                    $summary['product_images_downloaded']++;
                }
            }
        }
    }

    private function ensureCategory(int $tenantId, string $name, string $type): ?int
    {
        $existing = $this->categoryModel
            ->where('product_categories.tenant_id', $tenantId)
            ->where('product_categories.name', $name)
            ->where('product_categories.type', $type)
            ->first();

        if ($existing) return (int)$existing['id'];

        $id = $this->categoryModel->createForTenant([
            'name'      => $name,
            'type'      => $type,
            'is_active' => 1,
        ]);
        return $id ? (int)$id : null;
    }

    // ──────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────

    /**
     * Normaliza un nombre para comparación case-insensitive y sin tildes.
     */
    private function normKey(string $name): string
    {
        $n = mb_strtolower(trim($name), 'UTF-8');
        $n = strtr($n, [
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n',
            'à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u',
        ]);
        return preg_replace('/\s+/', ' ', $n);
    }
}
