<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePurchasesModule extends Migration
{
    public function up()
    {
        // TABLA 1: Compras (Facturas)
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'supplier_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'reference_number' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'purchase_date'    => ['type' => 'DATE'],
            'subtotal'         => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'tax_amount'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'total'            => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'amount_paid'      => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'status'           => ['type' => 'ENUM', 'constraint' => ['draft', 'pending', 'partial', 'paid', 'cancelled'], 'default' => 'draft'],
            'notes'            => ['type' => 'TEXT', 'null' => true],
            'created_by'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('supplier_id', 'suppliers', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('purchases');

        // TABLA 2: Items de la Compra
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'purchase_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'product_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255],
            'quantity'    => ['type' => 'DECIMAL', 'constraint' => '10,3'],
            'unit_cost'   => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'tax_rate'    => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => '0.00'],
            'tax_amount'  => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'subtotal'    => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('purchase_id', 'purchases', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('purchase_items');

        // TABLA 3: Pagos de la Compra (Egresos de caja)
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'purchase_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'amount'         => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'payment_method' => ['type' => 'ENUM', 'constraint' => ['cash', 'card', 'transfer', 'check', 'other']],
            'payment_date'   => ['type' => 'DATE'],
            'reference'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'notes'          => ['type' => 'TEXT', 'null' => true],
            'created_by'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('purchase_id', 'purchases', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('purchase_payments');
    }

    public function down() {
        $this->forge->dropTable('purchase_payments');
        $this->forge->dropTable('purchase_items');
        $this->forge->dropTable('purchases');
    }
}