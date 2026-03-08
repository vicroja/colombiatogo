<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSuppliers extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 150],
            'trade_name'    => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'tax_id'        => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true], // NIT, RUT
            'contact_name'  => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'email'         => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'phone'         => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'address'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'city'          => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'country'       => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'payment_terms' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'notes'         => ['type' => 'TEXT', 'null' => true],
            'is_active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('suppliers');
    }

    public function down() { $this->forge->dropTable('suppliers'); }
}