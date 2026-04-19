<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenantInvoicesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tenant_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'reservation_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true, // Puede ser nulo si es una venta POS suelta sin reserva
            ],
            'guest_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'document_type_api_id' => [
                'type'       => 'INT',
                'constraint' => 5,
                'comment'    => '7: Factura Venta, 20: POS, 0: Cobro Interno',
            ],
            'prefix' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'document_number' => [
                'type'       => 'INT',
                'constraint' => 20,
            ],
            'uuid' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'comment'    => 'CUFE o CUDE devuelto por la DIAN',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'pending_dian', 'validated_dian', 'rejected_dian', 'internal_issued'],
                'default'    => 'draft',
            ],
            'total_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'tax_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'pdf_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'xml_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'api_response' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Respuesta cruda de Matias API (JSON) para debug',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'status']);

        // Relación con tenants (asumiendo que tu tabla se llama 'tenants')
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('tenant_invoices');
    }

    public function down()
    {
        $this->forge->dropTable('tenant_invoices');
    }
}