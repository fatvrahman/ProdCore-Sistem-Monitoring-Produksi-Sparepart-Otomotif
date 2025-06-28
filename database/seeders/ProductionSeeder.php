<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Production;
use App\Models\User;
use App\Models\ProductType;
use App\Models\ProductionLine;
use App\Models\Machine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cek apakah sudah ada data produksi
        if (Production::count() > 0) {
            $this->command->info('Data produksi sudah ada. Melewati seeder.');
            return;
        }

        $this->command->info('Membuat data sample produksi...');

        // Ambil data yang diperlukan dari database yang sudah ada
        $operators = User::where('role_id', 2)->where('status', 'active')->get(); // Role operator = 2
        $productTypes = ProductType::where('is_active', true)->get();
        $productionLines = ProductionLine::where('status', 'active')->get();
        $machines = Machine::whereIn('status', ['running', 'idle'])->get();

        if ($operators->isEmpty() || $productTypes->isEmpty() || $productionLines->isEmpty() || $machines->isEmpty()) {
            $this->command->error('Data yang diperlukan tidak lengkap:');
            $this->command->line("- Operator (role_id=2): {$operators->count()}");
            $this->command->line("- Jenis Produk: {$productTypes->count()}");
            $this->command->line("- Lini Produksi: {$productionLines->count()}");
            $this->command->line("- Mesin: {$machines->count()}");
            return;
        }

        // Data shift yang sesuai dengan enum di database (cek dulu struktur tabel productions)
        $shiftOptions = ['pagi', 'siang', 'malam']; // Menggunakan bahasa Indonesia
        
        // Buat 25 data produksi sample
        $dataProduksi = [];
        
        for ($i = 1; $i <= 25; $i++) {
            // Tanggal random dalam 30 hari terakhir
            $tanggalProduksi = Carbon::now()->subDays(rand(1, 30));
            
            // Pilih data secara random
            $jenisProduk = $productTypes->random();
            $liniProduksi = $productionLines->random();
            
            // Pilih mesin yang sesuai dengan lini produksi
            $mesinTersedia = $machines->where('production_line_id', $liniProduksi->id);
            $mesin = $mesinTersedia->isNotEmpty() ? $mesinTersedia->random() : $machines->random();
            
            $operator = $operators->random();

            // Generate angka produksi yang realistis
            $kapasitasLini = $liniProduksi->capacity_per_hour ?? 500;
            $jamKerja = rand(6, 10);
            $efisiensi = rand(80, 95) / 100; // Efisiensi 80-95%
            
            $targetQuantity = (int) ($kapasitasLini * $jamKerja);
            $actualQuantity = (int) ($targetQuantity * $efisiensi);
            
            // Hitung defect dengan rate realistis (2-8%)
            $tingkatDefect = rand(2, 8) / 100;
            $defectQuantity = (int) ($actualQuantity * $tingkatDefect);
            $goodQuantity = $actualQuantity - $defectQuantity;

            // Generate nomor batch unik
            $nomorBatch = 'BATCH' . $tanggalProduksi->format('Ymd') . str_pad($i, 3, '0', STR_PAD_LEFT);

            // Pilih shift secara random
            $shift = $shiftOptions[array_rand($shiftOptions)];

            // Generate waktu produksi berdasarkan shift
            $jamMulaiShift = match($shift) {
                'pagi' => rand(6, 8),
                'siang' => rand(14, 16), 
                'malam' => rand(22, 23),
                default => 8
            };
            
            $waktuMulai = $tanggalProduksi->copy()->setHour($jamMulaiShift)->setMinute(rand(0, 59));
            $waktuSelesai = $waktuMulai->copy()->addHours($jamKerja)->addMinutes(rand(0, 59));
            $downtimeMenit = rand(0, 90); // Downtime 0-90 menit

            // Data raw material yang digunakan sesuai dengan database
            $bahanBakuDigunakan = [
                [
                    'material_id' => 1, // MAT001 - Serbuk Logam Tembaga
                    'quantity' => rand(50, 150),
                    'unit' => 'kg',
                    'unit_price' => 85000,
                    'nama' => 'Serbuk Logam Tembaga'
                ],
                [
                    'material_id' => 2, // MAT002 - Resin Phenolic
                    'quantity' => rand(20, 60),
                    'unit' => 'liter', 
                    'unit_price' => 125000,
                    'nama' => 'Resin Phenolic'
                ],
                [
                    'material_id' => 4, // MAT004 - Serbuk Besi
                    'quantity' => rand(80, 200),
                    'unit' => 'kg',
                    'unit_price' => 45000,
                    'nama' => 'Serbuk Besi'
                ],
                [
                    'material_id' => 9, // MAT009 - Backing Plate
                    'quantity' => $actualQuantity, // Rasio 1:1 dengan produksi
                    'unit' => 'pcs',
                    'unit_price' => 15000,
                    'nama' => 'Backing Plate'
                ]
            ];

            // Catatan produksi dalam bahasa Indonesia
            $catatanProduksi = [
                'Produksi berjalan normal sesuai target harian',
                'Sedikit delay karena maintenance rutin mesin',
                'Kualitas produk bagus, defect minimal',
                'Pelatihan operator berjalan dengan lancar',
                'Material baru digunakan, perlu penyesuaian parameter',
                'Pergantian shift berjalan smooth tanpa kendala',
                'Maintenance preventif terjadwal berjalan baik',
                'Produktivitas meningkat dibanding kemarin',
                'Setup mesin untuk produk baru membutuhkan waktu ekstra',
                'Tim kerja solid, target tercapai dengan baik'
            ];

            $dataProduksi[] = [
                'batch_number' => $nomorBatch,
                'production_date' => $tanggalProduksi->format('Y-m-d'),
                'shift' => $shift,
                'product_type_id' => $jenisProduk->id,
                'production_line_id' => $liniProduksi->id,
                'machine_id' => $mesin->id,
                'operator_id' => $operator->id,
                'target_quantity' => $targetQuantity,
                'actual_quantity' => $actualQuantity,
                'good_quantity' => $goodQuantity,
                'defect_quantity' => $defectQuantity,
                'start_time' => $waktuMulai,
                'end_time' => $waktuSelesai,
                'downtime_minutes' => $downtimeMenit,
                'raw_materials_used' => json_encode($bahanBakuDigunakan),
                'notes' => $catatanProduksi[array_rand($catatanProduksi)],
                'status' => 'completed', // PENTING: Set sebagai completed agar bisa di-QC
                'created_at' => $tanggalProduksi,
                'updated_at' => $tanggalProduksi
            ];
        }

        // Insert data secara batch untuk performa optimal
        DB::table('productions')->insert($dataProduksi);

        $this->command->info('✅ Berhasil membuat 25 data produksi!');
        $this->command->info('📊 Ringkasan Produksi:');
        
        // Tampilkan statistik ringkasan
        $totalProduksi = array_sum(array_column($dataProduksi, 'actual_quantity'));
        $totalBagus = array_sum(array_column($dataProduksi, 'good_quantity'));
        $totalDefect = array_sum(array_column($dataProduksi, 'defect_quantity'));
        $rataEfisiensi = round(($totalProduksi / array_sum(array_column($dataProduksi, 'target_quantity'))) * 100, 1);
        $tingkatDefect = round(($totalDefect / $totalProduksi) * 100, 2);

        $this->command->line("   • Total Target: " . number_format(array_sum(array_column($dataProduksi, 'target_quantity'))) . " pcs");
        $this->command->line("   • Total Aktual: " . number_format($totalProduksi) . " pcs");
        $this->command->line("   • Total Bagus: " . number_format($totalBagus) . " pcs");
        $this->command->line("   • Total Defect: " . number_format($totalDefect) . " pcs");
        $this->command->line("   • Rata-rata Efisiensi: {$rataEfisiensi}%");
        $this->command->line("   • Tingkat Defect: {$tingkatDefect}%");
        $this->command->line("   • Status: Semua diset 'completed' untuk inspeksi QC");
        
        $this->command->info('🎯 Siap untuk inspeksi Quality Control!');
    }
}