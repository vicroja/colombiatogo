<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWhatsappMessagesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'whatsapp_message_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'direction' => ['type' => 'ENUM', 'constraint' => ['incoming', 'outgoing']],
            'sender_phone' => ['type' => 'VARCHAR', 'constraint' => 25, 'null' => true],
            'recipient_phone' => ['type' => 'VARCHAR', 'constraint' => 25, 'null' => true],
            'message_body' => ['type' => 'TEXT', 'null' => true],
            'message_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'text'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'whatsapp_timestamp' => ['type' => 'DATETIME', 'null' => true],
            'raw_data' => ['type' => 'JSON', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'openai_thread' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'estado' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'conversation_state' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'appointment_id_relation' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'media_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'interactive_data' => ['type' => 'JSON', 'null' => true],
            'template_data' => ['type' => 'JSON', 'null' => true],
            'error_details' => ['type' => 'TEXT', 'null' => true],
            'is_saas' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'tenant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 99],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('whatsapp_message_id');
        $this->forge->addKey('direction');
        $this->forge->addKey('sender_phone');
        $this->forge->addKey('recipient_phone');
        $this->forge->addKey('created_at');
        $this->forge->createTable('whatsapp_messages');
    }

    public function down()
    {
        $this->forge->dropTable('whatsapp_messages');
    }
}