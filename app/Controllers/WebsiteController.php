<?php

namespace App\Controllers;

use App\Models\TenantWebsiteModel;
use App\Models\TenantMediaModel;

class WebsiteController extends BaseController
{

    public function index()
    {
        $websiteModel = new TenantWebsiteModel();
        $mediaModel = new TenantMediaModel();
        $tenantModel = new \App\Models\TenantModel(); // NUEVO: Modelo del Hotel

        $tenantId = session('active_tenant_id');
        $tenant = $tenantModel->find($tenantId); // NUEVO: Traemos los datos de Casa Lucerito

        // Buscar configuración de la web. Si no existe, creamos una vacía por defecto
        $website = $websiteModel->where('tenant_id', $tenantId)->first();
        if (!$website) {
            $websiteModel->insert([
                'tenant_id' => $tenantId,
                'theme_slug' => 'resort',
                'primary_color' => '#2E75B6'
            ]);
            $website = $websiteModel->where('tenant_id', $tenantId)->first();
        }

        // Traer las fotos
        $media = $mediaModel->where('tenant_id', $tenantId)
            ->where('entity_type', 'tenant')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('website/settings', [
            'website' => $website,
            'media'   => $media,
            'tenant'  => $tenant // NUEVO: Pasamos el hotel a la vista
        ]);
    }

    public function update()
    {
        $websiteModel = new TenantWebsiteModel();
        $id = $this->request->getPost('id');

        $data = [
            'theme_slug'      => $this->request->getPost('theme_slug'),
            'primary_color'   => $this->request->getPost('primary_color'),
            'hero_title'      => $this->request->getPost('hero_title'),
            'hero_subtitle'   => $this->request->getPost('hero_subtitle'),
            'about_text'      => $this->request->getPost('about_text'),
            'policies_text'   => $this->request->getPost('policies_text'),
            'instagram_url'   => $this->request->getPost('instagram_url'),
            'whatsapp_number' => $this->request->getPost('whatsapp_number'),
            'is_published'    => $this->request->getPost('is_published') ? 1 : 0,
        ];

        $websiteModel->update($id, $data);
        return redirect()->to('/website')->with('success', 'Configuración del sitio web actualizada.');
    }

    public function uploadMedia()
    {
        $mediaModel = new TenantMediaModel();
        $file = $this->request->getFile('media_file');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Identificar si es imagen o video
            $mimeType = $file->getMimeType();
            $fileType = strpos($mimeType, 'video') !== false ? 'video' : 'image';

            $newName = $file->getRandomName();
            // Lo guardamos en una subcarpeta pública
            $file->move(FCPATH . 'uploads/website', $newName);

            $mediaModel->insert([
                'tenant_id'   => session('active_tenant_id'),
                'entity_type' => 'tenant',
                'file_path'   => 'uploads/website/' . $newName,
                'file_type'   => $fileType
            ]);

            return redirect()->to('/website')->with('success', 'Archivo multimedia subido correctamente.');
        }

        return redirect()->to('/website')->with('error', 'Error al subir el archivo.');
    }

    public function deleteMedia($id)
    {
        $mediaModel = new TenantMediaModel();
        $media = $mediaModel->find($id);

        if ($media && $media['tenant_id'] == session('active_tenant_id')) {
            // Borrar el archivo físico del servidor
            if (file_exists(FCPATH . $media['file_path'])) {
                unlink(FCPATH . $media['file_path']);
            }
            // Borrar de la base de datos
            $mediaModel->delete($id);
            return redirect()->to('/website')->with('success', 'Archivo eliminado.');
        }

        return redirect()->to('/website');
    }
}