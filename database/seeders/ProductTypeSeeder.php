<?php
// File: database/seeders/ProductTypeSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductType;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder untuk membuat 10 jenis brakepad motor
     */
    public function run(): void
    {
        $productTypes = [
            [
                'code' => 'BP001',
                'name' => 'Brakepad Honda Beat',
                'brand' => 'Honda',
                'model' => 'Beat',
                'description' => 'Kampas rem untuk Honda Beat, all series',
                'standard_weight' => 120.50,
                'standard_thickness' => 4.5,
                'specifications' => [
                    'material' => 'Semi-metallic',
                    'operating_temp' => '200-450°C',
                    'friction_coefficient' => '0.35-0.45'
                ],
                'is_active' => true
            ],
            [
                'code' => 'BP002',
                'name' => 'Brakepad Honda Vario',
                'brand' => 'Honda',
                'model' => 'Vario',
                'description' => 'Kampas rem untuk Honda Vario 125/150',
                'standard_weight' => 135.75,
                'standard_thickness' => 5.0,
                'specifications' => [
                    'material' => 'Ceramic',
                    'operating_temp' => '200-500°C',
                    'friction_coefficient' => '0.40-0.50'
                ],
                'is_active' => true
            ],
            [
                'code' => 'BP003',
                'name' => 'Brakepad Honda Scoopy',
                'brand' => 'Honda',
                'model' => 'Scoopy',
                'description' => 'Kampas rem untuk Honda Scoopy all series',
                'standard_weight' => 115.25,
                'standard_thickness' => 4.2,
                'specifications' => [
                    'material' => 'Organic',
                    'operating_temp' => '150-400°C',
                    'friction_coefficient' => '0.30-0.40'
                ],
                'is_active' => true
            ],
            [
                'code' => 'BP004',
                'name' => 'Brakepad Yamaha Mio',
                'brand' => 'Yamaha',
                'model' => 'Mio',
                'description' => 'Kampas rem untuk Yamaha Mio series',
                'standard_weight' => 125.00,
                'standard_thickness' => 4.8,
                'specifications' => [
                    'material' => 'Semi-metallic',
                    'operating_temp' => '200-450°C',
                    'friction_coefficient' => '0.35-0.45'
                ],
                'is_active' => true
            ],
            [
                'code' => 'BP005',
                'name' => 'Brakepad Yamaha Nmax',
                'brand' => 'Yamaha',
                'model' => 'Nmax',
                'description' => 'Kampas rem untuk Yamaha Nmax 155',
                'standard_weight' => 145.50,
                'standard_thickness' => 5.5,
                'specifications' => [
                    'material' => 'Ceramic',
                    'operating_temp' => '250-550°C',
                    'friction_coefficient' => '0.42-0.52'
                ],
                'is_active' => true
            ],
            [
                'code' => 'BP006',
                'name' => 'Brakepad Yamaha Aerox',
                'brand' => 'Yamaha',
                'model' => 'Aerox',
                'description' => 'Kampas rem untuk Yamaha Aerox 155',
                'standard_weight' => 140.25,
                'standard_thickness' => 5.2,
                'specifications' => [
                    'material' => 'Semi-metallic',
                    'operating_temp' => '220-480°C',
                    'friction_coefficient' => '0.38-0.48'
                ],
                'is_active' => true
            ],
            [
                'code' => 'BP007',
                'name' => 'Brakepad Suzuki Address',
                'brand' => 'Suzuki',
                'model' => 'Address',
                'description' => 'Kampas rem untuk Suzuki Address 110',
                'standard_weight' => 118.75,
                'standard_thickness' => 4.3,
                'specifications' => [
                    'material' => 'Organic',
                    'operating_temp' => '150-420°C',
                    'friction_coefficient' => '0.32-0.42'
                ],
                'is_active' => true
            ],
            [
                'code' => 'BP008',
                'name' => 'Brakepad Suzuki Nex',
                'brand' => 'Suzuki',
                'model' => 'Nex',
                'description' => 'Kampas rem untuk Suzuki Nex 115',
                'standard_weight' => 122.50,
                'standard_thickness' => 4.6,
                'specifications' => [
                    'material' => 'Semi-metallic',
                    'operating_temp' => '200-460°C',
                    'friction_coefficient' => '0.36-0.46'
                ],
                'is_active' => true
            ],
            [
                'code' => 'BP009',
                'name' => 'Brakepad TVS Jupiter',
                'brand' => 'TVS',
                'model' => 'Jupiter',
                'description' => 'Kampas rem untuk TVS Jupiter MX',
                'standard_weight' => 128.00,
                'standard_thickness' => 4.7,
                'specifications' => [
                    'material' => 'Semi-metallic',
                    'operating_temp' => '210-470°C',
                    'friction_coefficient' => '0.37-0.47'
                ],
                'is_active' => true
            ],
            [
                'code' => 'BP010',
                'name' => 'Brakepad Kawasaki Ninja',
                'brand' => 'Kawasaki',
                'model' => 'Ninja',
                'description' => 'Kampas rem untuk Kawasaki Ninja 250',
                'standard_weight' => 165.75,
                'standard_thickness' => 6.0,
                'specifications' => [
                    'material' => 'Sintered',
                    'operating_temp' => '300-600°C',
                    'friction_coefficient' => '0.45-0.55'
                ],
                'is_active' => true
            ]
        ];

        foreach ($productTypes as $productData) {
            ProductType::create($productData);
        }

        echo "✅ ProductType seeder berhasil dijalankan!\n";
        echo "   🏍️ 10 jenis brakepad motor telah dibuat:\n";
        echo "   - Honda: Beat, Vario, Scoopy\n";
        echo "   - Yamaha: Mio, Nmax, Aerox\n";
        echo "   - Suzuki: Address, Nex\n";
        echo "   - TVS: Jupiter\n";
        echo "   - Kawasaki: Ninja\n";
    }
}