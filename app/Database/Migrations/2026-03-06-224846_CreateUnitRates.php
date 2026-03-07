<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUnitRates extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'unit_id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rate_plan_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'price_per_night'    => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'extra_person_price' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'min_nights'         => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 1],
            'is_active'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('unit_id', 'accommodation_units', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('rate_plan_id', 'rate_plans', 'id', 'CASCADE', 'CASCADE');
        // Evitamos duplicados: Una unidad solo puede tener un precio base por cada plan
        $this->forge->addUniqueKey(['unit_id', 'rate_plan_id']);

        $this->forge->createTable('unit_rates');
    }

    public function down() { $this->forge->dropTable('unit_rates'); }
}