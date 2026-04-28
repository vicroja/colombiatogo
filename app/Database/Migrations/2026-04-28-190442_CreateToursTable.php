<?php
// app/Database/Migrations/XXXX_CreateToursTable.php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateToursTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tenant_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,   // opcional: puede no pertenecer a categoría
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'duration_minutes' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 60,
            ],
            'meeting_point' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'min_pax' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 1,      // mínimo de personas para que salga el tour
            ],
            // Precio base — los schedules pueden sobreescribirlo
            'price_adult' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],
            'price_child' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
            ],
            'cancellation_policy' => [
                'type'       => 'ENUM',
                'constraint' => ['flexible', 'moderate', 'strict', 'non_refundable'],
                'default'    => 'flexible',
            ],
            'difficulty_level' => [
                'type'       => 'ENUM',
                'constraint' => ['easy', 'moderate', 'hard'],
                'default'    => 'easy',
            ],
            // JSON con lista de ítems incluidos: ["Almuerzo", "Seguro", "Transporte"]
            'included_json' => [
                'type' => 'JSON',
                'null' => true,
            ],
            // JSON con lista de ítems NO incluidos
            'excluded_json' => [
                'type' => 'JSON',
                'null' => true,
            ],
            // JSON con rutas de fotos, reutilizando lógica de tenant_media
            'media_json' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'is_active' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'product_categories', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('tours');
    }

    public function down()
    {
        $this->forge->dropTable('tours', true);
    }
}