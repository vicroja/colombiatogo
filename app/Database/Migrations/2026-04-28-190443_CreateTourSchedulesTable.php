<?php
// app/Database/Migrations/XXXX_CreateTourSchedulesTable.php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTourSchedulesTable extends Migration
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
            'tour_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'guide_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,   // puede asignarse después
            ],
            // Fecha y hora de salida del tour
            'start_datetime' => [
                'type' => 'DATETIME',
            ],
            'max_pax' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 10,
            ],
            // Se incrementa al confirmar reservas, se decrementa al cancelar
            'current_pax' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 0,
            ],
            // NULL = usa el precio base del tour padre
            'price_adult_override' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
            ],
            'price_child_override' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['scheduled', 'in_progress', 'completed', 'cancelled'],
                'default'    => 'scheduled',
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
        $this->forge->addKey('tour_id');
        $this->forge->addKey('guide_id');
        $this->forge->addForeignKey('tour_id', 'tours', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('guide_id', 'tour_guides', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('tour_schedules');
    }

    public function down()
    {
        $this->forge->dropTable('tour_schedules', true);
    }
}