<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadSourceModel extends Model
{
    protected $table         = 'lead_sources';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name','type','is_active'];
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getActive(): array
    {
        return $this->where('is_active', 1)->orderBy('name','ASC')->findAll();
    }
}
