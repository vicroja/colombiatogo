<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGuests extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'full_name'  => ['type' => 'VARCHAR', 'constraint' => 150],
            'document'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'phone'      => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('guests');
    }

    public function down() { $this->forge->dropTable('guests'); }
}