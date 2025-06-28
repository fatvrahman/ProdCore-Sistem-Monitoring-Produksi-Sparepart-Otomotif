<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\QualityControl;
use App\Models\Production;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class QualityControlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔍 Membuat data Quality Control...');

        // Cek apakah sudah ada data quality control
        if (QualityControl::count() > 0) {
            if ($this->command->confirm('⚠️ Data Quality Control sudah ada. Hapus dan buat ulang?')) {
                $this->command->info('🗑️ Menghapus data Quality Control yang ada...');
                QualityControl::truncate();
            } else {
                $this->command->info('⏭️ Melewati seeder.');
                return;
            }
        }

        // Ambil data yang diperlukan
        $inspektorQC = User::where('role_id', 3)->where('status', 'active')->get();
        $produksiSelesai = Production::where('status', 'completed')
            ->with(['productType', 'machine', 'operator'])
            ->orderBy('production_date', 'desc')
            ->get();

        if ($inspektorQC->isEmpty() || $produksiSelesai->isEmpty()) {
            $this->command->error('❌ Data yang diperlukan tidak lengkap:');
            $this->command->line("   • Inspektur QC (role_id=3): {$inspektorQC->count()}");
            $this->command->line("   • Produksi Selesai: {$produksiSelesai->count()}");
            $this->command->info('💡 Pastikan data user QC dan produksi sudah ada.');
            return;
        }

        $this->command->info("✅ Ditemukan {$produksiSelesai->count()} produksi yang selesai");
        $this->command->info("✅ Ditemukan {$inspektorQC->count()} inspector QC");

        // Data templates untuk generate QC data yang realistis
        $finalStatusOptions = ['approved', 'rejected', 'rework'];
        $defectCategories = ['dimensional', 'surface', 'material', 'assembly', 'contamination', 'other'];
        
        $testResultsTemplates = [
            'dimensional' => [
                'pass' => ['value' => '4.8mm', 'notes' => 'Semua dimensi sesuai toleransi yang ditetapkan'],
                'fail' => ['value' => '4.2mm', 'notes' => 'Dimensi di luar batas toleransi yang diizinkan']
            ],
            'visual' => [
                'pass' => ['value' => 'Grade A', 'notes' => 'Kualitas permukaan excellent, tidak ada cacat visual'],
                'fail' => ['value' => 'Grade C', 'notes' => 'Ditemukan cacat permukaan yang tidak dapat diterima']
            ],
            'hardness' => [
                'pass' => ['value' => '78 HRC', 'notes' => 'Tingkat kekerasan memenuhi spesifikasi minimum'],
                'fail' => ['value' => '65 HRC', 'notes' => 'Kekerasan di bawah spesifikasi yang ditetapkan']
            ],
            'friction' => [
                'pass' => ['value' => '0.45', 'notes' => 'Koefisien gesek dalam rentang standar kualitas'],
                'fail' => ['value' => '0.28', 'notes' => 'Koefisien gesek di bawah minimum yang diterima']
            ]
        ];

        $defectDescriptions = [
            'dimensional' => 'Beberapa produk memiliki dimensi yang melebihi toleransi standar',
            'surface' => 'Ditemukan cacat permukaan seperti goresan atau bintik pada brake pad',
            'material' => 'Komposisi material tidak sesuai dengan spesifikasi yang ditetapkan',
            'assembly' => 'Masalah pemasangan backing plate atau komponen tidak sejajar dengan benar',
            'contamination' => 'Ditemukan kontaminasi pada produk yang dapat mempengaruhi kualitas',
            'other' => 'Masalah kualitas umum yang memerlukan perhatian khusus dan analisis lebih lanjut'
        ];

        $correctiveActions = [
            'dimensional' => 'Kalibrasi ulang mesin dan penyesuaian parameter produksi',
            'surface' => 'Evaluasi parameter proses grinding, pelatihan ulang operator dan perawatan mesin grinding',
            'material' => 'Review supplier dan prosedur incoming material inspection',
            'assembly' => 'Inspeksi peralatan lini assembly, update work instruction dan training operator assembly',
            'contamination' => 'Implementasi kontrol kebersihan yang lebih ketat pada area produksi',
            'other' => 'Lakukan root cause analysis mendalam, implementasikan tindakan perbaikan dan monitoring ketat'
        ];

        $dataQualityControl = [];
        $index = 1;

        // Batasi produksi yang akan diinspeksi (maksimal 15 untuk testing)
        $produksiUntukInspeksi = $produksiSelesai->take(15);

        $this->command->info('📊 Membuat data inspeksi QC...');

        foreach ($produksiUntukInspeksi as $produksi) {
            $inspektur = $inspektorQC->random();
            
            // Tanggal inspeksi 1-2 hari setelah produksi
            $tanggalInspeksi = Carbon::parse($produksi->production_date)->addDays(rand(1, 2));
            
            // Sample size 50 untuk consistency
            $sampleSize = 50;
            
            // 80% kemungkinan lulus, 20% ada masalah
            $akanLulus = rand(1, 100) <= 80;
            
            if ($akanLulus) {
                $passedQty = rand(47, 50);  // Pass rate tinggi
                $failedQty = $sampleSize - $passedQty;
                $finalStatus = 'approved';
                $defectCategory = null;
                $defectDescription = null;
                $correctiveAction = null;
            } else {
                $passedQty = rand(35, 46);  // Pass rate lebih rendah
                $failedQty = $sampleSize - $passedQty;
                
                // 70% rejected, 30% rework
                $finalStatus = rand(1, 100) <= 70 ? 'rejected' : 'rework';
                $defectCategory = $defectCategories[array_rand($defectCategories)];
                $defectDescription = $defectDescriptions[$defectCategory];
                $correctiveAction = $correctiveActions[$defectCategory];
            }

            // Generate inspection criteria (3-4 kriteria)
            $criteria = ['dimensional', 'visual'];
            if (rand(1, 100) <= 70) $criteria[] = 'hardness';
            if (rand(1, 100) <= 50) $criteria[] = 'friction';

            // Generate test results
            $testResults = [];
            foreach ($criteria as $criterion) {
                // Jika QC lulus, semua test pass. Jika gagal, ada kemungkinan test fail
                $result = ($akanLulus || rand(1, 100) <= 85) ? 'pass' : 'fail';
                $template = $testResultsTemplates[$criterion][$result];
                
                $testResults[$criterion] = [
                    'result' => $result,
                    'value' => $template['value'],
                    'notes' => $template['notes'],
                    'is_critical' => in_array($criterion, ['dimensional', 'hardness', 'friction']) ? 'true' : 'false'
                ];
            }

            // Generate inspection number
            $inspectionNumber = 'QC' . $tanggalInspeksi->format('Ymd') . str_pad($index, 3, '0', STR_PAD_LEFT);

            // Generate notes
            $batchNumber = $produksi->batch_number ?? 'BATCH' . $produksi->id;
            $statusText = match($finalStatus) {
                'approved' => 'LOLOS - Batch memenuhi standar kualitas dan disetujui untuk distribusi',
                'rejected' => 'GAGAL - Batch tidak memenuhi standar kualitas dan ditolak untuk distribusi',
                'rework' => 'REWORK - Batch memerlukan perbaikan sebelum dapat didistribusikan'
            };
            $notes = "Hasil inspeksi {$statusText} - Batch {$batchNumber}";

            $dataQualityControl[] = [
                'inspection_number' => $inspectionNumber,
                'production_id' => $produksi->id,
                'qc_inspector_id' => $inspektur->id,
                'inspection_date' => $tanggalInspeksi,
                'sample_size' => $sampleSize,
                'passed_quantity' => $passedQty,
                'failed_quantity' => $failedQty,
                'inspection_criteria' => json_encode($criteria),
                'test_results' => json_encode($testResults),
                'defect_category' => $defectCategory,
                'defect_description' => $defectDescription,
                'corrective_action' => $correctiveAction,
                'final_status' => $finalStatus,
                'notes' => $notes,
                'created_at' => $tanggalInspeksi->subHour(),
                'updated_at' => $tanggalInspeksi->subHour()
            ];

            $index++;
        }

        // Insert data QC
        try {
            DB::table('quality_controls')->insert($dataQualityControl);
            
            $this->command->info('🎉 Berhasil membuat ' . count($dataQualityControl) . ' data inspeksi Quality Control!');
            
            // Update production status dengan status QC yang proper
            $this->updateProductionStatus($dataQualityControl);
            
            // Show summary
            $this->showSummary($dataQualityControl);
            
        } catch (Exception $e) {
            $this->command->error('❌ Error saat membuat data QC: ' . $e->getMessage());
            $this->command->info('💡 Periksa koneksi database dan struktur tabel');
        }
    }

    /**
     * Update production status dengan QC status yang proper
     * Sekarang bisa menggunakan VARCHAR values
     */
    private function updateProductionStatus(array $dataQualityControl): void
    {
        $this->command->info('🔄 Mengupdate status produksi dengan QC status...');
        
        $successCount = 0;
        foreach ($dataQualityControl as $dataQC) {
            try {
                // Sekarang bisa menggunakan status QC yang descriptive karena sudah VARCHAR
                $statusBaru = match($dataQC['final_status']) {
                    'approved' => 'qc_passed',   // ✅ QC Lulus
                    'rejected' => 'qc_failed',   // ❌ QC Gagal  
                    'rework' => 'qc_rework',     // 🔄 QC Perlu Rework
                    default => 'qc_pending'      // ⏳ QC Pending
                };
                
                DB::table('productions')
                    ->where('id', $dataQC['production_id'])
                    ->update([
                        'status' => $statusBaru,
                        'updated_at' => now()
                    ]);
                    
                $successCount++;
                
            } catch (Exception $e) {
                $this->command->error("❌ Error produksi {$dataQC['production_id']}: " . $e->getMessage());
            }
        }
        
        $this->command->info("✅ Berhasil update {$successCount} status produksi dengan QC status!");
    }

    /**
     * Show QC data summary
     */
    private function showSummary(array $dataQC): void
    {
        $total = count($dataQC);
        $approved = count(array_filter($dataQC, fn($qc) => $qc['final_status'] === 'approved'));
        $rejected = count(array_filter($dataQC, fn($qc) => $qc['final_status'] === 'rejected'));
        $rework = count(array_filter($dataQC, fn($qc) => $qc['final_status'] === 'rework'));
        
        $totalPassed = array_sum(array_column($dataQC, 'passed_quantity'));
        $totalFailed = array_sum(array_column($dataQC, 'failed_quantity'));
        $overallPassRate = round(($totalPassed / ($totalPassed + $totalFailed)) * 100, 1);
        
        $this->command->info('📊 Ringkasan QC Data:');
        $this->command->line("   • Total Inspeksi: {$total}");
        $this->command->line("   • Approved: {$approved} | Rejected: {$rejected} | Rework: {$rework}");
        $this->command->line("   • Overall Pass Rate: {$overallPassRate}%");
        $this->command->line("   • Total Sample: " . ($totalPassed + $totalFailed) . " pcs");
        
        $this->command->info('🎯 Data Quality Control siap digunakan!');
        $this->command->info('📈 Akses /quality-controls untuk melihat dashboard');
        $this->command->info('📊 Akses /quality-controls/trends untuk analisis trends');
    }
}