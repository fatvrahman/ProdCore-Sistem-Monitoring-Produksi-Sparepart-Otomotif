<?php
// File: database/seeders/DistributionSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Distribution;
use App\Models\Production;
use App\Models\User;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DistributionSeeder extends Seeder
{
    /**
     * Sample customer data
     */
    private $customers = [
        [
            'name' => 'CV. Sinar Jaya Motor',
            'address' => 'Jl. Raya Bekasi No. 123, Bekasi Timur, Bekasi'
        ],
        [
            'name' => 'PT. Maju Motor Sejahtera',
            'address' => 'Jl. Sudirman No. 45, Tangerang Selatan'
        ],
        [
            'name' => 'UD. Rizky Spare Part',
            'address' => 'Jl. Ahmad Yani No. 78, Depok, Jawa Barat'
        ],
        [
            'name' => 'Toko Motor Jaya Abadi',
            'address' => 'Jl. Gatot Subroto No. 156, Jakarta Selatan'
        ],
        [
            'name' => 'CV. Berkah Motor',
            'address' => 'Jl. Diponegoro No. 89, Bogor, Jawa Barat'
        ],
        [
            'name' => 'PT. Prima Motor Indonesia',
            'address' => 'Jl. Margonda Raya No. 234, Depok'
        ],
        [
            'name' => 'UD. Sukses Motor',
            'address' => 'Jl. Panglima Polim No. 67, Jakarta Selatan'
        ],
        [
            'name' => 'Bengkel Central Motor',
            'address' => 'Jl. Kemang Raya No. 145, Jakarta Selatan'
        ],
        [
            'name' => 'CV. Mandiri Spare Part',
            'address' => 'Jl. TB Simatupang No. 789, Jakarta Selatan'
        ],
        [
            'name' => 'Toko Onderdil Maju',
            'address' => 'Jl. Raya Serpong No. 321, Tangerang Selatan'
        ]
    ];

    /**
     * Sample driver data
     */
    private $drivers = [
        ['name' => 'Budi Hartono', 'vehicle' => 'B 1234 ABC'],
        ['name' => 'Agus Setiawan', 'vehicle' => 'B 5678 DEF'],
        ['name' => 'Dedi Kurniawan', 'vehicle' => 'B 9012 GHI'],
        ['name' => 'Joko Susilo', 'vehicle' => 'B 3456 JKL'],
        ['name' => 'Andi Prasetyo', 'vehicle' => 'B 7890 MNO'],
        ['name' => 'Rudi Gunawan', 'vehicle' => 'B 2468 PQR'],
        ['name' => 'Eko Wijaya', 'vehicle' => 'B 1357 STU'],
        ['name' => 'Hardi Santoso', 'vehicle' => 'B 9753 VWX']
    ];

    /**
     * Run the database seeds untuk generate sample distribution data
     */
    public function run(): void
    {
        // Ambil productions yang sudah ada dengan QC approved
        $approvedProductions = Production::whereHas('qualityControl', function($q) {
            $q->where('final_status', 'approved');
        })->where('status', 'completed')
          ->where('good_quantity', '>', 0)
          ->get();

        if ($approvedProductions->isEmpty()) {
            $this->command->warn('No approved productions found. Please run ProductionSeeder and QualityControlSeeder first.');
            return;
        }

        // Ambil users dengan role gudang
        $gudangUsers = User::whereHas('role', function($q) {
            $q->where('name', 'gudang');
        })->get();

        if ($gudangUsers->isEmpty()) {
            $this->command->warn('No gudang users found. Please run UserSeeder first.');
            return;
        }

        $this->command->info('Generating distribution data...');

        // Generate 15 distribution records
        $distributionCount = 0;
        $maxDistributions = 15;

        foreach ($approvedProductions as $production) {
            if ($distributionCount >= $maxDistributions) break;

            // Skip beberapa production untuk variasi
            if (rand(1, 3) === 1) continue;

            $customer = $this->customers[array_rand($this->customers)];
            $driver = $this->drivers[array_rand($this->drivers)];
            $gudangUser = $gudangUsers->random();

            // Calculate quantities (distribusi sebagian dari good_quantity)
            $availableQuantity = $production->good_quantity;
            $distributionQuantity = rand(
                max(1, intval($availableQuantity * 0.3)), // Minimal 30%
                min($availableQuantity, intval($availableQuantity * 0.8)) // Maksimal 80%
            );

            // Generate random distribution date (1-30 hari yang lalu)
            $distributionDate = Carbon::now()->subDays(rand(1, 30));

            // Create items array
            $items = [[
                'production_id' => $production->id,
                'batch_number' => $production->batch_number,
                'product_name' => $production->productType->name,
                'quantity' => $distributionQuantity,
                'unit_weight' => $production->productType->standard_weight
            ]];

            $totalWeight = $distributionQuantity * $production->productType->standard_weight;

            // Determine status based on date
            $status = $this->determineStatus($distributionDate);
            
            // Generate delivery number
            $deliveryNumber = 'DEL-' . $distributionDate->format('Ymd') . '-' . str_pad($distributionCount + 1, 4, '0', STR_PAD_LEFT);

            // Create distribution record
            $distribution = Distribution::create([
                'delivery_number' => $deliveryNumber,
                'distribution_date' => $distributionDate->toDateString(),
                'customer_name' => $customer['name'],
                'delivery_address' => $customer['address'],
                'vehicle_number' => $driver['vehicle'],
                'driver_name' => $driver['name'],
                'items' => $items,
                'total_quantity' => $distributionQuantity,
                'total_weight' => $totalWeight,
                'status' => $status['status'],
                'shipped_at' => $status['shipped_at'],
                'delivered_at' => $status['delivered_at'],
                'prepared_by' => $gudangUser->id,
                'notes' => $this->generateNotes($status['status'], $customer['name']),
                'created_at' => $distributionDate->copy()->addHours(rand(8, 16)),
                'updated_at' => $status['delivered_at'] ?? $status['shipped_at'] ?? $distributionDate->copy()->addHours(rand(8, 16))
            ]);

            // Create corresponding stock movements
            $this->createStockMovements($distribution, $distributionDate, $gudangUser->id);

            $distributionCount++;
            
            $this->command->info("Created distribution: {$deliveryNumber} for {$customer['name']}");
        }

        // Generate beberapa distribusi dengan multiple items
        $this->generateMultiItemDistributions($approvedProductions, $gudangUsers, $distributionCount);

        $this->command->info("Successfully generated {$distributionCount} distribution records with realistic data!");
    }

    /**
     * Determine status based on distribution date
     */
    private function determineStatus($distributionDate)
    {
        $daysDiff = Carbon::now()->diffInDays($distributionDate);
        
        if ($daysDiff >= 25) {
            // Older distributions - mostly delivered
            $statusChance = rand(1, 10);
            if ($statusChance <= 8) {
                // 80% delivered
                $shippedAt = $distributionDate->copy()->addHours(rand(2, 8));
                $deliveredAt = $shippedAt->copy()->addHours(rand(4, 24));
                return [
                    'status' => 'delivered',
                    'shipped_at' => $shippedAt,
                    'delivered_at' => $deliveredAt
                ];
            } elseif ($statusChance <= 9) {
                // 10% cancelled
                return [
                    'status' => 'cancelled',
                    'shipped_at' => null,
                    'delivered_at' => null
                ];
            } else {
                // 10% shipped (might be late)
                return [
                    'status' => 'shipped',
                    'shipped_at' => $distributionDate->copy()->addHours(rand(2, 8)),
                    'delivered_at' => null
                ];
            }
        } elseif ($daysDiff >= 10) {
            // Recent distributions - mix of statuses
            $statusChance = rand(1, 10);
            if ($statusChance <= 6) {
                // 60% delivered
                $shippedAt = $distributionDate->copy()->addHours(rand(2, 8));
                $deliveredAt = $shippedAt->copy()->addHours(rand(4, 48));
                return [
                    'status' => 'delivered',
                    'shipped_at' => $shippedAt,
                    'delivered_at' => $deliveredAt
                ];
            } elseif ($statusChance <= 8) {
                // 20% shipped
                return [
                    'status' => 'shipped',
                    'shipped_at' => $distributionDate->copy()->addHours(rand(2, 8)),
                    'delivered_at' => null
                ];
            } elseif ($statusChance <= 9) {
                // 10% prepared
                return [
                    'status' => 'prepared',
                    'shipped_at' => null,
                    'delivered_at' => null
                ];
            } else {
                // 10% cancelled
                return [
                    'status' => 'cancelled',
                    'shipped_at' => null,
                    'delivered_at' => null
                ];
            }
        } else {
            // Very recent distributions - mostly prepared or shipped
            $statusChance = rand(1, 10);
            if ($statusChance <= 4) {
                // 40% prepared
                return [
                    'status' => 'prepared',
                    'shipped_at' => null,
                    'delivered_at' => null
                ];
            } elseif ($statusChance <= 7) {
                // 30% shipped
                return [
                    'status' => 'shipped',
                    'shipped_at' => $distributionDate->copy()->addHours(rand(2, 8)),
                    'delivered_at' => null
                ];
            } elseif ($statusChance <= 9) {
                // 20% delivered
                $shippedAt = $distributionDate->copy()->addHours(rand(2, 8));
                $deliveredAt = $shippedAt->copy()->addHours(rand(4, 24));
                return [
                    'status' => 'delivered',
                    'shipped_at' => $shippedAt,
                    'delivered_at' => $deliveredAt
                ];
            } else {
                // 10% cancelled
                return [
                    'status' => 'cancelled',
                    'shipped_at' => null,
                    'delivered_at' => null
                ];
            }
        }
    }

    /**
     * Generate realistic notes based on status
     */
    private function generateNotes($status, $customerName)
    {
        $notes = [
            'prepared' => [
                "Pengiriman untuk {$customerName} siap dikirim besok pagi",
                "Semua item sudah dicek dan dikemas dengan baik",
                "Menunggu konfirmasi jadwal pengiriman dari customer",
                "Dokumen pengiriman sudah lengkap dan siap"
            ],
            'shipped' => [
                "Pengiriman sudah berangkat menuju {$customerName}",
                "Driver sudah dikonfirmasi dan dalam perjalanan",
                "Estimasi tiba dalam 4-6 jam perjalanan",
                "Customer sudah dikonfirmasi mengenai jadwal kedatangan"
            ],
            'delivered' => [
                "Pengiriman berhasil diterima dengan baik oleh {$customerName}",
                "Semua item sesuai dan tidak ada komplain",
                "Customer puas dengan kualitas produk yang dikirim",
                "Surat jalan sudah ditandatangani dan dikembalikan"
            ],
            'cancelled' => [
                "Pengiriman dibatalkan atas permintaan customer",
                "Perubahan jadwal mendadak dari pihak customer",
                "Kendala teknis pada kendaraan pengiriman",
                "Pembatalan karena perubahan pesanan customer"
            ]
        ];

        $statusNotes = $notes[$status] ?? ['Catatan distribusi untuk ' . $customerName];
        return $statusNotes[array_rand($statusNotes)];
    }

    /**
     * Create stock movements for distribution
     */
    private function createStockMovements($distribution, $distributionDate, $userId)
    {
        foreach ($distribution->items as $item) {
            StockMovement::create([
                'transaction_number' => 'TXN-' . $distribution->delivery_number,
                'transaction_date' => $distributionDate,
                'stock_type' => 'finished_goods',
                'item_id' => $item['production_id'],
                'item_type' => 'production',
                'movement_type' => 'out',
                'quantity' => $item['quantity'],
                'unit_price' => 0, // Finished goods tidak ada unit price
                'balance_before' => 0, // Will be calculated
                'balance_after' => 0,  // Will be calculated
                'reference_id' => $distribution->id,
                'reference_type' => 'distribution',
                'user_id' => $userId,
                'notes' => "Distribusi ke {$distribution->customer_name} - {$item['product_name']}",
                'created_at' => $distributionDate,
                'updated_at' => $distributionDate
            ]);
        }

        // Add status change movements if applicable
        if ($distribution->status !== 'prepared') {
            StockMovement::create([
                'transaction_number' => 'STS-' . $distribution->delivery_number,
                'transaction_date' => $distribution->shipped_at ?? $distributionDate,
                'stock_type' => 'finished_goods',
                'item_id' => $distribution->id,
                'item_type' => 'distribution_status',
                'movement_type' => 'status_change',
                'quantity' => 0,
                'unit_price' => 0,
                'balance_before' => 0,
                'balance_after' => 0,
                'reference_id' => $distribution->id,
                'reference_type' => 'distribution',
                'user_id' => $userId,
                'notes' => "Status changed to {$distribution->status}",
                'created_at' => $distribution->shipped_at ?? $distributionDate,
                'updated_at' => $distribution->shipped_at ?? $distributionDate
            ]);
        }
    }

    /**
     * Generate multi-item distributions
     */
    private function generateMultiItemDistributions($approvedProductions, $gudangUsers, &$distributionCount)
    {
        $maxMultiDistributions = 3;
        $currentMulti = 0;

        while ($currentMulti < $maxMultiDistributions && $distributionCount < 15) {
            $customer = $this->customers[array_rand($this->customers)];
            $driver = $this->drivers[array_rand($this->drivers)];
            $gudangUser = $gudangUsers->random();

            // Pilih 2-4 productions untuk multi-item
            $selectedProductions = $approvedProductions->random(rand(2, 4));
            $items = [];
            $totalQuantity = 0;
            $totalWeight = 0;

            foreach ($selectedProductions as $production) {
                $availableQuantity = $production->good_quantity;
                $distributionQuantity = rand(
                    max(1, intval($availableQuantity * 0.2)), // Minimal 20%
                    min($availableQuantity, intval($availableQuantity * 0.5)) // Maksimal 50%
                );

                $items[] = [
                    'production_id' => $production->id,
                    'batch_number' => $production->batch_number,
                    'product_name' => $production->productType->name,
                    'quantity' => $distributionQuantity,
                    'unit_weight' => $production->productType->standard_weight
                ];

                $totalQuantity += $distributionQuantity;
                $totalWeight += $distributionQuantity * $production->productType->standard_weight;
            }

            $distributionDate = Carbon::now()->subDays(rand(1, 15));
            $status = $this->determineStatus($distributionDate);
            $deliveryNumber = 'DEL-' . $distributionDate->format('Ymd') . '-' . str_pad($distributionCount + 1, 4, '0', STR_PAD_LEFT);

            $distribution = Distribution::create([
                'delivery_number' => $deliveryNumber,
                'distribution_date' => $distributionDate->toDateString(),
                'customer_name' => $customer['name'],
                'delivery_address' => $customer['address'],
                'vehicle_number' => $driver['vehicle'],
                'driver_name' => $driver['name'],
                'items' => $items,
                'total_quantity' => $totalQuantity,
                'total_weight' => $totalWeight,
                'status' => $status['status'],
                'shipped_at' => $status['shipped_at'],
                'delivered_at' => $status['delivered_at'],
                'prepared_by' => $gudangUser->id,
                'notes' => "Pengiriman multi-item ke {$customer['name']} - " . count($items) . " jenis produk",
                'created_at' => $distributionDate->copy()->addHours(rand(8, 16)),
                'updated_at' => $status['delivered_at'] ?? $status['shipped_at'] ?? $distributionDate->copy()->addHours(rand(8, 16))
            ]);

            $this->createStockMovements($distribution, $distributionDate, $gudangUser->id);

            $this->command->info("Created multi-item distribution: {$deliveryNumber} with " . count($items) . " items");

            $currentMulti++;
            $distributionCount++;
        }
    }
}