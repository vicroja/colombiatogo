<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCommissionAgents extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'             => ['type' => 'VARCHAR', 'constraint' => 150],
            'contact_info'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true], // Teléfono o Email
            'bank_details'     => ['type' => 'TEXT', 'null' => true], // Cuenta bancaria, Nequi, etc.
            'commission_type'  => ['type' => 'ENUM', 'constraint' => ['percentage', 'fixed'], 'default' => 'percentage'],
            'commission_value' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'tracking_code'    => ['type' => 'VARCHAR', 'constraint' => 50], // Ej: WALTER2026
            'is_active'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        // Un código debe ser único dentro del mismo hotel
        $this->forge->addUniqueKey(['tenant_id', 'tracking_code']);
        $this->forge->createTable('commission_agents');
    }

    public function down() { $this->forge->dropTable('commission_agents'); }
}