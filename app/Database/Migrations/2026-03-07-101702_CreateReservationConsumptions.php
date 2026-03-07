<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReservationConsumptions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'reservation_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'product_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'description'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'quantity'       => ['type' => 'INT', 'constraint' => 5, 'default' => 1],
            'unit_price'     => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'subtotal'       => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('reservation_id', 'reservations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('reservation_consumptions');
    }

    public function down() { $this->forge->dropTable('reservation_consumptions'); }
}