<?php
// app/Database/Migrations/XXXX_CreateTourReservationsTable.php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTourReservationsTable extends Migration
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
            'schedule_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'guest_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            // Si el huésped también tiene reserva de hotel, se enlaza aquí
            // para que el tour aparezca en su folio de alojamiento
            'parent_reservation_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            // Agente comisionista opcional
            'agent_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'num_adults' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'default'    => 1,
            ],
            'num_children' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'default'    => 0,
            ],
            'total_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],
            'pickup_location' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,   // punto de recogida si difiere del meeting_point
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'confirmed', 'no_show', 'completed', 'cancelled', 'refunded'],
                'default'    => 'pending',
            ],
            // Snapshot del precio al momento de reservar para auditoría
            'price_snapshot_json' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('schedule_id');
        $this->forge->addKey('guest_id');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('schedule_id', 'tour_schedules', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('guest_id', 'guests', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('parent_reservation_id', 'reservations', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('agent_id', 'commission_agents', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('tour_reservations');
    }

    public function down()
    {
        $this->forge->dropTable('tour_reservations', true);
    }
}