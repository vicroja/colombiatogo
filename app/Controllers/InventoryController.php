<?php

namespace App\Controllers;

use App\Models\AccommodationTypeModel;
use App\Models\AccommodationUnitModel;
use App\Services\PlanLimitService;
use App\Models\TenantMediaModel; // Asegúrate de tener e importar este modelo

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
    public function index()
    {
        $unitModel = new AccommodationUnitModel();
        $limitService = new PlanLimitService();

        // Join manual para traer el nombre del tipo de habitación en la misma consulta
        $units = $unitModel->select('accommodation_units.*, accommodation_types.name as type_name')
            ->join('accommodation_types', 'accommodation_types.id = accommodation_units.type_id')
            ->findAll();

        $data = [
            'units'     => $units,
            'limitInfo' => $limitService->getUnitUsageInfo()
        ];

        return view('inventory/index', $data);
    }

    public function create()
    {
        $limitService = new PlanLimitService();

        // Bloqueo de UI si ya no puede agregar más
        if (!$limitService->canAddUnit()) {
            return redirect()->to('/inventory')->with('error', 'Has alcanzado el límite de unidades de tu plan. Contacta a soporte para mejorar tu suscripción.');
        }

        $typeModel = new AccommodationTypeModel();
        // Si no hay tipos, creamos uno genérico automáticamente para facilitar la prueba
        if ($typeModel->countAllResults() == 0) {
            $typeModel->createForTenant([
                'name' => 'Habitación Estándar',
                'base_capacity' => 2,
                'max_capacity' => 2
            ]);
        }

        $data = [
            'types' => $typeModel->findAll()
        ];

        return view('inventory/create', $data);
    }

    public function store()
    {
        $limitService = new PlanLimitService();

        // El guardián de backend: Validación estricta por si intentan saltarse la UI
        if (!$limitService->canAddUnit()) {
            return redirect()->to('/inventory')->with('error', 'Límite de unidades excedido según su plan actual.');
        }

        $unitModel = new AccommodationUnitModel();

        $unitModel->createForTenant([
            'type_id' => $this->request->getPost('type_id'),
            'name'    => $this->request->getPost('name'),
            'status'  => 'available'
        ]);

        return redirect()->to('/inventory')->with('success', 'Habitación añadida al inventario con éxito.');
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