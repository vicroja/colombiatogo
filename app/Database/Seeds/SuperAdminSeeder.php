<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'name'          => 'Victor Rojas', // Tu usuario SuperAdmin
            'email'         => 'admin@pms.com',
            'password_hash' => password_hash('Admin123!', PASSWORD_BCRYPT), // Contraseña segura hasheada
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        // Insertar en la tabla super_admins
        $this->db->table('super_admins')->insert($data);
    }
}