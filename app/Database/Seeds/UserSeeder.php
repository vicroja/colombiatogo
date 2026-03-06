<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Buscamos la primera propiedad (tenant) que creaste desde el SuperAdmin
        $tenant = $this->db->table('tenants')->get()->getRowArray();

        if (!$tenant) {
            echo "No hay propiedades creadas. Ve al SuperAdmin y crea una primero.\n";
            return;
        }

        $data = [
            'tenant_id'     => $tenant['id'],
            'name'          => 'Admin Propiedad',
            'email'         => 'gerente@propiedad.com',
            'password_hash' => password_hash('Hotel123!', PASSWORD_BCRYPT),
            'role'          => 'admin',
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $this->db->table('users')->insert($data);
    }
}