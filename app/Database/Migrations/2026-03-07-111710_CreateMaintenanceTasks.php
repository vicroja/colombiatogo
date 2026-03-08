<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMaintenanceTasks extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'unit_id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'title'            => ['type' => 'VARCHAR', 'constraint' => 150],
            'description'      => ['type' => 'TEXT', 'null' => true],
            'priority'         => ['type' => 'ENUM', 'constraint' => ['baja', 'media', 'alta'], 'default' => 'media'],
            'status'           => ['type' => 'ENUM', 'constraint' => ['pending', 'in_progress', 'completed'], 'default' => 'pending'],
            'blocks_unit'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'scheduled_date'   => ['type' => 'DATE', 'null' => true],
            'reminder_sent_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('unit_id', 'accommodation_units', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('maintenance_tasks');
    }

    public function down() { $this->forge->dropTable('maintenance_tasks'); }
}