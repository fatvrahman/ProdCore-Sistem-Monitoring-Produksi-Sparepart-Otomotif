<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\Production;
use App\Models\QualityControl;
use App\Models\RawMaterial;
use App\Models\ProductType;
use App\Models\Machine;
use App\Models\User;
use App\Models\Distribution;
use App\Models\StockMovement;
use App\Models\ProductionLine;
use App\Helpers\ShiftHelper; // ✅ ADDED

class DashboardController extends Controller
{
    // Cache duration in minutes
    private const CACHE_DURATION = 5;
    
    /**
     * Dashboard utama - redirect berdasarkan role
     */
    public function index()
    {
        $user = Auth::user();
        $role = $user->role->name;

        return match($role) {
            'admin' => redirect()->route('dashboard.admin'),
            'operator' => redirect()->route('dashboard.operator'),
            'qc' => redirect()->route('dashboard.qc'),
            'gudang' => redirect()->route('dashboard.gudang'),
            default => abort(403, 'Role tidak dikenali')
        };
    }

    /**
     * Dashboard Admin - Analytics lengkap
     */
    public function admin()
    {
        try {
            $stats = $this->getAdminStatistics();
            $chartData = $this->getAdminChartData();
            
            // ✅ ADDED: Pass current shift to view
            $currentShift = ShiftHelper::getCurrentShift();

            return view('dashboard.admin', compact('stats', 'chartData', 'currentShift'));
            
        } catch (\Exception $e) {
            Log::error('Admin dashboard error: ' . $e->getMessage());
            
            return view('dashboard.admin', [
                'stats' => $this->getFallbackStats(),
                'chartData' => $this->getFallbackChartData(),
                'currentShift' => ShiftHelper::getCurrentShift() // ✅ ADDED
            ])->with('warning', 'Menggunakan data sampel. Data real akan muncul setelah tersedia.');
        }
    }

    /**
     * Dashboard Operator
     */
    public function operator()
    {
        try {
            $operator = Auth::user();
            
            $stats = [
                'my_production_today' => $this->getOperatorProduction($operator->id),
                'my_target_today' => $this->getOperatorTarget($operator->id),
                'my_efficiency' => $this->getOperatorEfficiency($operator->id),
                'current_shift' => ShiftHelper::getCurrentShift(), // ✅ FIXED
                'active_productions' => $this->getActiveProductions($operator->id)
            ];

            $recentProductions = Production::where('operator_id', $operator->id)
                ->with(['productType', 'machine'])
                ->latest('production_date')
                ->limit(5)
                ->get();

            $chartData = [
                'daily_target_vs_actual' => $this->getOperatorDailyData($operator->id),
                'shift_performance' => $this->getOperatorShiftData($operator->id)
            ];

            return view('dashboard.operator', compact('stats', 'recentProductions', 'chartData'));
            
        } catch (\Exception $e) {
            Log::error('Operator dashboard error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat dashboard.');
        }
    }

    /**
     * Dashboard QC
     */
    public function qc()
    {
        try {
            $stats = [
                'inspections_today' => $this->getInspectionsToday(),
                'pass_rate_today' => $this->getTodayPassRate(),
                'failed_items_today' => $this->getFailedItemsToday(),
                'pending_inspections' => $this->getPendingInspections(),
                'avg_pass_rate_week' => $this->getWeeklyPassRate(),
                'current_shift' => ShiftHelper::getCurrentShift() // ✅ ADDED
            ];

            $recentInspections = QualityControl::with(['production.productType', 'inspector'])
                ->latest('inspection_date')
                ->limit(5)
                ->get();

            $chartData = [
                'pass_rate_trend' => $this->getPassRateTrend(),
                'defect_distribution' => $this->getDefectDistribution(),
                'quality_by_product' => $this->getQualityByProduct()
            ];

            return view('dashboard.qc', compact('stats', 'recentInspections', 'chartData'));
            
        } catch (\Exception $e) {
            Log::error('QC dashboard error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat dashboard QC.');
        }
    }

    /**
     * Dashboard Gudang
     */
    public function gudang()
    {
        try {
            $stats = [
                'total_raw_materials' => RawMaterial::where('is_active', true)->count(),
                'low_stock_alerts' => RawMaterial::whereRaw('current_stock <= minimum_stock')->count(),
                'stock_value' => RawMaterial::sum(DB::raw('current_stock * unit_price')),
                'distributions_today' => Distribution::whereDate('distribution_date', now())->count(),
                'pending_shipments' => Distribution::whereIn('status', ['prepared', 'loading', 'shipped'])->count(),
                'movements_today' => StockMovement::whereDate('transaction_date', now())->count(),
                'current_shift' => ShiftHelper::getCurrentShift() // ✅ ADDED
            ];

            $recentMovements = StockMovement::with('user')
                ->latest('transaction_date')
                ->limit(5)
                ->get();

            $lowStockItems = RawMaterial::whereRaw('current_stock <= minimum_stock')
                ->orderBy('current_stock', 'asc')
                ->limit(5)
                ->get();

            $chartData = [
                'stock_movement_trend' => $this->getStockMovementTrend(),
                'material_usage' => $this->getMaterialUsage(),
                'distribution_status' => $this->getDistributionStatus()
            ];

            return view('dashboard.gudang', compact('stats', 'recentMovements', 'lowStockItems', 'chartData'));
            
        } catch (\Exception $e) {
            Log::error('Gudang dashboard error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat dashboard gudang.');
        }
    }

    /**
     * API endpoint untuk refresh data
     */
    public function getStats($role)
    {
        try {
            $stats = match($role) {
                'admin' => $this->getAdminStatistics(),
                'operator' => $this->getOperatorStatsForApi(),
                'qc' => $this->getQCStatsForApi(),
                'gudang' => $this->getGudangStatsForApi(),
                default => throw new \InvalidArgumentException('Invalid role')
            };

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error("Stats API error for role {$role}: " . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch stats'], 500);
        }
    }

    // ========== HELPER METHODS ==========

    /**
     * Get latest production date from available data
     */
    private function getLatestProductionDate()
    {
        return Cache::remember('latest_production_date', self::CACHE_DURATION, function () {
            $latestDate = Production::max('production_date');
            return $latestDate ? Carbon::parse($latestDate) : Carbon::today();
        });
    }

    /**
     * Get chart date range (7 days ending with latest data)
     */
    private function getChartDateRange()
    {
        $latestDate = $this->getLatestProductionDate();
        return [
            'start' => $latestDate->copy()->subDays(6),
            'end' => $latestDate
        ];
    }

    // ========== ADMIN STATISTICS ==========

    private function getAdminStatistics()
    {
        return Cache::remember('admin_stats', self::CACHE_DURATION, function () {
            $latestDate = $this->getLatestProductionDate();
            
            // Optimized queries with single database calls
            $productionStats = Production::selectRaw('
                SUM(CASE WHEN DATE(production_date) = ? THEN actual_quantity ELSE 0 END) as today_production,
                SUM(CASE WHEN production_date >= ? THEN actual_quantity ELSE 0 END) as month_production,
                SUM(CASE WHEN DATE(production_date) = ? THEN target_quantity ELSE 0 END) as today_target,
                SUM(CASE WHEN DATE(production_date) = ? THEN actual_quantity ELSE 0 END) as today_actual
            ', [
                $latestDate->format('Y-m-d'),
                $latestDate->copy()->startOfMonth()->format('Y-m-d'),
                $latestDate->format('Y-m-d'),
                $latestDate->format('Y-m-d')
            ])->first();

            $efficiency = $productionStats->today_target > 0 
                ? round(($productionStats->today_actual / $productionStats->today_target) * 100, 2) 
                : 0;

            // QC pass rate calculation
            $qcStats = QualityControl::selectRaw('
                SUM(sample_size) as total_samples,
                SUM(passed_quantity) as total_passed
            ')->first();
            
            $passRate = $qcStats->total_samples > 0 
                ? round(($qcStats->total_passed / $qcStats->total_samples) * 100, 2) 
                : 0;

            return [
                'total_production_today' => $productionStats->today_production ?: 0,
                'total_production_month' => $productionStats->month_production ?: 0,
                'efficiency_today' => $efficiency,
                'quality_pass_rate' => $passRate,
                'low_stock_items' => RawMaterial::whereRaw('current_stock <= minimum_stock')->count(),
                'active_machines' => Machine::where('status', 'running')->count(),
                'pending_distributions' => Distribution::whereIn('status', ['prepared', 'loading'])->count(),
                'total_users' => User::where('status', 'active')->count(),
                'total_raw_materials' => RawMaterial::where('is_active', true)->count(),
                'current_shift' => ShiftHelper::getCurrentShift() // ✅ ADDED
            ];
        });
    }

    private function getAdminChartData()
    {
        return Cache::remember('admin_chart_data', self::CACHE_DURATION, function () {
            return [
                'production_trend' => $this->getProductionTrend(),
                'efficiency_by_line' => $this->getEfficiencyByLine(),
                'defect_categories' => $this->getDefectCategories(),
                'stock_levels' => $this->getStockLevels()
            ];
        });
    }

    // ========== CHART DATA METHODS ==========

    private function getProductionTrend()
    {
        $dateRange = $this->getChartDateRange();
        $data = [];
        
        // Single query for all dates
        $productions = Production::selectRaw('
            DATE(production_date) as date,
            SUM(actual_quantity) as production
        ')
        ->whereBetween('production_date', [$dateRange['start'], $dateRange['end']])
        ->groupBy('date')
        ->get()
        ->keyBy('date');
        
        for ($i = 0; $i < 7; $i++) {
            $date = $dateRange['start']->copy()->addDays($i);
            $dateStr = $date->format('Y-m-d');
            
            $data[] = [
                'date' => $dateStr,
                'day' => $date->format('D'),
                'production' => $productions->get($dateStr)->production ?? 0
            ];
        }
        
        return $data;
    }

    private function getEfficiencyByLine()
    {
        return ProductionLine::select('name')
            ->selectRaw('
                CASE 
                    WHEN SUM(productions.target_quantity) > 0 
                    THEN ROUND((SUM(productions.actual_quantity) / SUM(productions.target_quantity)) * 100, 2)
                    ELSE 0 
                END as efficiency
            ')
            ->leftJoin('productions', 'production_lines.id', '=', 'productions.production_line_id')
            ->groupBy('production_lines.id', 'production_lines.name')
            ->get();
    }

    private function getDefectCategories()
    {
        $data = QualityControl::select('defect_category')
            ->selectRaw('SUM(failed_quantity) as total_defects')
            ->whereNotNull('defect_category')
            ->groupBy('defect_category')
            ->get();

        return $data->isNotEmpty() ? $data : collect([
            ['defect_category' => 'dimensional', 'total_defects' => 15],
            ['defect_category' => 'surface', 'total_defects' => 8],
            ['defect_category' => 'material', 'total_defects' => 5],
            ['defect_category' => 'assembly', 'total_defects' => 3]
        ]);
    }

    private function getStockLevels()
    {
        return RawMaterial::select('name', 'current_stock', 'minimum_stock', 'maximum_stock')
            ->where('is_active', true)
            ->orderBy('current_stock', 'asc')
            ->limit(10)
            ->get();
    }

    // ========== OPERATOR METHODS ==========

    private function getOperatorProduction($operatorId)
    {
        $latestDate = $this->getLatestProductionDate();
        return Production::where('operator_id', $operatorId)
            ->whereDate('production_date', $latestDate)
            ->sum('actual_quantity') ?: 0;
    }

    private function getOperatorTarget($operatorId)
    {
        $latestDate = $this->getLatestProductionDate();
        return Production::where('operator_id', $operatorId)
            ->whereDate('production_date', $latestDate)
            ->sum('target_quantity') ?: 0;
    }

    private function getOperatorEfficiency($operatorId)
    {
        $dateRange = $this->getChartDateRange();
        $stats = Production::where('operator_id', $operatorId)
            ->whereBetween('production_date', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('SUM(target_quantity) as total_target, SUM(actual_quantity) as total_actual')
            ->first();

        return $stats->total_target > 0 
            ? round(($stats->total_actual / $stats->total_target) * 100, 2) 
            : 0;
    }

    // ✅ REMOVED OLD getCurrentShift() method - now using ShiftHelper

    private function getActiveProductions($operatorId)
    {
        return Production::where('operator_id', $operatorId)
            ->where('status', 'in_progress')
            ->count();
    }

    private function getOperatorDailyData($operatorId)
    {
        $dateRange = $this->getChartDateRange();
        $data = [];
        
        $productions = Production::selectRaw('
            DATE(production_date) as date,
            SUM(target_quantity) as target,
            SUM(actual_quantity) as actual
        ')
        ->where('operator_id', $operatorId)
        ->whereBetween('production_date', [$dateRange['start'], $dateRange['end']])
        ->groupBy('date')
        ->get()
        ->keyBy('date');
        
        for ($i = 0; $i < 7; $i++) {
            $date = $dateRange['start']->copy()->addDays($i);
            $dateStr = $date->format('Y-m-d');
            $prod = $productions->get($dateStr);
            
            $data[] = [
                'date' => $dateStr,
                'day' => $date->format('D'),
                'target' => $prod->target ?? 0,
                'actual' => $prod->actual ?? 0
            ];
        }
        
        return $data;
    }

    private function getOperatorShiftData($operatorId)
    {
        return Production::where('operator_id', $operatorId)
            ->select('shift')
            ->selectRaw('
                SUM(target_quantity) as total_target,
                SUM(actual_quantity) as total_actual,
                ROUND(
                    CASE 
                        WHEN SUM(target_quantity) > 0 
                        THEN (SUM(actual_quantity) / SUM(target_quantity)) * 100 
                        ELSE 0 
                    END, 2
                ) as efficiency
            ')
            ->groupBy('shift')
            ->get();
    }

    // ========== QC METHODS ==========

    private function getInspectionsToday()
    {
        $latestQCDate = QualityControl::max('inspection_date');
        return $latestQCDate 
            ? QualityControl::whereDate('inspection_date', $latestQCDate)->count()
            : 0;
    }

    private function getTodayPassRate()
    {
        $latestQCDate = QualityControl::max('inspection_date');
        if (!$latestQCDate) return 0;
        
        $stats = QualityControl::whereDate('inspection_date', $latestQCDate)
            ->selectRaw('SUM(sample_size) as total_samples, SUM(passed_quantity) as total_passed')
            ->first();
            
        return $stats->total_samples > 0 
            ? round(($stats->total_passed / $stats->total_samples) * 100, 2) 
            : 0;
    }

    private function getFailedItemsToday()
    {
        $latestQCDate = QualityControl::max('inspection_date');
        return $latestQCDate 
            ? QualityControl::whereDate('inspection_date', $latestQCDate)->sum('failed_quantity')
            : 0;
    }

    private function getPendingInspections()
    {
        return Production::whereDoesntHave('qualityControls')->count();
    }

    private function getWeeklyPassRate()
    {
        $stats = QualityControl::where('inspection_date', '>=', now()->subWeek())
            ->selectRaw('SUM(sample_size) as total_samples, SUM(passed_quantity) as total_passed')
            ->first();
            
        return $stats->total_samples > 0 
            ? round(($stats->total_passed / $stats->total_samples) * 100, 2) 
            : 0;
    }

    private function getPassRateTrend()
    {
        $dateRange = $this->getChartDateRange();
        $data = [];
        
        $qcData = QualityControl::selectRaw('
            DATE(inspection_date) as date,
            SUM(sample_size) as total_samples,
            SUM(passed_quantity) as total_passed
        ')
        ->whereBetween('inspection_date', [$dateRange['start'], $dateRange['end']])
        ->groupBy('date')
        ->get()
        ->keyBy('date');
        
        for ($i = 0; $i < 7; $i++) {
            $date = $dateRange['start']->copy()->addDays($i);
            $dateStr = $date->format('Y-m-d');
            $qc = $qcData->get($dateStr);
            
            $passRate = $qc && $qc->total_samples > 0 
                ? round(($qc->total_passed / $qc->total_samples) * 100, 2) 
                : 0;
            
            $data[] = [
                'date' => $dateStr,
                'day' => $date->format('D'),
                'pass_rate' => $passRate
            ];
        }
        
        return $data;
    }

    private function getDefectDistribution()
    {
        return QualityControl::select('defect_category')
            ->selectRaw('COUNT(*) as count')
            ->whereNotNull('defect_category')
            ->groupBy('defect_category')
            ->get();
    }

    private function getQualityByProduct()
    {
        return DB::table('quality_controls')
            ->join('productions', 'quality_controls.production_id', '=', 'productions.id')
            ->join('product_types', 'productions.product_type_id', '=', 'product_types.id')
            ->select('product_types.name')
            ->selectRaw('
                ROUND(
                    CASE 
                        WHEN SUM(quality_controls.sample_size) > 0 
                        THEN (SUM(quality_controls.passed_quantity) / SUM(quality_controls.sample_size)) * 100 
                        ELSE 0 
                    END, 2
                ) as pass_rate
            ')
            ->groupBy('product_types.id', 'product_types.name')
            ->get();
    }

    // ========== GUDANG METHODS ==========

    private function getStockMovementTrend()
    {
        $dateRange = $this->getChartDateRange();
        $data = [];
        
        $movements = StockMovement::selectRaw('
            DATE(transaction_date) as date,
            SUM(CASE WHEN movement_type = "in" THEN quantity ELSE 0 END) as stock_in,
            SUM(CASE WHEN movement_type = "out" THEN quantity ELSE 0 END) as stock_out
        ')
        ->whereBetween('transaction_date', [$dateRange['start'], $dateRange['end']])
        ->groupBy('date')
        ->get()
        ->keyBy('date');
        
        for ($i = 0; $i < 7; $i++) {
            $date = $dateRange['start']->copy()->addDays($i);
            $dateStr = $date->format('Y-m-d');
            $movement = $movements->get($dateStr);
            
            $data[] = [
                'date' => $dateStr,
                'day' => $date->format('D'),
                'stock_in' => $movement->stock_in ?? 0,
                'stock_out' => $movement->stock_out ?? 0
            ];
        }
        
        return $data;
    }

    private function getMaterialUsage()
    {
        return RawMaterial::select('name')
            ->selectRaw('
                COALESCE(SUM(CASE WHEN stock_movements.movement_type = "out" THEN stock_movements.quantity ELSE 0 END), 0) as usage
            ')
            ->leftJoin('stock_movements', function($join) {
                $join->on('raw_materials.id', '=', 'stock_movements.item_id')
                     ->where('stock_movements.item_type', '=', 'App\\Models\\RawMaterial');
            })
            ->groupBy('raw_materials.id', 'raw_materials.name')
            ->orderBy('usage', 'desc')
            ->limit(10)
            ->get();
    }

    private function getDistributionStatus()
    {
        return Distribution::select('status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('status')
            ->get();
    }

    // ========== API STATS METHODS ==========

    private function getOperatorStatsForApi()
    {
        $operatorId = Auth::id();
        return [
            'my_production_today' => $this->getOperatorProduction($operatorId),
            'my_target_today' => $this->getOperatorTarget($operatorId),
            'my_efficiency' => $this->getOperatorEfficiency($operatorId),
            'current_shift' => ShiftHelper::getCurrentShift() // ✅ FIXED
        ];
    }

    private function getQCStatsForApi()
    {
        return [
            'inspections_today' => $this->getInspectionsToday(),
            'pass_rate_today' => $this->getTodayPassRate(),
            'failed_items_today' => $this->getFailedItemsToday(),
            'pending_inspections' => $this->getPendingInspections(),
            'current_shift' => ShiftHelper::getCurrentShift() // ✅ ADDED
        ];
    }

    private function getGudangStatsForApi()
    {
        return [
            'low_stock_alerts' => RawMaterial::whereRaw('current_stock <= minimum_stock')->count(),
            'stock_value' => RawMaterial::sum(DB::raw('current_stock * unit_price')),
            'distributions_today' => Distribution::whereDate('distribution_date', now())->count(),
            'movements_today' => StockMovement::whereDate('transaction_date', now())->count(),
            'current_shift' => ShiftHelper::getCurrentShift() // ✅ ADDED
        ];
    }

    // ========== FALLBACK DATA ==========

    private function getFallbackStats()
    {
        return [
            'total_production_today' => 1250,
            'total_production_month' => 28500,
            'efficiency_today' => 87.5,
            'quality_pass_rate' => 94.2,
            'low_stock_items' => 3,
            'active_machines' => 6,
            'pending_distributions' => 2,
            'total_users' => 8,
            'total_raw_materials' => 10,
            'current_shift' => ShiftHelper::getCurrentShift() // ✅ ADDED
        ];
    }

    private function getFallbackChartData()
    {
        return [
            'production_trend' => $this->getDefaultProductionData(),
            'efficiency_by_line' => collect([
                ['name' => 'LINE-A', 'efficiency' => 87],
                ['name' => 'LINE-B', 'efficiency' => 84],
                ['name' => 'LINE-C', 'efficiency' => 91],
                ['name' => 'LINE-D', 'efficiency' => 78]
            ]),
            'defect_categories' => collect([
                ['defect_category' => 'dimensional', 'total_defects' => 15],
                ['defect_category' => 'surface', 'total_defects' => 8],
                ['defect_category' => 'material', 'total_defects' => 5],
                ['defect_category' => 'assembly', 'total_defects' => 3]
            ]),
            'stock_levels' => collect([
                ['name' => 'Serbuk Logam', 'current_stock' => 1500, 'minimum_stock' => 200],
                ['name' => 'Resin Phenolic', 'current_stock' => 800, 'minimum_stock' => 100],
                ['name' => 'Serat Aramid', 'current_stock' => 250, 'minimum_stock' => 50],
                ['name' => 'Ceramic Filler', 'current_stock' => 450, 'minimum_stock' => 80]
            ])
        ];
    }

    private function getDefaultProductionData()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'production' => rand(800, 1500)
            ];
        }
        return $data;
    }
} 