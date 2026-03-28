<?php

namespace App\Controllers;

use App\Models\AccommodationTypeModel;
use App\Models\AccommodationUnitModel;
use App\Services\PlanLimitService;
use App\Models\TenantMediaModel; // Asegúrate de tener e importar este modelo
// Nuevos modelos para la jerarquía y características
use App\Models\AmenityModel;
use App\Models\BedTypeModel;
use App\Models\UnitAmenityModel;
use App\Models\UnitBedModel;

class InventoryController extends BaseController
{


    /**
     * Muestra el formulario de edición cargando toda la jerarquía de la unidad
     */
    public function editUnit($id)
    {
        $tenantId = session('active_tenant_id');

        $unitModel = new \App\Models\AccommodationUnitModel();
        $mediaModel = new \App\Models\TenantMediaModel();
        $typeModel = new \App\Models\AccommodationTypeModel();
        $amenityModel = new \App\Models\AmenityModel();
        $bedTypeModel = new \App\Models\BedTypeModel();

        // 1. Buscamos usando la jerarquía completa (Padre e Hijos)
        $unit = $unitModel->getUnitWithHierarchy($id);

        if (!$unit || $unit['tenant_id'] != $tenantId) {
            log_message('warning', "Intento de acceso a unidad no autorizada o inexistente. Tenant: {$tenantId}, Unidad: {$id}");
            return redirect()->to('/inventory')->with('error', 'Unidad no encontrada.');
        }

        $data = [
            'title'     => 'Editar Unidad: ' . $unit['name'],
            'unit'      => $unit,
            'types'     => $typeModel->findAll(),
            'amenities' => $amenityModel->where('tenant_id', $tenantId)->findAll(),
            'bedTypes'  => $bedTypeModel->where('tenant_id', $tenantId)->findAll(),
            'media'     => $mediaModel->where('entity_type', 'unit')
                ->where('entity_id', $id)
                ->orderBy('sort_order', 'ASC')
                ->findAll(),
        ];

        // Se confirma que la vista a usar es unit_edit.php
        return view('inventory/unit_edit', $data);
    }

    /**
     * Procesa la actualización de datos, jerarquía (habitaciones) y multimedia
     */
    public function updateUnit($id)
    {
        $tenantId = session('active_tenant_id');

        $unitModel = new \App\Models\AccommodationUnitModel();
        $unitBedModel = new \App\Models\UnitBedModel();
        $unitAmenityModel = new \App\Models\UnitAmenityModel();

        $unit = $unitModel->find($id);
        if (!$unit || $unit['tenant_id'] != $tenantId) {
            return redirect()->to('/inventory')->with('error', 'Operación no permitida.');
        }

        // Iniciamos la transacción global para Base de Datos
        $unitModel->db->transStart();

        try {
            log_message('info', "[InventoryController] Sincronizando edición jerárquica para Unidad ID: {$id}");

            // 1. Actualizar datos del Padre
            // Soportamos los names viejos y nuevos por compatibilidad con el front
            $updateData = [
                'name'          => $this->request->getPost('parent_name') ?? $this->request->getPost('name'),
                'type_id'       => $this->request->getPost('type_id'),
                'status'        => $this->request->getPost('status') ?? $unit['status'],
                'description'   => $this->request->getPost('parent_description') ?? $this->request->getPost('description'),
            ];
            $unitModel->update($id, $updateData);

            // 2. Sincronizar Amenidades del Padre (Borrar todas y recrear es más eficiente)
            $unitAmenityModel->where('unit_id', $id)->delete();
            $parentAmenities = $this->request->getPost('parent_amenities');
            if (!empty($parentAmenities) && is_array($parentAmenities)) {
                foreach ($parentAmenities as $amenityId) {
                    $unitAmenityModel->insert(['unit_id' => $id, 'amenity_id' => $amenityId]);
                }
            }

            // 3. Procesar Habitaciones Hijas
            $roomsPayload = $this->request->getPost('rooms') ?? [];
            $submittedRoomIds = []; // Rastrea habitaciones que se mantienen/crean

            foreach ($roomsPayload as $room) {
                $roomId = $room['id'] ?? null;

                $roomData = [
                    'tenant_id' => $tenantId,
                    'parent_id' => $id,
                    'type_id'   => $room['type_id'],
                    'name'      => $room['name'],
                    'bathrooms' => $room['bathrooms'] ?? 1,
                ];

                if (!empty($roomId)) {
                    // Si trae ID, la actualizamos
                    $unitModel->update($roomId, $roomData);
                    $submittedRoomIds[] = $roomId;
                } else {
                    // Si no trae ID, fue agregada por JS. La insertamos.
                    $roomData['status'] = 'available';
                    $roomId = $unitModel->insert($roomData, true);
                    $submittedRoomIds[] = $roomId;
                }

                // Sincronizar Camas de esta habitación
                $unitBedModel->where('unit_id', $roomId)->delete();
                if (!empty($room['beds']) && is_array($room['beds'])) {
                    foreach ($room['beds'] as $bed) {
                        if (!empty($bed['bed_type_id']) && !empty($bed['quantity'])) {
                            $unitBedModel->insert([
                                'unit_id'     => $roomId,
                                'bed_type_id' => $bed['bed_type_id'],
                                'quantity'    => $bed['quantity']
                            ]);
                        }
                    }
                }

                // Sincronizar Amenidades de esta habitación
                $unitAmenityModel->where('unit_id', $roomId)->delete();
                if (!empty($room['amenities']) && is_array($room['amenities'])) {
                    foreach ($room['amenities'] as $amenityId) {
                        $unitAmenityModel->insert(['unit_id' => $roomId, 'amenity_id' => $amenityId]);
                    }
                }
            }

            // 4. Limpieza: Eliminar habitaciones que fueron quitadas en la vista
            $existingRooms = $unitModel->where('parent_id', $id)->findAll();
            foreach ($existingRooms as $existingRoom) {
                if (!in_array($existingRoom['id'], $submittedRoomIds)) {
                    $unitModel->delete($existingRoom['id']);
                }
            }

            // 5. PROCESAMIENTO DE MULTIMEDIA (Se mantiene tu lógica original intacta)
            $existingDescriptions = $this->request->getPost('existing_media_descriptions');
            if ($existingDescriptions && is_array($existingDescriptions)) {
                $mediaModel = new \App\Models\TenantMediaModel();
                foreach ($existingDescriptions as $mediaId => $desc) {
                    $mediaModel->update($mediaId, ['description' => $desc]);
                }
            }

            $files = $this->request->getFileMultiple('media');
            $newDescriptions = $this->request->getPost('new_media_descriptions') ?? [];

            if ($files && isset($files[0]) && $files[0]->isValid()) {
                $mediaModel = new \App\Models\TenantMediaModel();
                $uploadPath = FCPATH . "uploads/tenants/{$tenantId}/units/";

                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                foreach ($files as $index => $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $newName = $file->getRandomName();
                        $file->move($uploadPath, $newName);

                        $mime = $file->getClientMimeType();
                        $fileType = strpos($mime, 'video') !== false ? 'video' : 'image';
                        $description = isset($newDescriptions[$index]) ? trim($newDescriptions[$index]) : null;

                        // Se utiliza insert directo inyectando el tenant_id
                        $mediaModel->insert([
                            'tenant_id'   => $tenantId,
                            'entity_type' => 'unit',
                            'entity_id'   => $id,
                            'file_path'   => "uploads/tenants/{$tenantId}/units/{$newName}",
                            'file_type'   => $fileType,
                            'description' => $description,
                            'is_main'     => 0,
                            'sort_order'  => 0
                        ]);
                    }
                }
            }

            $unitModel->db->transComplete();

            if ($unitModel->db->transStatus() === false) {
                throw new \Exception('Fallo en la transacción SQL general.');
            }

            return redirect()->to("/inventory/unit/edit/{$id}")->with('success', 'Unidad actualizada correctamente.');

        } catch (\Exception $e) {
            log_message('critical', "[InventoryController] Error en updateUnit(): " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un archivo multimedia específico de la unidad
     */
    public function deleteUnitMedia($mediaId)
    {
        $mediaModel = new \App\Models\TenantMediaModel();

        // Protegido por BaseMultiTenantModel
        $media = $mediaModel->find($mediaId);

        if ($media) {
            $filePath = FCPATH . $media['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $mediaModel->delete($mediaId);
        }

        return redirect()->back()->with('success', 'Archivo eliminado.');
    }




    /**
     * Lista el inventario ordenando jerárquicamente (Padres primero, luego sus hijos)
     */
    public function index()
    {
        $unitModel = new AccommodationUnitModel();
        $limitService = new PlanLimitService();

        $tenantId = session('active_tenant_id');

        // 1. Join manual con FILTRO MULTI-TENANT OBLIGATORIO y ordenamiento Padre/Hijo
        $units = $unitModel->select('accommodation_units.*, accommodation_types.name as type_name')
            ->join('accommodation_types', 'accommodation_types.id = accommodation_units.type_id', 'left')
            ->where('accommodation_units.tenant_id', $tenantId)
            // false evita que CodeIgniter rompa la sintaxis de la función COALESCE
            ->orderBy('COALESCE(accommodation_units.parent_id, accommodation_units.id) ASC', '', false)
            // Asegura que el Padre salga primero, y luego sus hijos
            ->orderBy('accommodation_units.parent_id', 'ASC')
            // Orden secundario alfabético como lo tenías originalmente
            ->orderBy('accommodation_units.name', 'ASC')
            ->findAll();

        log_message('info', "[InventoryController] Index cargado. Unidades encontradas: " . count($units) . " para el tenant: " . $tenantId);

        // 2. Traer información de los límites usando tu método original
        $data = [
            'units'     => $units,
            'limitInfo' => $limitService->getUnitUsageInfo()
        ];

        return view('inventory/index', $data);
    }

    /**
     * Carga la vista para crear una nueva cabaña/unidad con sus catálogos
     */
    public function create()
    {
        $tenantId = session('active_tenant_id');

        $amenityModel = new AmenityModel();
        $bedTypeModel = new BedTypeModel();
        $typeModel = new AccommodationTypeModel();

        $data = [
            'title'     => 'Crear Nueva Unidad/Cabaña',
            'types'     => $typeModel->findAll(),
            'amenities' => $amenityModel->where('tenant_id', $tenantId)->findAll(),
            'bedTypes'  => $bedTypeModel->where('tenant_id', $tenantId)->findAll(),
        ];

        return view('inventory/create', $data);
    }

    /**
     * Procesa la creación de la cabaña, sus habitaciones, camas y amenidades
     * usando una transacción estricta para evitar inconsistencias.
     */
    public function store()
    {
        $tenantId = session('active_tenant_id');

        $unitModel = new AccommodationUnitModel();
        $unitBedModel = new UnitBedModel();
        $unitAmenityModel = new UnitAmenityModel();

        // INICIO DE TRANSACCIÓN: Todo se guarda o nada se guarda
        $unitModel->db->transStart();

        try {
            log_message('info', "[InventoryController] Iniciando guardado de unidad jerárquica para Tenant ID: {$tenantId}");

            // 1. Crear la Unidad Padre (Ej. La Cabaña Completa)
            $parentData = [
                'tenant_id'     => $tenantId,
                'type_id'       => $this->request->getPost('type_id'),
                'name'          => $this->request->getPost('parent_name'),
                'description'   => $this->request->getPost('parent_description'),
                'status'        => 'available'
            ];

            // Insertamos y capturamos el ID
            $parentId = $unitModel->insert($parentData, true);

            if (!$parentId) {
                throw new \Exception('Fallo al insertar la entidad Padre en la base de datos.');
            }

            // 2. Asociar amenidades globales al Padre (Ej. Piscina)
            $parentAmenities = $this->request->getPost('parent_amenities');
            if (!empty($parentAmenities) && is_array($parentAmenities)) {
                foreach ($parentAmenities as $amenityId) {
                    $unitAmenityModel->insert([
                        'unit_id'    => $parentId,
                        'amenity_id' => $amenityId
                    ]);
                }
            }

            // 3. Iterar y guardar las Habitaciones Hijas
            $rooms = $this->request->getPost('rooms');

            if (!empty($rooms) && is_array($rooms)) {
                foreach ($rooms as $room) {
                    $roomData = [
                        'tenant_id'   => $tenantId,
                        'parent_id'   => $parentId, // Clave: Relación con la cabaña
                        'type_id'     => $room['type_id'],
                        'name'        => $room['name'],
                        'bathrooms'   => $room['bathrooms'] ?? 1,
                        'status'      => 'available'
                    ];

                    $roomId = $unitModel->insert($roomData, true);

                    if (!$roomId) {
                        throw new \Exception("Fallo al insertar la habitación hija: {$room['name']}");
                    }

                    // 4. Asignar tipos de cama a esta habitación
                    if (!empty($room['beds']) && is_array($room['beds'])) {
                        foreach ($room['beds'] as $bed) {
                            if (!empty($bed['bed_type_id']) && !empty($bed['quantity'])) {
                                $unitBedModel->insert([
                                    'unit_id'     => $roomId,
                                    'bed_type_id' => $bed['bed_type_id'],
                                    'quantity'    => $bed['quantity']
                                ]);
                            }
                        }
                    }

                    // 5. Asignar amenidades específicas a esta habitación (Ej. Aire Acondicionado)
                    if (!empty($room['amenities']) && is_array($room['amenities'])) {
                        foreach ($room['amenities'] as $amenityId) {
                            $unitAmenityModel->insert([
                                'unit_id'    => $roomId,
                                'amenity_id' => $amenityId
                            ]);
                        }
                    }
                }
            }

            // CIERRE DE TRANSACCIÓN
            $unitModel->db->transComplete();

            if ($unitModel->db->transStatus() === false) {
                throw new \Exception('La transacción SQL devolvió un estado fallido tras intentar confirmar.');
            }

            log_message('info', "[InventoryController] Cabaña ID {$parentId} y sub-unidades creadas con éxito.");
            return redirect()->to('/inventory')->with('success', 'Alojamiento creado exitosamente con toda su distribución.');

        } catch (\Exception $e) {
            // Rollback automático por transStart() de CodeIgniter
            log_message('critical', "[InventoryController] Error en store(): " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error al guardar la configuración: ' . $e->getMessage());
        }
    }




    public function uploadUnitMedia()
    {
        $mediaModel = new \App\Models\TenantMediaModel();
        $unitId = $this->request->getPost('unit_id');
        $file = $this->request->getFile('media_file');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $mimeType = $file->getMimeType();
            $fileType = strpos($mimeType, 'video') !== false ? 'video' : 'image';
            $newName = $file->getRandomName();

            // Guardar en subcarpeta de unidades
            if (!is_dir(FCPATH . 'uploads/units')) mkdir(FCPATH . 'uploads/units', 0777, true);
            $file->move(FCPATH . 'uploads/units', $newName);

            $mediaModel->insert([
                'tenant_id'   => session('active_tenant_id'),
                'entity_type' => 'unit',
                'entity_id'   => $unitId,
                'file_path'   => 'uploads/units/' . $newName,
                'file_type'   => $fileType
            ]);
            return redirect()->to("/inventory/edit-unit/{$unitId}")->with('success', 'Archivo subido con éxito.');
        }
        return redirect()->to("/inventory/edit-unit/{$unitId}")->with('error', 'Error al subir el archivo.');
    }

}