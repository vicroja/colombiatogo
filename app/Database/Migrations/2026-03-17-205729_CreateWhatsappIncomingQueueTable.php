<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWhatsappIncomingQueueTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'payload' => ['type' => 'JSON', 'null' => false],
            'status' => ['type' => 'ENUM', 'constraint' => ['PENDING', 'PROCESSING', 'COMPLETED', 'FAILED'], 'default' => 'PENDING'],
            'attempts' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
            'error_details' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'processed_at' => ['type' => 'DATETIME', 'null' => true],
            'tenant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 98],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->createTable('whatsapp_incoming_queue');
    }

    public function down()
    {
        $this->forge->dropTable('whatsapp_incoming_queue');
    }
}