<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReservationGuests extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'reservation_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'first_name'     => ['type' => 'VARCHAR', 'constraint' => 80],
            'last_name'      => ['type' => 'VARCHAR', 'constraint' => 80],
            'doc_type'       => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'doc_number'     => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'relationship'   => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true], // Ej: Esposa, Hijo
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // OJO: Esta tabla se vincula a la reserva, no directamente al tenant (la reserva ya aísla por tenant)
        $this->forge->addForeignKey('reservation_id', 'reservations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('reservation_guests');
    }

    public function down() { $this->forge->dropTable('reservation_guests'); }
}