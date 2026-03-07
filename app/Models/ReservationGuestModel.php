<?php
namespace App\Models;

use CodeIgniter\Model;

class ReservationGuestModel extends Model
{
    protected $table         = 'reservation_guests';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['reservation_id', 'first_name', 'last_name', 'doc_type', 'doc_number', 'relationship'];
    protected $useTimestamps = true;
    protected $updatedField  = null; // No tenemos updated_at en esta tabla
}