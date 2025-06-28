<?php
// File: database/seeders/ProductionLineSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductionLine;

class ProductionLineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder untuk membuat lini produksi
     */
    public function run(): void
    {
        $productionLines = [
            [
                'code' => 'LINE-A',
                'name' => 'Lini Produksi A',
                'description' => 'Lini produksi utama untuk brakepad Honda & Yamaha',
                'capacity_per_hour' => 500,
                'status' => 'active',
                'shift_schedule' => [
                    'shift_1' => ['start' => '06:00', 'end' => '14:00'],
                    'shift_2' => ['start' => '14:00', 'end' => '22:00'],
                    'shift_3' => ['start' => '22:00', 'end' => '06:00']
                ]
            ],
            [
                'code' => 'LINE-B',
                'name' => 'Lini Produksi B',
                'description' => 'Lini produksi untuk brakepad Suzuki & TVS',
                'capacity_per_hour' => 350,
                'status' => 'active',
                'shift_schedule' => [
                    'shift_1' => ['start' => '06:00', 'end' => '14:00'],
                    'shift_2' => ['start' => '14:00', 'end' => '22:00']
                ]
            ],
            [
                'code' => 'LINE-C',
                'name' => 'Lini Produksi C',
                'description' => 'Lini produksi khusus untuk brakepad premium (Kawasaki)',
                'capacity_per_hour' => 200,
                'status' => 'active',
                'shift_schedule' => [
                    'shift_1' => ['start' => '08:00', 'end' => '16:00']
                ]
            ],
            [
                'code' => 'LINE-D',
                'name' => 'Lini Produksi D',
                'description' => 'Lini produksi cadangan/maintenance',
                'capacity_per_hour' => 300,
                'status' => 'maintenance',
                'shift_schedule' => [
                    'shift_1' => ['start' => '06:00', 'end' => '14:00'],
                    'shift_2' => ['start' => '14:00', 'end' => '22:00']
                ]
            ]
        ];

        foreach ($productionLines as $lineData) {
            ProductionLine::create($lineData);
        }

        echo "✅ ProductionLine seeder berhasil dijalankan!\n";
        echo "   🏭 4 lini produksi telah dibuat:\n";
        echo "   - LINE-A: Kapasitas 500/jam (Honda & Yamaha)\n";
        echo "   - LINE-B: Kapasitas 350/jam (Suzuki & TVS)\n";
        echo "   - LINE-C: Kapasitas 200/jam (Premium - Kawasaki)\n";
        echo "   - LINE-D: Maintenance (Cadangan)\n";
    }
}