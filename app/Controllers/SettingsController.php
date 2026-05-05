<?php

namespace App\Controllers;

use App\Models\TenantModel;
use App\Models\TenantMediaModel;

class SettingsController extends BaseController
{
    // ── Tags permitidos (espejo del ENUM en BD) ────────────────────
    private const ALLOWED_TAGS = ['rut', 'cuenta_bancaria', 'seguro', 'contrato', 'otro'];

    // ── Tipos MIME aceptados ───────────────────────────────────────
    private const ALLOWED_MIME = [
        'image/jpeg', 'image/png', 'image/webp', 'application/pdf',
    ];

    private const MAX_SIZE_MB = 5;

    // ══════════════════════════════════════════════════════════════
    //  VISTAS
    // ══════════════════════════════════════════════════════════════

    public function index()
    {
        $tenantModel = new TenantModel();
        $mediaModel  = new TenantMediaModel();
        $tenantId    = session('active_tenant_id');

        $tenant    = $tenantModel->find($tenantId);
        $documents = $mediaModel->getByTenant($tenantId);

        return view('settings/general', [
            'tenant'    => $tenant,
            'documents' => $documents,
            'tags'      => TenantMediaModel::$tags,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  FORMULARIO PRINCIPAL (datos del tenant)
    // ══════════════════════════════════════════════════════════════

    public function update()
    {
        $tenantModel = new TenantModel();
        $tenantId    = session('active_tenant_id');

        $dataToUpdate = [
            'name'            => $this->request->getPost('name'),
            'email'           => $this->request->getPost('email'),
            'phone'           => $this->request->getPost('phone'),
            'address'         => $this->request->getPost('address'),
            'city'            => $this->request->getPost('city'),
            'country'         => $this->request->getPost('country'),
            'currency_code'   => $this->request->getPost('currency_code'),
            'currency_symbol' => $this->request->getPost('currency_symbol'),
            'timezone'        => $this->request->getPost('timezone'),
            'checkin_time'    => $this->request->getPost('checkin_time'),
            'checkout_time'   => $this->request->getPost('checkout_time'),
        ];

        // Logo
        $logoFile = $this->request->getFile('logo');
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            if (str_starts_with($logoFile->getMimeType(), 'image')) {
                $newName = $logoFile->getRandomName();
                $logoFile->move(FCPATH . 'uploads/logos', $newName);
                $dataToUpdate['logo_path'] = 'uploads/logos/' . $newName;
                session()->set('tenant_logo', $dataToUpdate['logo_path']);
            }
        }

        $tenantModel->update($tenantId, $dataToUpdate);

        session()->set([
            'tenant_name'     => $dataToUpdate['name'],
            'currency_symbol' => $dataToUpdate['currency_symbol'],
            'timezone'        => $dataToUpdate['timezone'],
        ]);

        return redirect()->to('/settings')->with('success', 'Configuración actualizada correctamente.');
    }

    // ══════════════════════════════════════════════════════════════
    //  AJAX — Subir documento
    //  POST /settings/documents/upload
    // ══════════════════════════════════════════════════════════════

    public function uploadDocument()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido.', 403);
        }

        $file = $this->request->getFile('document');

        // ── Validaciones ───────────────────────────────────────────
        if (!$file || !$file->isValid()) {
            return $this->jsonError('No se recibió ningún archivo válido.');
        }

        if ($file->hasMoved()) {
            return $this->jsonError('El archivo ya fue procesado.');
        }

        $mime = $file->getMimeType();
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return $this->jsonError('Tipo de archivo no permitido. Solo JPG, PNG, WEBP y PDF.');
        }

        $sizeMB = $file->getSize() / 1024 / 1024;
        if ($sizeMB > self::MAX_SIZE_MB) {
            return $this->jsonError('El archivo supera el límite de ' . self::MAX_SIZE_MB . ' MB.');
        }

        $tag = $this->request->getPost('tag');
        if (!in_array($tag, self::ALLOWED_TAGS, true)) {
            return $this->jsonError('Tag no válido.');
        }

        $description = trim($this->request->getPost('description') ?? '');

        // ── Mover archivo ──────────────────────────────────────────
        $tenantId  = session('active_tenant_id');
        $folder    = FCPATH . 'uploads/tenant_docs/' . $tenantId;

        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $newName  = $file->getRandomName();
        $file->move($folder, $newName);

        $filePath = 'uploads/tenant_docs/' . $tenantId . '/' . $newName;
        $fileType = ($mime === 'application/pdf') ? 'pdf' : 'image';

        // ── Guardar en BD ──────────────────────────────────────────
        $mediaModel = new TenantMediaModel();
        $id = $mediaModel->insert([
            'tenant_id'   => $tenantId,
            'entity_type' => 'tenant',
            'entity_id'   => $tenantId,
            'file_path'   => $filePath,
            'file_type'   => $fileType,
            'tag'         => $tag,
            'description' => $description,
            'is_main'     => 0,
            'sort_order'  => 0,
        ]);

        $tagLabel = TenantMediaModel::$tags[$tag] ?? $tag;

        return $this->response->setJSON([
            'success'     => true,
            'id'          => $id,
            'file_path'   => base_url($filePath),
            'file_type'   => $fileType,
            'tag'         => $tag,
            'tag_label'   => $tagLabel,
            'description' => $description,
            'created_at'  => date('d/m/Y H:i'),
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  AJAX — Eliminar documento
    //  POST /settings/documents/delete/{id}
    // ══════════════════════════════════════════════════════════════

    public function deleteDocument(int $id)
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Acceso no permitido.', 403);
        }

        $mediaModel = new TenantMediaModel();
        $tenantId   = session('active_tenant_id');

        $doc = $mediaModel->find($id);

        if (!$doc || (int)$doc['tenant_id'] !== (int)$tenantId) {
            return $this->jsonError('Documento no encontrado.', 404);
        }

        // Eliminar archivo físico
        $physicalPath = FCPATH . $doc['file_path'];
        if (file_exists($physicalPath)) {
            unlink($physicalPath);
        }

        $mediaModel->delete($id);

        return $this->response->setJSON(['success' => true]);
    }

    // ── Helper privado ─────────────────────────────────────────────
    private function jsonError(string $message, int $status = 422)
    {
        return $this->response
            ->setStatusCode($status)
            ->setJSON(['success' => false, 'message' => $message]);
    }
}