<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenantWebsites extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tenant_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'theme_slug'      => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'boutique'], // 'boutique', 'resort', 'corporate'
            'primary_color'   => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '#2E75B6'],
            'hero_title'      => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true], // Ej: "Escapa a la naturaleza"
            'hero_subtitle'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'about_text'      => ['type' => 'TEXT', 'null' => true],
            'policies_text'   => ['type' => 'TEXT', 'null' => true], // Letra chica para los huéspedes
            'instagram_url'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'facebook_url'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'whatsapp_number' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'is_published'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0], // 1 = Web activa
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // Aseguramos que un hotel solo tenga 1 configuración de página web
        $this->forge->addUniqueKey('tenant_id');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tenant_websites');
    }

    public function down() { $this->forge->dropTable('tenant_websites'); }
}