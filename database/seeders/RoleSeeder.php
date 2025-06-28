<?php
// File: database/seeders/RoleSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder untuk membuat role/peran user dalam sistem
     */
    public function run(): void
    {
        try {
            $roles = [
                [
                    'name' => 'admin',
                    'display_name' => 'Administrator',
                    'description' => 'Administrator sistem dengan akses penuh',
                    'permissions' => [
                        'dashboard.view',
                        'users.view', 'users.create', 'users.edit', 'users.delete',
                        'productions.view', 'productions.create', 'productions.edit', 'productions.delete',
                        'quality_controls.view', 'quality_controls.create', 'quality_controls.edit', 'quality_controls.delete',
                        'stock_movements.view', 'stock_movements.create', 'stock_movements.edit', 'stock_movements.delete',
                        'distributions.view', 'distributions.create', 'distributions.edit', 'distributions.delete',
                        'reports.view', 'reports.export',
                        'master_data.view', 'master_data.edit'
                    ],
                    'is_active' => true
                ],
                [
                    'name' => 'operator',
                    'display_name' => 'Operator Produksi',
                    'description' => 'Operator produksi untuk input data harian',
                    'permissions' => [
                        'dashboard.view',
                        'productions.view', 'productions.create', 'productions.edit',
                        'stock_movements.view'
                    ],
                    'is_active' => true
                ],
                [
                    'name' => 'qc',
                    'display_name' => 'Quality Control',
                    'description' => 'Quality Control untuk inspeksi dan laporan kualitas',
                    'permissions' => [
                        'dashboard.view',
                        'productions.view',
                        'quality_controls.view', 'quality_controls.create', 'quality_controls.edit',
                        'reports.view'
                    ],
                    'is_active' => true
                ],
                [
                    'name' => 'gudang',
                    'display_name' => 'Tim Gudang & Distribusi',
                    'description' => 'Tim gudang untuk monitoring stok dan distribusi',
                    'permissions' => [
                        'dashboard.view',
                        'stock_movements.view', 'stock_movements.create', 'stock_movements.edit',
                        'distributions.view', 'distributions.create', 'distributions.edit',
                        'reports.view'
                    ],
                    'is_active' => true
                ]
            ];

            foreach ($roles as $roleData) {
                Role::create($roleData);
            }

            echo "✅ Role seeder berhasil dijalankan!\n";
            echo "   - Admin: Akses penuh sistem\n";
            echo "   - Operator: Input data produksi\n";
            echo "   - QC: Inspeksi kualitas\n";
            echo "   - Gudang: Monitoring stok & distribusi\n";
            
        } catch (\Exception $e) {
            echo "❌ Error di RoleSeeder: " . $e->getMessage() . "\n";
        }
    }
}