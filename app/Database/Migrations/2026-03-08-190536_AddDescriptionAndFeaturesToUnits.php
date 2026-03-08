<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDescriptionAndFeaturesToUnits extends Migration
{
    public function up()
    {
        // Definimos las nuevas columnas que necesitamos
        $fields = [
            'description' => [
                'type'       => 'TEXT',
                'collation'  => 'utf8mb4_general_ci',
                'null'       => true,
                'after'      => 'name' // Lo colocamos después del nombre para mantener el orden lógico
            ],
            'features_json' => [
                'type'       => 'JSON',
                'null'       => true,
                'after'      => 'description' // Lo colocamos después de la descripción
            ],
        ];

        // Agregamos las columnas a la tabla existente
        $this->forge->addColumn('accommodation_units', $fields);

        // Log para dejar rastro de que la BD fue modificada correctamente
        log_message('info', 'Migración ejecutada: Se añadieron description y features_json a accommodation_units.');
    }

    public function down()
    {
        // Si necesitamos hacer rollback, eliminamos las columnas
        $this->forge->dropColumn('accommodation_units', ['description', 'features_json']);

        log_message('info', 'Rollback ejecutado: Se eliminaron description y features_json de accommodation_units.');
    }
}