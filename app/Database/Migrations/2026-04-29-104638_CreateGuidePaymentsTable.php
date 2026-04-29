<?php
// app/Database/Migrations/XXXX_CreateGuidePaymentsTable.php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGuidePaymentsTable extends Migration
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
            'guide_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            // NULL si es pago de periodo (salary mensual)
            'schedule_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
            ],
            // Snapshot del modelo al momento del cálculo para auditoría
            'payment_model_snapshot' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            // Desglose del cálculo: pax, tarifas, total vendido, etc.
            'calculation_detail_json' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'paid', 'cancelled'],
                'default'    => 'pending',
            ],
            'payment_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'payment_method' => [
                'type'       => 'ENUM',
                'constraint' => ['cash', 'bank_transfer', 'other'],
                'null'       => true,
            ],
            'reference' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addKey('guide_id');
        $this->forge->addKey('schedule_id');
        $this->forge->addKey('tenant_id');
        $this->forge->addForeignKey('tenant_id',   'tenants',        'id', 'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('guide_id',    'tour_guides',    'id', 'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('schedule_id', 'tour_schedules', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('guide_payments');
    }

    public function down()
    {
        $this->forge->dropTable('guide_payments', true);
    }
}