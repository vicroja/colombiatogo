<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFeaturesToAccommodationUnits extends Migration
{
    public function up()
    {
        // Definimos los nuevos campos a agregar a la tabla
        $fields = [
            'description' => [
                'type'       => 'TEXT',
                'collation'  => 'utf8mb4_general_ci',
                'null'       => true,
                'after'      => 'name' // Se ubicará después del campo 'name'
            ],
            'features_json' => [
                'type'       => 'JSON',
                'null'       => true,
                'after'      => 'description' // Se ubicará después del campo 'description'
            ],
        ];

        // Añadimos las columnas a la tabla 'accommodation_units'
        $this->forge->addColumn('accommodation_units', $fields);

        // CI4 loguea automáticamente las migraciones exitosas, pero es buena práctica saber qué hace este método.
    }

    public function down()
    {
        // En caso de hacer rollback (php spark migrate:rollback), eliminamos las columnas creadas
        $this->forge->dropColumn('accommodation_units', ['description', 'features_json']);
    }
}