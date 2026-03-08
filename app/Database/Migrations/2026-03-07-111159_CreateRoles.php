<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true], // NULL = Rol del sistema
            'name'             => ['type' => 'VARCHAR', 'constraint' => 80],
            'slug'             => ['type' => 'VARCHAR', 'constraint' => 80],
            'permissions_json' => ['type' => 'TEXT', 'null' => true],
            'is_system'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('roles');
    }

    public function down() { $this->forge->dropTable('roles'); }
}