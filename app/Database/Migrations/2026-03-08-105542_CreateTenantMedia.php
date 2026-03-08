<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenantMedia extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'entity_type' => ['type' => 'ENUM', 'constraint' => ['tenant', 'unit'], 'default' => 'tenant'],
            'entity_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true], // Null si es del tenant, ID de la cabaña si es 'unit'
            'file_path'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'file_type'   => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'image'], // 'image' o 'video'
            'is_main'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0], // 1 = Foto principal/Portada
            'sort_order'  => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        // No ponemos llave foránea estricta a entity_id porque puede ser de diferentes tablas, lo manejamos por código.
        $this->forge->createTable('tenant_media');
    }

    public function down() { $this->forge->dropTable('tenant_media'); }
}