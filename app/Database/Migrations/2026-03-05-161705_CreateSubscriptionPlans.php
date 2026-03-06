<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSubscriptionPlans extends Migration
{
    public function up()
    {
        // Definición de la tabla subscription_plans
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 80],
            'slug'          => ['type' => 'VARCHAR', 'constraint' => 40, 'unique' => true],
            'description'   => ['type' => 'TEXT', 'null' => true],
            'price'         => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'currency'      => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'USD'],
            'billing_cycle' => ['type' => 'ENUM', 'constraint' => ['monthly', 'annual', 'one_time'], 'default' => 'monthly'],
            'trial_days'    => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
            'limits_json'   => ['type' => 'JSON'],
            'is_public'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'is_active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'sort_order'    => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'color'         => ['type' => 'VARCHAR', 'constraint' => 7, 'default' => '#2563EB'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('subscription_plans');
    }

    public function down()
    {
        $this->forge->dropTable('subscription_plans');
    }
}