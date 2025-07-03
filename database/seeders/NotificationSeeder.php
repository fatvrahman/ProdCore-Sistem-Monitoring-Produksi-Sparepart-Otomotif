<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use App\Models\Production;
use App\Models\QualityControl;
use App\Models\RawMaterial;
use App\Models\Distribution;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users by role
        $admin = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->first();
        $operators = User::whereHas('role', fn($q) => $q->where('name', 'operator'))->get();
        $qcUsers = User::whereHas('role', fn($q) => $q->where('name', 'qc'))->get();
        $gudangUsers = User::whereHas('role', fn($q) => $q->where('name', 'gudang'))->get();

        // Get some sample data
        $productions = Production::take(5)->get();
        $qcRecords = QualityControl::take(3)->get();
        $materials = RawMaterial::take(3)->get();
        $distributions = Distribution::take(2)->get();

        $notifications = [];

        // ==================== PRODUCTION NOTIFICATIONS ====================
        
        // Admin notifications about production
        if ($admin && $productions->count() > 0) {
            foreach ($productions->take(3) as $index => $production) {
                $notifications[] = [
                    'user_id' => $admin->id,
                    'type' => 'production',
                    'title' => 'Batch Produksi Baru',
                    'message' => "Batch {$production->batch_number} telah dibuat oleh {$production->operator->name}",
                    'data' => json_encode([
                        'batch_number' => $production->batch_number,
                        'product_type' => $production->productType->name,
                        'target_quantity' => $production->target_quantity,
                        'operator' => $production->operator->name
                    ]),
                    'action_url' => route('productions.show', $production->id),
                    'priority' => 'normal',
                    'read_at' => $index > 0 ? null : Carbon::now()->subMinutes(30), // First one unread
                    'created_at' => Carbon::now()->subHours($index + 1),
                    'updated_at' => Carbon::now()->subHours($index + 1)
                ];
            }
        }

        // Operator notifications about their production
        foreach ($operators->take(2) as $operatorIndex => $operator) {
            $userProductions = Production::where('operator_id', $operator->id)->take(2)->get();
            
            foreach ($userProductions as $index => $production) {
                $efficiency = $production->target_quantity > 0 
                    ? round(($production->actual_quantity / $production->target_quantity) * 100, 1) 
                    : 0;

                $notifications[] = [
                    'user_id' => $operator->id,
                    'type' => 'production',
                    'title' => $efficiency > 100 ? 'Target Terlampaui! 🎉' : 'Produksi Selesai',
                    'message' => "Batch {$production->batch_number} selesai dengan efisiensi {$efficiency}%",
                    'data' => json_encode([
                        'batch_number' => $production->batch_number,
                        'efficiency' => $efficiency,
                        'actual_quantity' => $production->actual_quantity,
                        'target_quantity' => $production->target_quantity
                    ]),
                    'action_url' => route('productions.show', $production->id),
                    'priority' => $efficiency > 110 ? 'high' : 'normal',
                    'read_at' => $index == 0 ? null : Carbon::now()->subMinutes(45),
                    'created_at' => Carbon::now()->subHours(2 + $index),
                    'updated_at' => Carbon::now()->subHours(2 + $index)
                ];
            }
        }

        // ==================== QC NOTIFICATIONS ====================
        
        // QC notifications about inspections needed
        foreach ($qcUsers as $qcIndex => $qcUser) {
            if ($productions->count() > $qcIndex) {
                $production = $productions[$qcIndex];
                
                $notifications[] = [
                    'user_id' => $qcUser->id,
                    'type' => 'qc',
                    'title' => 'Inspeksi Diperlukan',
                    'message' => "Batch {$production->batch_number} memerlukan inspeksi kualitas",
                    'data' => json_encode([
                        'batch_number' => $production->batch_number,
                        'product_type' => $production->productType->name,
                        'actual_quantity' => $production->actual_quantity
                    ]),
                    'action_url' => route('quality-controls.create', ['production' => $production->id]),
                    'priority' => 'high',
                    'read_at' => null, // Unread
                    'created_at' => Carbon::now()->subMinutes(30),
                    'updated_at' => Carbon::now()->subMinutes(30)
                ];
            }
        }

        // Admin notifications about QC results
        if ($admin && $qcRecords->count() > 0) {
            foreach ($qcRecords->take(2) as $index => $qc) {
                $passRate = $qc->sample_size > 0 
                    ? round(($qc->passed_quantity / $qc->sample_size) * 100, 1) 
                    : 0;

                $notifications[] = [
                    'user_id' => $admin->id,
                    'type' => 'qc',
                    'title' => $qc->final_status === 'approved' ? 'QC Lolos ✅' : 'QC Gagal ❌',
                    'message' => "Inspeksi {$qc->inspection_number} selesai - Pass rate: {$passRate}%",
                    'data' => json_encode([
                        'inspection_number' => $qc->inspection_number,
                        'batch_number' => $qc->production->batch_number,
                        'final_status' => $qc->final_status,
                        'pass_rate' => $passRate,
                        'inspector' => $qc->inspector->name
                    ]),
                    'action_url' => route('quality-controls.show', $qc->id),
                    'priority' => $qc->final_status === 'rejected' ? 'urgent' : 'normal',
                    'read_at' => $index > 0 ? Carbon::now()->subMinutes(15) : null,
                    'created_at' => Carbon::now()->subHours(1 + $index),
                    'updated_at' => Carbon::now()->subHours(1 + $index)
                ];
            }
        }

        // ==================== STOCK NOTIFICATIONS ====================
        
        // Gudang notifications about low stock
        foreach ($gudangUsers as $gudangIndex => $gudangUser) {
            if ($materials->count() > $gudangIndex) {
                $material = $materials[$gudangIndex];
                $percentage = $material->minimum_stock > 0 
                    ? round(($material->current_stock / $material->minimum_stock) * 100, 1) 
                    : 0;

                $notifications[] = [
                    'user_id' => $gudangUser->id,
                    'type' => 'stock',
                    'title' => $percentage < 50 ? 'Stok Kritis! 🚨' : 'Stok Rendah ⚠️',
                    'message' => "Stok {$material->name} tinggal {$material->current_stock} {$material->unit} ({$percentage}% dari minimum)",
                    'data' => json_encode([
                        'item_name' => $material->name,
                        'current_stock' => $material->current_stock,
                        'minimum_stock' => $material->minimum_stock,
                        'unit' => $material->unit,
                        'percentage' => $percentage,
                        'supplier' => $material->supplier
                    ]),
                    'action_url' => route('stocks.materials.show', $material->id),
                    'priority' => $percentage < 50 ? 'urgent' : 'high',
                    'read_at' => null, // Unread - important for stock alerts
                    'created_at' => Carbon::now()->subMinutes(15 + $gudangIndex * 10),
                    'updated_at' => Carbon::now()->subMinutes(15 + $gudangIndex * 10)
                ];
            }
        }

        // Admin stock notifications
        if ($admin) {
            $lowStockCount = RawMaterial::whereRaw('current_stock <= minimum_stock')->count();
            
            $notifications[] = [
                'user_id' => $admin->id,
                'type' => 'stock',
                'title' => 'Laporan Stok Harian',
                'message' => "Terdapat {$lowStockCount} material dengan stok rendah yang memerlukan perhatian",
                'data' => json_encode([
                    'low_stock_count' => $lowStockCount,
                    'total_materials' => RawMaterial::where('is_active', true)->count(),
                    'report_date' => today()->format('Y-m-d')
                ]),
                'action_url' => route('stocks.alerts'),
                'priority' => $lowStockCount > 5 ? 'high' : 'normal',
                'read_at' => Carbon::now()->subMinutes(60),
                'created_at' => Carbon::now()->subHours(6),
                'updated_at' => Carbon::now()->subHours(6)
            ];
        }

        // ==================== DISTRIBUTION NOTIFICATIONS ====================
        
        // Gudang notifications about distributions
        foreach ($gudangUsers->take(1) as $gudangUser) {
            if ($distributions->count() > 0) {
                foreach ($distributions as $index => $distribution) {
                    $notifications[] = [
                        'user_id' => $gudangUser->id,
                        'type' => 'distribution',
                        'title' => 'Pengiriman Siap Dikirim 🚛',
                        'message' => "Pengiriman {$distribution->delivery_number} untuk {$distribution->customer_name} siap dikirim",
                        'data' => json_encode([
                            'delivery_number' => $distribution->delivery_number,
                            'customer' => $distribution->customer_name,
                            'quantity' => $distribution->total_quantity,
                            'status' => $distribution->status
                        ]),
                        'action_url' => route('distributions.show', $distribution->id),
                        'priority' => 'normal',
                        'read_at' => $index > 0 ? Carbon::now()->subMinutes(20) : null,
                        'created_at' => Carbon::now()->subMinutes(45 + $index * 15),
                        'updated_at' => Carbon::now()->subMinutes(45 + $index * 15)
                    ];
                }
            }
        }

        // ==================== SYSTEM NOTIFICATIONS ====================
        
        // Broadcast system notifications
        $allUsers = User::where('status', 'active')->get();
        
        foreach ($allUsers->take(5) as $index => $user) {
            // Daily report notification
            $notifications[] = [
                'user_id' => $user->id,
                'type' => 'system',
                'title' => 'Laporan Harian Sistem',
                'message' => 'Laporan aktivitas harian telah dibuat: 15 batch diproduksi, 94.2% pass rate QC',
                'data' => json_encode([
                    'date' => today()->format('Y-m-d'),
                    'total_production' => 15,
                    'qc_pass_rate' => 94.2,
                    'completed_batches' => 12,
                    'pending_qc' => 3
                ]),
                'action_url' => route('reports.production'),
                'priority' => 'low',
                'read_at' => $index % 2 == 0 ? Carbon::now()->subHours(2) : null,
                'created_at' => Carbon::now()->subHours(8),
                'updated_at' => Carbon::now()->subHours(8)
            ];

            // Maintenance notification (for first 3 users)
            if ($index < 3) {
                $notifications[] = [
                    'user_id' => $user->id,
                    'type' => 'system',
                    'title' => 'Maintenance Terjadwal',
                    'message' => 'Maintenance sistem dijadwalkan besok pukul 02:00 - 04:00 WIB',
                    'data' => json_encode([
                        'maintenance_date' => now()->addDay()->format('Y-m-d'),
                        'start_time' => '02:00',
                        'end_time' => '04:00',
                        'type' => 'routine_maintenance'
                    ]),
                    'action_url' => null,
                    'priority' => 'normal',
                    'read_at' => null, // Unread - important system notification
                    'created_at' => Carbon::now()->subHours(4),
                    'updated_at' => Carbon::now()->subHours(4)
                ];
            }
        }

        // Success/Warning notifications for admins
        if ($admin) {
            $notifications[] = [
                'user_id' => $admin->id,
                'type' => 'success',
                'title' => 'Backup Database Berhasil',
                'message' => 'Backup database harian berhasil dibuat - File: backup_' . now()->format('Y_m_d') . '.sql',
                'data' => json_encode([
                    'backup_file' => 'backup_' . now()->format('Y_m_d') . '.sql',
                    'file_size' => '45.2 MB',
                    'backup_time' => now()->format('Y-m-d H:i:s')
                ]),
                'action_url' => route('settings.backup'),
                'priority' => 'low',
                'read_at' => Carbon::now()->subMinutes(30),
                'created_at' => Carbon::now()->subHours(3),
                'updated_at' => Carbon::now()->subHours(3)
            ];

            $notifications[] = [
                'user_id' => $admin->id,
                'type' => 'alert',
                'title' => 'Performa Server',
                'message' => 'CPU usage mencapai 85% - monitoring diperlukan untuk mencegah bottleneck',
                'data' => json_encode([
                    'cpu_usage' => 85,
                    'memory_usage' => 72,
                    'disk_usage' => 45,
                    'active_connections' => 156
                ]),
                'action_url' => route('settings.system'),
                'priority' => 'high',
                'read_at' => null, // Unread - important alert
                'created_at' => Carbon::now()->subMinutes(45),
                'updated_at' => Carbon::now()->subMinutes(45)
            ];
        }

        // Insert all notifications
        foreach (array_chunk($notifications, 50) as $chunk) {
            Notification::insert($chunk);
        }

        $this->command->info('✅ Sample notifications created successfully!');
        $this->command->info('📊 Total notifications: ' . count($notifications));
        $this->command->info('👥 Notifications per user:');
        
        foreach (User::with('role')->get() as $user) {
            $userNotificationCount = Notification::where('user_id', $user->id)->count();
            $unreadCount = Notification::where('user_id', $user->id)->whereNull('read_at')->count();
            
            $this->command->info("   - {$user->name} ({$user->role->display_name}): {$userNotificationCount} total, {$unreadCount} unread");
        }
    }
}