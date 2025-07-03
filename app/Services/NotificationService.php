<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Production;
use App\Models\QualityControl;
use App\Models\RawMaterial;
use App\Models\Distribution;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create production-related notifications
     */
    public function createProductionNotification(Production $production, string $event): void
    {
        try {
            switch ($event) {
                case 'created':
                    $this->notifyProductionCreated($production);
                    break;
                case 'started':
                    $this->notifyProductionStarted($production);
                    break;
                case 'completed':
                    $this->notifyProductionCompleted($production);
                    break;
                case 'quality_review':
                    $this->notifyProductionQualityReview($production);
                    break;
                case 'target_exceeded':
                    $this->notifyTargetExceeded($production);
                    break;
                case 'target_missed':
                    $this->notifyTargetMissed($production);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Production notification error: ' . $e->getMessage());
        }
    }

    /**
     * Create QC-related notifications
     */
    public function createQCNotification(QualityControl $qc, string $event): void
    {
        try {
            switch ($event) {
                case 'inspection_required':
                    $this->notifyInspectionRequired($qc);
                    break;
                case 'inspection_completed':
                    $this->notifyInspectionCompleted($qc);
                    break;
                case 'quality_failed':
                    $this->notifyQualityFailed($qc);
                    break;
                case 'quality_passed':
                    $this->notifyQualityPassed($qc);
                    break;
                case 'rework_required':
                    $this->notifyReworkRequired($qc);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('QC notification error: ' . $e->getMessage());
        }
    }

    /**
     * Create stock-related notifications
     */
    public function createStockNotification(RawMaterial $material, string $event): void
    {
        try {
            switch ($event) {
                case 'low_stock':
                    $this->notifyLowStock($material);
                    break;
                case 'out_of_stock':
                    $this->notifyOutOfStock($material);
                    break;
                case 'stock_replenished':
                    $this->notifyStockReplenished($material);
                    break;
                case 'expiry_warning':
                    $this->notifyExpiryWarning($material);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Stock notification error: ' . $e->getMessage());
        }
    }

    /**
     * Create distribution-related notifications
     */
    public function createDistributionNotification(Distribution $distribution, string $event): void
    {
        try {
            switch ($event) {
                case 'prepared':
                    $this->notifyDistributionPrepared($distribution);
                    break;
                case 'shipped':
                    $this->notifyDistributionShipped($distribution);
                    break;
                case 'delivered':
                    $this->notifyDistributionDelivered($distribution);
                    break;
                case 'delayed':
                    $this->notifyDistributionDelayed($distribution);
                    break;
                case 'cancelled':
                    $this->notifyDistributionCancelled($distribution);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Distribution notification error: ' . $e->getMessage());
        }
    }

    /**
     * Create system notifications
     */
    public function createSystemNotification(string $event, array $data = []): void
    {
        try {
            switch ($event) {
                case 'maintenance_scheduled':
                    $this->notifyMaintenanceScheduled($data);
                    break;
                case 'backup_completed':
                    $this->notifyBackupCompleted($data);
                    break;
                case 'system_update':
                    $this->notifySystemUpdate($data);
                    break;
                case 'daily_report':
                    $this->notifyDailyReport($data);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('System notification error: ' . $e->getMessage());
        }
    }

    // ==================== PRODUCTION NOTIFICATIONS ====================

    private function notifyProductionCreated(Production $production): void
    {
        // Notify QC team about new production batch
        Notification::createForRole(
            'qc',
            'production',
            'Batch Produksi Baru',
            "Batch {$production->batch_number} telah dibuat dan siap untuk inspeksi",
            [
                'batch_number' => $production->batch_number,
                'product_type' => $production->productType->name,
                'target_quantity' => $production->target_quantity,
                'operator' => $production->operator->name
            ],
            'normal',
            route('quality-controls.create', ['production' => $production->id])
        );

        // Notify admin
        Notification::createForRole(
            'admin',
            'production',
            'Batch Produksi Baru',
            "Batch {$production->batch_number} telah dibuat oleh {$production->operator->name}",
            [
                'batch_number' => $production->batch_number,
                'product_type' => $production->productType->name,
                'target_quantity' => $production->target_quantity,
                'operator' => $production->operator->name
            ],
            'normal',
            route('productions.show', $production)
        );
    }

    private function notifyProductionStarted(Production $production): void
    {
        // Notify admin about production start
        Notification::createForRole(
            'admin',
            'production',
            'Produksi Dimulai',
            "Batch {$production->batch_number} telah dimulai oleh {$production->operator->name}",
            [
                'batch_number' => $production->batch_number,
                'product_type' => $production->productType->name,
                'start_time' => $production->start_time,
                'operator' => $production->operator->name,
                'shift' => $production->shift
            ],
            'normal',
            route('productions.show', $production)
        );

        // Notify operator confirmation
        Notification::createForUser(
            $production->operator_id,
            'production',
            'Produksi Dimulai',
            "Anda telah memulai produksi batch {$production->batch_number}",
            [
                'batch_number' => $production->batch_number,
                'target_quantity' => $production->target_quantity,
                'start_time' => $production->start_time
            ],
            'normal',
            route('productions.show', $production)
        );
    }

    private function notifyProductionCompleted(Production $production): void
    {
        $efficiency = $production->target_quantity > 0 
            ? round(($production->actual_quantity / $production->target_quantity) * 100, 1) 
            : 0;

        // Notify QC team for inspection
        Notification::createForRole(
            'qc',
            'production',
            'Produksi Selesai - Siap Inspeksi',
            "Batch {$production->batch_number} telah selesai produksi dengan efisiensi {$efficiency}%",
            [
                'batch_number' => $production->batch_number,
                'actual_quantity' => $production->actual_quantity,
                'target_quantity' => $production->target_quantity,
                'efficiency' => $efficiency,
                'good_quantity' => $production->good_quantity,
                'defect_quantity' => $production->defect_quantity
            ],
            'high',
            route('quality-controls.create', ['production' => $production->id])
        );

        // Notify gudang team
        Notification::createForRole(
            'gudang',
            'production',
            'Produksi Selesai',
            "Batch {$production->batch_number} selesai dan akan segera masuk ke stock",
            [
                'batch_number' => $production->batch_number,
                'good_quantity' => $production->good_quantity,
                'product_type' => $production->productType->name
            ],
            'normal',
            route('stocks.finished-goods')
        );
    }

    private function notifyTargetExceeded(Production $production): void
    {
        $overProduction = $production->actual_quantity - $production->target_quantity;
        $percentage = round(($overProduction / $production->target_quantity) * 100, 1);

        // Notify operator (achievement)
        Notification::createForUser(
            $production->operator_id,
            'production',
            'Target Terlampaui! 🎉',
            "Selamat! Batch {$production->batch_number} melampaui target sebesar {$overProduction} unit ({$percentage}%)",
            [
                'batch_number' => $production->batch_number,
                'target_quantity' => $production->target_quantity,
                'actual_quantity' => $production->actual_quantity,
                'over_production' => $overProduction,
                'percentage' => $percentage
            ],
            'normal',
            route('productions.show', $production)
        );

        // Notify admin
        Notification::createForRole(
            'admin',
            'production',
            'Target Produksi Terlampaui! 🎉',
            "Batch {$production->batch_number} melampaui target sebesar {$overProduction} unit ({$percentage}%) oleh {$production->operator->name}",
            [
                'batch_number' => $production->batch_number,
                'target_quantity' => $production->target_quantity,
                'actual_quantity' => $production->actual_quantity,
                'over_production' => $overProduction,
                'percentage' => $percentage,
                'operator' => $production->operator->name
            ],
            'normal',
            route('productions.show', $production)
        );
    }

    private function notifyTargetMissed(Production $production): void
    {
        $shortfall = $production->target_quantity - $production->actual_quantity;
        $percentage = round(($shortfall / $production->target_quantity) * 100, 1);

        // Notify operator
        Notification::createForUser(
            $production->operator_id,
            'production',
            'Target Tidak Tercapai ⚠️',
            "Batch {$production->batch_number} tidak mencapai target sebesar {$shortfall} unit ({$percentage}%)",
            [
                'batch_number' => $production->batch_number,
                'target_quantity' => $production->target_quantity,
                'actual_quantity' => $production->actual_quantity,
                'shortfall' => $shortfall,
                'percentage' => $percentage,
                'operator' => $production->operator->name
            ],
            'high',
            route('productions.show', $production)
        );

        // Notify admin
        Notification::createForRole(
            'admin',
            'production',
            'Target Produksi Tidak Tercapai',
            "Batch {$production->batch_number} kurang {$shortfall} unit dari target ({$percentage}%)",
            [
                'batch_number' => $production->batch_number,
                'target_quantity' => $production->target_quantity,
                'actual_quantity' => $production->actual_quantity,
                'shortfall' => $shortfall,
                'percentage' => $percentage,
                'operator' => $production->operator->name
            ],
            'high',
            route('productions.show', $production)
        );
    }

    private function notifyProductionQualityReview(Production $production): void
    {
        // Notify QC team for urgent review
        Notification::createForRole(
            'qc',
            'production',
            'Review Kualitas Diperlukan',
            "Batch {$production->batch_number} memerlukan review kualitas segera",
            [
                'batch_number' => $production->batch_number,
                'actual_quantity' => $production->actual_quantity,
                'good_quantity' => $production->good_quantity,
                'defect_quantity' => $production->defect_quantity,
                'operator' => $production->operator->name
            ],
            'urgent',
            route('quality-controls.create', ['production' => $production->id])
        );

        // Notify admin
        Notification::createForRole(
            'admin',
            'production',
            'Produksi Menunggu QC Review',
            "Batch {$production->batch_number} dalam status quality review",
            [
                'batch_number' => $production->batch_number,
                'status' => 'quality_review'
            ],
            'normal',
            route('productions.show', $production)
        );
    }

    // ==================== QC NOTIFICATIONS ====================

    private function notifyInspectionRequired(QualityControl $qc): void
    {
        // Notify specific QC inspector
        Notification::createForUser(
            $qc->qc_inspector_id,
            'qc',
            'Inspeksi Diperlukan',
            "Batch {$qc->production->batch_number} memerlukan inspeksi kualitas",
            [
                'inspection_number' => $qc->inspection_number,
                'batch_number' => $qc->production->batch_number,
                'product_type' => $qc->production->productType->name,
                'sample_size' => $qc->sample_size
            ],
            'high',
            route('quality-controls.show', $qc)
        );
    }

    private function notifyInspectionCompleted(QualityControl $qc): void
    {
        $passRate = $qc->sample_size > 0 
            ? round(($qc->passed_quantity / $qc->sample_size) * 100, 1) 
            : 0;

        // Notify production operator
        Notification::createForUser(
            $qc->production->operator_id,
            'qc',
            'Inspeksi Selesai',
            "Inspeksi batch {$qc->production->batch_number} selesai dengan pass rate {$passRate}%",
            [
                'inspection_number' => $qc->inspection_number,
                'batch_number' => $qc->production->batch_number,
                'final_status' => $qc->final_status,
                'pass_rate' => $passRate,
                'inspector' => $qc->qcInspector->name // ← FIXED: Changed from inspector to qcInspector
            ],
            'normal',
            route('productions.show', $qc->production)
        );

        // Notify admin
        Notification::createForRole(
            'admin',
            'qc',
            'Laporan Inspeksi',
            "Inspeksi {$qc->inspection_number} selesai - Status: " . ucfirst($qc->final_status),
            [
                'inspection_number' => $qc->inspection_number,
                'batch_number' => $qc->production->batch_number,
                'final_status' => $qc->final_status,
                'pass_rate' => $passRate
            ],
            $qc->final_status === 'rejected' ? 'high' : 'normal',
            route('quality-controls.show', $qc)
        );
    }

    private function notifyQualityFailed(QualityControl $qc): void
    {
        // Notify all relevant parties about quality failure
        $message = "GAGAL QC: Batch {$qc->production->batch_number} tidak memenuhi standar kualitas";
        
        // Notify operator
        Notification::createForUser(
            $qc->production->operator_id,
            'qc',
            'Batch Gagal QC',
            $message,
            [
                'inspection_number' => $qc->inspection_number,
                'batch_number' => $qc->production->batch_number,
                'defect_category' => $qc->defect_category,
                'defect_description' => $qc->defect_description,
                'corrective_action' => $qc->corrective_action
            ],
            'urgent',
            route('quality-controls.show', $qc)
        );

        // Notify admin
        Notification::createForRole(
            'admin',
            'qc',
            'Alert: Batch Gagal QC',
            $message,
            [
                'inspection_number' => $qc->inspection_number,
                'batch_number' => $qc->production->batch_number,
                'defect_category' => $qc->defect_category,
                'failed_quantity' => $qc->failed_quantity,
                'inspector' => $qc->qcInspector->name // ← FIXED: Changed from inspector to qcInspector
            ],
            'urgent',
            route('quality-controls.show', $qc)
        );
    }

    private function notifyQualityPassed(QualityControl $qc): void
    {
        $passRate = $qc->sample_size > 0 
            ? round(($qc->passed_quantity / $qc->sample_size) * 100, 1) 
            : 0;

        // Notify operator about successful QC
        Notification::createForUser(
            $qc->production->operator_id,
            'qc',
            'QC Berhasil ✅',
            "Batch {$qc->production->batch_number} LOLOS QC dengan pass rate {$passRate}%",
            [
                'inspection_number' => $qc->inspection_number,
                'batch_number' => $qc->production->batch_number,
                'pass_rate' => $passRate,
                'inspector' => $qc->qcInspector->name
            ],
            'normal',
            route('quality-controls.show', $qc)
        );

        // Notify gudang team for distribution preparation
        Notification::createForRole(
            'gudang',
            'qc',
            'Batch Siap Distribusi',
            "Batch {$qc->production->batch_number} telah lolos QC dan siap untuk distribusi",
            [
                'inspection_number' => $qc->inspection_number,
                'batch_number' => $qc->production->batch_number,
                'quantity' => $qc->production->good_quantity,
                'pass_rate' => $passRate
            ],
            'normal',
            route('distributions.create', ['batch' => $qc->production->id])
        );

        // Notify admin
        Notification::createForRole(
            'admin',
            'qc',
            'QC Approved',
            "Batch {$qc->production->batch_number} berhasil lolos inspeksi QC",
            [
                'inspection_number' => $qc->inspection_number,
                'batch_number' => $qc->production->batch_number,
                'pass_rate' => $passRate,
                'inspector' => $qc->qcInspector->name
            ],
            'normal',
            route('quality-controls.show', $qc)
        );
    }

    private function notifyReworkRequired(QualityControl $qc): void
    {
        // Notify operator about rework needed
        Notification::createForUser(
            $qc->production->operator_id,
            'qc',
            'Rework Diperlukan',
            "Batch {$qc->production->batch_number} memerlukan perbaikan sebelum dapat didistribusikan",
            [
                'inspection_number' => $qc->inspection_number,
                'batch_number' => $qc->production->batch_number,
                'defect_category' => $qc->defect_category,
                'corrective_action' => $qc->corrective_action,
                'inspector' => $qc->qcInspector->name // ← FIXED: Changed from inspector to qcInspector
            ],
            'high',
            route('productions.show', $qc->production)
        );

        // Notify admin
        Notification::createForRole(
            'admin',
            'qc',
            'Batch Memerlukan Rework',
            "Batch {$qc->production->batch_number} memerlukan perbaikan berdasarkan hasil QC",
            [
                'inspection_number' => $qc->inspection_number,
                'batch_number' => $qc->production->batch_number,
                'defect_category' => $qc->defect_category,
                'failed_quantity' => $qc->failed_quantity
            ],
            'high',
            route('quality-controls.show', $qc)
        );
    }

    // ==================== STOCK NOTIFICATIONS ====================

    private function notifyLowStock(RawMaterial $material): void
    {
        $percentage = $material->minimum_stock > 0 
            ? round(($material->current_stock / $material->minimum_stock) * 100, 1) 
            : 0;

        Notification::createForRole(
            'gudang',
            'stock',
            'Stok Rendah ⚠️',
            "Stok {$material->name} tinggal {$material->current_stock} {$material->unit} ({$percentage}% dari minimum)",
            [
                'item_name' => $material->name,
                'current_stock' => $material->current_stock,
                'minimum_stock' => $material->minimum_stock,
                'unit' => $material->unit,
                'percentage' => $percentage,
                'supplier' => $material->supplier
            ],
            'high',
            route('stocks.materials.show', $material)
        );

        // Also notify admin if critically low (below 50% of minimum)
        if ($percentage < 50) {
            Notification::createForRole(
                'admin',
                'stock',
                'Stok Kritis! 🚨',
                "URGENT: Stok {$material->name} sangat rendah - segera lakukan pembelian",
                [
                    'item_name' => $material->name,
                    'current_stock' => $material->current_stock,
                    'minimum_stock' => $material->minimum_stock,
                    'unit' => $material->unit,
                    'percentage' => $percentage
                ],
                'urgent',
                route('stocks.materials.show', $material)
            );
        }
    }

    private function notifyOutOfStock(RawMaterial $material): void
    {
        Notification::createForRole(
            'admin',
            'stock',
            'STOK HABIS! 🚨',
            "Material {$material->name} sudah habis - produksi mungkin terganggu",
            [
                'item_name' => $material->name,
                'current_stock' => $material->current_stock,
                'unit' => $material->unit,
                'supplier' => $material->supplier
            ],
            'urgent',
            route('stocks.materials.show', $material)
        );

        Notification::createForRole(
            'gudang',
            'stock',
            'Stok Habis',
            "Material {$material->name} habis - segera koordinasi pembelian",
            [
                'item_name' => $material->name,
                'supplier' => $material->supplier,
                'unit_price' => $material->unit_price
            ],
            'urgent',
            route('stocks.materials.show', $material)
        );
    }

    private function notifyStockReplenished(RawMaterial $material): void
    {
        Notification::createForRole(
            'gudang',
            'stock',
            'Stok Diisi Ulang ✅',
            "Stok {$material->name} telah diisi ulang - Current: {$material->current_stock} {$material->unit}",
            [
                'item_name' => $material->name,
                'current_stock' => $material->current_stock,
                'unit' => $material->unit,
                'supplier' => $material->supplier
            ],
            'normal',
            route('stocks.materials.show', $material)
        );

        // Also notify admin
        Notification::createForRole(
            'admin',
            'stock',
            'Konfirmasi Pengisian Stok',
            "Material {$material->name} telah diisi ulang",
            [
                'item_name' => $material->name,
                'current_stock' => $material->current_stock,
                'unit' => $material->unit
            ],
            'low',
            route('stocks.materials.show', $material)
        );
    }

    private function notifyExpiryWarning(RawMaterial $material): void
    {
        Notification::createForRole(
            'gudang',
            'stock',
            'Peringatan Expired ⏰',
            "Material {$material->name} akan expired dalam 7 hari - segera gunakan",
            [
                'item_name' => $material->name,
                'current_stock' => $material->current_stock,
                'unit' => $material->unit,
                'expiry_warning' => true
            ],
            'high',
            route('stocks.materials.show', $material)
        );

        // Critical warning to admin
        Notification::createForRole(
            'admin',
            'stock',
            'Material Akan Expired',
            "URGENT: Material {$material->name} akan expired - tindakan diperlukan",
            [
                'item_name' => $material->name,
                'current_stock' => $material->current_stock,
                'unit' => $material->unit
            ],
            'urgent',
            route('stocks.materials.show', $material)
        );
    }

    // ==================== DISTRIBUTION NOTIFICATIONS ====================

    private function notifyDistributionPrepared(Distribution $distribution): void
    {
        Notification::createForRole(
            'gudang',
            'distribution',
            'Distribusi Disiapkan',
            "Pengiriman {$distribution->delivery_number} telah disiapkan untuk {$distribution->customer_name}",
            [
                'delivery_number' => $distribution->delivery_number,
                'customer' => $distribution->customer_name,
                'quantity' => $distribution->total_quantity,
                'status' => $distribution->status
            ],
            'normal',
            route('distributions.show', $distribution)
        );
    }

    private function notifyDistributionShipped(Distribution $distribution): void
    {
        Notification::createForRole(
            'admin',
            'distribution',
            'Pengiriman Dikirim 🚛',
            "Pengiriman {$distribution->delivery_number} sudah dikirim ke {$distribution->customer_name}",
            [
                'delivery_number' => $distribution->delivery_number,
                'customer' => $distribution->customer_name,
                'quantity' => $distribution->total_quantity,
                'shipped_at' => $distribution->shipped_at?->format('Y-m-d H:i')
            ],
            'normal',
            route('distributions.show', $distribution)
        );
    }

    private function notifyDistributionDelivered(Distribution $distribution): void
    {
        Notification::createForRole(
            'admin',
            'distribution',
            'Pengiriman Sampai ✅',
            "Pengiriman {$distribution->delivery_number} telah sampai ke {$distribution->customer_name}",
            [
                'delivery_number' => $distribution->delivery_number,
                'customer' => $distribution->customer_name,
                'quantity' => $distribution->total_quantity,
                'delivered_at' => $distribution->delivered_at?->format('Y-m-d H:i')
            ],
            'normal',
            route('distributions.show', $distribution)
        );
    }

    private function notifyDistributionDelayed(Distribution $distribution): void
    {
        $delayDays = now()->diffInDays($distribution->distribution_date);
        
        Notification::createForRole(
            'admin',
            'distribution',
            'Pengiriman Terlambat ⏰',
            "Pengiriman {$distribution->delivery_number} terlambat {$delayDays} hari",
            [
                'delivery_number' => $distribution->delivery_number,
                'customer' => $distribution->customer_name,
                'delay_days' => $delayDays,
                'scheduled_date' => $distribution->distribution_date->format('Y-m-d')
            ],
            'high',
            route('distributions.show', $distribution)
        );

        Notification::createForRole(
            'gudang',
            'distribution',
            'Tindak Lanjut Pengiriman',
            "Harap tindak lanjuti pengiriman {$distribution->delivery_number} yang terlambat",
            [
                'delivery_number' => $distribution->delivery_number,
                'customer' => $distribution->customer_name,
                'delay_days' => $delayDays
            ],
            'high',
            route('distributions.show', $distribution)
        );
    }

    private function notifyDistributionCancelled(Distribution $distribution): void
    {
        Notification::createForRole(
            'admin',
            'distribution',
            'Pengiriman Dibatalkan ❌',
            "Pengiriman {$distribution->delivery_number} telah dibatalkan",
            [
                'delivery_number' => $distribution->delivery_number,
                'customer' => $distribution->customer_name,
                'quantity' => $distribution->total_quantity,
                'cancelled_at' => now()->format('Y-m-d H:i')
            ],
            'normal',
            route('distributions.show', $distribution)
        );

        Notification::createForRole(
            'gudang',
            'distribution',
            'Pengiriman Dibatalkan',
            "Pengiriman {$distribution->delivery_number} dibatalkan - koordinasi stok diperlukan",
            [
                'delivery_number' => $distribution->delivery_number,
                'customer' => $distribution->customer_name,
                'quantity' => $distribution->total_quantity
            ],
            'high',
            route('distributions.show', $distribution)
        );
    }

    // ==================== SYSTEM NOTIFICATIONS ====================

    private function notifyMaintenanceScheduled(array $data): void
    {
        Notification::createBroadcast(
            'system',
            'Maintenance Terjadwal',
            "Maintenance sistem dijadwalkan pada {$data['date']} pukul {$data['time']}",
            $data,
            'normal'
        );
    }

    private function notifyBackupCompleted(array $data): void
    {
        Notification::createForRole(
            'admin',
            'system',
            'Backup Selesai',
            "Backup database berhasil dilakukan - File: {$data['filename']}",
            $data,
            'low',
            route('settings.backup')
        );
    }

    private function notifySystemUpdate(array $data): void
    {
        Notification::createBroadcast(
            'system',
            'Update Sistem',
            "Sistem telah diperbarui ke versi {$data['version']}",
            $data,
            'normal'
        );
    }

    private function notifyDailyReport(array $data): void
    {
        $message = "Laporan harian: {$data['total_production']} unit diproduksi, {$data['qc_pass_rate']}% pass rate";
        
        Notification::createForRole(
            'admin',
            'system',
            'Laporan Harian',
            $message,
            $data,
            'normal',
            route('reports.production')
        );
    }

    // ==================== UTILITY METHODS ====================

    /**
     * Check and create stock alerts for all materials
     */
    public function checkStockLevels(): void
    {
        $lowStockMaterials = RawMaterial::whereRaw('current_stock <= minimum_stock')
            ->where('is_active', true)
            ->get();

        foreach ($lowStockMaterials as $material) {
            if ($material->current_stock == 0) {
                $this->createStockNotification($material, 'out_of_stock');
            } else {
                $this->createStockNotification($material, 'low_stock');
            }
        }
    }

    /**
     * Check for delayed distributions
     */
    public function checkDelayedDistributions(): void
    {
        $delayedDistributions = Distribution::where('status', 'shipped')
            ->where('distribution_date', '<', now()->subDays(2))
            ->get();

        foreach ($delayedDistributions as $distribution) {
            $this->createDistributionNotification($distribution, 'delayed');
        }
    }

    /**
     * Create daily summary notification
     */
    public function createDailySummary(): void
    {
        $today = today();
        
        $summary = [
            'date' => $today->format('Y-m-d'),
            'total_production' => Production::whereDate('production_date', $today)->sum('actual_quantity'),
            'completed_batches' => Production::whereDate('production_date', $today)->where('status', 'completed')->count(),
            'qc_inspections' => QualityControl::whereDate('inspection_date', $today)->count(),
            'qc_pass_rate' => $this->calculateTodayPassRate(),
            'low_stock_items' => RawMaterial::whereRaw('current_stock <= minimum_stock')->count(),
            'active_distributions' => Distribution::whereIn('status', ['prepared', 'loading', 'shipped'])->count()
        ];

        $this->createSystemNotification('daily_report', $summary);
    }

    private function calculateTodayPassRate(): float
    {
        $todayInspections = QualityControl::whereDate('inspection_date', today())->get();
        
        if ($todayInspections->isEmpty()) {
            return 0;
        }

        $totalSample = $todayInspections->sum('sample_size');
        $totalPassed = $todayInspections->sum('passed_quantity');

        return $totalSample > 0 ? round(($totalPassed / $totalSample) * 100, 1) : 0;
    }

    /**
     * Clean old notifications (older than specified days)
     */
    public function cleanOldNotifications(int $days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))->delete();
    }
}