<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * LeadSettingsModel
 * Key/value de configuración del módulo de leads.
 */
class LeadSettingsModel extends Model
{
    protected $table         = 'lead_settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['key_name','value','description','updated_at'];
    protected $useTimestamps = false;

    public function getValue(string $key, $default = null)
    {
        $row = $this->where('key_name', $key)->first();
        return $row['value'] ?? $default;
    }

    public function setValue(string $key, $value): bool
    {
        $row = $this->where('key_name', $key)->first();
        $data = [
            'value'      => (string)$value,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($row) {
            return $this->update($row['id'], $data);
        }
        $data['key_name'] = $key;
        return (bool)$this->insert($data);
    }
}
