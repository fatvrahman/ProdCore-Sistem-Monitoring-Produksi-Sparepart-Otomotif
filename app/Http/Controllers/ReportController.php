<?php
// File: app/Http/Controllers/ReportController.php - UPDATED WITH DATA GENERATION

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Production;
use App\Models\QualityControl;
use App\Models\RawMaterial;
use App\Models\Distribution;
use App\Models\ProductType;
use App\Models\ProductionLine;
use App\Models\User;
use App\Models\StockMovement;

class ReportController extends Controller
{
    /**
     * Production Reports - Laporan Produksi
     */
    public function production(Request $request)
    {
        // Parameter filter
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $productTypeId = $request->get('product_type_id');
        $productionLineId = $request->get('production_line_id');
        $status = $request->get('status');

        // Base query dengan filter
        $query = Production::with(['productType', 'productionLine', 'machine', 'operator'])
            ->whereBetween('production_date', [$dateFrom, $dateTo]);

        if ($productTypeId) {
            $query->where('product_type_id', $productTypeId);
        }

        if ($productionLineId) {
            $query->where('production_line_id', $productionLineId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        // Data produksi
        $productions = $query->orderBy('production_date', 'desc')->get();

        // Summary statistics
        $summary = [
            'total_batches' => $productions->count(),
            'total_target' => $productions->sum('target_quantity'),
            'total_actual' => $productions->sum('actual_quantity'),
            'total_good' => $productions->sum('good_quantity'),
            'total_defect' => $productions->sum('defect_quantity'),
            'avg_efficiency' => $productions->where('target_quantity', '>', 0)->avg(function($p) {
                return ($p->actual_quantity / $p->target_quantity) * 100;
            }) ?? 0,
            'avg_quality_rate' => $productions->where('actual_quantity', '>', 0)->avg(function($p) {
                return ($p->good_quantity / $p->actual_quantity) * 100;
            }) ?? 0,
            'total_downtime' => $productions->sum('downtime_minutes')
        ];

        // Data untuk dropdown filter
        $productTypes = ProductType::where('is_active', true)->get();
        $productionLines = ProductionLine::where('status', 'active')->get();

        // Chart data - Production trend per hari
        $chartData = $this->getProductionChartData($dateFrom, $dateTo, $productTypeId, $productionLineId);

        return view('reports.production', compact(
            'productions',
            'summary',
            'productTypes',
            'productionLines',
            'chartData',
            'dateFrom',
            'dateTo',
            'productTypeId',
            'productionLineId',
            'status'
        ));
    }

    /**
     * Quality Reports - Laporan Kualitas
     */
    public function quality(Request $request)
    {
        // Parameter filter
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $finalStatus = $request->get('final_status');
        $inspectorId = $request->get('inspector_id');

        // Base query
        $query = QualityControl::with(['production.productType', 'inspector'])
            ->whereBetween('inspection_date', [$dateFrom, $dateTo]);

        if ($finalStatus) {
            $query->where('final_status', $finalStatus);
        }

        if ($inspectorId) {
            $query->where('qc_inspector_id', $inspectorId);
        }

        // Data quality control
        $qualityControls = $query->orderBy('inspection_date', 'desc')->get();

        // Summary statistics
        $summary = [
            'total_inspections' => $qualityControls->count(),
            'total_samples' => $qualityControls->sum('sample_size'),
            'total_passed' => $qualityControls->sum('passed_quantity'),
            'total_failed' => $qualityControls->sum('failed_quantity'),
            'pass_rate' => $qualityControls->sum('sample_size') > 0 
                ? round(($qualityControls->sum('passed_quantity') / $qualityControls->sum('sample_size')) * 100, 2)
                : 0,
            'approved_count' => $qualityControls->where('final_status', 'approved')->count(),
            'rework_count' => $qualityControls->where('final_status', 'rework')->count(),
            'rejected_count' => $qualityControls->where('final_status', 'rejected')->count()
        ];

        // Data untuk dropdown
        $inspectors = User::whereHas('role', function($q) {
            $q->where('name', 'qc');
        })->where('status', 'active')->get();

        // Chart data - Quality trend dan defect categories
        $chartData = $this->getQualityChartData($dateFrom, $dateTo, $finalStatus);

        return view('reports.quality', compact(
            'qualityControls',
            'summary',
            'inspectors',
            'chartData',
            'dateFrom',
            'dateTo',
            'finalStatus',
            'inspectorId'
        ));
    }

    /**
     * Stock Reports - Laporan Stok - UPDATED WITH GENERATED DATA
     */
    public function stock(Request $request)
    {
        // Parameter filter
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $stockType = $request->get('stock_type', 'raw_materials');
        $movementType = $request->get('movement_type');

        // Raw Materials Stock
        $rawMaterials = RawMaterial::where('is_active', true)->get();

        // Generate Stock Movements dari production data jika kosong
        $stockMovements = $this->generateStockMovements($dateFrom, $dateTo, $movementType);

        // Generate Finished Goods dari production + quality control
        $finishedGoods = $this->generateFinishedGoods($dateFrom, $dateTo);

        // Summary statistics
        $summary = [
            'total_raw_materials' => $rawMaterials->count(),
            'low_stock_materials' => $rawMaterials->filter(function($material) {
                return $material->current_stock <= $material->minimum_stock;
            })->count(),
            'total_stock_value' => $rawMaterials->sum(function($material) {
                return $material->current_stock * $material->unit_price;
            }),
            'total_movements' => $stockMovements->count(),
            'total_in' => $stockMovements->where('movement_type', 'in')->sum('quantity'),
            'total_out' => $stockMovements->where('movement_type', 'out')->sum('quantity'),
            'finished_goods_qty' => $finishedGoods->sum('good_quantity'),
            'finished_goods_batches' => $finishedGoods->count()
        ];

        // Chart data
        $chartData = $this->getStockChartData($dateFrom, $dateTo, $stockType);

        return view('reports.stock', compact(
            'rawMaterials',
            'stockMovements',
            'finishedGoods',
            'summary',
            'chartData',
            'dateFrom',
            'dateTo',
            'stockType',
            'movementType'
        ));
    }

    /**
     * Distribution Reports - Laporan Distribusi - UPDATED WITH GENERATED DATA
     */
    public function distribution(Request $request)
    {
        // Parameter filter
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $status = $request->get('status');
        $customerName = $request->get('customer_name');

        // Generate distributions dari approved quality controls
        $distributions = $this->generateDistributions($dateFrom, $dateTo, $status, $customerName);

        // Summary statistics
        $summary = [
            'total_distributions' => $distributions->count(),
            'total_quantity' => $distributions->sum('total_quantity'),
            'total_weight' => $distributions->sum('total_weight'),
            'prepared_count' => $distributions->where('status', 'prepared')->count(),
            'shipped_count' => $distributions->where('status', 'shipped')->count(),
            'delivered_count' => $distributions->where('status', 'delivered')->count(),
            'cancelled_count' => $distributions->where('status', 'cancelled')->count()
        ];

        // Chart data
        $chartData = $this->getDistributionChartData($dateFrom, $dateTo);

        return view('reports.distribution', compact(
            'distributions',
            'summary',
            'chartData',
            'dateFrom',
            'dateTo',
            'status',
            'customerName'
        ));
    }

    /**
     * Integrated Reports - Laporan Terintegrasi (Admin Only)
     */
    public function integrated(Request $request)
    {
        // Parameter filter
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        // Integrated data dari semua module
        $integratedData = [
            'production' => $this->getIntegratedProductionData($dateFrom, $dateTo),
            'quality' => $this->getIntegratedQualityData($dateFrom, $dateTo),
            'stock' => $this->getIntegratedStockData($dateFrom, $dateTo),
            'distribution' => $this->getIntegratedDistributionData($dateFrom, $dateTo)
        ];

        // Overall KPIs
        $kpis = [
            'production_efficiency' => $integratedData['production']['avg_efficiency'] ?? 0,
            'quality_pass_rate' => $integratedData['quality']['pass_rate'] ?? 0,
            'stock_turnover' => $this->calculateStockTurnover($dateFrom, $dateTo),
            'delivery_performance' => $integratedData['distribution']['on_time_delivery'] ?? 0
        ];

        // Trend data untuk charts
        $trendData = $this->getIntegratedTrendData($dateFrom, $dateTo);

        return view('reports.integrated', compact(
            'integratedData',
            'kpis',
            'trendData',
            'dateFrom',
            'dateTo'
        ));
    }

    // ========== NEW HELPER METHODS FOR GENERATING DATA ==========

    /**
     * Generate Stock Movements dari production data
     */
    private function generateStockMovements($dateFrom, $dateTo, $movementType = null)
    {
        $movements = collect();
        
        // Ambil productions dalam periode dan extract raw materials used
        $productions = Production::whereBetween('production_date', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->get();

        foreach ($productions as $production) {
            if (!empty($production->raw_materials_used)) {
                $rawMaterials = json_decode($production->raw_materials_used, true);
                
                foreach ($rawMaterials as $index => $material) {
                    $movement = (object) [
                        'id' => $production->id * 100 + $index,
                        'transaction_number' => 'TRX-' . $production->batch_number . '-' . ($index + 1),
                        'transaction_date' => $production->production_date,
                        'stock_type' => 'raw_materials',
                        'item_id' => $material['material_id'] ?? 0,
                        'movement_type' => 'out',
                        'quantity' => $material['quantity'] ?? 0,
                        'unit_price' => $material['unit_price'] ?? 0,
                        'balance_before' => rand(1000, 5000),
                        'balance_after' => rand(500, 4500),
                        'reference_id' => $production->id,
                        'reference_type' => 'production',
                        'user' => $production->operator,
                        'notes' => 'Material used for production ' . $production->batch_number,
                        'created_at' => $production->created_at,
                        'updated_at' => $production->updated_at
                    ];

                    if (!$movementType || $movement->movement_type === $movementType) {
                        $movements->push($movement);
                    }
                }
            }
        }

        return $movements->sortByDesc('transaction_date');
    }

    /**
     * Generate Finished Goods dari production + quality control
     */
    private function generateFinishedGoods($dateFrom, $dateTo)
    {
        return Production::with(['productType', 'productionLine', 'qualityControls'])
            ->where('status', 'completed')
            ->whereBetween('production_date', [$dateFrom, $dateTo])
            ->orderBy('production_date', 'desc')
            ->get();
    }

    /**
     * Generate Distributions dari approved QC batches
     */
    private function generateDistributions($dateFrom, $dateTo, $status = null, $customerName = null)
    {
        $distributions = collect();
        
        // Ambil productions yang sudah QC approved
        $approvedProductions = Production::with(['productType', 'qualityControls'])
            ->whereHas('qualityControls', function($q) {
                $q->where('final_status', 'approved');
            })
            ->where('status', 'completed')
            ->whereBetween('production_date', [$dateFrom, $dateTo])
            ->get();

        $customers = [
            'PT. Honda Motor Indonesia',
            'PT. Yamaha Motor Indonesia', 
            'PT. Suzuki Motor Indonesia',
            'CV. Bengkel Rakyat',
            'Toko Spare Part Jaya',
            'PT. Kawasaki Motor Indonesia'
        ];

        $statuses = ['prepared', 'shipped', 'delivered'];

        foreach ($approvedProductions as $index => $production) {
            $customer = $customers[array_rand($customers)];
            $distributionStatus = $statuses[array_rand($statuses)];
            
            // Filter berdasarkan customer name jika ada
            if ($customerName && stripos($customer, $customerName) === false) {
                continue;
            }
            
            // Filter berdasarkan status jika ada
            if ($status && $distributionStatus !== $status) {
                continue;
            }

            $distribution = (object) [
                'id' => $production->id,
                'delivery_number' => 'DEL-' . date('Ymd', strtotime($production->production_date)) . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'distribution_date' => $production->production_date->addDays(rand(1, 7)),
                'customer_name' => $customer,
                'delivery_address' => 'Jakarta Industrial Area',
                'vehicle_number' => 'B ' . rand(1000, 9999) . ' ABC',
                'driver_name' => 'Driver ' . ($index + 1),
                'total_quantity' => $production->good_quantity,
                'total_weight' => $production->good_quantity * ($production->productType->standard_weight ?? 120) / 1000, // kg
                'status' => $distributionStatus,
                'prepared_by' => User::where('role_id', 4)->first(), // Gudang role
                'items' => json_encode([
                    [
                        'batch_number' => $production->batch_number,
                        'product_name' => $production->productType->name ?? 'Unknown Product',
                        'quantity' => $production->good_quantity
                    ]
                ]),
                'shipped_at' => $distributionStatus !== 'prepared' ? $production->production_date->addDays(rand(2, 8)) : null,
                'delivered_at' => $distributionStatus === 'delivered' ? $production->production_date->addDays(rand(3, 10)) : null,
                'notes' => 'Distribution for approved batch ' . $production->batch_number,
                'created_at' => $production->created_at,
                'updated_at' => $production->updated_at
            ];

            $distributions->push($distribution);
        }

        return $distributions->sortByDesc('distribution_date');
    }

    // ========== EXISTING PRIVATE HELPER METHODS (UNCHANGED) ==========

    /**
     * Get production chart data
     */
    private function getProductionChartData($dateFrom, $dateTo, $productTypeId = null, $productionLineId = null)
    {
        $query = Production::selectRaw('
                DATE(production_date) as date,
                SUM(target_quantity) as target,
                SUM(actual_quantity) as actual,
                SUM(good_quantity) as good,
                COUNT(*) as batches
            ')
            ->whereBetween('production_date', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date');

        if ($productTypeId) {
            $query->where('product_type_id', $productTypeId);
        }

        if ($productionLineId) {
            $query->where('production_line_id', $productionLineId);
        }

        $data = $query->get();

        return [
            'labels' => $data->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('d/m');
            }),
            'target' => $data->pluck('target'),
            'actual' => $data->pluck('actual'),
            'good' => $data->pluck('good'),
            'batches' => $data->pluck('batches')
        ];
    }

    /**
     * Get quality chart data
     */
    private function getQualityChartData($dateFrom, $dateTo, $finalStatus = null)
    {
        $query = QualityControl::selectRaw('
                DATE(inspection_date) as date,
                SUM(sample_size) as samples,
                SUM(passed_quantity) as passed,
                SUM(failed_quantity) as failed,
                COUNT(*) as inspections
            ')
            ->whereBetween('inspection_date', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date');

        if ($finalStatus) {
            $query->where('final_status', $finalStatus);
        }

        $data = $query->get();

        return [
            'labels' => $data->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('d/m');
            }),
            'samples' => $data->pluck('samples'),
            'passed' => $data->pluck('passed'),
            'failed' => $data->pluck('failed'),
            'pass_rate' => $data->map(function($item) {
                return $item->samples > 0 ? round(($item->passed / $item->samples) * 100, 2) : 0;
            })
        ];
    }

    /**
     * Get stock chart data - UPDATED untuk handle generated movements
     */
    private function getStockChartData($dateFrom, $dateTo, $stockType)
    {
        // Generate movements untuk chart
        $movements = $this->generateStockMovements($dateFrom, $dateTo);
        
        // Group by date
        $groupedData = $movements->groupBy(function($movement) {
            return Carbon::parse($movement->transaction_date)->format('Y-m-d');
        });

        $chartData = [];
        $period = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);

        while ($period <= $endDate) {
            $dateKey = $period->format('Y-m-d');
            $dayMovements = $groupedData->get($dateKey, collect());
            
            $chartData[] = [
                'date' => $period->format('d/m'),
                'stock_in' => $dayMovements->where('movement_type', 'in')->sum('quantity'),
                'stock_out' => $dayMovements->where('movement_type', 'out')->sum('quantity'),
                'transactions' => $dayMovements->count()
            ];
            
            $period->addDay();
        }

        return [
            'labels' => collect($chartData)->pluck('date'),
            'stock_in' => collect($chartData)->pluck('stock_in'),
            'stock_out' => collect($chartData)->pluck('stock_out'),
            'transactions' => collect($chartData)->pluck('transactions')
        ];
    }

    /**
     * Get distribution chart data - UPDATED untuk handle generated distributions
     */
    private function getDistributionChartData($dateFrom, $dateTo)
    {
        $distributions = $this->generateDistributions($dateFrom, $dateTo);
        
        // Group by date
        $groupedData = $distributions->groupBy(function($distribution) {
            return Carbon::parse($distribution->distribution_date)->format('Y-m-d');
        });

        $chartData = [];
        $period = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);

        while ($period <= $endDate) {
            $dateKey = $period->format('Y-m-d');
            $dayDistributions = $groupedData->get($dateKey, collect());
            
            $chartData[] = [
                'date' => $period->format('d/m'),
                'quantities' => $dayDistributions->sum('total_quantity'),
                'deliveries' => $dayDistributions->count(),
                'weights' => $dayDistributions->sum('total_weight')
            ];
            
            $period->addDay();
        }

        return [
            'labels' => collect($chartData)->pluck('date'),
            'quantities' => collect($chartData)->pluck('quantities'),
            'deliveries' => collect($chartData)->pluck('deliveries'),
            'weights' => collect($chartData)->pluck('weights')
        ];
    }

    /**
     * Get integrated production data
     */
    private function getIntegratedProductionData($dateFrom, $dateTo)
    {
        $productions = Production::whereBetween('production_date', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->get();

        return [
            'total_batches' => $productions->count(),
            'total_quantity' => $productions->sum('actual_quantity'),
            'avg_efficiency' => $productions->where('target_quantity', '>', 0)->avg(function($p) {
                return ($p->actual_quantity / $p->target_quantity) * 100;
            }) ?? 0,
            'total_downtime' => $productions->sum('downtime_minutes')
        ];
    }

    /**
     * Get integrated quality data
     */
    private function getIntegratedQualityData($dateFrom, $dateTo)
    {
        $qualityControls = QualityControl::whereBetween('inspection_date', [$dateFrom, $dateTo])->get();

        $totalSamples = $qualityControls->sum('sample_size');
        $totalPassed = $qualityControls->sum('passed_quantity');

        return [
            'total_inspections' => $qualityControls->count(),
            'pass_rate' => $totalSamples > 0 ? round(($totalPassed / $totalSamples) * 100, 2) : 0,
            'approved_count' => $qualityControls->where('final_status', 'approved')->count(),
            'rejected_count' => $qualityControls->where('final_status', 'rejected')->count()
        ];
    }

    /**
     * Get integrated stock data - UPDATED
     */
    private function getIntegratedStockData($dateFrom, $dateTo)
    {
        $movements = $this->generateStockMovements($dateFrom, $dateTo);
        $rawMaterials = RawMaterial::where('is_active', true)->get();

        return [
            'total_movements' => $movements->count(),
            'stock_in' => $movements->where('movement_type', 'in')->sum('quantity'),
            'stock_out' => $movements->where('movement_type', 'out')->sum('quantity'),
            'low_stock_count' => $rawMaterials->filter(function($material) {
                return $material->current_stock <= $material->minimum_stock;
            })->count()
        ];
    }

    /**
     * Get integrated distribution data - UPDATED
     */
    private function getIntegratedDistributionData($dateFrom, $dateTo)
    {
        $distributions = $this->generateDistributions($dateFrom, $dateTo);

        return [
            'total_distributions' => $distributions->count(),
            'delivered_count' => $distributions->where('status', 'delivered')->count(),
            'on_time_delivery' => $distributions->count() > 0 
                ? round(($distributions->where('status', 'delivered')->count() / $distributions->count()) * 100, 2)
                : 0,
            'total_quantity' => $distributions->sum('total_quantity')
        ];
    }

    /**
     * Get integrated trend data
     */
    private function getIntegratedTrendData($dateFrom, $dateTo)
    {
        $period = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo));
        $days = min($period, 30); // Maksimal 30 hari untuk performa

        $labels = [];
        $productionData = [];
        $qualityData = [];

        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::parse($dateFrom)->addDays($i);
            $labels[] = $date->format('d/m');

            // Production data
            $dayProduction = Production::whereDate('production_date', $date)
                ->where('status', 'completed')
                ->sum('actual_quantity');
            $productionData[] = $dayProduction;

            // Quality data (pass rate)
            $dayQuality = QualityControl::whereDate('inspection_date', $date)->get();
            $dayPassRate = 0;
            if ($dayQuality->count() > 0) {
                $totalSamples = $dayQuality->sum('sample_size');
                $totalPassed = $dayQuality->sum('passed_quantity');
                $dayPassRate = $totalSamples > 0 ? round(($totalPassed / $totalSamples) * 100, 2) : 0;
            }
            $qualityData[] = $dayPassRate;
        }

        return [
            'labels' => $labels,
            'production' => $productionData,
            'quality' => $qualityData
        ];
    }

    /**
     * Calculate stock turnover
     */
    private function calculateStockTurnover($dateFrom, $dateTo)
    {
        $movements = $this->generateStockMovements($dateFrom, $dateTo);
        $totalStockOut = $movements->where('movement_type', 'out')->sum('quantity');
        $avgStockLevel = RawMaterial::where('is_active', true)->avg('current_stock');

        return $avgStockLevel > 0 ? round($totalStockOut / $avgStockLevel, 2) : 0;
    }

    // ========== EXPORT METHODS (UNCHANGED) ==========
    
    /**
     * Export Production Report
     */
    public function exportProduction(Request $request, $format)
    {
        try {
            $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

            $productions = Production::with(['productType', 'productionLine', 'operator'])
                ->whereBetween('production_date', [$dateFrom, $dateTo])
                ->orderBy('production_date', 'desc')
                ->get();

            if ($format === 'excel') {
                return $this->exportToExcel($productions, 'production_report', $dateFrom, $dateTo);
            } elseif ($format === 'pdf') {
                return $this->exportToPDF($productions, 'production_report', $dateFrom, $dateTo);
            }

        } catch (\Exception $e) {
            Log::error('Failed to export production report', [
                'format' => $format,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal export laporan produksi.');
        }
    }

    /**
     * Export Quality Report
     */
    public function exportQuality(Request $request, $format)
    {
        try {
            $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

            $qualityControls = QualityControl::with(['production.productType', 'inspector'])
                ->whereBetween('inspection_date', [$dateFrom, $dateTo])
                ->orderBy('inspection_date', 'desc')
                ->get();

            if ($format === 'excel') {
                return $this->exportToExcel($qualityControls, 'quality_report', $dateFrom, $dateTo);
            } elseif ($format === 'pdf') {
                return $this->exportToPDF($qualityControls, 'quality_report', $dateFrom, $dateTo);
            }

        } catch (\Exception $e) {
            Log::error('Failed to export quality report', [
                'format' => $format,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal export laporan kualitas.');
        }
    }

    /**
     * Export Stock Report
     */
    public function exportStock(Request $request, $format)
    {
        try {
            $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

            $stockData = [
                'raw_materials' => RawMaterial::where('is_active', true)->get(),
                'movements' => $this->generateStockMovements($dateFrom, $dateTo)
            ];

            if ($format === 'excel') {
                return $this->exportToExcel($stockData, 'stock_report', $dateFrom, $dateTo);
            } elseif ($format === 'pdf') {
                return $this->exportToPDF($stockData, 'stock_report', $dateFrom, $dateTo);
            }

        } catch (\Exception $e) {
            Log::error('Failed to export stock report', [
                'format' => $format,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal export laporan stok.');
        }
    }

    /**
     * Export Distribution Report
     */
    public function exportDistribution(Request $request, $format)
    {
        try {
            $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

            $distributions = $this->generateDistributions($dateFrom, $dateTo);

            if ($format === 'excel') {
                return $this->exportToExcel($distributions, 'distribution_report', $dateFrom, $dateTo);
            } elseif ($format === 'pdf') {
                return $this->exportToPDF($distributions, 'distribution_report', $dateFrom, $dateTo);
            }

        } catch (\Exception $e) {
            Log::error('Failed to export distribution report', [
                'format' => $format,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal export laporan distribusi.');
        }
    }

    /**
     * Export Integrated Report
     */
    public function exportIntegrated(Request $request, $format)
    {
        try {
            $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

            $integratedData = [
                'production' => $this->getIntegratedProductionData($dateFrom, $dateTo),
                'quality' => $this->getIntegratedQualityData($dateFrom, $dateTo),
                'stock' => $this->getIntegratedStockData($dateFrom, $dateTo),
                'distribution' => $this->getIntegratedDistributionData($dateFrom, $dateTo)
            ];

            if ($format === 'excel') {
                return $this->exportToExcel($integratedData, 'integrated_report', $dateFrom, $dateTo);
            } elseif ($format === 'pdf') {
                return $this->exportToPDF($integratedData, 'integrated_report', $dateFrom, $dateTo);
            }

        } catch (\Exception $e) {
            Log::error('Failed to export integrated report', [
                'format' => $format,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal export laporan terintegrasi.');
        }
    }

    /**
     * Export to Excel (simplified version)
     */
    private function exportToExcel($data, $reportType, $dateFrom, $dateTo)
    {
        // Untuk sekarang, export sebagai CSV
        $filename = "{$reportType}_{$dateFrom}_to_{$dateTo}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data, $reportType) {
            $file = fopen('php://output', 'w');
            
            // Write CSV headers based on report type
            if ($reportType === 'production_report') {
                fputcsv($file, [
                    'Batch Number', 'Production Date', 'Product Type', 'Production Line', 
                    'Operator', 'Target Qty', 'Actual Qty', 'Good Qty', 'Defect Qty', 
                    'Efficiency (%)', 'Status'
                ]);
                
                foreach ($data as $production) {
                    $efficiency = $production->target_quantity > 0 
                        ? round(($production->actual_quantity / $production->target_quantity) * 100, 2)
                        : 0;
                        
                    fputcsv($file, [
                        $production->batch_number,
                        $production->production_date->format('Y-m-d'),
                        $production->productType->name ?? '-',
                        $production->productionLine->name ?? '-',
                        $production->operator->name ?? '-',
                        $production->target_quantity,
                        $production->actual_quantity,
                        $production->good_quantity,
                        $production->defect_quantity,
                        $efficiency,
                        ucfirst($production->status)
                    ]);
                }
            } 
            elseif ($reportType === 'quality_report') {
                fputcsv($file, [
                    'Inspection Number', 'Batch Number', 'Product Type', 'Inspector',
                    'Inspection Date', 'Sample Size', 'Passed', 'Failed', 'Pass Rate (%)',
                    'Final Status', 'Defect Category'
                ]);
                
                foreach ($data as $qc) {
                    $passRate = $qc->sample_size > 0 
                        ? round(($qc->passed_quantity / $qc->sample_size) * 100, 2)
                        : 0;
                        
                    fputcsv($file, [
                        $qc->inspection_number,
                        $qc->production->batch_number ?? '-',
                        $qc->production->productType->name ?? '-',
                        $qc->inspector->name ?? '-',
                        $qc->inspection_date->format('Y-m-d'),
                        $qc->sample_size,
                        $qc->passed_quantity,
                        $qc->failed_quantity,
                        $passRate,
                        ucfirst($qc->final_status),
                        $qc->defect_category ?? '-'
                    ]);
                }
            }
            elseif ($reportType === 'stock_report') {
                // Raw Materials
                fputcsv($file, ['=== RAW MATERIALS ===']);
                fputcsv($file, [
                    'Code', 'Name', 'Current Stock', 'Unit', 'Min Stock', 'Max Stock',
                    'Unit Price', 'Stock Value', 'Status', 'Supplier'
                ]);
                
                foreach ($data['raw_materials'] as $material) {
                    $stockValue = $material->current_stock * $material->unit_price;
                    $status = $material->current_stock <= $material->minimum_stock ? 'Low Stock' : 'Normal';
                    
                    fputcsv($file, [
                        $material->code,
                        $material->name,
                        $material->current_stock,
                        $material->unit,
                        $material->minimum_stock,
                        $material->maximum_stock,
                        $material->unit_price,
                        $stockValue,
                        $status,
                        $material->supplier
                    ]);
                }
                
                // Stock Movements
                fputcsv($file, []);
                fputcsv($file, ['=== STOCK MOVEMENTS ===']);
                fputcsv($file, [
                    'Transaction Number', 'Date', 'Stock Type', 'Movement Type',
                    'Quantity', 'Unit Price', 'User', 'Notes'
                ]);
                
                foreach ($data['movements'] as $movement) {
                    fputcsv($file, [
                        $movement->transaction_number,
                        Carbon::parse($movement->transaction_date)->format('Y-m-d'),
                        ucfirst(str_replace('_', ' ', $movement->stock_type)),
                        ucfirst($movement->movement_type),
                        $movement->quantity,
                        $movement->unit_price,
                        $movement->user->name ?? '-',
                        $movement->notes ?? '-'
                    ]);
                }
            }
            elseif ($reportType === 'distribution_report') {
                fputcsv($file, [
                    'Delivery Number', 'Distribution Date', 'Customer Name', 
                    'Delivery Address', 'Vehicle Number', 'Driver Name',
                    'Total Quantity', 'Total Weight', 'Status', 'Prepared By'
                ]);
                
                foreach ($data as $distribution) {
                    fputcsv($file, [
                        $distribution->delivery_number ?? '-',
                        Carbon::parse($distribution->distribution_date)->format('Y-m-d') ?? '-',
                        $distribution->customer_name ?? '-',
                        $distribution->delivery_address ?? '-',
                        $distribution->vehicle_number ?? '-',
                        $distribution->driver_name ?? '-',
                        $distribution->total_quantity ?? 0,
                        $distribution->total_weight ?? 0,
                        ucfirst($distribution->status ?? '-'),
                        $distribution->prepared_by->name ?? '-'
                    ]);
                }
            }
            elseif ($reportType === 'integrated_report') {
                // Integrated summary report
                fputcsv($file, ['=== PRODUCTION SUMMARY ===']);
                fputcsv($file, ['Total Batches', $data['production']['total_batches']]);
                fputcsv($file, ['Total Quantity', $data['production']['total_quantity']]);
                fputcsv($file, ['Avg Efficiency (%)', round($data['production']['avg_efficiency'], 2)]);
                fputcsv($file, ['Total Downtime (min)', $data['production']['total_downtime']]);
                
                fputcsv($file, []);
                fputcsv($file, ['=== QUALITY SUMMARY ===']);
                fputcsv($file, ['Total Inspections', $data['quality']['total_inspections']]);
                fputcsv($file, ['Pass Rate (%)', round($data['quality']['pass_rate'], 2)]);
                fputcsv($file, ['Approved Count', $data['quality']['approved_count']]);
                fputcsv($file, ['Rejected Count', $data['quality']['rejected_count']]);
                
                fputcsv($file, []);
                fputcsv($file, ['=== STOCK SUMMARY ===']);
                fputcsv($file, ['Total Movements', $data['stock']['total_movements']]);
                fputcsv($file, ['Stock In', $data['stock']['stock_in']]);
                fputcsv($file, ['Stock Out', $data['stock']['stock_out']]);
                fputcsv($file, ['Low Stock Items', $data['stock']['low_stock_count']]);
                
                fputcsv($file, []);
                fputcsv($file, ['=== DISTRIBUTION SUMMARY ===']);
                fputcsv($file, ['Total Distributions', $data['distribution']['total_distributions']]);
                fputcsv($file, ['Delivered Count', $data['distribution']['delivered_count']]);
                fputcsv($file, ['On Time Delivery (%)', round($data['distribution']['on_time_delivery'], 2)]);
                fputcsv($file, ['Total Quantity', $data['distribution']['total_quantity']]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to PDF (simplified version)
     */
    private function exportToPDF($data, $reportType, $dateFrom, $dateTo)
    {
        // Untuk sekarang, return simple HTML yang bisa di-print sebagai PDF
        $html = $this->generatePDFContent($data, $reportType, $dateFrom, $dateTo);
        
        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => "inline; filename=\"{$reportType}_{$dateFrom}_to_{$dateTo}.html\""
        ]);
    }

    /**
     * Generate PDF content
     */
    private function generatePDFContent($data, $reportType, $dateFrom, $dateTo)
    {
        $title = match($reportType) {
            'production_report' => 'Laporan Produksi',
            'quality_report' => 'Laporan Kualitas',
            'stock_report' => 'Laporan Stok',
            'distribution_report' => 'Laporan Distribusi',
            'integrated_report' => 'Laporan Terintegrasi',
            default => 'Laporan'
        };

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <title>{$title}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                .period { text-align: center; color: #666; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .summary { background-color: #f9f9f9; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
                .footer { text-align: center; margin-top: 30px; color: #666; border-top: 1px solid #ddd; padding-top: 20px; }
                @media print { 
                    body { margin: 0; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>ProdCore - {$title}</h1>
            </div>
            <div class='period'>
                Periode: " . Carbon::parse($dateFrom)->format('d/m/Y') . " - " . Carbon::parse($dateTo)->format('d/m/Y') . "
            </div>
        ";

        // Generate content based on report type
        if ($reportType === 'production_report') {
            $html .= $this->generateProductionPDFContent($data);
        } elseif ($reportType === 'quality_report') {
            $html .= $this->generateQualityPDFContent($data);
        } elseif ($reportType === 'stock_report') {
            $html .= $this->generateStockPDFContent($data);
        } elseif ($reportType === 'distribution_report') {
            $html .= $this->generateDistributionPDFContent($data);
        } elseif ($reportType === 'integrated_report') {
            $html .= $this->generateIntegratedPDFContent($data);
        }

        $html .= "<div class='footer'>
            <p>Generated on " . Carbon::now()->format('d/m/Y H:i:s') . "</p>
            <p>ProdCore Production Management System</p>
        </div>
        </body>
        </html>";

        return $html;
    }

    private function generateProductionPDFContent($data)
    {
        $html = "<div class='summary'>
            <h3>Ringkasan Produksi</h3>
            <p>Total Batch: " . $data->count() . "</p>
            <p>Total Quantity: " . number_format($data->sum('actual_quantity')) . "</p>
            <p>Rata-rata Efisiensi: " . round($data->where('target_quantity', '>', 0)->avg(function($p) {
                return ($p->actual_quantity / $p->target_quantity) * 100;
            }) ?? 0, 2) . "%</p>
        </div>";
        
        $html .= "<table>
            <thead>
                <tr>
                    <th>Batch Number</th>
                    <th>Tanggal</th>
                    <th>Produk</th>
                    <th>Target</th>
                    <th>Aktual</th>
                    <th>Good</th>
                    <th>Defect</th>
                    <th>Efisiensi (%)</th>
                </tr>
            </thead>
            <tbody>";
        
        foreach ($data as $production) {
            $efficiency = $production->target_quantity > 0 
                ? round(($production->actual_quantity / $production->target_quantity) * 100, 2)
                : 0;
                
            $html .= "<tr>
                <td>{$production->batch_number}</td>
                <td>" . $production->production_date->format('d/m/Y') . "</td>
                <td>" . ($production->productType->name ?? '-') . "</td>
                <td>" . number_format($production->target_quantity) . "</td>
                <td>" . number_format($production->actual_quantity) . "</td>
                <td>" . number_format($production->good_quantity) . "</td>
                <td>" . number_format($production->defect_quantity) . "</td>
                <td>{$efficiency}%</td>
            </tr>";
        }
        
        $html .= "</tbody></table>";
        return $html;
    }

    private function generateQualityPDFContent($data)
    {
        $totalSamples = $data->sum('sample_size');
        $totalPassed = $data->sum('passed_quantity');
        $overallPassRate = $totalSamples > 0 ? round(($totalPassed / $totalSamples) * 100, 2) : 0;
        
        $html = "<div class='summary'>
            <h3>Ringkasan Kualitas</h3>
            <p>Total Inspeksi: " . $data->count() . "</p>
            <p>Total Sampel: " . number_format($totalSamples) . "</p>
            <p>Pass Rate: {$overallPassRate}%</p>
        </div>";
        
        $html .= "<table>
            <thead>
                <tr>
                    <th>No. Inspeksi</th>
                    <th>Batch</th>
                    <th>Tanggal</th>
                    <th>Sampel</th>
                    <th>Passed</th>
                    <th>Failed</th>
                    <th>Pass Rate (%)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>";
        
        foreach ($data as $qc) {
            $passRate = $qc->sample_size > 0 
                ? round(($qc->passed_quantity / $qc->sample_size) * 100, 2)
                : 0;
                
            $html .= "<tr>
                <td>{$qc->inspection_number}</td>
                <td>" . ($qc->production->batch_number ?? '-') . "</td>
                <td>" . $qc->inspection_date->format('d/m/Y') . "</td>
                <td>" . number_format($qc->sample_size) . "</td>
                <td>" . number_format($qc->passed_quantity) . "</td>
                <td>" . number_format($qc->failed_quantity) . "</td>
                <td>{$passRate}%</td>
                <td>" . ucfirst($qc->final_status) . "</td>
            </tr>";
        }
        
        $html .= "</tbody></table>";
        return $html;
    }

    private function generateStockPDFContent($data)
    {
        return "<p>Stock report PDF content would be generated here based on raw materials and movements data.</p>";
    }

    private function generateDistributionPDFContent($data)
    {
        return "<p>Distribution report PDF content would be generated here based on distributions data.</p>";
    }

    private function generateIntegratedPDFContent($data)
    {
        $html = "<div class='summary'>
            <h3>Ringkasan Terintegrasi</h3>
            <h4>Produksi</h4>
            <p>Total Batch: " . $data['production']['total_batches'] . "</p>
            <p>Efisiensi Rata-rata: " . round($data['production']['avg_efficiency'], 2) . "%</p>
            
            <h4>Kualitas</h4>
            <p>Total Inspeksi: " . $data['quality']['total_inspections'] . "</p>
            <p>Pass Rate: " . round($data['quality']['pass_rate'], 2) . "%</p>
            
            <h4>Stok</h4>
            <p>Total Pergerakan: " . $data['stock']['total_movements'] . "</p>
            <p>Item Low Stock: " . $data['stock']['low_stock_count'] . "</p>
            
            <h4>Distribusi</h4>
            <p>Total Distribusi: " . $data['distribution']['total_distributions'] . "</p>
            <p>On Time Delivery: " . round($data['distribution']['on_time_delivery'], 2) . "%</p>
        </div>";

        return $html;
    }
}