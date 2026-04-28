<?php
// app/Database/Migrations/XXXX_AlterCommissionsPolymorphic.php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterCommissionsPolymorphic extends Migration
{
    public function up()
    {
        // Añadimos entity_type para distinguir si la comisión es de reserva de hotel o de tour
        $this->forge->addColumn('commissions', [
            'entity_type' => [
                'type'       => 'ENUM',
                'constraint' => ['reservation', 'tour_reservation'],
                'default'    => 'reservation',
                'after'      => 'reservation_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('commissions', 'entity_type');
    }
}