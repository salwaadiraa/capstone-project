<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{Role, User, Pegawai, ApMethod};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['Admin', 'Pegawai', 'SPV', 'Finance'];
        foreach ($roles as $r) {
            Role::create(['nama_role' => $r]);
        }

        $users = [
            ['name' => 'Admin Sistem', 'username' => 'admin', 'role_id' => 1],
            ['name' => 'Budi Santoso', 'username' => 'pegawai', 'role_id' => 2],
            ['name' => 'Andi Wijaya', 'username' => 'spv', 'role_id' => 3],
            ['name' => 'Siti Aminah', 'username' => 'finance', 'role_id' => 4],
        ];

        foreach ($users as $u) {
            $user = User::create([
                'name' => $u['name'],
                'username' => $u['username'],
                'role_id' => $u['role_id'],
                'password' => Hash::make('123456'),
            ]);

            Pegawai::create([
                'user_id' => $user->id,
                'nama_lengkap' => $u['name'],
                'nik' => 'EMP-00' . $user->id,
                'ttl' => 'Jakarta, 01-01-1995'
            ]);

            ApMethod::create([
                'user_id' => $user->id,
                'nama_bank' => 'BCA',
                'no_rekening' => '123456789' . $user->id,
                'atas_nama' => $u['name']
            ]);
        }
    }
}