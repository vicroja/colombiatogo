<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterPaymentsPolymorphic extends Migration
{
    public function up()
    {
        $this->forge->addColumn('payments', [
            'entity_type' => [
                'type'       => 'ENUM',
                'constraint' => ['reservation', 'tour_reservation'],
                'default'    => 'reservation',
                'after'      => 'reservation_id',
            ],
        ]);

        // Hacemos reservation_id nullable para pagos de tours puros
        $this->db->query('ALTER TABLE payments DROP FOREIGN KEY payments_reservation_id_foreign');
        $this->db->query('ALTER TABLE payments MODIFY reservation_id int unsigned DEFAULT NULL');
    }

    public function down()
    {
        //
    }
}
