<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'reservation_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'amount'         => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'payment_method' => ['type' => 'ENUM', 'constraint' => ['cash', 'credit_card', 'bank_transfer'], 'default' => 'cash'],
            'reference'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('reservation_id', 'reservations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('payments');
    }

    public function down() { $this->forge->dropTable('payments'); }
}