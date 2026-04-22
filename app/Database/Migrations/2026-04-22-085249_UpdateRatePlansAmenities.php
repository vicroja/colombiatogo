<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateRatePlansAmenities extends Migration
{
    public function up(): void
    {
        // Reemplazar includes_breakfast por amenities_json + campos nuevos
        $this->forge->modifyColumn('rate_plans', [
            'includes_breakfast' => [
                // Renombramos/eliminamos via addColumn + dropColumn
                // CI4 Forge no tiene renameColumn directo, lo hacemos así:
                'name' => 'includes_breakfast', // se elimina abajo
            ],
        ]);

        // Agregar las nuevas columnas
        $this->forge->addColumn('rate_plans', [
            'amenities_json' => [
                'type'    => 'JSON',
                'null'    => true,
                'after'   => 'description',
            ],
            'cancellation_policy' => [
                'type'       => 'ENUM',
                'constraint' => ['flexible', 'moderate', 'strict', 'non_refundable'],
                'default'    => 'flexible',
                'after'      => 'amenities_json',
            ],
            'min_nights_default' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
                'default'    => 1,
                'after'      => 'cancellation_policy',
            ],
        ]);

        // Migrar datos existentes: si includes_breakfast = 1, guardar en JSON
        $this->db->query("
            UPDATE rate_plans
            SET amenities_json = JSON_OBJECT('breakfast', true)
            WHERE includes_breakfast = 1
        ");

        // Ahora sí eliminamos la columna vieja
        $this->forge->dropColumn('rate_plans', 'includes_breakfast');
    }

    public function down(): void
    {
        $this->forge->addColumn('rate_plans', [
            'includes_breakfast' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after'   => 'description',
            ],
        ]);

        // Restaurar datos
        $this->db->query("
            UPDATE rate_plans
            SET includes_breakfast = 1
            WHERE JSON_EXTRACT(amenities_json, '$.breakfast') = true
        ");

        $this->forge->dropColumn('rate_plans', 'amenities_json');
        $this->forge->dropColumn('rate_plans', 'cancellation_policy');
        $this->forge->dropColumn('rate_plans', 'min_nights_default');
    }
}