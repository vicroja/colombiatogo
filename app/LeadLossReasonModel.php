<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadLossReasonModel extends Model
{
    protected $table         = 'lead_loss_reasons';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name','is_active'];
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getActive(): array
    {
        return $this->where('is_active', 1)->orderBy('name','ASC')->findAll();
    }
}
