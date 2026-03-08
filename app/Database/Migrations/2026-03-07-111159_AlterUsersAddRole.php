<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterUsersAddRole extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'tenant_id' // O after 'id' dependiendo de tu tabla
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'role_id');
    }
}