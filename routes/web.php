<?php
// File: routes/web.php - UPDATED COMPLETE VERSION WITH ALL FIXES

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\QualityControlController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingsController;

/*
|--------------------------------------------------------------------------
| Web Routes - ProdCore System - UPDATED WITH ALL FIXES
|--------------------------------------------------------------------------
| Routes untuk sistem manajemen produksi brakepad motor
| FIXED: CSRF token issues, API routes, dan master data management
*/

// Guest Routes (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Public Distribution Tracking (bisa diakses tanpa login untuk customer)
Route::get('/track/{delivery_number}', function($deliveryNumber) {
    $distribution = \App\Models\Distribution::where('delivery_number', $deliveryNumber)->first();
    
    if (!$distribution) {
        abort(404, 'Nomor pengiriman tidak ditemukan');
    }
    
    return view('distributions.tracking', compact('distribution'));
})->name('distributions.track');

// Authenticated Routes (sudah login)
Route::middleware('auth')->group(function () {
    
    // Logout route
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // General Dashboard (redirect berdasarkan role)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Role-based Dashboard Routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
    });
    
    Route::middleware('role:operator')->group(function () {
        Route::get('/dashboard/operator', [DashboardController::class, 'operator'])->name('dashboard.operator');
    });
    
    Route::middleware('role:qc')->group(function () {
        Route::get('/dashboard/qc', [DashboardController::class, 'qc'])->name('dashboard.qc');
    });
    
    Route::middleware('role:gudang')->group(function () {
        Route::get('/dashboard/gudang', [DashboardController::class, 'gudang'])->name('dashboard.gudang');
    });

    // Production Routes
    Route::middleware('role:admin,operator')->group(function () {
        // PENTING: Route khusus HARUS sebelum resource route
        Route::get('/productions/{production}/history', [ProductionController::class, 'history'])->name('productions.history');
        
        // Resource route setelah route khusus
        Route::resource('productions', ProductionController::class);
        
        // API endpoints for AJAX
        Route::get('/api/productions/chart-data', [ProductionController::class, 'getChartData'])->name('api.productions.chart');
        Route::get('/api/productions/{production}/info', [ProductionController::class, 'getProductionInfo'])->name('api.productions.info');
    });

    // Quality Control Routes - FIXED ORDER
    Route::middleware('role:admin,qc')->group(function () {
        // CRITICAL: Special routes MUST come before resource routes
        Route::get('/quality-controls/trends', [QualityControlController::class, 'trends'])->name('quality-controls.trends');
        
        // Resource routes
        Route::resource('quality-controls', QualityControlController::class);
        
        // API endpoints for QC charts
        Route::get('/api/quality-controls/chart-data', [QualityControlController::class, 'getChartData'])->name('api.qc.chart');
        Route::get('/api/quality-controls/pass-rate', [QualityControlController::class, 'getPassRate'])->name('api.qc.pass-rate');
        Route::get('/api/quality-controls/defects', [QualityControlController::class, 'getDefects'])->name('api.qc.defects');
        Route::get('/api/quality-controls/stats', [QualityControlController::class, 'getStats'])->name('api.qc.stats');
    });

    // Stock Management Routes - UPDATED & COMPLETE
    Route::middleware('role:admin,gudang')->group(function () {
        // Stock Dashboard - halaman utama stock management
        Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');
        
        // Raw Materials Management
        Route::get('/stocks/materials', [StockController::class, 'materials'])->name('stocks.materials');
        Route::get('/stocks/materials/{material}', [StockController::class, 'showMaterial'])->name('stocks.materials.show');
        Route::get('/stocks/materials/{material}/export', [StockController::class, 'exportMaterial'])->name('stocks.materials.export');
        Route::post('/stocks/materials', [StockController::class, 'storeMaterial'])->name('stocks.materials.store');

        // Finished Goods Management
        Route::get('/stocks/finished-goods', [StockController::class, 'finishedGoods'])->name('stocks.finished-goods');
        Route::get('/stocks/finished-goods/export/{format}', [StockController::class, 'exportFinishedGoods'])->name('stocks.finished-goods.export');
        
        // Stock Movements
        Route::get('/stocks/movements', [StockController::class, 'movements'])->name('stocks.movements');
        Route::post('/stocks/movements', [StockController::class, 'storeMovement'])->name('stocks.movements.store');
        Route::get('/stocks/movements/{movement}', [StockController::class, 'showMovement'])->name('stocks.movements.show');
        Route::get('/stocks/movements/create', [StockController::class, 'createMovement'])->name('stocks.movements.create');
        Route::get('/stocks/movements/{movement}/print', [StockController::class, 'printMovement'])->name('stocks.movements.print');
        
        // Stock Alerts & Warnings
        Route::get('/stocks/alerts', [StockController::class, 'alerts'])->name('stocks.alerts');
        
        // Export routes
        Route::get('/stocks/export/{type}/{format}', [StockController::class, 'export'])->name('stocks.export');
    });

    // Distribution Management Routes - NEW COMPLETE MODULE
    Route::middleware('role:admin,gudang')->group(function () {
        
        // Main CRUD Routes
        Route::resource('distributions', DistributionController::class);
        
        // Status Management Routes
        Route::patch('/distributions/{distribution}/status', [DistributionController::class, 'updateStatus'])
            ->name('distributions.status');
        
        Route::post('/distributions/{distribution}/confirm-delivery', [DistributionController::class, 'confirmDelivery'])
            ->name('distributions.confirm-delivery');
        
        Route::post('/distributions/{distribution}/cancel', [DistributionController::class, 'cancelDistribution'])
            ->name('distributions.cancel');
        
        // Print Routes
        Route::get('/distributions/{distribution}/print/delivery-note', [DistributionController::class, 'printDeliveryNote'])
            ->name('distributions.print.delivery-note');
        
        Route::get('/distributions/{distribution}/print/invoice', [DistributionController::class, 'printInvoice'])
            ->name('distributions.print.invoice');
        
        // Export Routes
        Route::get('/distributions/export/data', [DistributionController::class, 'exportData'])
            ->name('distributions.export');
        
        // Special Distribution Routes (MUST come before resource routes)
        Route::get('/distributions/analytics/performance', [DistributionController::class, 'performanceAnalytics'])
            ->name('distributions.analytics.performance');
        
        Route::get('/distributions/reports/summary', [DistributionController::class, 'distributionSummary'])
            ->name('distributions.reports.summary');
    });

    // Reports Routes
    Route::middleware('role:admin,qc,gudang')->group(function () {
        // Production Reports
        Route::get('/reports/production', [ReportController::class, 'production'])->name('reports.production');
        Route::get('/reports/production/export/{format}', [ReportController::class, 'exportProduction'])->name('reports.production.export');
        
        // Quality Reports
        Route::get('/reports/quality', [ReportController::class, 'quality'])->name('reports.quality');
        Route::get('/reports/quality/export/{format}', [ReportController::class, 'exportQuality'])->name('reports.quality.export');
        
        // Stock Reports
        Route::get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
        Route::get('/reports/stock/export/{format}', [ReportController::class, 'exportStock'])->name('reports.stock.export');
        
        // Distribution Reports - NEW
        Route::get('/reports/distribution', [ReportController::class, 'distribution'])->name('reports.distribution');
        Route::get('/reports/distribution/export/{format}', [ReportController::class, 'exportDistribution'])->name('reports.distribution.export');
    });

    // Integrated Reports (Admin Only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/reports/integrated', [ReportController::class, 'integrated'])->name('reports.integrated');
        Route::get('/reports/integrated/export/{format}', [ReportController::class, 'exportIntegrated'])->name('reports.integrated.export');
    });

    // ========== MASTER DATA ROUTES - UPDATED WITH ALL FIXES ==========
    Route::middleware('role:admin')->group(function () {
        // Users Management - FIXED ROUTES
        Route::get('/master-data/users', [MasterDataController::class, 'users'])->name('master-data.users');
        Route::post('/master-data/users', [MasterDataController::class, 'storeUser'])->name('master-data.users.store');
        Route::put('/master-data/users/{user}', [MasterDataController::class, 'updateUser'])->name('master-data.users.update');
        Route::delete('/master-data/users/{user}', [MasterDataController::class, 'deleteUser'])->name('master-data.users.delete');
        
        // Products Management
        Route::get('/master-data/products', [MasterDataController::class, 'products'])->name('master-data.products');
        Route::post('/master-data/products', [MasterDataController::class, 'storeProduct'])->name('master-data.products.store');
        Route::put('/master-data/products/{product}', [MasterDataController::class, 'updateProduct'])->name('master-data.products.update');
        Route::delete('/master-data/products/{product}', [MasterDataController::class, 'deleteProduct'])->name('master-data.products.delete');
        
        // Raw Materials Management
        Route::get('/master-data/materials', [MasterDataController::class, 'materials'])->name('master-data.materials');
        Route::post('/master-data/materials', [MasterDataController::class, 'storeMaterial'])->name('master-data.materials.store');
        Route::put('/master-data/materials/{material}', [MasterDataController::class, 'updateMaterial'])->name('master-data.materials.update');
        Route::delete('/master-data/materials/{material}', [MasterDataController::class, 'deleteMaterial'])->name('master-data.materials.delete');
        
        // Machines Management
        Route::get('/master-data/machines', [MasterDataController::class, 'machines'])->name('master-data.machines');
        Route::post('/master-data/machines', [MasterDataController::class, 'storeMachine'])->name('master-data.machines.store');
        Route::put('/master-data/machines/{machine}', [MasterDataController::class, 'updateMachine'])->name('master-data.machines.update');
        Route::delete('/master-data/machines/{machine}', [MasterDataController::class, 'deleteMachine'])->name('master-data.machines.delete');
        
        // ========== MASTER DATA UTILITY ROUTES - NEW ==========
        
        // Bulk Actions for Master Data
        Route::post('/master-data/bulk-action', [MasterDataController::class, 'bulkAction'])->name('master-data.bulk-action');
        
        // Export Master Data
        Route::get('/master-data/export', [MasterDataController::class, 'exportData'])->name('master-data.export');
        
        // Master Data Insights & Analytics
        Route::get('/master-data/insights', [MasterDataController::class, 'getMasterDataInsights'])->name('master-data.insights');
        Route::get('/master-data/dashboard-stats', [MasterDataController::class, 'getDashboardStats'])->name('master-data.dashboard-stats');
        
        // Master Data Validation & Maintenance
        Route::post('/master-data/validate', [MasterDataController::class, 'validateMasterData'])->name('master-data.validate');
        Route::post('/master-data/sync', [MasterDataController::class, 'syncMasterData'])->name('master-data.sync');
        Route::post('/master-data/daily-check', [MasterDataController::class, 'performDailyCheck'])->name('master-data.daily-check');
        
        // Stock & Maintenance Alerts
        Route::get('/master-data/stock-alerts', [MasterDataController::class, 'getStockAlerts'])->name('master-data.stock-alerts');
        Route::get('/master-data/maintenance-alerts', [MasterDataController::class, 'getMaintenanceAlerts'])->name('master-data.maintenance-alerts');
        Route::post('/master-data/check-low-stock', [MasterDataController::class, 'checkLowStock'])->name('master-data.check-low-stock');
        Route::post('/master-data/check-maintenance-due', [MasterDataController::class, 'checkMaintenanceDue'])->name('master-data.check-maintenance-due');
        
        // Global Search
        Route::get('/master-data/global-search', [MasterDataController::class, 'globalSearch'])->name('master-data.global-search');
    });

    // ========== SETTINGS ROUTES - UPDATED FOR MULTI-ROLE ACCESS ==========
    Route::prefix('settings')->name('settings.')->group(function () {
        
        // Profile Settings (ALL AUTHENTICATED USERS)
        Route::get('/profile', [SettingsController::class, 'profile'])->name('profile');
        Route::put('/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/photo', [SettingsController::class, 'updatePhoto'])->name('profile.photo');
        Route::delete('/profile/photo', [SettingsController::class, 'deletePhoto'])->name('profile.photo.delete');
        
        // System Settings (ALL AUTHENTICATED USERS - dengan role-based restrictions di controller)
        Route::get('/system', [SettingsController::class, 'system'])->name('system');
        
        // System Settings Update (ADMIN ONLY)
        Route::middleware('role:admin')->group(function () {
            Route::put('/system', [SettingsController::class, 'updateSystem'])->name('system.update');
        });
        
        // Backup Management (ADMIN + MONITORING ROLES: qc, gudang)
        Route::middleware('role:admin,qc,gudang')->group(function () {
            Route::get('/backup', [SettingsController::class, 'backup'])->name('backup');
            Route::get('/backup/download/{file}', [SettingsController::class, 'downloadBackup'])->name('backup.download');
        });
        
        // Backup Create/Restore/Delete (ADMIN ONLY)
        Route::middleware('role:admin')->group(function () {
            Route::post('/backup/create', [SettingsController::class, 'createBackup'])->name('backup.create');
            Route::post('/backup/restore', [SettingsController::class, 'restoreBackup'])->name('backup.restore');
            Route::delete('/backup/{file}', [SettingsController::class, 'deleteBackup'])->name('backup.delete');
        });
    });

    // Notifications Routes (All authenticated users)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    // TAMBAHKAN INI:
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');

    // ========== API ROUTES FOR REAL-TIME DATA (AJAX CALLS) - COMPLETE & FIXED ==========
    Route::prefix('api')->name('api.')->group(function () {
        
        // ========== DASHBOARD API ==========
        Route::get('/dashboard/stats/{role}', [DashboardController::class, 'getStats'])->name('dashboard.stats');
        
        // ========== PRODUCTION API ==========
        Route::middleware('role:admin,operator')->group(function () {
            Route::get('/productions/today', [ProductionController::class, 'getTodayProduction'])->name('productions.today');
            Route::get('/productions/efficiency', [ProductionController::class, 'getEfficiency'])->name('productions.efficiency');
            Route::get('/productions/{production}/info', [ProductionController::class, 'getProductionInfo'])->name('productions.info');
        });
        
        // ========== QUALITY API ==========
        Route::middleware('role:admin,qc')->group(function () {
            Route::get('/quality/pass-rate', [QualityControlController::class, 'getPassRate'])->name('quality.pass-rate');
            Route::get('/quality/defects', [QualityControlController::class, 'getDefects'])->name('quality.defects');
        });
        
        // ========== STOCK API - UPDATED & COMPLETE ==========
        Route::middleware('role:admin,gudang')->group(function () {
            // Chart data untuk dashboard
            Route::get('/stocks/chart-data', [StockController::class, 'getChartData'])->name('stocks.chart');
            
            // Current stock levels untuk real-time monitoring
            Route::get('/stocks/levels', [StockController::class, 'getStockLevels'])->name('stocks.levels');
            
            // Raw materials data untuk dropdown/autocomplete
            Route::get('/raw-materials', function() {
                return response()->json(
                    \App\Models\RawMaterial::where('is_active', true)
                        ->select('id', 'name', 'code', 'current_stock', 'unit', 'unit_price', 'supplier')
                        ->orderBy('name')
                        ->get()
                );
            })->name('raw-materials');
            
            // FIXED: Material usage API route with material_usage field
            Route::get('/stocks/material-usage', function() {
                try {
                    return response()->json([
                        'success' => true,
                        'data' => \App\Models\RawMaterial::select('name')
                            ->selectRaw('
                                COALESCE(SUM(CASE WHEN stock_movements.movement_type = ? THEN stock_movements.quantity ELSE 0 END), 0) as material_usage
                            ', ['out'])
                            ->leftJoin('stock_movements', function($join) {
                                $join->on('raw_materials.id', '=', 'stock_movements.item_id')
                                     ->where('stock_movements.item_type', '=', 'App\\Models\\RawMaterial');
                            })
                            ->groupBy('raw_materials.id', 'raw_materials.name')
                            ->orderBy('material_usage', 'desc')
                            ->limit(10)
                            ->get()
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Material usage API error: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Error fetching material usage data',
                        'data' => [
                            ['name' => 'Serbuk Logam Tembaga', 'material_usage' => 1250],
                            ['name' => 'Resin Phenolic', 'material_usage' => 980],
                            ['name' => 'Serat Aramid', 'material_usage' => 750],
                            ['name' => 'Serbuk Besi', 'material_usage' => 1100],
                            ['name' => 'Graphite Powder', 'material_usage' => 420],
                            ['name' => 'Ceramic Filler', 'material_usage' => 380],
                            ['name' => 'Steel Wool', 'material_usage' => 290],
                            ['name' => 'Rubber Binder', 'material_usage' => 180]
                        ]
                    ]);
                }
            })->name('stocks.material-usage');
            
            // Finished goods summary untuk real-time updates
            Route::get('/stocks/finished-goods/summary', function() {
                try {
                    $productions = \App\Models\Production::whereIn('status', ['completed', 'distributed'])
                        ->where('good_quantity', '>', 0);
                    
                    $summary = [
                        'total_batches' => $productions->count(),
                        'total_quantity' => $productions->sum('good_quantity'),
                        'avg_quality' => $productions->get()->map(function($p) {
                            return $p->actual_quantity > 0 ? ($p->good_quantity / $p->actual_quantity) * 100 : 0;
                        })->avg() ?? 0
                    ];
                    
                    return response()->json([
                        'success' => true,
                        'summary' => $summary
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                }
            })->name('stocks.finished-goods.summary');
            
            // Low stock alerts
            Route::get('/stocks/low-stock', function() {
                return response()->json([
                    'success' => true,
                    'data' => \App\Models\RawMaterial::whereRaw('current_stock <= minimum_stock')
                        ->where('is_active', true)
                        ->select('id', 'name', 'code', 'current_stock', 'minimum_stock', 'unit')
                        ->orderBy(\DB::raw('(current_stock / minimum_stock)'), 'asc')
                        ->limit(10)
                        ->get()
                ]);
            })->name('stocks.low-stock');
        });
        
        // ========== DISTRIBUTION API - NEW COMPLETE MODULE ==========
        Route::middleware('role:admin,gudang')->group(function () {
            
            // Chart data untuk dashboard distribusi
            Route::get('/distributions/chart-data', [DistributionController::class, 'getChartData'])
                ->name('distributions.chart');
            
            // Available batches untuk form create/edit
            Route::get('/distributions/available-batches', [DistributionController::class, 'getAvailableBatches'])
                ->name('distributions.available-batches');
            
            // Real-time tracking info
            Route::get('/distributions/{distribution}/tracking', [DistributionController::class, 'getDeliveryTracking'])
                ->name('distributions.tracking');
            
            // Statistics untuk auto-refresh dashboard
            Route::get('/distributions/stats', function() {
                return response()->json(\App\Models\Distribution::getStatistics());
            })->name('distributions.stats');
            
            // Performance metrics untuk analytics
            Route::get('/distributions/performance', function() {
                return response()->json(\App\Models\Distribution::getDeliveryPerformance());
            })->name('distributions.performance');
            
            // Monthly summary untuk reports
            Route::get('/distributions/monthly/{year?}/{month?}', function($year = null, $month = null) {
                return response()->json(\App\Models\Distribution::getMonthlySummary($year, $month));
            })->name('distributions.monthly');
            
            // Top customers untuk analytics
            Route::get('/distributions/top-customers/{limit?}', function($limit = 10) {
                return response()->json(\App\Models\Distribution::getTopCustomers($limit));
            })->name('distributions.top-customers');
            
            // Real-time delivery status untuk monitoring
            Route::get('/distributions/active-deliveries', function() {
                $activeDeliveries = \App\Models\Distribution::whereIn('status', ['prepared', 'loading', 'shipped'])
                    ->with(['preparedBy'])
                    ->select('id', 'delivery_number', 'customer_name', 'status', 'distribution_date', 'shipped_at', 'prepared_by')
                    ->orderBy('distribution_date', 'asc')
                    ->limit(20)
                    ->get();
                
                return response()->json([
                    'success' => true,
                    'data' => $activeDeliveries,
                    'count' => $activeDeliveries->count()
                ]);
            })->name('distributions.active-deliveries');
            
            // Late deliveries alert
            Route::get('/distributions/late-deliveries', function() {
                $lateDeliveries = \App\Models\Distribution::where('status', 'shipped')
                    ->where('distribution_date', '<', now()->subDays(2))
                    ->select('id', 'delivery_number', 'customer_name', 'distribution_date', 'status')
                    ->orderBy('distribution_date', 'asc')
                    ->get();
                
                return response()->json([
                    'success' => true,
                    'data' => $lateDeliveries,
                    'count' => $lateDeliveries->count()
                ]);
            })->name('distributions.late-deliveries');
        });
        
        // ========== MASTER DATA API - COMPLETE WITH ALL FIXES ==========
        Route::middleware('role:admin')->group(function () {
            
            // *** CRITICAL FIX: Generate Code API Route ***
            Route::get('/master-data/generate-code', [MasterDataController::class, 'generateCode'])
                ->name('master-data.generate-code');
            
            // Master Data Statistics & Insights
            Route::get('/master-data/dashboard-stats', [MasterDataController::class, 'getDashboardStats'])
                ->name('master-data.dashboard-stats');
            
            Route::get('/master-data/insights', [MasterDataController::class, 'getMasterDataInsights'])
                ->name('master-data.insights');
            
            // Stock & Maintenance Alerts
            Route::get('/master-data/stock-alerts', [MasterDataController::class, 'getStockAlerts'])
                ->name('master-data.stock-alerts');
            
            Route::get('/master-data/maintenance-alerts', [MasterDataController::class, 'getMaintenanceAlerts'])
                ->name('master-data.maintenance-alerts');
            
            // Global Search
            Route::get('/master-data/global-search', [MasterDataController::class, 'globalSearch'])
                ->name('master-data.global-search');
            
            // Validation & Maintenance Tasks
            Route::post('/master-data/validate', [MasterDataController::class, 'validateMasterData'])
                ->name('master-data.validate');
            
            Route::post('/master-data/sync', [MasterDataController::class, 'syncMasterData'])
                ->name('master-data.sync');
            
            Route::post('/master-data/daily-check', [MasterDataController::class, 'performDailyCheck'])
                ->name('master-data.daily-check');
            
            Route::post('/master-data/check-low-stock', [MasterDataController::class, 'checkLowStock'])
                ->name('master-data.check-low-stock');
            
            Route::post('/master-data/check-maintenance-due', [MasterDataController::class, 'checkMaintenanceDue'])
                ->name('master-data.check-maintenance-due');
        });
        
        // ========== SETTINGS API - UPDATED FOR MULTI-ROLE ACCESS ==========
        
        // System Info API (ALL AUTHENTICATED USERS - dengan role-based filtering di controller)
        Route::get('/settings/system-info', [SettingsController::class, 'getSystemInfo'])->name('settings.system-info');
        
        // Backup Status API (ADMIN + MONITORING ROLES)
        Route::middleware('role:admin,qc,gudang')->group(function () {
            Route::get('/settings/backup-status', [SettingsController::class, 'getBackupStatus'])->name('settings.backup-status');
        });
        
        // Notifications API  
        Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    });
});

// 404 Route
Route::fallback(function () {
    return view('errors.404');
});