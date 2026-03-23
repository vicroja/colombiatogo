<?php

namespace App\Models;

use CodeIgniter\Model;

class ReservationGuestModel extends Model
{
    protected $table            = 'reservation_guests';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    // Campos permitidos basados en tu esquema SQL
    protected $allowedFields    = [
        'reservation_id',
        'first_name',
        'last_name',
        'doc_type',
        'doc_number',
        'relationship',
        'created_at'
    ];

    protected $useTimestamps = false; // La tabla solo tiene created_at, usaremos datetime default
}