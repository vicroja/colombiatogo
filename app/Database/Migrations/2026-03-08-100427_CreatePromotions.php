<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePromotions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'code'          => ['type' => 'VARCHAR', 'constraint' => 50],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'discount_type' => ['type' => 'ENUM', 'constraint' => ['percentage', 'fixed']],
            'discount_value'=> ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'valid_from'    => ['type' => 'DATE'],
            'valid_until'   => ['type' => 'DATE'],
            'max_uses'      => ['type' => 'INT', 'constraint' => 11, 'default' => 0], // 0 = sin límite
            'current_uses'  => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'is_active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('promotions');
    }

    public function down() { $this->forge->dropTable('promotions'); }
}