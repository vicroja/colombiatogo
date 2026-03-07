<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRatePlans extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'               => ['type' => 'VARCHAR', 'constraint' => 100],
            'description'        => ['type' => 'TEXT', 'null' => true],
            'includes_breakfast' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_default'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_active'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('rate_plans');
    }

    public function down() { $this->forge->dropTable('rate_plans'); }
}