<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCommissions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'reservation_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'agent_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'amount'         => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'status'         => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'paid', 'cancelled'], 'default' => 'pending'],
            // pending: Reserva creada
            // approved: Huésped hizo check-out y pagó
            // paid: Ya le transferimos al agente
            // cancelled: El huésped no llegó (No-show)
            'paid_at'        => ['type' => 'DATETIME', 'null' => true], // Cuándo le pagamos al agente
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('reservation_id', 'reservations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('agent_id', 'commission_agents', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('commissions');
    }

    public function down() { $this->forge->dropTable('commissions'); }
}