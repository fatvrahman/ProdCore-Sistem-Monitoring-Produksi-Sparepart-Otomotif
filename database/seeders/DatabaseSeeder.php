<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Operator',
            'email' => 'operator@prodcore.com',
            'password' => bcrypt('123'),
            'role' => 'operator'
        ]);
        User::create([
            'name' => 'QC',
            'email' => 'qc@prodcore.com',
            'password' => bcrypt('123'),
            'role' => 'qc'
        ]);
        User::create([
            'name' => 'Gudang',
            'email' => 'gudang@prodcore.com',
            'password' => bcrypt('123'),
            'role' => 'gudang'
        ]);
        User::create([
            'name' => 'Admin',
            'email' => 'admin@prodcore.com',
            'password' => bcrypt('123'),
            'role' => 'admin'
        ]);
    }
}
