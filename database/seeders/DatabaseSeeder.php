<?php

namespace Database\Seeders;

use App\Models\Departement;
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

        Role::create([
            'nama_role' => 'Pengantar',
        ]);

        User::create([
            'nama_karyawan' => 'admin',
            'password' => '61b838edadbae4df9b75',
            'role_id' => 1,
        ]);

        Departement::create([
            'nama_departement' => 'MA 1',
        ]);
        Departement::create([
            'nama_departement' => 'MA 2',
        ]);
        Departement::create([
            'nama_departement' => 'MA 3',
        ]);
        Departement::create([
            'nama_departement' => 'MA 4',
        ]);
        Departement::create([
            'nama_departement' => 'MA 5',
        ]);
        Departement::create([
            'nama_departement' => 'MA 6',
        ]);
        Departement::create([
            'nama_departement' => 'MA 7',
        ]);
        Departement::create([
            'nama_departement' => 'GEN MAINT',
        ]);
        Departement::create([
            'nama_departement' => 'LOC 1',
        ]);
        Departement::create([
            'nama_departement' => 'LOC 2',
        ]);
        Departement::create([
            'nama_departement' => 'LOC 3',
        ]);

        Departement::create([
            'nama_departement' => 'FOC 1',
        ]);

        Departement::create([
            'nama_departement' => 'FOC 2',
        ]);

        Departement::create([
            'nama_departement' => 'KPC',
        ]);

        Departement::create([
            'nama_departement' => 'OM 60 & NBM',
        ]);
        Departement::create([
            'nama_departement' => 'UTL 05, 50, 500',
        ]);
        Departement::create([
            'nama_departement' => 'UTL RFCC',
        ]);
        Departement::create([
            'nama_departement' => 'RFCC',
        ]);
        Departement::create([
            'nama_departement' => 'ISOM LNHT',
        ]);
        Departement::create([
            'nama_departement' => 'LABORATORIUM',
        ]);
        Departement::create([
            'nama_departement' => 'PPTL',
        ]);
        Departement::create([
            'nama_departement' => 'OM 70',
        ]);

        Departement::create([
            'nama_departement' => 'WORKSHOP',
        ]);
        Departement::create([
            'nama_departement' => 'HSSE',
        ]);
        Departement::create([
            'nama_departement' => 'ME 1',
        ]);
        Departement::create([
            'nama_departement' => 'ME 2',
        ]);
        Departement::create([
            'nama_departement' => 'PROD 1',
        ]);
        Departement::create([
            'nama_departement' => 'PROD 2',
        ]);
        Departement::create([
            'nama_departement' => 'PROD 3',
        ]);
        Departement::create([
            'nama_departement' => 'EIIE',
        ]);
        Departement::create([
            'nama_departement' => 'GROUP HEAD / SMOM',
        ]);
    }
}
