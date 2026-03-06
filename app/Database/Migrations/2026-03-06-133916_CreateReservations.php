<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReservations extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'guest_id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'accommodation_unit_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'check_in_date'         => ['type' => 'DATE'],
            'check_out_date'        => ['type' => 'DATE'],
            // ESTADOS ESTRICTOS DE LA RESERVA
            'status'                => ['type' => 'ENUM', 'constraint' => ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'], 'default' => 'pending'],
            'total_price'           => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('guest_id', 'guests', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('accommodation_unit_id', 'accommodation_units', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('reservations');
    }

    public function down() { $this->forge->dropTable('reservations'); }
}