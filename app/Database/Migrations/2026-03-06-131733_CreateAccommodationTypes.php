<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAccommodationTypes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 100], // Ej. Cabaña Familiar
            'description'   => ['type' => 'TEXT', 'null' => true],
            'base_capacity' => ['type' => 'TINYINT', 'constraint' => 2, 'default' => 2],
            'max_capacity'  => ['type' => 'TINYINT', 'constraint' => 2, 'default' => 2],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('accommodation_types');
    }

    public function down()
    {
        $this->forge->dropTable('accommodation_types');
    }
}