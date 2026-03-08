<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterAccommodationUnitsAddFeatures extends Migration
{
    public function up()
    {
        $this->forge->addColumn('accommodation_units', [
            'bathrooms' => [
                'type'       => 'DECIMAL',
                'constraint' => '3,1',
                'default'    => 1.0,
                'after'      => 'max_occupancy'
            ],
            'beds_info' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'bathrooms'
            ],
            'amenities' => [
                'type'       => 'JSON',
                'null'       => true,
                'after'      => 'beds_info'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('accommodation_units', ['bathrooms', 'beds_info', 'amenities']);
    }
}