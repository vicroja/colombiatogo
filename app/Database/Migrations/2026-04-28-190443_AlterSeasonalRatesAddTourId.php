<?php
// app/Database/Migrations/XXXX_AlterSeasonalRatesAddTourId.php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterSeasonalRatesAddTourId extends Migration
{
    public function up()
    {
        // Añadimos tour_id nullable: NULL = aplica a todos los tours (igual que unit_id)
        $this->forge->addColumn('seasonal_rates', [
            'tour_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'unit_id',
            ],
        ]);

        $this->db->query('ALTER TABLE seasonal_rates ADD INDEX idx_tour_id (tour_id)');
        $this->db->query('ALTER TABLE seasonal_rates ADD CONSTRAINT sr_tour_id_foreign FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->forge->dropForeignKey('seasonal_rates', 'sr_tour_id_foreign');
        $this->forge->dropColumn('seasonal_rates', 'tour_id');
    }
}