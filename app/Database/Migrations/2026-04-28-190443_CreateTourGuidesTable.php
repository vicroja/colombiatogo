<?php
// app/Database/Migrations/XXXX_CreateTourGuidesTable.php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTourGuidesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tenant_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            // NULL si es guía externo (freelance)
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'document' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'specialty' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,   // ej: "Senderismo", "Buceo", "Cultura"
            ],
            'languages' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,   // ej: "ES, EN, FR"
            ],
            'is_active' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
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
        $this->forge->addKey('tenant_id');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('tour_guides');
    }

    public function down()
    {
        $this->forge->dropTable('tour_guides', true);
    }
}