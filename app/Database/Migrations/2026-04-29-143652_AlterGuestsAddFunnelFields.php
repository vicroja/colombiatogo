<?php
// app/Database/Migrations/XXXX_AlterGuestsAddFunnelFields.php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterGuestsAddFunnelFields extends Migration
{
    public function up()
    {
        // funnel_stage: en qué punto del proceso de venta/atención está el guest
        $this->forge->addColumn('guests', [
            'funnel_stage' => [
                'type'       => 'ENUM',
                'constraint' => ['cold', 'interested', 'evaluating', 'objecting', 'ready_close', 'post_booking'],
                'default'    => 'cold',
                'after'      => 'chat_state',
            ],
            // Persiste estado de la conversación entre mensajes:
            // objeciones detectadas, precio revelado, tours consultados, etc.
            'conversation_context_json' => [
                'type'  => 'JSON',
                'null'  => true,
                'after' => 'funnel_stage',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('guests', ['funnel_stage', 'conversation_context_json']);
    }
}