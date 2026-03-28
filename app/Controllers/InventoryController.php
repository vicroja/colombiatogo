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
     * Muestra el formulario de edición de una unidad específica
     */
    public function editUnit($id)
    {
        $unitModel = new \App\Models\AccommodationUnitModel();
        $mediaModel = new \App\Models\TenantMediaModel();
        $typeModel = new \App\Models\AccommodationTypeModel();

        // 1. Buscamos directamente por ID.
        // Tu BaseMultiTenantModel automáticamente asegurará que pertenezca al 'active_tenant_id'
        $unit = $unitModel->find($id);

        if (!$unit) {
            $tenantId = session('active_tenant_id');
            log_message('warning', "Intento de acceso a unidad no autorizada o inexistente. Tenant: {$tenantId}, Unidad: {$id}");
            return redirect()->to('/inventory')->with('error', 'Unidad no encontrada.');
        }

        $data = [
            'title' => 'Editar Unidad: ' . $unit['name'],
            'unit' => $unit,
            // 2. findAll() también es filtrado automáticamente por tu modelo base
            'types' => $typeModel->findAll(),
            'media' => $mediaModel->where('entity_type', 'unit')
                ->where('entity_id', $id)
                ->orderBy('sort_order', 'ASC')
                ->findAll(),
            'features' => json_decode($unit['features_json'] ?? '{}', true) ?? []
        ];

        return view('inventory/unit_edit', $data);
    }

    /**
     * Procesa la actualización de datos, características y subida de archivos
     */
    public function updateUnit($id)
    {
        $unitModel = new \App\Models\AccommodationUnitModel();

        $unit = $unitModel->find($id);
        if (!$unit) {
            return redirect()->to('/inventory')->with('error', 'Operación no permitida.');
        }

        $features = [
            'bathrooms'    => $this->request->getPost('bathrooms') ?? 1,
            'beds'         => $this->request->getPost('beds') ?? 1,
            'has_ac'       => $this->request->getPost('has_ac') ? true : false,
            'has_wifi'     => $this->request->getPost('has_wifi') ? true : false,
            'has_tv'       => $this->request->getPost('has_tv') ? true : false,
            'has_kitchen'  => $this->request->getPost('has_kitchen') ? true : false,
            'is_private'   => $this->request->getPost('is_private') ? true : false,
            'pet_friendly' => $this->request->getPost('pet_friendly') ? true : false,
        ];

        $updateData = [
            'name'          => $this->request->getPost('name'),
            'type_id'       => $this->request->getPost('type_id'),
            'status'        => $this->request->getPost('status'),
            'description'   => $this->request->getPost('description'),
            'features_json' => json_encode($features)
        ];

        $unitModel->update($id, $updateData);
        $tenantId = session('active_tenant_id'); // Usamos la variable correcta para la carpeta
        log_message('info', "Unidad {$id} actualizada por Tenant {$tenantId}");

// INSERCIÓN: 1. Actualizar descripciones de la multimedia existente
        $existingDescriptions = $this->request->getPost('existing_media_descriptions');
        if ($existingDescriptions && is_array($existingDescriptions)) {
            $mediaModel = new \App\Models\TenantMediaModel();
            foreach ($existingDescriptions as $mediaId => $desc) {
                // Actualizamos la descripción de los archivos que ya están en la BD
                $mediaModel->update($mediaId, ['description' => $desc]);
            }
            log_message('info', "Descripciones de multimedia actualizadas para la unidad {$id}");
        }

        // INSERCIÓN: 2. Procesamiento de archivos multimedia nuevos con sus descripciones
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

                    // Emparejamos el archivo con su descripción por el índice del arreglo
                    $description = isset($newDescriptions[$index]) ? trim($newDescriptions[$index]) : null;

                    $mediaModel->createForTenant([
                        'entity_type' => 'unit',
                        'entity_id'   => $id,
                        'file_path'   => "uploads/tenants/{$tenantId}/units/{$newName}",
                        'file_type'   => $fileType,
                        'description' => $description, // Guardamos la descripción
                        'is_main'     => 0,
                        'sort_order'  => 0
                    ]);
                    log_message('info', "Nuevo archivo {$fileType} subido para unidad {$id} con descripción: {$description}");
                }
            }
        }

        return redirect()->to("/inventory/unit/edit/{$id}")->with('success', 'Unidad actualizada correctamente.');
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
        $tenantId = session('active_tenant_id');
        $db = \Config\Database::connect();

        // Usamos Query Builder para traer el nombre del tipo y ordenar la jerarquía.
        // COALESCE agrupa a los hijos con su padre.
        $builder = $db->table('accommodation_units au');
        $builder->select('au.*, t.name as type_name');
        $builder->join('accommodation_types t', 't.id = au.type_id', 'left');
        $builder->where('au.tenant_id', $tenantId);
        // Se pasa '' como dirección y 'false' para desactivar el auto-escape del Query Builder en funciones nativas de SQL
        $builder->orderBy('COALESCE(au.parent_id, au.id) ASC', '', false);
        $builder->orderBy('au.parent_id', 'ASC');

        $units = $builder->get()->getResultArray();

        $limitService = new PlanLimitService();
        $limitInfo = $limitService->getLimitInfo($tenantId, 'units');

        log_message('info', "[InventoryController] Index cargado. Unidades encontradas: " . count($units));

        return view('inventory/index', [
            'units'     => $units,
            'limitInfo' => $limitInfo
        ]);
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