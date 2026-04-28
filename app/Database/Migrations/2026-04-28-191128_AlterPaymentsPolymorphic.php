<?php
// app/Database/Migrations/XXXX_AlterPaymentsPolymorphic.php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterPaymentsPolymorphic extends Migration
{
    public function up()
    {
        // 1. Eliminamos la FK original antes de modificar la columna
        $this->db->query('ALTER TABLE payments DROP FOREIGN KEY payments_reservation_id_foreign');

        // 2. Hacemos reservation_id nullable (tours puros no tienen reserva de hotel)
        $this->db->query('ALTER TABLE payments MODIFY COLUMN reservation_id int unsigned DEFAULT NULL');

        // 3. Agregamos entity_type para distinguir a qué entidad pertenece el pago
        $this->forge->addColumn('payments', [
            'entity_type' => [
                'type'       => 'ENUM',
                'constraint' => ['reservation', 'tour_reservation'],
                'default'    => 'reservation',
                'after'      => 'reservation_id',
            ],
        ]);

        // 4. Volvemos a agregar la FK pero ahora sin restricción de NOT NULL
        $this->db->query('ALTER TABLE payments ADD CONSTRAINT payments_reservation_id_foreign FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE payments DROP FOREIGN KEY payments_reservation_id_foreign');
        $this->forge->dropColumn('payments', 'entity_type');
        $this->db->query('ALTER TABLE payments MODIFY COLUMN reservation_id int unsigned NOT NULL');
        $this->db->query('ALTER TABLE payments ADD CONSTRAINT payments_reservation_id_foreign FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }
}