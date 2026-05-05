<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Generado con:
 *   php spark make:migration AddTagToTenantMedia
 *
 * Archivo resultante (ejemplo de nombre real que genera Spark):
 *   app/Database/Migrations/2025-05-05-000001_AddTagToTenantMedia.php
 */
class AddTagToTenantMedia extends Migration
{
    public function up(): void
    {
        // Agregar columna tag después de file_type
        $this->forge->addColumn('tenant_media', [
            'tag' => [
                'type'       => "ENUM('rut','cuenta_bancaria','certificado_camara','poliza_seguro','contrato','otro')",
                'null'       => false,
                'default'    => 'otro',
                'after'      => 'file_type',
            ],
        ]);

        // Índice para consultas rápidas del asistente virtual (Etapa 2)
        $this->db->query('
            CREATE INDEX idx_tenant_media_tenant_tag
            ON tenant_media (tenant_id, tag)
        ');
    }

    public function down(): void
    {
        // Eliminar índice primero, luego la columna
        $this->db->query('DROP INDEX idx_tenant_media_tenant_tag ON tenant_media');
        $this->forge->dropColumn('tenant_media', 'tag');
    }
}