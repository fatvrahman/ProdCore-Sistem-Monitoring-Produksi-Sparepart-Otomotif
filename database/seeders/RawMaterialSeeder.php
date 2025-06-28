<?php
// File: database/seeders/RawMaterialSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RawMaterial;

class RawMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder untuk membuat bahan baku produksi brakepad
     */
    public function run(): void
    {
        $rawMaterials = [
            [
                'code' => 'MAT001',
                'name' => 'Serbuk Logam Tembaga',
                'unit' => 'kg',
                'current_stock' => 1500.00,
                'minimum_stock' => 200.00,
                'maximum_stock' => 2000.00,
                'unit_price' => 85000.00,
                'supplier' => 'PT. Logam Mulia',
                'description' => 'Serbuk tembaga berkualitas tinggi untuk campuran brakepad',
                'is_active' => true
            ],
            [
                'code' => 'MAT002',
                'name' => 'Resin Phenolic',
                'unit' => 'liter',
                'current_stock' => 800.50,
                'minimum_stock' => 100.00,
                'maximum_stock' => 1000.00,
                'unit_price' => 125000.00,
                'supplier' => 'CV. Kimia Jaya',
                'description' => 'Resin pengikat untuk struktur brakepad',
                'is_active' => true
            ],
            [
                'code' => 'MAT003',
                'name' => 'Serat Aramid',
                'unit' => 'kg',
                'current_stock' => 250.75,
                'minimum_stock' => 50.00,
                'maximum_stock' => 400.00,
                'unit_price' => 275000.00,
                'supplier' => 'PT. Fiber Indonesia',
                'description' => 'Serat aramid untuk kekuatan dan tahan panas',
                'is_active' => true
            ],
            [
                'code' => 'MAT004',
                'name' => 'Serbuk Besi',
                'unit' => 'kg',
                'current_stock' => 2200.00,
                'minimum_stock' => 300.00,
                'maximum_stock' => 3000.00,
                'unit_price' => 45000.00,
                'supplier' => 'CV. Metal Works',
                'description' => 'Serbuk besi grade industri untuk base material',
                'is_active' => true
            ],
            [
                'code' => 'MAT005',
                'name' => 'Graphite Powder',
                'unit' => 'kg',
                'current_stock' => 180.25,
                'minimum_stock' => 30.00,
                'maximum_stock' => 250.00,
                'unit_price' => 95000.00,
                'supplier' => 'PT. Carbon Tech',
                'description' => 'Bubuk graphite untuk pelumas dan konduktivitas panas',
                'is_active' => true
            ],
            [
                'code' => 'MAT006',
                'name' => 'Ceramic Filler',
                'unit' => 'kg',
                'current_stock' => 450.50,
                'minimum_stock' => 80.00,
                'maximum_stock' => 600.00,
                'unit_price' => 155000.00,
                'supplier' => 'CV. Ceramic Plus',
                'description' => 'Filler keramik untuk brakepad tipe ceramic',
                'is_active' => true
            ],
            [
                'code' => 'MAT007',
                'name' => 'Steel Wool',
                'unit' => 'kg',
                'current_stock' => 95.75,
                'minimum_stock' => 20.00,
                'maximum_stock' => 150.00,
                'unit_price' => 65000.00,
                'supplier' => 'PT. Steel Indonesia',
                'description' => 'Steel wool untuk tekstur dan friction',
                'is_active' => true
            ],
            [
                'code' => 'MAT008',
                'name' => 'Rubber Binder',
                'unit' => 'liter',
                'current_stock' => 320.00,
                'minimum_stock' => 50.00,
                'maximum_stock' => 400.00,
                'unit_price' => 75000.00,
                'supplier' => 'CV. Rubber Tech',
                'description' => 'Pengikat karet untuk fleksibilitas',
                'is_active' => true
            ],
            [
                'code' => 'MAT009',
                'name' => 'Backing Plate',
                'unit' => 'pcs',
                'current_stock' => 5000.00,
                'minimum_stock' => 500.00,
                'maximum_stock' => 8000.00,
                'unit_price' => 15000.00,
                'supplier' => 'PT. Metal Stamping',
                'description' => 'Plat backing berbagai ukuran untuk brakepad',
                'is_active' => true
            ],
            [
                'code' => 'MAT010',
                'name' => 'Anti-Noise Shim',
                'unit' => 'pcs',
                'current_stock' => 3500.00,
                'minimum_stock' => 300.00,
                'maximum_stock' => 5000.00,
                'unit_price' => 5000.00,
                'supplier' => 'CV. Noise Control',
                'description' => 'Shim anti-noise untuk mengurangi bunyi',
                'is_active' => true
            ]
        ];

        foreach ($rawMaterials as $materialData) {
            RawMaterial::create($materialData);
        }

        echo "✅ RawMaterial seeder berhasil dijalankan!\n";
        echo "   🧱 10 jenis bahan baku telah dibuat:\n";
        echo "   - Serbuk Logam: Tembaga, Besi\n";
        echo "   - Pengikat: Resin Phenolic, Rubber Binder\n";
        echo "   - Serat: Aramid, Steel Wool\n";
        echo "   - Filler: Graphite, Ceramic\n";
        echo "   - Komponen: Backing Plate, Anti-Noise Shim\n";
    }
}