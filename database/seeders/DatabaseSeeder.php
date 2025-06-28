<?php
// File: database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Main seeder yang menjalankan semua seeder dalam urutan yang benar
     */
    public function run(): void
    {
        echo "🚀 Memulai seeding database ProdCore...\n\n";

        // PENTING: Nonaktifkan foreign key checks untuk MySQL
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Kosongkan tabel yang mungkin sudah ada data
        \DB::table('users')->truncate();
        \DB::table('roles')->truncate();
        \DB::table('product_types')->truncate();
        \DB::table('production_lines')->truncate();
        \DB::table('raw_materials')->truncate();
        \DB::table('machines')->truncate();

        // Aktifkan kembali foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Urutan seeder sangat penting karena ada foreign key constraints
        $this->call([
            // 1. Master data tanpa foreign key
            RoleSeeder::class,
            ProductTypeSeeder::class,
            ProductionLineSeeder::class,
            RawMaterialSeeder::class,
            
            // 2. Data dengan foreign key
            UserSeeder::class,           // Membutuhkan roles
            MachineSeeder::class,        // Membutuhkan production_lines
            
            // 3. Transactional data (opsional untuk development)
            // ProductionSeeder::class,     // Membutuhkan users, product_types, machines
            // QualityControlSeeder::class, // Membutuhkan productions, users
            // StockMovementSeeder::class,  // Membutuhkan raw_materials, users
            // DistributionSeeder::class,   // Membutuhkan users, product_types
        ]);

        echo "\n✅ Seeding database selesai!\n";
        echo "📊 Database ProdCore siap digunakan.\n\n";
        echo "🔐 Login Credentials:\n";
        echo "   👑 Admin: admin@prodcore.com / admin123\n";
        echo "   🔧 Operator: budi.operator@prodcore.com / password\n";
        echo "   🔍 QC: maya.qc@prodcore.com / password\n";
        echo "   📦 Gudang: tono.gudang@prodcore.com / password\n\n";
        echo "🌐 Jalankan: php artisan serve\n";
        echo "📱 Akses: http://localhost:8000\n";
    }
}