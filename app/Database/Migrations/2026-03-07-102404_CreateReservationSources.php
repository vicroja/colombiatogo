<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReservationSources extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 80],
            'color'      => ['type' => 'VARCHAR', 'constraint' => 7, 'default' => '#2E75B6'], // Para pintar el calendario luego
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('reservation_sources');
    }

    public function down() { $this->forge->dropTable('reservation_sources'); }
}