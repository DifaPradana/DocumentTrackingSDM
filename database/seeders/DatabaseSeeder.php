<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Role::create([
            'nama_role' => 'Admin',
        ]);

        Role::create([
            'nama_role' => 'Office',
        ]);

        Role::create([
            'nama_role' => 'ADM Kilang',
        ]);

        User::create([
            'nama_karyawan' => 'admin',
            'password' => '61b838edadbae4df9b75',
            'role_id' => 1,
        ]);

        User::create([
            'nama_karyawan' => 'difa pradana',
            'password' => 'dips',
            'role_id' => 2,
        ]);

        User::create([
            'nama_karyawan' => 'testcuy',
            'password' => 'dips',
            'role_id' => 3,
        ]);
    }
}
