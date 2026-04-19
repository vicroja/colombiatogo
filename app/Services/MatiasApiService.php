<?php
namespace App\Services;

use App\Models\TenantModel;

class MatiasApiService
{
    // TODO: Idealmente, mover esta URL a tu archivo .env como MATIAS_API_URL
    protected $baseUrl = 'https://api.matias-api.com/api/ubl2.1';
    protected $token;
    protected $tenantId;

    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
        $tenantModel = new TenantModel();
        $tenant = $tenantModel->find($tenantId);

        if (!$tenant) {
            log_message('error', "[MatiasAPI] Error de inicio: Tenant ID {$tenantId} no encontrado.");
            throw new \Exception("Tenant no encontrado.");
        }

        $settings = json_decode($tenant['settings_json'], true);

        // Verificamos si el token existe en la configuración del tenant
        if (!isset($settings['matias_api_token']) || empty($settings['matias_api_token'])) {
            log_message('error', "[MatiasAPI] El Tenant ID {$tenantId} intentó facturar pero no tiene 'matias_api_token' configurado.");
            throw new \Exception("El hotel no tiene configurado el token de facturación electrónica.");
        }

        $this->token = $settings['matias_api_token'];
    }

    /**
     * Motor principal para peticiones cURL a Matias API
     */
    private function sendRequest($method, $endpoint, $data = [])
    {
        $client = \Config\Services::curlrequest();

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json'
            ],
            'http_errors' => false, // Evita que CI4 lance excepciones fatales en errores 4xx o 5xx
            'timeout'     => 30     // Timeout prudente para la DIAN
        ];

        if (in_array($method, ['POST', 'PUT']) && !empty($data)) {
            $options['json'] = $data;
        }

        $url = $this->baseUrl . $endpoint;

        // Log crucial para debug en caso de falla de estructura JSON
        log_message('info', "[MatiasAPI] Solicitud {$method} a {$url}");

        try {
            $response = $client->request($method, $url, $options);
            $statusCode = $response->getStatusCode();
            $body = $response->getBody();

            $decodedBody = json_decode($body, true);

            if ($statusCode >= 400) {
                // Log de error crítico
                log_message('error', "[MatiasAPI] Fallo en API ({$statusCode}): " . $body);
            }

            return [
                'status_code' => $statusCode,
                'success'     => ($statusCode >= 200 && $statusCode < 300),
                'response'    => $decodedBody ?? $body
            ];

        } catch (\Exception $e) {
            log_message('error', "[MatiasAPI] Excepción cURL: " . $e->getMessage());
            return [
                'status_code' => 500,
                'success'     => false,
                'response'    => ['message' => 'Error de conexión con el servidor de facturación.']
            ];
        }
    }

    /**
     * Emite una factura electrónica (Venta o POS)
     */
    public function emitInvoice(array $payload)
    {
        return $this->sendRequest('POST', '/invoice', $payload);
    }

    /**
     * Consulta el estado de un documento previamente enviado
     */
    public function checkStatus($trackId)
    {
        return $this->sendRequest('GET', '/status/document/' . $trackId);
    }
}

/*
 *
 <?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\MatiasApiService;
use App\Libraries\Billing\MatiasPayloadBuilder;
// Asumimos que tienes estos modelos:
use App\Models\ReservationModel;
use App\Models\GuestModel;
use App\Models\TenantModel;

class CheckoutController extends BaseController
{
    public function processCheckout($reservationId)
    {
        // 1. Cargar datos necesarios de la base de datos
        $reservationModel = new ReservationModel();
        $reservation = $reservationModel->find($reservationId);

        $guestModel = new GuestModel();
        $guest = $guestModel->find($reservation['guest_id']);

        $tenantModel = new TenantModel();
        $tenant = $tenantModel->find($reservation['tenant_id']);

        // Supongamos que tienes un método para traer los consumos/noches
        $lines = $reservationModel->getInvoiceItems($reservationId);

        // 2. Transformar los datos del PMS al estándar de la DIAN
        $payloadBuilder = new MatiasPayloadBuilder();
        $invoicePayload = $payloadBuilder->buildStandardInvoice($tenant, $guest, $reservation, $lines);

        // 3. Iniciar el Servicio API y Enviar
        $db = \Config\Database::connect();

        try {
            $apiService = new MatiasApiService($tenant['id']);
            $response = $apiService->emitInvoice($invoicePayload);

            // 4. Preparar el registro para la tabla `tenant_invoices`
            $invoiceData = [
                'tenant_id'            => $tenant['id'],
                'reservation_id'       => $reservationId,
                'guest_id'             => $guest['id'],
                'document_type_api_id' => 7, // Factura
                'prefix'               => $invoicePayload['prefix'],
                'document_number'      => $invoicePayload['document_number'],
                'total_amount'         => $invoicePayload['legal_monetary_totals']['payable_amount'],
                'tax_amount'           => $invoicePayload['tax_totals'][0]['tax_amount'] ?? 0,
                'api_response'         => json_encode($response['response'])
            ];

            if ($response['success']) {
                // Éxito: Matias recibió el documento
                $invoiceData['status'] = 'pending_dian'; // Queda pendiente de validación DIAN
                $invoiceData['uuid']   = $response['response']['document_key'] ?? null;

                log_message('info', "[Checkout] Factura enviada a Matias API. ResId: {$reservationId}");

                $db->table('tenant_invoices')->insert($invoiceData);
                return redirect()->back()->with('success', 'Factura enviada a la DIAN con éxito.');
            } else {
                // Fallo de validación en la API (Ej: Faltan datos del cliente)
                $invoiceData['status'] = 'rejected_dian';

                log_message('error', "[Checkout] Rechazo de Matias API. ResId: {$reservationId}. Error: " . json_encode($response['response']));

                $db->table('tenant_invoices')->insert($invoiceData);
                return redirect()->back()->with('error', 'Error al emitir factura: ' . ($response['response']['message'] ?? 'Revisa los logs.'));
            }

        } catch (\Exception $e) {
            log_message('critical', "[Checkout] Excepción fatal facturando ResId {$reservationId}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error de configuración: ' . $e->getMessage());
        }
    }
}
2. Resumen Técnico del Módulo de Facturación (Documentación para Desarrolladores / AI)
Arquitectura General
El módulo de Facturación Electrónica está diseñado para CodeIgniter 4 bajo un esquema Multi-Tenant estricto. Su objetivo es intermediar entre los datos operativos del PMS (Property Management System) y la API v3.0.0 de "Matias API", el proveedor tecnológico ante la DIAN en Colombia. La arquitectura sigue el principio de responsabilidad única, separando la transformación de datos, la comunicación HTTP y la persistencia en base de datos.

1. Gestión Multi-Tenant y Autenticación
No existe un token global. La API requiere un Personal Access Token (PAT) por cada empresa emisora. Este token se almacena de forma aislada en la tabla tenants, dentro del campo JSON nativo settings_json bajo la clave "matias_api_token". El servicio core extrae este token dinámicamente instanciando el Tenant activo en tiempo de ejecución, previniendo fugas de información entre hoteles.

2. Persistencia (Base de Datos)
Se implementó la tabla tenant_invoices mediante migraciones de CI4 (CreateTenantInvoicesTable). Esta tabla es el registro maestro de documentos fiscales y no fiscales (Facturas, POS, Recibos internos). Relaciona directamente el tenant_id, reservation_id y guest_id. Utiliza un campo status tipo ENUM (draft, pending_dian, validated_dian, rejected_dian) para manejar la máquina de estados del documento. Guarda el CUFE/CUDE devuelto en el campo uuid y almacena el payload de respuesta crudo (api_response) para auditoría y debug.

3. Servicio de Comunicación (MatiasApiService)
Ubicado en app/Services/MatiasApiService.php, actúa como un Wrapper de cURL estandarizado usando la librería HTTP nativa de CI4. Sus responsabilidades son:

Inyectar los headers obligatorios (Authorization: Bearer {token}).

Capturar timeouts y excepciones de red para evitar rupturas de la aplicación (http_errors => false).

Mapear las respuestas en un arreglo estandarizado con un flag booleano success derivado del código HTTP (200-299).

4. Transformación de Datos (MatiasPayloadBuilder)
Ubicado en app/Libraries/Billing/MatiasPayloadBuilder.php, es el motor de mapeo. Su función crítica es traducir los modelos relacionales del PMS a la jerarquía JSON exacta requerida por Matias.

Regla de Negocio Crítica: La API de Matias requiere los IDs internos de su base de datos, no los códigos de la DIAN (Ej: type_document_id debe ser 7 para Factura de Venta, no 01).

Calcula y formatea subtotales e impuestos (legal_monetary_totals y tax_totals) asegurando precisión decimal de 2 dígitos, requisito de validación matemática de la DIAN (Regla FAS07/FAX07).

Mapea tipos de documento de identidad y regímenes tributarios a las tablas internas del proveedor.
 */