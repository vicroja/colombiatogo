<?php
// app/Database/Migrations/XXXX_AlterTourGuidesAddPaymentFields.php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterTourGuidesAddPaymentFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tour_guides', [
            // Modelo de pago del guía
            'payment_model' => [
                'type'       => 'ENUM',
                'constraint' => ['fixed_per_tour', 'per_pax', 'commission_pct', 'mixed', 'salary'],
                'default'    => 'fixed_per_tour',
                'after'      => 'is_active',
            ],
            // Monto fijo por salida (fixed_per_tour y mixed)
            'rate_fixed' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'after'      => 'payment_model',
            ],
            // Monto por adulto (per_pax y mixed)
            'rate_per_adult' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'after'      => 'rate_fixed',
            ],
            // Monto por niño (per_pax y mixed)
            'rate_per_child' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'after'      => 'rate_per_adult',
            ],
            // Porcentaje sobre total vendido (commission_pct)
            'commission_pct' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'after'      => 'rate_per_child',
            ],
            // Mínimo de pax antes de aplicar tarifa extra (mixed)
            'min_pax_for_bonus' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'commission_pct',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'min_pax_for_bonus',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tour_guides', [
            'payment_model', 'rate_fixed', 'rate_per_adult',
            'rate_per_child', 'commission_pct', 'min_pax_for_bonus', 'notes',
        ]);
    }
}