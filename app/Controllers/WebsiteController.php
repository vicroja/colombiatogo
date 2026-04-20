<?php

namespace App\Controllers;

use App\Models\TenantWebsiteModel;
use App\Models\TenantMediaModel;
use App\Models\TenantModel;
use App\Models\AccommodationUnitModel;
use App\Models\UnitRateModel;
use App\Models\RatePlanModel;
use App\Models\GeminiModel;

/**
 * WebsiteController
 *
 * Gestiona el builder del sitio web público del hotel.
 * Incluye endpoints AJAX para preview en tiempo real y generación con IA.
 */
class WebsiteController extends BaseController
{
    private TenantWebsiteModel  $websiteModel;
    private TenantMediaModel    $mediaModel;
    private TenantModel         $tenantModel;
    private AccommodationUnitModel $unitModel;
    private UnitRateModel       $unitRateModel;
    private RatePlanModel       $ratePlanModel;
    private GeminiModel         $geminiModel;
    private int                 $tenantId;
    private array               $tenant;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface            $logger
    ): void {
        parent::initController($request, $response, $logger);

        $this->tenantId     = session('active_tenant_id');
        $this->tenantModel  = new TenantModel();
        $this->websiteModel = new TenantWebsiteModel();
        $this->mediaModel   = new TenantMediaModel();
        $this->unitModel    = new AccommodationUnitModel();
        $this->unitRateModel= new UnitRateModel();
        $this->ratePlanModel= new RatePlanModel();
        $this->geminiModel  = new GeminiModel();
        $this->tenant       = $this->tenantModel->find($this->tenantId) ?? [];
    }

    // =========================================================================
    // INDEX — Builder principal
    // =========================================================================
    public function index(): string
    {
        // Crear registro web si no existe
        $website = $this->websiteModel
            ->where('tenant_id', $this->tenantId)
            ->first();

        if (!$website) {
            $this->websiteModel->insert([
                'tenant_id'     => $this->tenantId,
                'theme_slug'    => 'resort',
                'primary_color' => '#2E75B6',
            ]);
            $website = $this->websiteModel
                ->where('tenant_id', $this->tenantId)
                ->first();
        }

        // Fotos del hotel
        $media = $this->mediaModel
            ->where('tenant_id', $this->tenantId)
            ->where('entity_type', 'tenant')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('is_main', 'DESC')
            ->findAll();

        // Unidades con su tarifa base (solo unidades raíz — sin parent)
        $units = $this->unitModel
            ->where('parent_id IS NULL')
            ->where('status !=', 'maintenance')
            ->findAll();

        // Enriquecer cada unidad con su tarifa base y foto
        $defaultPlan = $this->ratePlanModel
            ->where('is_default', 1)
            ->first();

        foreach ($units as &$unit) {
            // Tarifa base
            $rate = null;
            if ($defaultPlan) {
                $rate = $this->unitRateModel
                    ->where('unit_id', $unit['id'])
                    ->where('rate_plan_id', $defaultPlan['id'])
                    ->first();
            }
            if (!$rate) {
                $rate = $this->unitRateModel
                    ->where('unit_id', $unit['id'])
                    ->where('is_active', 1)
                    ->first();
            }
            $unit['price_per_night'] = $rate['price_per_night'] ?? null;

            // Foto principal de la unidad
            $photo = $this->mediaModel
                ->where('entity_type', 'unit')
                ->where('entity_id', $unit['id'])
                ->where('is_main', 1)
                ->first();
            if (!$photo) {
                $photo = $this->mediaModel
                    ->where('entity_type', 'unit')
                    ->where('entity_id', $unit['id'])
                    ->first();
            }
            $unit['main_photo'] = $photo['file_path'] ?? null;
        }
        unset($unit);

        // Plantillas disponibles
        $themes = [
            'resort'    => [
                'name'     => 'Resort & Naturaleza',
                'desc'     => 'Ideal para cabañas, glamping y ecohoteles',
                'preview'  => 'assets/themes/resort-preview.jpg',
                'tags'     => ['naturaleza', 'cabañas', 'rural'],
            ],
            'boutique'  => [
                'name'     => 'Boutique Urbano',
                'desc'     => 'Minimalista y elegante para hoteles de ciudad',
                'preview'  => 'assets/themes/boutique-preview.jpg',
                'tags'     => ['ciudad', 'moderno', 'minimalista'],
            ],
            'corporate' => [
                'name'     => 'Corporativo',
                'desc'     => 'Profesional para hoteles de negocios',
                'preview'  => 'assets/themes/corporate-preview.jpg',
                'tags'     => ['negocios', 'formal', 'ejecutivo'],
            ],
        ];

        return view('website/settings', [
            'tenant'  => $this->tenant,
            'website' => $website,
            'media'   => $media,
            'units'   => $units,
            'themes'  => $themes,
        ]);
    }

    // =========================================================================
    // UPDATE — Guardar configuración
    // =========================================================================
    public function update(): \CodeIgniter\HTTP\RedirectResponse
    {
        $id = $this->request->getPost('id');

        $data = [
            'theme_slug'      => $this->request->getPost('theme_slug'),
            'primary_color'   => $this->request->getPost('primary_color'),
            'hero_title'      => $this->request->getPost('hero_title'),
            'hero_subtitle'   => $this->request->getPost('hero_subtitle'),
            'about_text'      => $this->request->getPost('about_text'),
            'policies_text'   => $this->request->getPost('policies_text'),
            'instagram_url'   => $this->request->getPost('instagram_url'),
            'facebook_url'    => $this->request->getPost('facebook_url'),
            'whatsapp_number' => $this->request->getPost('whatsapp_number'),
            'is_published'    => $this->request->getPost('is_published') ? 1 : 0,
        ];

        $this->websiteModel->update($id, $data);

        log_message('info', "[Website] Tenant {$this->tenantId} actualizó su sitio web.");

        return redirect()->to('/website')
            ->with('success', '✅ Sitio web actualizado correctamente.');
    }

    // =========================================================================
    // UPLOAD MEDIA — Subir foto
    // =========================================================================
    public function uploadMedia(): \CodeIgniter\HTTP\RedirectResponse
    {
        $file = $this->request->getFile('media_file');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $mimeType = $file->getMimeType();
            $fileType = strpos($mimeType, 'video') !== false ? 'video' : 'image';
            $newName  = $file->getRandomName();

            $file->move(FCPATH . 'uploads/website/' . $this->tenantId, $newName);

            $path = 'uploads/website/' . $this->tenantId . '/' . $newName;

            // Verificar si es la primera foto → marcarla como principal
            $existing = $this->mediaModel
                ->where('tenant_id', $this->tenantId)
                ->where('entity_type', 'tenant')
                ->countAllResults();

            $this->mediaModel->insert([
                'tenant_id'   => $this->tenantId,
                'entity_type' => 'tenant',
                'entity_id'   => $this->tenantId,
                'file_path'   => $path,
                'file_type'   => $fileType,
                'is_main'     => ($existing === 0) ? 1 : 0,
                'sort_order'  => $existing,
            ]);

            log_message('info', "[Website] Media subida para tenant {$this->tenantId}: {$path}");
            return redirect()->to('/website')
                ->with('success', 'Foto subida correctamente.');
        }

        return redirect()->to('/website')
            ->with('error', 'Error al subir el archivo.');
    }

    // =========================================================================
    // DELETE MEDIA
    // =========================================================================
    public function deleteMedia(int $id): \CodeIgniter\HTTP\ResponseInterface|\CodeIgniter\HTTP\RedirectResponse
    {
        $media  = $this->mediaModel->find($id);
        $isAjax = $this->request->isAJAX();

        if ($media && $media['tenant_id'] == $this->tenantId) {
            if (file_exists(FCPATH . $media['file_path'])) {
                unlink(FCPATH . $media['file_path']);
            }
            $this->mediaModel->delete($id);

            log_message('info', "[Website] Media #{$id} eliminada por tenant {$this->tenantId}");

            if ($isAjax) {
                return $this->response->setJSON(['success' => true]);
            }
            return redirect()->to('/website')->with('success', 'Foto eliminada.');
        }

        if ($isAjax) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autorizado']);
        }
        return redirect()->to('/website');
    }

    // =========================================================================
    // SET MAIN PHOTO — Marcar foto como portada (AJAX)
    // =========================================================================
    public function setMainPhoto(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $media = $this->mediaModel->find($id);

        if (!$media || $media['tenant_id'] != $this->tenantId) {
            return $this->response->setJSON(['success' => false]);
        }

        // Quitar is_main de todas las fotos del tenant
        $this->mediaModel
            ->where('tenant_id', $this->tenantId)
            ->where('entity_type', 'tenant')
            ->set(['is_main' => 0])
            ->update();

        // Marcar la seleccionada
        $this->mediaModel->update($id, ['is_main' => 1]);

        log_message('info', "[Website] Foto #{$id} marcada como portada para tenant {$this->tenantId}");

        return $this->response->setJSON(['success' => true]);
    }

    // =========================================================================
    // REORDER PHOTOS — Reordenar galería (AJAX)
    // =========================================================================
    public function reorderPhotos(): \CodeIgniter\HTTP\ResponseInterface
    {
        $order = $this->request->getJSON(true)['order'] ?? [];

        foreach ($order as $idx => $mediaId) {
            $media = $this->mediaModel->find($mediaId);
            if ($media && $media['tenant_id'] == $this->tenantId) {
                $this->mediaModel->update($mediaId, ['sort_order' => $idx]);
            }
        }

        return $this->response->setJSON(['success' => true]);
    }

    // =========================================================================
    // AI GENERATE — Endpoints de IA para el builder (AJAX)
    // =========================================================================
    public function aiGenerate(): \CodeIgniter\HTTP\ResponseInterface
    {
        $input  = $this->request->getJSON(true);
        $action = $input['action'] ?? '';

        log_message('info', "[Website/AI] Acción: {$action} — tenant {$this->tenantId}");

        $result = match($action) {
            'hero'        => $this->aiGenerateHero($input),
            'about'       => $this->aiGenerateAbout($input),
            'policies'    => $this->aiGeneratePolicies($input),
            'full_content'=> $this->aiGenerateFullContent($input),
            default       => ['success' => false, 'message' => 'Acción no reconocida']
        };

        return $this->response->setJSON($result);
    }

    // ── Generar hero title + subtitle ─────────────────────────────────────────
    private function aiGenerateHero(array $input): array
    {
        $about = trim($input['about'] ?? '');
        $name  = $this->tenant['name'] ?? '';
        $city  = $this->tenant['city'] ?? '';

        $prompt = "Hotel/alojamiento: '{$name}' en {$city}. " .
            ($about ? "Descripción: '{$about}'. " : '') .
            "Genera un JSON con exactamente estas claves: " .
            "'hero_title' (máximo 8 palabras, evocador, sin comillas) y " .
            "'hero_subtitle' (máximo 15 palabras, invita a reservar, sin comillas). " .
            "Solo el JSON puro, sin markdown.";

        $result = $this->geminiModel->generateText($prompt, 200, 0.8);

        if ($result['success']) {
            $clean   = preg_replace('/```json|```/', '', $result['text']);
            $decoded = json_decode(trim($clean), true);
            if ($decoded && isset($decoded['hero_title'])) {
                return ['success' => true, 'data' => $decoded];
            }
        }

        return ['success' => false, 'message' => 'No se pudo generar el hero.'];
    }

    // ── Generar texto "Acerca de" ─────────────────────────────────────────────
    private function aiGenerateAbout(array $input): array
    {
        $hints = trim($input['hints'] ?? '');
        $name  = $this->tenant['name'] ?? '';
        $city  = $this->tenant['city'] ?? '';

        // Contexto adicional: unidades configuradas
        $units = $this->unitModel->where('parent_id IS NULL')->findAll();
        $unitNames = implode(', ', array_column($units, 'name'));

        $prompt = "Eres un experto en marketing hotelero. " .
            "El alojamiento se llama '{$name}', ubicado en {$city}. " .
            ($unitNames ? "Sus unidades son: {$unitNames}. " : '') .
            ($hints ? "El propietario describió su lugar así: '{$hints}'. " : '') .
            "Escribe un texto 'Acerca de nosotros' cálido, auténtico y atractivo " .
            "de máximo 4 oraciones. Solo el texto, sin títulos.";

        $result = $this->geminiModel->generateText($prompt, 400, 0.75);

        if ($result['success']) {
            return ['success' => true, 'text' => trim($result['text'])];
        }

        return ['success' => false, 'message' => 'No se pudo generar el texto.'];
    }

    // ── Generar políticas de estadía ──────────────────────────────────────────
    private function aiGeneratePolicies(array $input): array
    {
        $name     = $this->tenant['name'] ?? '';
        $checkin  = $this->tenant['checkin_time']  ?? '15:00';
        $checkout = $this->tenant['checkout_time'] ?? '12:00';
        $hints    = trim($input['hints'] ?? '');

        $prompt = "Genera políticas de estadía claras y amables para el alojamiento '{$name}'. " .
            "Check-in: {$checkin}. Check-out: {$checkout}. " .
            ($hints ? "Indicaciones del propietario: '{$hints}'. " : '') .
            "Incluye: horarios, cancelación, mascotas, fumado, ruido. " .
            "Formato: lista con viñetas usando '•'. Máximo 6 puntos. Solo el texto.";

        $result = $this->geminiModel->generateText($prompt, 400, 0.5);

        if ($result['success']) {
            return ['success' => true, 'text' => trim($result['text'])];
        }

        return ['success' => false, 'message' => 'No se pudo generar las políticas.'];
    }

    // ── Generar todo el contenido de una vez ──────────────────────────────────
    private function aiGenerateFullContent(array $input): array
    {
        $heroResult     = $this->aiGenerateHero($input);
        $aboutResult    = $this->aiGenerateAbout($input);
        $policiesResult = $this->aiGeneratePolicies($input);

        return [
            'success'  => true,
            'hero'     => $heroResult['data']  ?? null,
            'about'    => $aboutResult['text'] ?? null,
            'policies' => $policiesResult['text'] ?? null,
        ];
    }

    // =========================================================================
    // PREVIEW — Renderiza el sitio para el iframe (sin filtro de published)
    // =========================================================================
    public function preview(): string
    {
        $website = $this->websiteModel
            ->where('tenant_id', $this->tenantId)
            ->first();

        if (!$website) {
            return '<p style="padding:2rem;color:#666">Sin configuración</p>';
        }

        $media = $this->mediaModel
            ->where('tenant_id', $this->tenantId)
            ->where('entity_type', 'tenant')
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        $units = $this->unitModel
            ->where('parent_id IS NULL')
            ->where('status !=', 'maintenance')
            ->findAll();

        // Enriquecer unidades igual que en index()
        $defaultPlan = $this->ratePlanModel->where('is_default', 1)->first();
        foreach ($units as &$unit) {
            $rate = null;
            if ($defaultPlan) {
                $rate = $this->unitRateModel
                    ->where('unit_id', $unit['id'])
                    ->where('rate_plan_id', $defaultPlan['id'])
                    ->first();
            }
            if (!$rate) {
                $rate = $this->unitRateModel
                    ->where('unit_id', $unit['id'])
                    ->where('is_active', 1)
                    ->first();
            }
            $unit['price_per_night'] = $rate['price_per_night'] ?? null;

            $photo = $this->mediaModel
                ->where('entity_type', 'unit')
                ->where('entity_id', $unit['id'])
                ->first();
            $unit['main_photo'] = $photo['file_path'] ?? null;
        }
        unset($unit);

        $theme = $website['theme_slug'] ?: 'resort';

        return view("public/themes/{$theme}/index", [
            'tenant'     => $this->tenant,
            'website'    => $website,
            'media'      => $media,
            'units'      => $units,
            'is_preview' => true,
        ]);
    }
}