<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsers extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            // CAMPO CRÍTICO: Vincula al empleado con una propiedad específica
            'tenant_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 120],
            'email'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'          => ['type' => 'ENUM', 'constraint' => ['admin', 'receptionist', 'housekeeping'], 'default' => 'receptionist'],
            'is_active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'last_login_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        // Un correo no puede repetirse dentro de la misma propiedad
        $this->forge->addUniqueKey(['tenant_id', 'email']);

        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}