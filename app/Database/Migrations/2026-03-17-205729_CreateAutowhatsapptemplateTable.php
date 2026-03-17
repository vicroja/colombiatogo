<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAutowhatsapptemplateTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'type' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'trigger_event_source' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'trigger_condition_field' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'trigger_delay_value' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'trigger_delay_unit' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'recipient_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'GUEST'], // Cambiado de OWNER a GUEST
            'whatsapp_message_format' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'TEXT'],
            'meta_template_base_text' => ['type' => 'TEXT', 'null' => true],
            'message_body_text' => ['type' => 'TEXT', 'null' => true],
            'meta_template_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'meta_template_language_code' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'es'],
            'meta_template_components_config_json' => ['type' => 'TEXT', 'null' => true],
            'interactive_config_json' => ['type' => 'TEXT', 'null' => true],
            'static_media_url' => ['type' => 'VARCHAR', 'constraint' => 1024, 'null' => true],
            'static_media_caption' => ['type' => 'TEXT', 'null' => true],
            'static_document_filename' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'priority' => ['type' => 'TINYINT', 'constraint' => 3, 'default' => 10],
            'status' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Inactive'],
            'tenant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 98], // Reemplaza hospital_id
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'type', 'status']);
        $this->forge->addKey(['tenant_id', 'trigger_event_source', 'status']);
        $this->forge->createTable('autowhatsapptemplate');
    }

    public function down()
    {
        $this->forge->dropTable('autowhatsapptemplate');
    }
}