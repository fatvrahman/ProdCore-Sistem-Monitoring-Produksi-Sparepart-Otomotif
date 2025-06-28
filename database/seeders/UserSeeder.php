<?php
// File: database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder untuk membuat user dummy dengan berbagai role
     */
    public function run(): void
    {
        try {
            // Ambil role yang sudah dibuat
            $adminRole = Role::where('name', 'admin')->first();
            $operatorRole = Role::where('name', 'operator')->first();
            $qcRole = Role::where('name', 'qc')->first();
            $gudangRole = Role::where('name', 'gudang')->first();

            // Cek apakah role sudah ada
            if (!$adminRole || !$operatorRole || !$qcRole || !$gudangRole) {
                echo "❌ Error: Role belum dibuat! Jalankan RoleSeeder terlebih dahulu.\n";
                return;
            }

            $users = [
                // Admin Users
                [
                    'name' => 'Admin ProdCore',
                    'email' => 'admin@prodcore.com',
                    'password' => Hash::make('admin123'),
                    'role_id' => $adminRole->id,
                    'employee_id' => 'ADM001',
                    'phone' => '081234567890',
                    'status' => 'active'
                ],
                
                // Operator Users
                [
                    'name' => 'Budi Santoso',
                    'email' => 'budi.operator@prodcore.com',
                    'password' => Hash::make('password'),
                    'role_id' => $operatorRole->id,
                    'employee_id' => 'OP001',
                    'phone' => '081234567891',
                    'status' => 'active'
                ],
                [
                    'name' => 'Sari Wulandari',
                    'email' => 'sari.operator@prodcore.com',
                    'password' => Hash::make('password'),
                    'role_id' => $operatorRole->id,
                    'employee_id' => 'OP002',
                    'phone' => '081234567892',
                    'status' => 'active'
                ],
                [
                    'name' => 'Ahmad Rizki',
                    'email' => 'ahmad.operator@prodcore.com',
                    'password' => Hash::make('password'),
                    'role_id' => $operatorRole->id,
                    'employee_id' => 'OP003',
                    'phone' => '081234567893',
                    'status' => 'active'
                ],
                
                // QC Users
                [
                    'name' => 'Maya QC',
                    'email' => 'maya.qc@prodcore.com',
                    'password' => Hash::make('password'),
                    'role_id' => $qcRole->id,
                    'employee_id' => 'QC001',
                    'phone' => '081234567894',
                    'status' => 'active'
                ],
                [
                    'name' => 'Denny Inspektur',
                    'email' => 'denny.qc@prodcore.com',
                    'password' => Hash::make('password'),
                    'role_id' => $qcRole->id,
                    'employee_id' => 'QC002',
                    'phone' => '081234567895',
                    'status' => 'active'
                ],
                
                // Gudang Users
                [
                    'name' => 'Tono Gudang',
                    'email' => 'tono.gudang@prodcore.com',
                    'password' => Hash::make('password'),
                    'role_id' => $gudangRole->id,
                    'employee_id' => 'GDG001',
                    'phone' => '081234567896',
                    'status' => 'active'
                ],
                [
                    'name' => 'Rina Distribusi',
                    'email' => 'rina.gudang@prodcore.com',
                    'password' => Hash::make('password'),
                    'role_id' => $gudangRole->id,
                    'employee_id' => 'GDG002',
                    'phone' => '081234567897',
                    'status' => 'active'
                ]
            ];

            foreach ($users as $userData) {
                User::create($userData);
            }

            echo "✅ User seeder berhasil dijalankan!\n";
            echo "   📧 Login Credentials:\n";
            echo "   👑 Admin: admin@prodcore.com / admin123\n";
            echo "   🔧 Operator: budi.operator@prodcore.com / password\n";
            echo "   🔍 QC: maya.qc@prodcore.com / password\n";
            echo "   📦 Gudang: tono.gudang@prodcore.com / password\n";
            
        } catch (\Exception $e) {
            echo "❌ Error di UserSeeder: " . $e->getMessage() . "\n";
            echo "   File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
        }
    }
}