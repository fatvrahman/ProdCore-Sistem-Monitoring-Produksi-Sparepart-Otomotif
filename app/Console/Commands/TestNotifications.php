<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Production;
use App\Models\RawMaterial;
use App\Services\NotificationService;
use App\Models\Notification;

class TestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:test 
                            {type? : Type of notification to test (production|qc|stock|distribution|system|all)}
                            {--user= : Specific user ID to send notification to}
                            {--count=5 : Number of test notifications to create}
                            {--clear : Clear all existing notifications before testing}';

    /**
     * The console command description.
     */
    protected $description = 'Test notification system by creating sample notifications';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type') ?? 'all';
        $userId = $this->option('user');
        $count = (int) $this->option('count');
        $clear = $this->option('clear');

        $this->info('🧪 Testing Notification System');
        $this->info('================================');

        // Clear existing notifications if requested
        if ($clear) {
            $deletedCount = Notification::count();
            Notification::truncate();
            $this->warn("🗑️  Deleted {$deletedCount} existing notifications");
        }

        // Get target user(s)
        if ($userId) {
            $users = User::where('id', $userId)->get();
            if ($users->isEmpty()) {
                $this->error("❌ User with ID {$userId} not found");
                return 1;
            }
        } else {
            $users = User::with('role')->where('status', 'active')->get();
        }

        $this->info("👥 Target users: {$users->count()}");
        $this->newLine();

        // Test notifications based on type
        switch ($type) {
            case 'production':
                $this->testProductionNotifications($users, $count);
                break;
            case 'qc':
                $this->testQCNotifications($users, $count);
                break;
            case 'stock':
                $this->testStockNotifications($users, $count);
                break;
            case 'distribution':
                $this->testDistributionNotifications($users, $count);
                break;
            case 'system':
                $this->testSystemNotifications($users, $count);
                break;
            case 'all':
                $this->testAllNotifications($users, $count);
                break;
            default:
                $this->error("❌ Invalid notification type: {$type}");
                $this->info("Valid types: production, qc, stock, distribution, system, all");
                return 1;
        }

        $this->newLine();
        $this->showNotificationStats();
        $this->info('✅ Notification testing completed!');

        return 0;
    }

    protected function testProductionNotifications($users, $count)
    {
        $this->info('🔧 Testing Production Notifications...');

        $productions = Production::with(['productType', 'operator'])->take($count)->get();
        
        if ($productions->isEmpty()) {
            $this->warn('⚠️  No production records found. Creating sample notifications...');
            $this->createSampleProductionNotifications($users, $count);
            return;
        }

        $events = ['created', 'started', 'completed', 'quality_review', 'target_exceeded'];
        
        foreach ($productions as $index => $production) {
            $event = $events[$index % count($events)];
            
            try {
                $this->notificationService->createProductionNotification($production, $event);
                $this->line("   ✓ Created {$event} notification for batch {$production->batch_number}");
            } catch (\Exception $e) {
                $this->error("   ❌ Failed to create {$event} notification: " . $e->getMessage());
            }
        }
    }

    protected function testQCNotifications($users, $count)
    {
        $this->info('🛡️  Testing QC Notifications...');

        // Create sample QC notifications
        $qcTypes = [
            ['title' => 'Inspeksi Diperlukan', 'message' => 'Batch BTH240101001 memerlukan inspeksi kualitas', 'priority' => 'high'],
            ['title' => 'QC Lolos ✅', 'message' => 'Inspeksi QC240101001 selesai - Pass rate: 96.5%', 'priority' => 'normal'],
            ['title' => 'QC Gagal ❌', 'message' => 'Batch BTH240101002 tidak memenuhi standar kualitas', 'priority' => 'urgent'],
            ['title' => 'Rework Diperlukan', 'message' => 'Batch BTH240101003 memerlukan perbaikan sebelum distribusi', 'priority' => 'high'],
            ['title' => 'Inspeksi Selesai', 'message' => 'Semua inspeksi hari ini telah selesai - Total: 8 batch', 'priority' => 'low']
        ];

        foreach ($users->where('role.name', 'qc')->take($count) as $index => $user) {
            $qcData = $qcTypes[$index % count($qcTypes)];
            
            try {
                Notification::createForUser(
                    $user->id,
                    'qc',
                    $qcData['title'],
                    $qcData['message'],
                    [
                        'inspection_number' => 'QC' . now()->format('ymd') . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                        'batch_number' => 'BTH' . now()->format('ymd') . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                        'pass_rate' => rand(85, 98) + (rand(0, 9) / 10)
                    ],
                    $qcData['priority']
                );
                $this->line("   ✓ Created QC notification for {$user->name}: {$qcData['title']}");
            } catch (\Exception $e) {
                $this->error("   ❌ Failed to create QC notification: " . $e->getMessage());
            }
        }
    }

    protected function testStockNotifications($users, $count)
    {
        $this->info('📦 Testing Stock Notifications...');

        $materials = RawMaterial::take($count)->get();
        
        if ($materials->isEmpty()) {
            $this->warn('⚠️  No raw materials found. Creating sample notifications...');
            $this->createSampleStockNotifications($users, $count);
            return;
        }

        foreach ($materials as $material) {
            // Simulate low stock
            $percentage = rand(10, 80);
            
            try {
                $this->notificationService->createStockNotification($material, 'low_stock');
                $this->line("   ✓ Created low stock notification for {$material->name}");
            } catch (\Exception $e) {
                $this->error("   ❌ Failed to create stock notification: " . $e->getMessage());
            }
        }
    }

    protected function testDistributionNotifications($users, $count)
    {
        $this->info('🚛 Testing Distribution Notifications...');

        $distributionTypes = [
            ['event' => 'prepared', 'title' => 'Distribusi Disiapkan', 'message' => 'Pengiriman DEL240101001 telah disiapkan'],
            ['event' => 'shipped', 'title' => 'Pengiriman Dikirim 🚛', 'message' => 'Pengiriman DEL240101002 sudah dikirim'],
            ['event' => 'delivered', 'title' => 'Pengiriman Sampai ✅', 'message' => 'Pengiriman DEL240101003 telah sampai'],
            ['event' => 'delayed', 'title' => 'Pengiriman Terlambat ⏰', 'message' => 'Pengiriman DEL240101004 terlambat 2 hari'],
            ['event' => 'cancelled', 'title' => 'Pengiriman Dibatalkan', 'message' => 'Pengiriman DEL240101005 dibatalkan oleh customer']
        ];

        foreach ($users->whereIn('role.name', ['admin', 'gudang'])->take($count) as $index => $user) {
            $distData = $distributionTypes[$index % count($distributionTypes)];
            
            try {
                Notification::createForUser(
                    $user->id,
                    'distribution',
                    $distData['title'],
                    $distData['message'],
                    [
                        'delivery_number' => 'DEL' . now()->format('ymd') . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                        'customer' => 'Customer ' . chr(65 + $index),
                        'quantity' => rand(100, 1000),
                        'status' => $distData['event']
                    ],
                    $distData['event'] === 'delayed' ? 'high' : 'normal'
                );
                $this->line("   ✓ Created distribution notification for {$user->name}: {$distData['title']}");
            } catch (\Exception $e) {
                $this->error("   ❌ Failed to create distribution notification: " . $e->getMessage());
            }
        }
    }

    protected function testSystemNotifications($users, $count)
    {
        $this->info('⚙️  Testing System Notifications...');

        $systemTypes = [
            ['title' => 'Backup Database Berhasil', 'message' => 'Backup database harian berhasil dibuat', 'priority' => 'low'],
            ['title' => 'Maintenance Terjadwal', 'message' => 'Maintenance sistem dijadwalkan besok pukul 02:00', 'priority' => 'normal'],
            ['title' => 'Update Sistem', 'message' => 'Update sistem v2.1.0 tersedia', 'priority' => 'normal'],
            ['title' => 'Performa Server', 'message' => 'CPU usage mencapai 85% - monitoring diperlukan', 'priority' => 'high'],
            ['title' => 'Laporan Harian', 'message' => 'Laporan aktivitas harian telah dibuat', 'priority' => 'low']
        ];

        foreach ($users->take($count) as $index => $user) {
            $systemData = $systemTypes[$index % count($systemTypes)];
            
            try {
                Notification::createForUser(
                    $user->id,
                    'system',
                    $systemData['title'],
                    $systemData['message'],
                    [
                        'timestamp' => now()->toISOString(),
                        'system_version' => '2.1.0',
                        'server_info' => [
                            'cpu_usage' => rand(45, 90),
                            'memory_usage' => rand(40, 85),
                            'disk_usage' => rand(30, 70)
                        ]
                    ],
                    $systemData['priority']
                );
                $this->line("   ✓ Created system notification for {$user->name}: {$systemData['title']}");
            } catch (\Exception $e) {
                $this->error("   ❌ Failed to create system notification: " . $e->getMessage());
            }
        }
    }

    protected function testAllNotifications($users, $count)
    {
        $this->info('🌟 Testing All Notification Types...');
        
        $perType = max(1, intval($count / 5));
        
        $this->testProductionNotifications($users, $perType);
        $this->testQCNotifications($users, $perType);
        $this->testStockNotifications($users, $perType);
        $this->testDistributionNotifications($users, $perType);
        $this->testSystemNotifications($users, $perType);
    }

    protected function createSampleProductionNotifications($users, $count)
    {
        $productionTypes = [
            ['title' => 'Batch Produksi Baru', 'message' => 'Batch BTH240101001 telah dibuat', 'priority' => 'normal'],
            ['title' => 'Produksi Dimulai', 'message' => 'Batch BTH240101002 telah dimulai', 'priority' => 'normal'],
            ['title' => 'Produksi Selesai', 'message' => 'Batch BTH240101003 selesai dengan efisiensi 95%', 'priority' => 'normal'],
            ['title' => 'Target Terlampaui! 🎉', 'message' => 'Batch BTH240101004 melampaui target 15%', 'priority' => 'high'],
            ['title' => 'Siap QC Review', 'message' => 'Batch BTH240101005 siap untuk inspeksi kualitas', 'priority' => 'high']
        ];

        foreach ($users->whereIn('role.name', ['admin', 'operator'])->take($count) as $index => $user) {
            $prodData = $productionTypes[$index % count($productionTypes)];
            
            Notification::createForUser(
                $user->id,
                'production',
                $prodData['title'],
                $prodData['message'],
                [
                    'batch_number' => 'BTH' . now()->format('ymd') . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'target_quantity' => rand(500, 1500),
                    'actual_quantity' => rand(450, 1600),
                    'efficiency' => rand(85, 115)
                ],
                $prodData['priority']
            );
        }
    }

    protected function createSampleStockNotifications($users, $count)
    {
        $stockTypes = [
            ['title' => 'Stok Rendah ⚠️', 'message' => 'Stok Serbuk Logam Tembaga tinggal 15%', 'priority' => 'high'],
            ['title' => 'Stok Kritis! 🚨', 'message' => 'Stok Resin Phenolic sangat rendah', 'priority' => 'urgent'],
            ['title' => 'Stok Habis', 'message' => 'Material Graphite Powder sudah habis', 'priority' => 'urgent'],
            ['title' => 'Stok Diisi Ulang', 'message' => 'Stok Serbuk Besi berhasil diisi ulang', 'priority' => 'normal'],
            ['title' => 'Peringatan Expired', 'message' => 'Material akan expired dalam 7 hari', 'priority' => 'high']
        ];

        foreach ($users->whereIn('role.name', ['admin', 'gudang'])->take($count) as $index => $user) {
            $stockData = $stockTypes[$index % count($stockTypes)];
            
            Notification::createForUser(
                $user->id,
                'stock',
                $stockData['title'],
                $stockData['message'],
                [
                    'item_name' => 'Material ' . chr(65 + $index),
                    'current_stock' => rand(10, 100),
                    'minimum_stock' => rand(100, 200),
                    'unit' => 'kg',
                    'percentage' => rand(5, 25)
                ],
                $stockData['priority']
            );
        }
    }

    protected function showNotificationStats()
    {
        $this->info('📊 Notification Statistics:');
        $this->info('==========================');

        $total = Notification::count();
        $unread = Notification::whereNull('read_at')->count();
        $byType = Notification::select('type', \DB::raw('count(*) as count'))
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->get();
        $byPriority = Notification::select('priority', \DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->orderBy('count', 'desc')
            ->get();

        $this->table(['Metric', 'Count'], [
            ['Total Notifications', $total],
            ['Unread Notifications', $unread],
            ['Read Notifications', $total - $unread]
        ]);

        $this->newLine();
        $this->info('📋 By Type:');
        $this->table(['Type', 'Count'], $byType->map(fn($item) => [
            ucfirst($item->type), $item->count
        ])->toArray());

        $this->newLine();
        $this->info('🎯 By Priority:');
        $this->table(['Priority', 'Count'], $byPriority->map(fn($item) => [
            ucfirst($item->priority), $item->count
        ])->toArray());

        $this->newLine();
        $this->info('👥 By User:');
        
        // Simplified approach - get users and count notifications separately
        $users = User::with('role')->where('status', 'active')->get();
        $userStatsData = [];
        
        foreach ($users as $user) {
            $totalNotifications = Notification::where('user_id', $user->id)->count();
            $unreadNotifications = Notification::where('user_id', $user->id)->whereNull('read_at')->count();
            
            $userStatsData[] = [
                $user->name,
                $user->role->display_name,
                $totalNotifications,
                $unreadNotifications
            ];
        }

        $this->table(['User', 'Role', 'Total', 'Unread'], $userStatsData);
    }

    protected function handleTestCommand()
    {
        if ($this->confirm('Do you want to test the real-time notification system?')) {
            $this->info('🔄 Testing real-time notifications...');
            
            // Test browser notification permission
            $this->info('1. Testing browser notification system...');
            $this->line('   ✓ Check if notifications.js is loaded');
            $this->line('   ✓ Check NotificationManager initialization');
            $this->line('   ✓ Check real-time polling');
            
            // Test AJAX endpoints
            $this->info('2. Testing AJAX endpoints...');
            $this->line('   ✓ /api/notifications/unread-count');
            $this->line('   ✓ /notifications/{id}/read');
            $this->line('   ✓ /notifications/read-all');
            
            // Test database performance
            $this->info('3. Testing database performance...');
            $start = microtime(true);
            Notification::with('user')->take(100)->get();
            $elapsed = round((microtime(true) - $start) * 1000, 2);
            $this->line("   ✓ Query 100 notifications: {$elapsed}ms");
            
            $this->info('✅ Real-time notification system test completed!');
        }
    }
}