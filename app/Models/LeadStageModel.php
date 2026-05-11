<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadStageModel extends Model
{
    protected $table         = 'lead_stages';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name','slug','order_position','probability','is_won','is_lost','color','is_active'];
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getActiveOrdered(): array
    {
        return $this->where('is_active', 1)->orderBy('order_position','ASC')->findAll();
    }
}
