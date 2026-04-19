<?php
namespace App\Libraries\Billing;

class MatiasPayloadBuilder
{
    /**
     * Construye el arreglo (que luego será JSON) para una Factura de Venta (Tipo 01 - ID API 7)
     */
    public function buildStandardInvoice(array $tenant, array $guest, array $reservation, array $lines)
    {
        // NOTA: Se asume que $tenant tiene los datos desempaquetados de settings_json
        // ej: $tenant['billing_resolution'], $tenant['billing_prefix']

        $payload = [
            "resolution_number" => $tenant['billing_resolution'] ?? "0", // OBLIGATORIO
            "prefix"            => $tenant['billing_prefix'] ?? "FEV",   // OBLIGATORIO
            "document_number"   => (int) $reservation['invoice_number'], // Consecutivo interno del PMS
            "operation_type_id" => 1, // 1 = Estándar
            "type_document_id"  => 7, // 7 = Factura de Venta Estándar (ID en API)
            "graphic_representation" => 1, // Queremos que Matias genere el PDF
            "send_email"        => 1,      // Queremos que Matias lo envíe por email

            // Datos del Huésped
            "customer" => [
                "country_id"           => "45", // Colombia
                "city_id"              => "836", // Medellín por defecto (Debes mapearlo dinámicamente según tu DB)
                "identity_document_id" => $this->mapDocumentType($guest['document_type'] ?? 'CC'),
                "type_organization_id" => ($guest['is_company'] ?? 0) ? 1 : 2, // 1: Jurídica, 2: Natural
                "tax_regime_id"        => 2, // 2: No responsable de IVA (por defecto, ajustar si el hotel cobra IVA)
                "tax_level_id"         => 5, // 5: No aplica - Otros
                "company_name"         => trim(($guest['first_name'] ?? '') . ' ' . ($guest['last_name'] ?? '')),
                "dni"                  => $guest['document_number'],
                "email"                => $guest['email'],
                "mobile"               => $guest['phone'] ?? ""
            ],

            // Forma de Pago (Asumimos pago de contado al hacer checkout)
            "payments" => [
                [
                    "payment_method_id" => 1, // 1: Contado
                    "means_payment_id"  => 10, // 10: Efectivo (Debes mapear si pagó con tarjeta, ej: 48)
                    "value_paid"        => number_format($reservation['total_paid'], 2, '.', '')
                ]
            ],

            // Totales (Regla DIAN: deben coincidir matemáticamente con las líneas)
            "legal_monetary_totals" => [
                "line_extension_amount" => number_format($reservation['subtotal'], 2, '.', ''),
                "tax_exclusive_amount"  => number_format($reservation['subtotal'], 2, '.', ''), // Base grabable total
                "tax_inclusive_amount"  => number_format($reservation['total'], 2, '.', ''),
                "payable_amount"        => number_format($reservation['total'], 2, '.', '')
            ],
        ];

        // Construcción de líneas e impuestos
        $payload['lines'] = $this->buildLines($lines);
        $payload['tax_totals'] = $this->buildTaxTotals($lines);

        return $payload;
    }

    /**
     * Mapea los tipos de documento de tu sistema a los IDs de la API
     */
    private function mapDocumentType($type)
    {
        $map = [
            'CC'  => '13',
            'CE'  => '22',
            'NIT' => '31',
            'PAS' => '41', // Pasaporte
        ];
        return $map[strtoupper($type)] ?? '13';
    }

    /**
     * Construye el arreglo de items (noches, consumos)
     */
    private function buildLines(array $items)
    {
        $lines = [];
        foreach ($items as $item) {
            $lines[] = [
                "invoiced_quantity"     => (string)$item['quantity'],
                "quantity_units_id"     => "1093", // 1093 = Unidad estándar
                "line_extension_amount" => number_format($item['subtotal'], 2, '.', ''),
                "free_of_charge_indicator" => false,
                "description"           => $item['description'],
                "code"                  => $item['sku'] ?? 'GEN01', // Código interno del ítem
                "type_item_identifications_id" => "4",
                "reference_price_id"    => "1",
                "price_amount"          => number_format($item['price'], 2, '.', ''),
                "base_quantity"         => (string)$item['quantity'],

                // Impuestos a nivel de línea. Dejo IVA 0% como base si tu tenant no cobra IVA aún.
                "tax_totals" => [
                    [
                        "tax_id"         => "1", // 1 = IVA
                        "tax_amount"     => 0,   // Lógica de cálculo pendiente
                        "taxable_amount" => number_format($item['subtotal'], 2, '.', ''),
                        "percent"        => 0
                    ]
                ]
            ];
        }
        return $lines;
    }

    /**
     * Totales de impuestos agrupados a nivel de factura
     */
    private function buildTaxTotals(array $items)
    {
        // Por ahora, asumimos 0% de IVA como base (Régimen simplificado/No responsable).
        // Si el tenant es responsable de IVA, sumaremos los taxes de $items aquí.

        $totalTaxable = array_sum(array_column($items, 'subtotal'));

        return [
            [
                "tax_id"         => "1", // 1 = IVA
                "tax_amount"     => 0,
                "taxable_amount" => number_format($totalTaxable, 2, '.', ''),
                "percent"        => 0
            ]
        ];
    }
}