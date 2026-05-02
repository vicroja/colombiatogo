<?php
/**
 * Script de migración: Regenerar prompts de T5 y T8 con PmsPromptBuilder v2.0
 *
 * Ejecutar UNA VEZ desde CLI:
 *   php spark run:migration-prompt  (si lo defines como Command)
 *   o directamente: php public/index.php (adaptado como endpoint protegido)
 *
 * Hace backup en ai_prompts_backup antes de sobrescribir.
 */

// Bootstrap CI4 (ajustar path si es necesario)
// require_once ROOTPATH . 'app/Services/PmsPromptBuilder.php';
// require_once ROOTPATH . 'app/Services/ToolsSchemaBuilder.php';

// Para ejecutar como script standalone con CI4:
// cd /var/www/html && php -r "define('FCPATH', __DIR__.'/public/'); require 'public/index.php';"

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\PmsPromptBuilder;

class MigratePrompts extends BaseCommand
{
    protected $group       = 'custom';
    protected $name        = 'prompts:migrate';
    protected $description = 'Migra los prompts de tenants al formato PmsPromptBuilder v2.0';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        // ── BACKUP PREVENTIVO ─────────────────────────────────────────────────
        CLI::write('Creando backup de ai_prompts...', 'yellow');
        $db->query("
            CREATE TABLE IF NOT EXISTS `ai_prompts_backup_" . date('Ymd_His') . "` AS
            SELECT * FROM `ai_prompts`
        ");
        CLI::write('Backup creado.', 'green');

        // ── CONFIGURACIÓN DE TENANTS A MIGRAR ─────────────────────────────────
        $tenants = [
            [
                'id'              => 5,
                'assistant_name'  => 'Adrianita',
                'tone'            => 'formal',
                'has_accommodation'=> true,
                'has_tours'       => true,
            ],
            [
                'id'              => 8,
                'assistant_name'  => 'Alfonsino',
                'tone'            => 'formal',
                'has_accommodation'=> true,
                'has_tours'       => true,
            ],
        ];

        foreach ($tenants as $config) {
            $tenantId = $config['id'];

            CLI::write("Procesando Tenant {$tenantId} ({$config['assistant_name']})...", 'cyan');

            // Cargar datos del tenant desde BD
            $tenant = $db->table('tenants')->where('id', $tenantId)->get()->getRowArray();

            if (!$tenant) {
                CLI::error("  Tenant {$tenantId} no encontrado. Saltando.");
                continue;
            }

            $result = PmsPromptBuilder::saveForTenant(
                tenantId:         $tenantId,
                tenant:           $tenant,
                hasAccommodation: $config['has_accommodation'],
                hasTours:         $config['has_tours'],
                assistantName:    $config['assistant_name'],
                tone:             $config['tone']
            );

            if ($result) {
                CLI::write("  OK: Prompt v2.0 generado y guardado para Tenant {$tenantId}.", 'green');
            } else {
                CLI::error("  ERROR: Fallo al guardar prompt para Tenant {$tenantId}.");
            }
        }

        // ── VERIFICACIÓN POST-MIGRACIÓN ───────────────────────────────────────
        CLI::write("\nVerificación post-migración:", 'yellow');

        $rows = $db->query("
            SELECT tenant_id,
                   JSON_LENGTH(tools_schema_json) AS num_tools,
                   CASE WHEN system_instruction LIKE '%metadata%' THEN 'SI' ELSE 'NO' END AS tiene_metadata,
                   CASE WHEN system_instruction LIKE '%funnel_stage%' THEN 'SI' ELSE 'NO' END AS tiene_funnel,
                   updated_at
            FROM ai_prompts
            WHERE profile_role = 'assistant'
              AND tenant_id IN (5, 8)
            ORDER BY tenant_id
        ")->getResult();

        foreach ($rows as $row) {
            CLI::write(sprintf(
                "  Tenant %d | tools: %d | metadata: %s | funnel: %s | actualizado: %s",
                $row->tenant_id,
                $row->num_tools,
                $row->tiene_metadata,
                $row->tiene_funnel,
                $row->updated_at
            ), $row->tiene_metadata === 'SI' ? 'green' : 'red');
        }

        CLI::write("\nMigración completada.", 'green');
    }
}