<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddChatStatesToGuests extends Migration
{
    public function up()
    {
        $fields = [
            'ai_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '1 = IA activa, 0 = Humano',
                'after'      => 'phone' // O ajusta según la estructura de tu tabla
            ],
            'chat_state' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'CLOSED',
                'comment'    => 'ACTIVE, CLOSED, WAITING_USER, WINDOW_CLOSED, OMITTED',
                'after'      => 'ai_active'
            ],
        ];

        $this->forge->addColumn('guests', $fields);

        // Índice para búsquedas rápidas en el dashboard
        $this->db->query('CREATE INDEX idx_guest_state ON guests (tenant_id, chat_state)');
        log_message('info', '[Migración] Campos ai_active y chat_state añadidos a guests.');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE guests DROP INDEX idx_guest_state');
        $this->forge->dropColumn('guests', ['ai_active', 'chat_state']);
    }
}