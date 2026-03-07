<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSeasonalRates extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'unit_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true], // NULL = Aplica a todas
            'rate_plan_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true], // NULL = Aplica a todos
            'name'           => ['type' => 'VARCHAR', 'constraint' => 100],
            'start_date'     => ['type' => 'DATE'],
            'end_date'       => ['type' => 'DATE'],
            'modifier_type'  => ['type' => 'ENUM', 'constraint' => ['fixed', 'percent_increase', 'percent_decrease'], 'default' => 'percent_increase'],
            'modifier_value' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'priority'       => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 10], // Mayor prioridad manda
            'is_active'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('unit_id', 'accommodation_units', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('rate_plan_id', 'rate_plans', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('seasonal_rates');
    }

    public function down() { $this->forge->dropTable('seasonal_rates'); }
}