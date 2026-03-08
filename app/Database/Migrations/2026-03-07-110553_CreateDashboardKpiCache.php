<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDashboardKpiCache extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'kpi_key'       => ['type' => 'VARCHAR', 'constraint' => 80],
            'value_json'    => ['type' => 'TEXT'], // Usamos TEXT para simular JSON y mantener compatibilidad
            'period_start'  => ['type' => 'DATE', 'null' => true],
            'period_end'    => ['type' => 'DATE', 'null' => true],
            'calculated_at' => ['type' => 'DATETIME'],
            'expires_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('dashboard_kpi_cache');
    }

    public function down() { $this->forge->dropTable('dashboard_kpi_cache'); }
}