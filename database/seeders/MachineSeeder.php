<?php
// File: database/seeders/MachineSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Machine;
use App\Models\ProductionLine;

class MachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder untuk membuat mesin-mesin produksi
     */
    public function run(): void
    {
        // Ambil production lines yang sudah dibuat
        $lineA = ProductionLine::where('code', 'LINE-A')->first();
        $lineB = ProductionLine::where('code', 'LINE-B')->first();
        $lineC = ProductionLine::where('code', 'LINE-C')->first();
        $lineD = ProductionLine::where('code', 'LINE-D')->first();

        $machines = [
            // Mesin di LINE-A
            [
                'code' => 'MCH001',
                'name' => 'Hydraulic Press A1',
                'production_line_id' => $lineA->id,
                'brand' => 'Komatsu',
                'model' => 'HP-500',
                'manufacture_year' => 2020,
                'capacity_per_hour' => 250,
                'status' => 'running',
                'last_maintenance_date' => now()->subDays(15),
                'next_maintenance_date' => now()->addDays(15),
                'notes' => 'Mesin press utama untuk forming brakepad'
            ],
            [
                'code' => 'MCH002',
                'name' => 'Hydraulic Press A2',
                'production_line_id' => $lineA->id,
                'brand' => 'Komatsu',
                'model' => 'HP-500',
                'manufacture_year' => 2020,
                'capacity_per_hour' => 250,
                'status' => 'running',
                'last_maintenance_date' => now()->subDays(10),
                'next_maintenance_date' => now()->addDays(20),
                'notes' => 'Mesin press cadangan LINE-A'
            ],
            [
                'code' => 'MCH003',
                'name' => 'Curing Oven A1',
                'production_line_id' => $lineA->id,
                'brand' => 'Nabertherm',
                'model' => 'CO-800',
                'manufacture_year' => 2019,
                'capacity_per_hour' => 300,
                'status' => 'running',
                'last_maintenance_date' => now()->subDays(5),
                'next_maintenance_date' => now()->addDays(25),
                'notes' => 'Oven curing untuk pengerasan brakepad'
            ],

            // Mesin di LINE-B
            [
                'code' => 'MCH004',
                'name' => 'Hydraulic Press B1',
                'production_line_id' => $lineB->id,
                'brand' => 'Schuler',
                'model' => 'SP-400',
                'manufacture_year' => 2018,
                'capacity_per_hour' => 200,
                'status' => 'running',
                'last_maintenance_date' => now()->subDays(20),
                'next_maintenance_date' => now()->addDays(10),
                'notes' => 'Mesin press LINE-B'
            ],
            [
                'code' => 'MCH005',
                'name' => 'Grinding Machine B1',
                'production_line_id' => $lineB->id,
                'brand' => 'Okamoto',
                'model' => 'GM-300',
                'manufacture_year' => 2019,
                'capacity_per_hour' => 150,
                'status' => 'idle',
                'last_maintenance_date' => now()->subDays(8),
                'next_maintenance_date' => now()->addDays(22),
                'notes' => 'Mesin grinding untuk finishing'
            ],

            // Mesin di LINE-C
            [
                'code' => 'MCH006',
                'name' => 'Premium Press C1',
                'production_line_id' => $lineC->id,
                'brand' => 'Aida',
                'model' => 'PP-600',
                'manufacture_year' => 2021,
                'capacity_per_hour' => 150,
                'status' => 'running',
                'last_maintenance_date' => now()->subDays(3),
                'next_maintenance_date' => now()->addDays(27),
                'notes' => 'Mesin khusus untuk brakepad premium'
            ],
            [
                'code' => 'MCH007',
                'name' => 'Sintering Furnace C1',
                'production_line_id' => $lineC->id,
                'brand' => 'Carbolite',
                'model' => 'SF-1200',
                'manufacture_year' => 2021,
                'capacity_per_hour' => 100,
                'status' => 'running',
                'last_maintenance_date' => now()->subDays(12),
                'next_maintenance_date' => now()->addDays(18),
                'notes' => 'Furnace untuk sintering material premium'
            ],

            // Mesin di LINE-D (Maintenance)
            [
                'code' => 'MCH008',
                'name' => 'Backup Press D1',
                'production_line_id' => $lineD->id,
                'brand' => 'Amada',
                'model' => 'BP-350',
                'manufacture_year' => 2017,
                'capacity_per_hour' => 180,
                'status' => 'maintenance',
                'last_maintenance_date' => now()->subDays(2),
                'next_maintenance_date' => now()->addDays(5),
                'notes' => 'Mesin backup sedang maintenance'
            ],
            [
                'code' => 'MCH009',
                'name' => 'Quality Testing Machine',
                'production_line_id' => $lineA->id,
                'brand' => 'Instron',
                'model' => 'QT-100',
                'manufacture_year' => 2020,
                'capacity_per_hour' => 50,
                'status' => 'idle',
                'last_maintenance_date' => now()->subDays(7),
                'next_maintenance_date' => now()->addDays(23),
                'notes' => 'Mesin testing kualitas produk'
            ],
            [
                'code' => 'MCH010',
                'name' => 'Packaging Machine',
                'production_line_id' => $lineB->id,
                'brand' => 'Bosch',
                'model' => 'PKG-200',
                'manufacture_year' => 2019,
                'capacity_per_hour' => 400,
                'status' => 'running',
                'last_maintenance_date' => now()->subDays(18),
                'next_maintenance_date' => now()->addDays(12),
                'notes' => 'Mesin packaging otomatis'
            ]
        ];

        foreach ($machines as $machineData) {
            Machine::create($machineData);
        }

        echo "✅ Machine seeder berhasil dijalankan!\n";
        echo "   🏭 10 mesin produksi telah dibuat:\n";
        echo "   - LINE-A: 4 mesin (Press, Curing, Testing)\n";
        echo "   - LINE-B: 3 mesin (Press, Grinding, Packaging)\n";
        echo "   - LINE-C: 2 mesin (Premium Press, Sintering)\n";
        echo "   - LINE-D: 1 mesin (Backup - Maintenance)\n";
    }
}