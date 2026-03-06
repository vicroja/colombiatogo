<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenants extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'              => ['type' => 'VARCHAR', 'constraint' => 120],
            'slug'              => ['type' => 'VARCHAR', 'constraint' => 80, 'unique' => true],
            'logo_path'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'address'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'city'              => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'country'           => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'phone'             => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'email'             => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'website'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'timezone'          => ['type' => 'VARCHAR', 'constraint' => 60, 'default' => 'America/Bogota'],
            'currency_code'     => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'COP'],
            'currency_symbol'   => ['type' => 'VARCHAR', 'constraint' => 5, 'default' => '$'],
            'checkin_time'      => ['type' => 'TIME', 'default' => '15:00:00'],
            'checkout_time'     => ['type' => 'TIME', 'default' => '12:00:00'],
            'settings_json'     => ['type' => 'JSON', 'null' => true],
            'is_active'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],

            // Campos de Control del SuperAdmin
            'onboarding_status' => ['type' => 'ENUM', 'constraint' => ['pending', 'in_progress', 'complete'], 'default' => 'pending'],
            'is_suspended'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'suspended_reason'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'trial_ends_at'     => ['type' => 'DATE', 'null' => true],
            'current_plan_slug' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],

            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('tenants');
    }

    public function down()
    {
        $this->forge->dropTable('tenants');
    }
}