<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProducts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'category_id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'                    => ['type' => 'VARCHAR', 'constraint' => 150],
            'description'             => ['type' => 'TEXT', 'null' => true],
            'sku'                     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'unit_price'              => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'is_available_for_guests' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'is_active'               => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'              => ['type' => 'DATETIME', 'null' => true],
            'updated_at'              => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'product_categories', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('products');
    }

    public function down() { $this->forge->dropTable('products'); }
}