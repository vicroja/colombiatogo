<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSourceToReservations extends Migration
{
    public function up()
    {
        // Añadimos la columna source_id después de guest_id
        $this->forge->addColumn('reservations', [
            'source_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'guest_id'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('reservations', 'source_id');
    }
}