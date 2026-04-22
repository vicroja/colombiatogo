<?php

namespace App\Controllers;

use App\Models\AccommodationTypeModel;
use App\Models\AccommodationUnitModel;
use App\Services\PlanLimitService;
use App\Models\TenantMediaModel;
use App\Models\AmenityModel;
use App\Models\BedTypeModel;
use App\Models\UnitAmenityModel;
use App\Models\UnitBedModel;

class InventoryController extends BaseController
{

    // =========================================================================
    // INDEX
    // =========================================================================
    public function index()
    {
        $tenantId     = session('active_tenant_id');
        $limitService = new PlanLimitService();
        $unitModel    = new AccommodationUnitModel();

        $units = $unitModel
            ->select('accommodation_units.*, accommodation_types.name as type_name')
            ->join('accommodation_types', 'accommodation_types.id = accommodation_units.type_id', 'left')
            ->where('accommodation_units.tenant_id', $tenantId)
            ->orderBy('COALESCE(accommodation_units.parent_id, accommodation_units.id) ASC', '', false)
            ->orderBy('accommodation_units.parent_id', 'ASC')
            ->orderBy('accommodation_units.name', 'ASC')
            ->findAll();

        return view('inventory/index', [
            'units'     => $units,
            'limitInfo' => $limitService->getUnitUsageInfo(),
        ]);
    }


    // =========================================================================
    // WIZARD — Muestra el paso actual
    // =========================================================================
    public function wizardStep(int $step = 1)
    {
        $tenantId     = session('active_tenant_id');
        $typeModel    = new AccommodationTypeModel();
        $amenityModel = new AmenityModel();
        $bedTypeModel = new BedTypeModel();
        $mediaModel   = new TenantMediaModel();

        // Pasos 2 y 3 requieren que el paso 1 ya haya sido completado
        $unitId = session('wizard_unit_id');
        if ($step > 1 && empty($unitId)) {
            return redirect()->to('/inventory/wizard/step/1')
                ->with('error', 'Primero completa la información base.');
        }

        $existingPhotos = [];
        if ($step === 3 && $unitId) {
            $existingPhotos = $mediaModel
                ->where('entity_type', 'unit')
                ->where('entity_id', $unitId)
                ->orderBy('sort_order', 'ASC')
                ->findAll();
        }

        // FIX: pasar unitName y unitId explícitamente a la vista
        return view('inventory/wizard_layout', [
            'currentStep'    => $step,
            'types'          => $typeModel->findAll(),
            'amenities'      => $amenityModel->where('tenant_id', $tenantId)->findAll(),
            'bedTypes'       => $bedTypeModel->whereIn('tenant_id', [0, $tenantId])->orderBy('tenant_id', 'ASC')->orderBy('name', 'ASC')->findAll(),
            'existingPhotos' => $existingPhotos,
            'unitId'         => $unitId,
            'unitName'       => session('wizard_unit_name') ?? '',
        ]);
    }

    // /inventory/create → limpia sesión y arranca wizard
    public function create()
    {
        session()->remove(['wizard_unit_id', 'wizard_unit_name', 'wizard_amenities']);
        return redirect()->to('/inventory/wizard/step/1');
    }

    // Saltar paso opcional
    public function wizardSkip(int $step)
    {
        $next = $step + 1;
        if ($next > 3) {
            $unitName = session('wizard_unit_name') ?? 'la unidad';
            session()->remove(['wizard_unit_id', 'wizard_unit_name', 'wizard_amenities']);
            return redirect()->to('/inventory')
                ->with('success', "Unidad «{$unitName}» creada. Puedes completar los detalles en cualquier momento.");
        }
        return redirect()->to("/inventory/wizard/step/{$next}");
    }


    // =========================================================================
    // WIZARD — Guarda cada paso
    // =========================================================================
    public function wizardSave(int $step)
    {
        return match($step) {
            1 => $this->saveStep1(),
            2 => $this->saveStep2(),
            3 => $this->saveStep3(),
            default => redirect()->to('/inventory'),
        };
    }

    // ── Paso 1: Crear unidad padre + camas (simple) o sub-habitaciones (compuesta)
    private function saveStep1()
    {
        $tenantId     = session('active_tenant_id');
        $unitModel    = new AccommodationUnitModel();
        $unitBedModel = new UnitBedModel();

        // Validación básica antes de tocar la BD
        $name   = trim($this->request->getPost('parent_name') ?? '');
        $typeId = $this->request->getPost('type_id');
        if (empty($name) || empty($typeId)) {
            return redirect()->back()->withInput()
                ->with('error', 'El nombre y el tipo son obligatorios.');
        }

        $unitModel->db->transStart();

        // 1. Insertar unidad padre
        $parentData = [
            'tenant_id'      => $tenantId,
            'type_id'        => $typeId,
            'name'           => $name,
            'description'    => $this->request->getPost('parent_description') ?? null,
            'base_occupancy' => (int)($this->request->getPost('parent_base_occupancy') ?? 2),
            'max_occupancy'  => (int)($this->request->getPost('max_occupancy') ?? 4),
            'bathrooms'      => (float)($this->request->getPost('bathrooms') ?? 1),
            'beds_info'      => $this->request->getPost('beds_info') ?? null,
            'status'         => 'available',
        ];

        $parentId = $unitModel->insert($parentData, true);

        $unitModel->db->transComplete();

        if ($unitModel->db->transStatus() === false || !$parentId) {
            log_message('error', "[InventoryWizard] Error al insertar unidad para tenant {$tenantId}");
            return redirect()->back()->withInput()
                ->with('error', 'Error al guardar la unidad. Intenta de nuevo.');
        }

        $mode = $this->request->getPost('unit_mode');

        // 2a. Modo simple → camas de la unidad padre
        if ($mode !== 'compound') {
            $simpleBeds = $this->request->getPost('simple_beds') ?? [];
            foreach ($simpleBeds as $bed) {
                if (!empty($bed['bed_type_id']) && !empty($bed['quantity'])) {
                    $unitBedModel->insert([
                        'unit_id'     => $parentId,
                        'bed_type_id' => $bed['bed_type_id'],
                        'quantity'    => (int)$bed['quantity'],
                    ]);
                }
            }
        }

        // 2b. Modo compuesto → sub-habitaciones con sus camas
        if ($mode === 'compound') {
            $rooms = $this->request->getPost('rooms') ?? [];
            foreach ($rooms as $room) {
                if (empty(trim($room['name'] ?? ''))) continue;

                $roomId = $unitModel->insert([
                    'tenant_id'  => $tenantId,
                    'parent_id'  => $parentId,
                    'type_id'    => $room['type_id'] ?? $typeId,
                    'name'       => trim($room['name']),
                    'bathrooms'  => (float)($room['bathrooms'] ?? 1),
                    'status'     => 'available',
                ], true);

                foreach ($room['beds'] ?? [] as $bed) {
                    if (!empty($bed['bed_type_id']) && !empty($bed['quantity'])) {
                        $unitBedModel->insert([
                            'unit_id'     => $roomId,
                            'bed_type_id' => $bed['bed_type_id'],
                            'quantity'    => (int)$bed['quantity'],
                        ]);
                    }
                }
            }
        }

        // Guardar en sesión para los pasos siguientes
        session()->set([
            'wizard_unit_id'   => $parentId,
            'wizard_unit_name' => $name,
        ]);

        log_message('info', "[InventoryWizard] Paso 1 OK — Unidad '{$name}' ID {$parentId} para tenant {$tenantId}");

        return redirect()->to('/inventory/wizard/step/2')
            ->with('success', '¡Unidad creada! Ahora configura sus amenidades.');
    }

    // ── Paso 2: Guardar amenidades ────────────────────────────────────────
    private function saveStep2()
    {
        $tenantId         = session('active_tenant_id');
        $unitId           = session('wizard_unit_id');
        $unitAmenityModel = new UnitAmenityModel();
        $unitModel        = new AccommodationUnitModel();

        if (!$unitId) {
            return redirect()->to('/inventory/wizard/step/1')
                ->with('error', 'Sesión expirada. Completa el paso 1 de nuevo.');
        }

        // Amenidades del catálogo canónico → JSON en la columna amenities
        $amenities     = $this->request->getPost('amenities') ?? [];
        $amenitiesJson = [];
        foreach ($amenities as $key) {
            $amenitiesJson[$key] = true;
        }

        // Verificar que 'amenities' esté en allowedFields del modelo antes de actualizar
        // Si no está, este update simplemente no guarda nada sin error visible
        $unitModel->update($unitId, ['amenities' => json_encode($amenitiesJson)]);

        // Amenidades personalizadas del tenant → tabla unit_amenities
        $unitAmenityModel->where('unit_id', $unitId)->delete();
        foreach ($this->request->getPost('custom_amenities') ?? [] as $amenityId) {
            $unitAmenityModel->insert(['unit_id' => $unitId, 'amenity_id' => $amenityId]);
        }

        session()->set('wizard_amenities', $amenities);
        log_message('info', "[InventoryWizard] Paso 2 OK — Amenidades para unidad {$unitId}");

        return redirect()->to('/inventory/wizard/step/3')
            ->with('success', 'Amenidades guardadas.');
    }

    // ── Paso 3: Subir fotos y finalizar ──────────────────────────────────
    private function saveStep3()
    {
        $tenantId   = session('active_tenant_id');
        $unitId     = session('wizard_unit_id');
        // FIX: leer el nombre ANTES de limpiar la sesión
        $unitName   = session('wizard_unit_name') ?? 'la unidad';
        $mediaModel = new TenantMediaModel();

        if (!$unitId) {
            return redirect()->to('/inventory')
                ->with('success', 'Unidad lista en el inventario.');
        }

        $files = $this->request->getFileMultiple('photos');

        if ($files && isset($files[0]) && $files[0]->isValid()) {
            $uploadPath = FCPATH . "uploads/tenants/{$tenantId}/units/";
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);

            foreach ($files as $file) {
                if (!$file->isValid() || $file->hasMoved()) continue;

                $newName = $file->getRandomName();
                $file->move($uploadPath, $newName);

                $mediaModel->insert([
                    'tenant_id'   => $tenantId,
                    'entity_type' => 'unit',
                    'entity_id'   => $unitId,
                    'file_path'   => "uploads/tenants/{$tenantId}/units/{$newName}",
                    'file_type'   => 'image',
                    'sort_order'  => 0,
                ]);
            }
            log_message('info', "[InventoryWizard] Paso 3 OK — Fotos para unidad {$unitId}");
        }

        // FIX: limpiar sesión DESPUÉS de leer los datos que necesitamos
        session()->remove(['wizard_unit_id', 'wizard_unit_name', 'wizard_amenities']);

        return redirect()->to('/inventory')
            ->with('success', "¡Todo listo! «{$unitName}» está configurada y disponible.");
    }


    // =========================================================================
    // EDICIÓN DE UNIDAD
    // =========================================================================
    public function editUnit($id)
    {
        $tenantId     = session('active_tenant_id');
        $unitModel    = new AccommodationUnitModel();
        $mediaModel   = new TenantMediaModel();
        $typeModel    = new AccommodationTypeModel();
        $amenityModel = new AmenityModel();
        $bedTypeModel = new BedTypeModel();

        $unit = $unitModel->getUnitWithHierarchy($id);

        if (!$unit || $unit['tenant_id'] != $tenantId) {
            return redirect()->to('/inventory')->with('error', 'Unidad no encontrada.');
        }

        return view('inventory/unit_edit', [
            'title'     => 'Editar: ' . $unit['name'],
            'unit'      => $unit,
            'types'     => $typeModel->findAll(),
            'amenities' => $amenityModel->where('tenant_id', $tenantId)->findAll(),
            'bedTypes'  => $bedTypeModel->whereIn('tenant_id', [0, $tenantId])->orderBy('tenant_id', 'ASC')->orderBy('name', 'ASC')->findAll(),
            'media'     => $mediaModel
                ->where('entity_type', 'unit')
                ->where('entity_id', $id)
                ->orderBy('sort_order', 'ASC')
                ->findAll(),
        ]);
    }

    public function updateUnit($id)
    {
        $tenantId         = session('active_tenant_id');
        $unitModel        = new AccommodationUnitModel();
        $unitBedModel     = new UnitBedModel();
        $unitAmenityModel = new UnitAmenityModel();

        $unit = $unitModel->find($id);
        if (!$unit || $unit['tenant_id'] != $tenantId) {
            return redirect()->to('/inventory')->with('error', 'Operación no permitida.');
        }

        $unitModel->db->transStart();

        try {
            $unitModel->update($id, [
                'name'           => $this->request->getPost('parent_name') ?? $this->request->getPost('name'),
                'type_id'        => $this->request->getPost('type_id'),
                'status'         => $this->request->getPost('status') ?? $unit['status'],
                'description'    => $this->request->getPost('parent_description') ?? $this->request->getPost('description'),
                'base_occupancy' => (int)($this->request->getPost('parent_base_occupancy') ?? 2),
                'max_occupancy'  => $this->request->getPost('max_occupancy') ?? null,
                'bathrooms'      => (float)($this->request->getPost('bathrooms') ?? 1),
                'beds_info'      => $this->request->getPost('beds_info') ?? null,
            ]);

            $unitAmenityModel->where('unit_id', $id)->delete();
            foreach ($this->request->getPost('parent_amenities') ?? [] as $amenityId) {
                $unitAmenityModel->insert(['unit_id' => $id, 'amenity_id' => $amenityId]);
            }

            $roomsPayload     = $this->request->getPost('rooms') ?? [];
            $submittedRoomIds = [];

            foreach ($roomsPayload as $room) {
                $roomId   = $room['id'] ?? null;
                $roomData = [
                    'tenant_id' => $tenantId,
                    'parent_id' => $id,
                    'type_id'   => $room['type_id'],
                    'name'      => $room['name'],
                    'bathrooms' => (float)($room['bathrooms'] ?? 1),
                ];

                if (!empty($roomId)) {
                    $unitModel->update($roomId, $roomData);
                    $submittedRoomIds[] = $roomId;
                } else {
                    $roomData['status'] = 'available';
                    $roomId = $unitModel->insert($roomData, true);
                    $submittedRoomIds[] = $roomId;
                }

                $unitBedModel->where('unit_id', $roomId)->delete();
                foreach ($room['beds'] ?? [] as $bed) {
                    if (!empty($bed['bed_type_id']) && !empty($bed['quantity'])) {
                        $unitBedModel->insert([
                            'unit_id'     => $roomId,
                            'bed_type_id' => $bed['bed_type_id'],
                            'quantity'    => (int)$bed['quantity'],
                        ]);
                    }
                }

                $unitAmenityModel->where('unit_id', $roomId)->delete();
                foreach ($room['amenities'] ?? [] as $amenityId) {
                    $unitAmenityModel->insert(['unit_id' => $roomId, 'amenity_id' => $amenityId]);
                }
            }

            foreach ($unitModel->where('parent_id', $id)->findAll() as $existing) {
                if (!in_array($existing['id'], $submittedRoomIds)) {
                    $unitModel->delete($existing['id']);
                }
            }

            $unitModel->db->transComplete();
            if ($unitModel->db->transStatus() === false) {
                throw new \Exception('Error en la transacción.');
            }

            return redirect()->to("/inventory/unit/edit/{$id}")
                ->with('success', 'Unidad actualizada correctamente.');

        } catch (\Exception $e) {
            log_message('error', "[InventoryController] updateUnit: " . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }


    // =========================================================================
    // MEDIA
    // =========================================================================
    public function uploadUnitMedia()
    {
        $mediaModel = new TenantMediaModel();
        $tenantId   = session('active_tenant_id');
        $unitId     = $this->request->getPost('unit_id');
        $file       = $this->request->getFile('media_file');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $mime     = $file->getMimeType();
            $fileType = strpos($mime, 'video') !== false ? 'video' : 'image';
            $newName  = $file->getRandomName();
            $path     = FCPATH . "uploads/tenants/{$tenantId}/units/";

            if (!is_dir($path)) mkdir($path, 0777, true);
            $file->move($path, $newName);

            $mediaModel->insert([
                'tenant_id'   => $tenantId,
                'entity_type' => 'unit',
                'entity_id'   => $unitId,
                'file_path'   => "uploads/tenants/{$tenantId}/units/{$newName}",
                'file_type'   => $fileType,
                'sort_order'  => 0,
            ]);

            return redirect()->to("/inventory/unit/edit/{$unitId}")->with('success', 'Archivo subido.');
        }

        return redirect()->to("/inventory/unit/edit/{$unitId}")->with('error', 'Error al subir el archivo.');
    }

    public function deleteUnitMedia($id)
    {
        $mediaModel = new TenantMediaModel();
        $media      = $mediaModel->find($id);

        if ($media) {
            $fullPath = FCPATH . $media['file_path'];
            if (file_exists($fullPath)) unlink($fullPath);
            $mediaModel->delete($id);
        }

        return redirect()->back()->with('success', 'Imagen eliminada.');
    }
}