<?php
namespace App\Models;
use CodeIgniter\Model;

class ReservationGuestModel extends Model
{
    protected $table      = 'reservation_guests';
    protected $primaryKey = 'id';

    // 1. APAGA los timestamps automáticos de CodeIgniter para evitar el error
    protected $useTimestamps = false;

    // 2. Asegúrate de que estos sean tus allowedFields EXACTOS
    protected $allowedFields = [
        'reservation_id',
        'first_name',
        'last_name',
        'doc_type',
        'doc_number',
        'relationship'
    ];
}