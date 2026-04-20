<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGuestCrmTables extends Migration
{
    public function up(): void
    {
        // ── Notas manuales del personal sobre el huésped ──────────────────
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true,
                'auto_increment' => true],
            'tenant_id'  => ['type' => 'INT', 'unsigned' => true],
            'guest_id'   => ['type' => 'INT', 'unsigned' => true],
            'note'       => ['type' => 'TEXT'],
            'created_by' => ['type' => 'INT', 'unsigned' => true,
                'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('guest_id');
        $this->forge->createTable('guest_notes');

        // ── Historial de mensajes CRM enviados ────────────────────────────
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true,
                'auto_increment' => true],
            'tenant_id'    => ['type' => 'INT', 'unsigned' => true],
            'guest_id'     => ['type' => 'INT', 'unsigned' => true],
            'channel'      => ['type' => 'ENUM',
                'constraint' => ['whatsapp', 'email'],
                'default' => 'whatsapp'],
            'message_body' => ['type' => 'TEXT'],
            'ai_generated' => ['type' => 'TINYINT', 'constraint' => 1,
                'default' => 0],
            'sent_at'      => ['type' => 'DATETIME', 'null' => true],
            'status'       => ['type' => 'ENUM',
                'constraint' => ['draft','sent','delivered'],
                'default' => 'draft'],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');
        $this->forge->addKey('guest_id');
        $this->forge->createTable('crm_messages');
    }

    public function down(): void
    {
        $this->forge->dropTable('crm_messages',  true);
        $this->forge->dropTable('guest_notes',   true);
    }
}