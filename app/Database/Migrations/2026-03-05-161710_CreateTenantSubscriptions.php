<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenantSubscriptions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'plan_id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'status'               => ['type' => 'ENUM', 'constraint' => ['trial', 'active', 'past_due', 'suspended', 'cancelled'], 'default' => 'trial'],
            'started_at'           => ['type' => 'DATE'],
            'trial_ends_at'        => ['type' => 'DATE', 'null' => true],
            'current_period_start' => ['type' => 'DATE'],
            'current_period_end'   => ['type' => 'DATE'],
            'grace_period_days'    => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 5],
            'suspended_at'         => ['type' => 'DATETIME', 'null' => true],
            'cancelled_at'         => ['type' => 'DATETIME', 'null' => true],
            'cancellation_reason'  => ['type' => 'TEXT', 'null' => true],
            'notes'                => ['type' => 'TEXT', 'null' => true],
            'created_by'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);

        // Llaves foráneas para garantizar la integridad de los datos
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('plan_id', 'subscription_plans', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'super_admins', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('tenant_subscriptions');

        // Índice recomendado en la especificación para búsquedas rápidas
        // Se ejecuta una consulta raw porque CI4 forge index tiene limitaciones con múltiples columnas
        $this->db->query('CREATE INDEX idx_tenant_active ON tenant_subscriptions (tenant_id, status)');
    }

    public function down()
    {
        // El orden es importante para evitar errores de restricción de clave foránea
        $this->forge->dropTable('tenant_subscriptions');
    }
}