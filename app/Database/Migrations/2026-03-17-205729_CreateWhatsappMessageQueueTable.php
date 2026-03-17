<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWhatsappMessageQueueTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'autowhatsapptemplate_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'recipient_phone' => ['type' => 'VARCHAR', 'constraint' => 25],
            'recipient_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'related_appointment_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'related_openai_thread' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'related_patient_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'related_guest_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true], // Era related_owner_id
            'related_doctor_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'related_referred_by_proveedor_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'related_initial_service_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'related_case_history_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'related_prescription_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'related_lab_ids_csv' => ['type' => 'TEXT', 'null' => true],
            'tenant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 99],
            'shortcode_data_override_json' => ['type' => 'TEXT', 'null' => true],
            'scheduled_send_datetime_utc' => ['type' => 'DATETIME'],
            'processing_status' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'PENDING'],
            'send_attempts' => ['type' => 'TINYINT', 'constraint' => 3, 'default' => 0],
            'last_attempt_datetime_utc' => ['type' => 'DATETIME', 'null' => true],
            'sent_whatsapp_message_id' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'response_interaction_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'error_log' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'is_saas' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('autowhatsapptemplate_id');
        $this->forge->addKey(['scheduled_send_datetime_utc', 'processing_status', 'tenant_id']);
        $this->forge->addKey('related_appointment_id');
        $this->forge->addKey('related_openai_thread');
        $this->forge->createTable('whatsapp_message_queue');
    }

    public function down()
    {
        $this->forge->dropTable('whatsapp_message_queue');
    }
}