<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAccommodationUnits extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'type_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 50], // Ej. Cabaña 101
            'status'        => ['type' => 'ENUM', 'constraint' => ['available', 'occupied', 'maintenance', 'blocked'], 'default' => 'available'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('type_id', 'accommodation_types', 'id', 'RESTRICT', 'CASCADE');
        // Aseguramos que no haya dos habitaciones llamadas "101" en el mismo hotel
        $this->forge->addUniqueKey(['tenant_id', 'name']);

        $this->forge->createTable('accommodation_units');
    }

    public function down()
    {
        $this->forge->dropTable('accommodation_units');
    }
}