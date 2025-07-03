<?php
// File: app/Http/Controllers/QualityControlController.php

namespace App\Http\Controllers;

use App\Models\QualityControl;
use App\Models\Production;
use App\Models\User;
use App\Models\ProductType;
use App\Services\NotificationService; // ✅ ADDED
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class QualityControlController extends Controller
{
    protected $notificationService; // ✅ ADDED

    /**
     * Constructor - Inject NotificationService
     */
    public function __construct(NotificationService $notificationService) // ✅ ADDED
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Inspection criteria configuration
     */
    protected $inspectionCriteria = [
        'dimensional' => [
            'name' => 'Dimensional Check',
            'parameters' => ['Length', 'Width', 'Thickness', 'Tolerances'],
            'is_critical' => true
        ],
        'visual' => [
            'name' => 'Visual Inspection',
            'parameters' => ['Surface Quality', 'Color', 'Texture', 'Defects'],
            'is_critical' => false
        ],
        'hardness' => [
            'name' => 'Hardness Test',
            'parameters' => ['HRC Value', 'Consistency', 'Distribution'],
            'is_critical' => true
        ],
        'friction' => [
            'name' => 'Friction Coefficient',
            'parameters' => ['Coefficient Value', 'Temperature Stability', 'Wear Rate'],
            'is_critical' => true
        ]
    ];

    public function index(Request $request)
    {
        // Get filters
        $filters = [
            'inspector_id' => $request->get('inspector_id'),
            'final_status' => $request->get('final_status'),
            'product_type' => $request->get('product_type'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'sort' => $request->get('sort', 'inspection_date'),
            'direction' => $request->get('direction', 'desc')
        ];

        // Base query with eager loading
        $query = QualityControl::with([
            'production.productType',
            'production.machine',
            'production.operator',
            'qcInspector'
        ]);

        // Apply role-based filtering
        if (Auth::user()->role->name === 'qc') {
            $query->where('qc_inspector_id', Auth::id());
        }

        // Apply filters
        if ($filters['inspector_id']) {
            $query->where('qc_inspector_id', $filters['inspector_id']);
        }

        if ($filters['final_status']) {
            $query->where('final_status', $filters['final_status']);
        }

        if ($filters['product_type']) {
            $query->whereHas('production.productType', function ($q) use ($filters) {
                $q->where('id', $filters['product_type']);
            });
        }

        if ($filters['date_from']) {
            $query->where('inspection_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->where('inspection_date', '<=', $filters['date_to']);
        }

        // Apply sorting
        $query->orderBy($filters['sort'], $filters['direction']);

        // Get paginated results
        $qualityControls = $query->paginate(20);

        // Get summary statistics
        $summaryStats = $this->getSummaryStats($filters);

        // Get filter options
        $inspectors = User::whereHas('role', function ($q) {
            $q->where('name', 'qc');
        })->get();

        $productTypes = ProductType::active()->get();

        // Get trends data untuk mini chart
        $trendsData = $this->getTrendsData(7); // 7 hari terakhir

        return view('quality-controls.index', compact(
            'qualityControls',
            'filters',
            'summaryStats',
            'inspectors',
            'productTypes',
            'trendsData'
        ));
    }

    public function create()
    {
        // Get available productions for inspection
        $availableProductions = Production::with(['productType', 'machine', 'operator'])
            ->where('status', 'completed')
            ->whereDoesntHave('qualityControls')
            ->orderBy('production_date', 'desc')
            ->limit(20)
            ->get();

        // Generate inspection number
        $inspectionNumber = $this->generateInspectionNumber();

        // Get QC inspectors
        $inspectors = User::whereHas('role', function ($q) {
            $q->where('name', 'qc');
        })->get();

        return view('quality-controls.create', [
            'availableProductions' => $availableProductions,
            'inspectionNumber' => $inspectionNumber,
            'inspectors' => $inspectors,
            'inspectionCriteria' => $this->inspectionCriteria
        ]);
    }

    public function store(Request $request)
    {
        // Validation rules
        $request->validate([
            'production_id' => 'required|exists:productions,id',
            'inspection_date' => 'required|date|before_or_equal:today',
            'qc_inspector_id' => 'nullable|exists:users,id',
            'sample_size' => 'required|integer|min:1|max:1000',
            'passed_quantity' => 'required|integer|min:0',
            'failed_quantity' => 'required|integer|min:0',
            'inspection_criteria' => 'required|array|min:1',
            'test_results' => 'required|array',
            'defect_category' => 'nullable|string',
            'defect_description' => 'nullable|string',
            'corrective_action' => 'nullable|string',
            'notes' => 'nullable|string'
        ], [
            'production_id.required' => 'Pilih produksi yang akan diinspeksi',
            'inspection_criteria.required' => 'Pilih minimal satu kriteria inspeksi',
            'sample_size.max' => 'Sample size maksimal 1000 unit'
        ]);

        // Additional validation
        $totalQuantity = $request->passed_quantity + $request->failed_quantity;
        if ($totalQuantity > $request->sample_size) {
            return back()->withErrors([
                'passed_quantity' => 'Total passed + failed quantity tidak boleh melebihi sample size'
            ])->withInput();
        }

        // Check if production already has QC inspection
        $existingQC = QualityControl::where('production_id', $request->production_id)->first();
        if ($existingQC) {
            return back()->withErrors([
                'production_id' => 'Produksi ini sudah memiliki inspeksi quality control'
            ])->withInput();
        }

        DB::beginTransaction();
        try {
            // Set inspector ID
            $inspectorId = $request->qc_inspector_id ?: Auth::id();

            // Determine final status based on test results
            $finalStatus = $this->determineFinalStatus(
                $request->test_results,
                $request->failed_quantity,
                $request->inspection_criteria
            );

            // Create quality control record
            $qualityControl = QualityControl::create([
                'inspection_number' => $this->generateInspectionNumber(),
                'production_id' => $request->production_id,
                'qc_inspector_id' => $inspectorId,
                'inspection_date' => $request->inspection_date,
                'sample_size' => $request->sample_size,
                'passed_quantity' => $request->passed_quantity,
                'failed_quantity' => $request->failed_quantity,
                'inspection_criteria' => $request->inspection_criteria,
                'test_results' => $request->test_results,
                'defect_category' => $request->defect_category,
                'defect_description' => $request->defect_description,
                'corrective_action' => $request->corrective_action,
                'final_status' => $finalStatus,
                'notes' => $this->generateInspectionNotes($request, $finalStatus)
            ]);

            // ✅ TRIGGER QC NOTIFICATIONS
            $this->notificationService->createQCNotification($qualityControl, 'inspection_completed');

            // ✅ TRIGGER STATUS-SPECIFIC NOTIFICATIONS
            switch ($finalStatus) {
                case 'approved':
                    $this->notificationService->createQCNotification($qualityControl, 'quality_passed');
                    break;
                    
                case 'rejected':
                    $this->notificationService->createQCNotification($qualityControl, 'quality_failed');
                    break;
                    
                case 'rework':
                    $this->notificationService->createQCNotification($qualityControl, 'rework_required');
                    break;
            }

            // Update production status berdasarkan hasil QC
            $this->updateProductionStatus($request->production_id, $finalStatus);

            // Clear cache
            Cache::forget('qc_summary_stats');
            Cache::flush(); // Clear all QC related cache

            DB::commit();

            return redirect()->route('quality-controls.show', $qualityControl)
                ->with('success', 'Inspeksi quality control berhasil dibuat dengan status: ' . strtoupper($finalStatus))
                ->with('trigger_update', true); // ✅ SIGNAL FOR FRONTEND UPDATE

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function show(QualityControl $qualityControl)
    {
        // Load relationships
        $qualityControl->load([
            'production.productType',
            'production.machine',
            'production.operator',
            'production.productionLine',
            'qcInspector'
        ]);

        // Decode JSON fields
        $inspectionCriteria = is_string($qualityControl->inspection_criteria) 
            ? json_decode($qualityControl->inspection_criteria, true) 
            : $qualityControl->inspection_criteria;

        $testResults = is_string($qualityControl->test_results) 
            ? json_decode($qualityControl->test_results, true) 
            : $qualityControl->test_results;

        // Get available criteria untuk reference
        $availableCriteria = $this->inspectionCriteria;

        // Build detailed criteria dengan actual data
        $detailedCriteria = [];
        if (is_array($inspectionCriteria)) {
            foreach ($inspectionCriteria as $criteriaKey) {
                if (isset($availableCriteria[$criteriaKey])) {
                    $detailedCriteria[$criteriaKey] = $availableCriteria[$criteriaKey];
                }
            }
        }

        // Calculate metrics
        $metrics = $this->calculateInspectionMetrics($qualityControl);

        // Get related inspections (same product type)
        $relatedInspections = QualityControl::with(['production.productType', 'qcInspector'])
            ->whereHas('production', function ($q) use ($qualityControl) {
                $q->where('product_type_id', $qualityControl->production->product_type_id);
            })
            ->where('id', '!=', $qualityControl->id)
            ->orderBy('inspection_date', 'desc')
            ->limit(6)
            ->get();

        return view('quality-controls.show', compact(
            'qualityControl',
            'inspectionCriteria',
            'testResults',
            'detailedCriteria',
            'metrics',
            'relatedInspections'
        ));
    }

    public function edit(QualityControl $qualityControl)
    {
        // Check permissions
        if (Auth::user()->role->name === 'qc' && $qualityControl->qc_inspector_id !== Auth::id()) {
            abort(403, 'Anda hanya dapat mengedit inspeksi yang Anda buat');
        }

        // Load relationships
        $qualityControl->load([
            'production.productType',
            'production.machine',
            'production.operator',
            'qcInspector'
        ]);

        // Decode JSON fields
        $inspectionCriteria = is_string($qualityControl->inspection_criteria) 
            ? json_decode($qualityControl->inspection_criteria, true) 
            : $qualityControl->inspection_criteria;

        $testResults = is_string($qualityControl->test_results) 
            ? json_decode($qualityControl->test_results, true) 
            : $qualityControl->test_results;

        // Get available criteria
        $availableCriteria = $this->inspectionCriteria;

        // Get QC inspectors
        $inspectors = User::whereHas('role', function ($q) {
            $q->where('name', 'qc');
        })->get();

        return view('quality-controls.edit', compact(
            'qualityControl',
            'inspectionCriteria',
            'testResults',
            'availableCriteria',
            'inspectors'
        ));
    }

    public function update(Request $request, QualityControl $qualityControl)
    {
        // Check permissions
        if (Auth::user()->role->name === 'qc' && $qualityControl->qc_inspector_id !== Auth::id()) {
            abort(403, 'Anda hanya dapat mengedit inspeksi yang Anda buat');
        }

        // Validation
        $request->validate([
            'inspection_date' => 'required|date|before_or_equal:today',
            'qc_inspector_id' => 'nullable|exists:users,id',
            'sample_size' => 'required|integer|min:1|max:1000',
            'passed_quantity' => 'required|integer|min:0',
            'failed_quantity' => 'required|integer|min:0',
            'inspection_criteria' => 'required|array|min:1',
            'test_results' => 'required|array',
            'defect_category' => 'nullable|string',
            'defect_description' => 'nullable|string',
            'corrective_action' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        // Additional validation
        $totalQuantity = $request->passed_quantity + $request->failed_quantity;
        if ($totalQuantity > $request->sample_size) {
            return back()->withErrors([
                'passed_quantity' => 'Total passed + failed quantity tidak boleh melebihi sample size'
            ])->withInput();
        }

        DB::beginTransaction();
        try {
            // Store old status for comparison
            $oldStatus = $qualityControl->final_status;

            // Determine final status
            $finalStatus = $this->determineFinalStatus(
                $request->test_results,
                $request->failed_quantity,
                $request->inspection_criteria
            );

            // Update quality control record
            $qualityControl->update([
                'inspection_date' => $request->inspection_date,
                'qc_inspector_id' => $request->qc_inspector_id ?: $qualityControl->qc_inspector_id,
                'sample_size' => $request->sample_size,
                'passed_quantity' => $request->passed_quantity,
                'failed_quantity' => $request->failed_quantity,
                'inspection_criteria' => $request->inspection_criteria,
                'test_results' => $request->test_results,
                'defect_category' => $request->defect_category,
                'defect_description' => $request->defect_description,
                'corrective_action' => $request->corrective_action,
                'final_status' => $finalStatus,
                'notes' => $request->notes
            ]);

            // ✅ TRIGGER NOTIFICATIONS IF STATUS CHANGED
            if ($oldStatus !== $finalStatus) {
                $this->notificationService->createQCNotification($qualityControl, 'inspection_completed');
                
                switch ($finalStatus) {
                    case 'approved':
                        $this->notificationService->createQCNotification($qualityControl, 'quality_passed');
                        break;
                        
                    case 'rejected':
                        $this->notificationService->createQCNotification($qualityControl, 'quality_failed');
                        break;
                        
                    case 'rework':
                        $this->notificationService->createQCNotification($qualityControl, 'rework_required');
                        break;
                }
            }

            // Update production status
            $this->updateProductionStatus($qualityControl->production_id, $finalStatus);

            // Clear cache
            Cache::forget('qc_summary_stats');
            Cache::flush();

            DB::commit();

            return redirect()->route('quality-controls.show', $qualityControl)
                ->with('success', 'Inspeksi quality control berhasil diperbarui')
                ->with('trigger_update', true); // ✅ SIGNAL FOR FRONTEND UPDATE

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withErrors([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * ✅ NEW METHOD: Update inspection status only via API
     */
    public function updateStatus(Request $request, QualityControl $qualityControl)
    {
        $validated = $request->validate([
            'final_status' => 'required|in:pending,approved,rejected,rework',
            'defect_category' => 'nullable|string',
            'defect_description' => 'nullable|string',
            'corrective_action' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $qualityControl->final_status;
            
            // Update QC status
            $qualityControl->update(array_filter($validated));

            // ✅ TRIGGER STATUS-BASED NOTIFICATIONS
            if ($oldStatus !== $validated['final_status']) {
                switch ($validated['final_status']) {
                    case 'approved':
                        $this->notificationService->createQCNotification($qualityControl, 'quality_passed');
                        break;
                        
                    case 'rejected':
                        $this->notificationService->createQCNotification($qualityControl, 'quality_failed');
                        break;
                        
                    case 'rework':
                        $this->notificationService->createQCNotification($qualityControl, 'rework_required');
                        break;
                }
                
                // Update production status
                $this->updateProductionStatus($qualityControl->production_id, $validated['final_status']);
            }

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Status inspeksi berhasil diperbarui',
                'trigger_update' => true // ✅ SIGNAL FOR FRONTEND UPDATE
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status inspeksi'
            ], 500);
        }
    }

    /**
     * ✅ NEW METHOD: Require inspection for production
     */
    public function requireInspection(Request $request, Production $production)
    {
        $validated = $request->validate([
            'priority' => 'nullable|in:normal,high,urgent'
        ]);

        try {
            // Check if production already has QC
            if ($production->qualityControls()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produksi ini sudah memiliki inspeksi QC'
                ], 400);
            }

            // Create placeholder QC record for tracking
            $qualityControl = QualityControl::create([
                'inspection_number' => $this->generateInspectionNumber(),
                'production_id' => $production->id,
                'qc_inspector_id' => null, // Will be assigned when inspection starts
                'inspection_date' => Carbon::today(),
                'sample_size' => 0,
                'passed_quantity' => 0,
                'failed_quantity' => 0,
                'inspection_criteria' => [],
                'test_results' => [],
                'final_status' => 'pending',
                'notes' => 'Menunggu inspeksi dari QC team'
            ]);

            // ✅ TRIGGER INSPECTION REQUIRED NOTIFICATION
            $this->notificationService->createQCNotification($qualityControl, 'inspection_required');

            return response()->json([
                'success' => true,
                'message' => 'Permintaan inspeksi berhasil dibuat',
                'data' => [
                    'inspection_number' => $qualityControl->inspection_number,
                    'qc_id' => $qualityControl->id
                ],
                'trigger_update' => true // ✅ SIGNAL FOR FRONTEND UPDATE
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat permintaan inspeksi'
            ], 500);
        }
    }

    public function destroy(QualityControl $qualityControl)
    {
        // Only admin can delete
        if (Auth::user()->role->name !== 'admin') {
            abort(403, 'Hanya admin yang dapat menghapus inspeksi');
        }

        DB::beginTransaction();
        try {
            $inspectionNumber = $qualityControl->inspection_number;
            $productionId = $qualityControl->production_id;

            // Delete the inspection
            $qualityControl->delete();

            // Reset production status jika perlu
            $this->resetProductionStatus($productionId);

            // Clear cache
            Cache::forget('qc_summary_stats');
            Cache::flush();

            DB::commit();

            return redirect()->route('quality-controls.index')
                ->with('success', "Inspeksi {$inspectionNumber} berhasil dihapus");

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withErrors([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function trends(Request $request)
    {
        $period = $request->get('period', '30'); // default 30 hari
        $chartType = $request->get('chart_type', 'pass_rate');
        $productType = $request->get('product_type');
        $inspectorId = $request->get('inspector_id');

        // Get trends data
        $trendsData = $this->getTrendsData($period, $productType, $inspectorId);
        
        // Get chart data berdasarkan type
        $chartData = $this->getChartDataByType($chartType, $period, $productType, $inspectorId);
        
        // Get summary statistics
        $summaryStats = $this->getTrendsSummaryStats($period, $productType, $inspectorId);
        
        // Get filter options
        $inspectors = User::whereHas('role', function ($q) {
            $q->where('name', 'qc');
        })->get();
        
        $productTypes = ProductType::active()->get();
        
        // Get insights berdasarkan data
        $insights = $this->generateTrendsInsights($trendsData, $summaryStats);

        return view('quality-controls.trends', compact(
            'trendsData',
            'chartData',
            'summaryStats',
            'insights',
            'inspectors',
            'productTypes',
            'period',
            'chartType',
            'productType',
            'inspectorId'
        ));
    }

    // API Methods untuk AJAX calls
    public function getChartData(Request $request)
    {
        $period = $request->get('period', '30');
        $chartType = $request->get('chart_type', 'pass_rate');
        $productType = $request->get('product_type');
        $inspectorId = $request->get('inspector_id');

        $cacheKey = "prodcore_cache_qc_chart_{$chartType}_{$period}days";
        if ($productType) $cacheKey .= "_product_{$productType}";
        if ($inspectorId) $cacheKey .= "_inspector_{$inspectorId}";

        $data = Cache::remember($cacheKey, 300, function () use ($period, $chartType, $productType, $inspectorId) {
            return $this->getChartDataByType($chartType, $period, $productType, $inspectorId);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'timestamp' => now()->toISOString(),
            'trigger_update' => true // ✅ SIGNAL FOR FRONTEND UPDATE
        ]);
    }

    public function getPassRate(Request $request)
    {
        $period = $request->get('period', '30');
        
        $data = Cache::remember("qc_pass_rate_{$period}days", 300, function () use ($period) {
            $query = QualityControl::where('inspection_date', '>=', Carbon::now()->subDays($period));
            
            if (Auth::user()->role->name === 'qc') {
                $query->where('qc_inspector_id', Auth::id());
            }
            
            $totalInspected = $query->sum(DB::raw('passed_quantity + failed_quantity'));
            $totalPassed = $query->sum('passed_quantity');
            $totalFailed = $query->sum('failed_quantity');
            
            $passRate = $totalInspected > 0 ? round(($totalPassed / $totalInspected) * 100, 1) : 0;
            
            return [
                'pass_rate' => $passRate,
                'total_inspected' => $totalInspected,
                'passed_quantity' => $totalPassed,
                'failed_quantity' => $totalFailed
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'trigger_update' => true // ✅ SIGNAL FOR FRONTEND UPDATE
        ]);
    }

    public function getDefects(Request $request)
    {
        $period = $request->get('period', '30');
        
        $data = Cache::remember("qc_defects_{$period}days", 300, function () use ($period) {
            $query = QualityControl::where('inspection_date', '>=', Carbon::now()->subDays($period))
                ->where('failed_quantity', '>', 0)
                ->whereNotNull('defect_category');
            
            if (Auth::user()->role->name === 'qc') {
                $query->where('qc_inspector_id', Auth::id());
            }
            
            return $query->select('defect_category', DB::raw('count(*) as count'))
                ->groupBy('defect_category')
                ->orderBy('count', 'desc')
                ->get();
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'trigger_update' => true // ✅ SIGNAL FOR FRONTEND UPDATE
        ]);
    }

    public function getStats(Request $request)
    {
        $period = $request->get('period', '30');
        
        $data = Cache::remember("qc_stats_{$period}days", 300, function () use ($period) {
            $query = QualityControl::where('inspection_date', '>=', Carbon::now()->subDays($period));
            
            if (Auth::user()->role->name === 'qc') {
                $query->where('qc_inspector_id', Auth::id());
            }
            
            $totalInspections = $query->count();
            $passedInspections = $query->where('final_status', 'approved')->count();
            $failedInspections = $query->where('final_status', 'rejected')->count();
            $reworkInspections = $query->where('final_status', 'rework')->count();
            
            $totalQuantity = $query->sum(DB::raw('passed_quantity + failed_quantity'));
            $passedQuantity = $query->sum('passed_quantity');
            $failedQuantity = $query->sum('failed_quantity');
            
            $passRate = $totalInspections > 0 ? round(($passedInspections / $totalInspections) * 100, 1) : 0;
            $quantityPassRate = $totalQuantity > 0 ? round(($passedQuantity / $totalQuantity) * 100, 1) : 0;
            
            return [
                'total_inspections' => $totalInspections,
                'passed_inspections' => $passedInspections,
                'failed_inspections' => $failedInspections,
                'rework_inspections' => $reworkInspections,
                'pass_rate' => $passRate,
                'total_quantity' => $totalQuantity,
                'passed_quantity' => $passedQuantity,
                'failed_quantity' => $failedQuantity,
                'quantity_pass_rate' => $quantityPassRate
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'trigger_update' => true // ✅ SIGNAL FOR FRONTEND UPDATE
        ]);
    }

    // Helper Methods
    private function generateInspectionNumber()
    {
        $today = Carbon::now();
        $prefix = 'QC' . $today->format('Ymd');
        
        $lastInspection = QualityControl::where('inspection_number', 'like', $prefix . '%')
            ->orderBy('inspection_number', 'desc')
            ->first();
        
        if ($lastInspection) {
            $lastNumber = intval(substr($lastInspection->inspection_number, -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        return $prefix . $newNumber;
    }

    private function determineFinalStatus($testResults, $failedQuantity, $criteria)
    {
        // Jika ada failed quantity, otomatis failed
        if ($failedQuantity > 0) {
            return 'rejected';
        }

        // Check critical test failures
        foreach ($testResults as $criteriaKey => $result) {
            if (isset($result['is_critical']) && $result['is_critical'] === 'true') {
                if (isset($result['result']) && $result['result'] === 'fail') {
                    return 'rejected';
                }
            }
        }

        // Check general test failures
        $failedTests = 0;
        $totalTests = count($testResults);
        
        foreach ($testResults as $result) {
            if (isset($result['result']) && $result['result'] === 'fail') {
                $failedTests++;
            }
        }

        // Jika lebih dari 50% test gagal, set sebagai rework
        if ($totalTests > 0 && ($failedTests / $totalTests) > 0.5) {
            return 'rework';
        }

        // Jika ada beberapa test gagal tapi tidak critical, approved dengan catatan
        return 'approved';
    }

    private function generateInspectionNotes($request, $finalStatus)
    {
        $batch = Production::find($request->production_id)->batch_number ?? '';
        
        $statusText = [
            'approved' => 'LOLOS - Batch memenuhi standar kualitas dan disetujui untuk distribusi',
            'rejected' => 'GAGAL - Batch tidak memenuhi standar kualitas dan ditolak untuk distribusi',
            'rework' => 'REWORK - Batch memerlukan perbaikan sebelum dapat didistribusikan'
        ];

        $baseNote = 'Hasil inspeksi ' . ($statusText[$finalStatus] ?? 'PENDING');
        $userNote = $request->notes ? ' - ' . $request->notes : '';
        $batchNote = $batch ? ' - Batch ' . $batch : '';

        return $baseNote . $batchNote . $userNote;
    }

    private function updateProductionStatus($productionId, $finalStatus)
    {
        $production = Production::find($productionId);
        if ($production) {
            $production->update([
                'status' => $finalStatus === 'approved' ? 'quality_approved' : 'quality_review'
            ]);
        }
    }

    private function resetProductionStatus($productionId)
    {
        $production = Production::find($productionId);
        if ($production) {
            $production->update(['status' => 'completed']);
        }
    }

    private function getSummaryStats($filters = [])
    {
        $cacheKey = 'qc_summary_stats_' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 300, function () use ($filters) {
            $query = QualityControl::query();

            // Apply role-based filtering
            if (Auth::user()->role->name === 'qc') {
                $query->where('qc_inspector_id', Auth::id());
            }

            // Apply same filters as index
            if (!empty($filters['inspector_id'])) {
                $query->where('qc_inspector_id', $filters['inspector_id']);
            }

            if (!empty($filters['final_status'])) {
                $query->where('final_status', $filters['final_status']);
            }

            if (!empty($filters['product_type'])) {
                $query->whereHas('production.productType', function ($q) use ($filters) {
                    $q->where('id', $filters['product_type']);
                });
            }

            if (!empty($filters['date_from'])) {
                $query->where('inspection_date', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->where('inspection_date', '<=', $filters['date_to']);
            }

            $totalInspections = $query->count();
            $passedInspections = $query->where('final_status', 'approved')->count();
            $failedInspections = $query->where('final_status', 'rejected')->count();
            
            $totalQuantity = $query->sum(DB::raw('passed_quantity + failed_quantity'));
            $passedQuantity = $query->sum('passed_quantity');
            $failedQuantity = $query->sum('failed_quantity');
            
            $passRate = $totalInspections > 0 ? round(($passedInspections / $totalInspections) * 100, 1) : 0;
            $quantityPassRate = $totalQuantity > 0 ? round(($passedQuantity / $totalQuantity) * 100, 1) : 0;

            return [
                'total_inspections' => $totalInspections,
                'passed_inspections' => $passedInspections,
                'failed_inspections' => $failedInspections,
                'pass_rate' => $passRate,
                'total_quantity' => $totalQuantity,
                'passed_quantity' => $passedQuantity,
                'failed_quantity' => $failedQuantity,
                'quantity_pass_rate' => $quantityPassRate
            ];
        });
    }

    private function getTrendsData($days, $productType = null, $inspectorId = null, $isPrevious = false)
    {
        $cacheKey = "qc_trends_{$days}days";
        if ($productType) $cacheKey .= "_product_{$productType}";
        if ($inspectorId) $cacheKey .= "_inspector_{$inspectorId}";
        if ($isPrevious) $cacheKey .= "_previous";

        return Cache::remember($cacheKey, 300, function () use ($days, $productType, $inspectorId, $isPrevious) {
            if ($isPrevious) {
                $endDate = Carbon::now()->subDays($days);
                $startDate = $endDate->copy()->subDays($days);
            } else {
                $startDate = Carbon::now()->subDays($days);
                $endDate = Carbon::now();
            }
            
            $query = QualityControl::with(['production.productType', 'qcInspector'])
                ->where('inspection_date', '>=', $startDate)
                ->where('inspection_date', '<=', $endDate)
                ->orderBy('inspection_date');

            if (Auth::user()->role->name === 'qc' && !$inspectorId) {
                $query->where('qc_inspector_id', Auth::id());
            } elseif ($inspectorId) {
                $query->where('qc_inspector_id', $inspectorId);
            }

            if ($productType) {
                $query->whereHas('production', function ($q) use ($productType) {
                    $q->where('product_type_id', $productType);
                });
            }

            return $query->get();
        });
    }

    private function getChartDataByType($chartType, $period, $productType = null, $inspectorId = null)
    {
        $trendsData = $this->getTrendsData($period, $productType, $inspectorId);

        switch ($chartType) {
            case 'pass_rate':
                return $this->generatePassRateChart($trendsData);
            
            case 'defects':
                return $this->generateDefectsChart($trendsData);
            
            case 'inspector_performance':
                return $this->generateInspectorPerformanceChart($trendsData);
            
            case 'product_quality':
                return $this->generateProductQualityChart($trendsData);
            
            default:
                return $this->generatePassRateChart($trendsData);
        }
    }

    private function generatePassRateChart($trendsData)
    {
        $groupedData = $trendsData->groupBy(function ($item) {
            return $item->inspection_date->format('Y-m-d');
        });

        $labels = collect();
        $data = collect();

        foreach ($groupedData as $date => $inspections) {
            $totalQuantity = $inspections->sum(function ($inspection) {
                return $inspection->passed_quantity + $inspection->failed_quantity;
            });
            
            $passedQuantity = $inspections->sum('passed_quantity');
            $passRate = $totalQuantity > 0 ? round(($passedQuantity / $totalQuantity) * 100, 1) : 0;

            $labels->push(Carbon::parse($date)->format('d/m'));
            $data->push($passRate);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Pass Rate (%)',
                    'data' => $data,
                    'borderColor' => '#28a745',
                    'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
                    'fill' => true
                ]
            ]
        ];
    }

    private function generateDefectsChart($trendsData)
    {
        $defects = $trendsData->where('failed_quantity', '>', 0)
            ->whereNotNull('defect_category')
            ->groupBy('defect_category')
            ->map(function ($group) {
                return $group->count();
            });

        return [
            'labels' => $defects->keys(),
            'datasets' => [
                [
                    'label' => 'Jumlah Defect',
                    'data' => $defects->values(),
                    'backgroundColor' => [
                        '#dc3545',
                        '#ffc107',
                        '#fd7e14',
                        '#6f42c1',
                        '#20c997',
                        '#17a2b8'
                    ]
                ]
            ]
        ];
    }

    private function generateInspectorPerformanceChart($trendsData)
    {
        $inspectorData = $trendsData->groupBy('qc_inspector_id')
            ->map(function ($inspections, $inspectorId) {
                $inspector = User::find($inspectorId);
                $totalInspections = $inspections->count();
                $passedInspections = $inspections->where('final_status', 'approved')->count();
                $passRate = $totalInspections > 0 ? round(($passedInspections / $totalInspections) * 100, 1) : 0;

                return [
                    'inspector' => $inspector ? $inspector->name : 'Unknown',
                    'inspections' => $totalInspections,
                    'pass_rate' => $passRate
                ];
            })
            ->values();

        return [
            'labels' => $inspectorData->pluck('inspector'),
            'datasets' => [
                [
                    'label' => 'Jumlah Inspeksi',
                    'data' => $inspectorData->pluck('inspections'),
                    'backgroundColor' => '#435ebe',
                    'yAxisID' => 'y'
                ],
                [
                    'label' => 'Pass Rate (%)',
                    'data' => $inspectorData->pluck('pass_rate'),
                    'backgroundColor' => '#28a745',
                    'type' => 'line',
                    'yAxisID' => 'y1'
                ]
            ]
        ];
    }

    private function generateProductQualityChart($trendsData)
    {
        $productData = $trendsData->groupBy('production.product_type_id')
            ->map(function ($inspections) {
                $totalQuantity = $inspections->sum(function ($inspection) {
                    return $inspection->passed_quantity + $inspection->failed_quantity;
                });
                
                $passedQuantity = $inspections->sum('passed_quantity');
                $passRate = $totalQuantity > 0 ? round(($passedQuantity / $totalQuantity) * 100, 1) : 0;

                $productName = $inspections->first()->production->productType->name ?? 'Unknown';

                return [
                    'product' => $productName,
                    'pass_rate' => $passRate,
                    'total_quantity' => $totalQuantity
                ];
            })
            ->values();

        return [
            'labels' => $productData->pluck('product'),
            'datasets' => [
                [
                    'label' => 'Pass Rate (%)',
                    'data' => $productData->pluck('pass_rate'),
                    'backgroundColor' => [
                        '#435ebe',
                        '#28a745',
                        '#ffc107',
                        '#dc3545',
                        '#6f42c1',
                        '#20c997',
                        '#17a2b8',
                        '#fd7e14',
                        '#e83e8c',
                        '#6c757d'
                    ]
                ]
            ]
        ];
    }

    private function getTrendsSummaryStats($period, $productType = null, $inspectorId = null)
    {
        $trendsData = $this->getTrendsData($period, $productType, $inspectorId);

        $totalInspections = $trendsData->count();
        $passedInspections = $trendsData->where('final_status', 'approved')->count();
        $failedInspections = $trendsData->where('final_status', 'rejected')->count();
        $reworkInspections = $trendsData->where('final_status', 'rework')->count();

        $totalQuantity = $trendsData->sum(function ($inspection) {
            return $inspection->passed_quantity + $inspection->failed_quantity;
        });
        
        $passedQuantity = $trendsData->sum('passed_quantity');
        $failedQuantity = $trendsData->sum('failed_quantity');

        $passRate = $totalInspections > 0 ? round(($passedInspections / $totalInspections) * 100, 1) : 0;
        $quantityPassRate = $totalQuantity > 0 ? round(($passedQuantity / $totalQuantity) * 100, 1) : 0;

        // Calculate trends (comparison dengan periode sebelumnya)
        $previousPeriodData = $this->getTrendsData($period, $productType, $inspectorId, true);
        $previousPassRate = $previousPeriodData->count() > 0 ? 
            round(($previousPeriodData->where('final_status', 'approved')->count() / $previousPeriodData->count()) * 100, 1) : 0;

        $trendDirection = $passRate > $previousPassRate ? 'up' : ($passRate < $previousPassRate ? 'down' : 'stable');
        $trendPercentage = $previousPassRate > 0 ? round((($passRate - $previousPassRate) / $previousPassRate) * 100, 1) : 0;

        return [
            'total_inspections' => $totalInspections,
            'passed_inspections' => $passedInspections,
            'failed_inspections' => $failedInspections,
            'rework_inspections' => $reworkInspections,
            'pass_rate' => $passRate,
            'total_quantity' => $totalQuantity,
            'passed_quantity' => $passedQuantity,
            'failed_quantity' => $failedQuantity,
            'quantity_pass_rate' => $quantityPassRate,
            'trend_direction' => $trendDirection,
            'trend_percentage' => abs($trendPercentage),
            'avg_sample_size' => $totalInspections > 0 ? round($trendsData->avg('sample_size'), 1) : 0
        ];
    }

    private function generateTrendsInsights($trendsData, $summaryStats)
    {
        $insights = [];

        // Pass rate insight
        if ($summaryStats['pass_rate'] >= 95) {
            $insights[] = [
                'type' => 'success',
                'title' => 'Excellent Quality Performance',
                'message' => "Pass rate {$summaryStats['pass_rate']}% menunjukkan kualitas produksi yang sangat baik.",
                'icon' => 'fas fa-check-circle'
            ];
        } elseif ($summaryStats['pass_rate'] >= 85) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Good Quality with Room for Improvement',
                'message' => "Pass rate {$summaryStats['pass_rate']}% masih dalam batas wajar, namun dapat ditingkatkan.",
                'icon' => 'fas fa-exclamation-triangle'
            ];
        } else {
            $insights[] = [
                'type' => 'danger',
                'title' => 'Quality Attention Required',
                'message' => "Pass rate {$summaryStats['pass_rate']}% di bawah standar. Perlu tindakan perbaikan segera.",
                'icon' => 'fas fa-times-circle'
            ];
        }

        // Trend insight
        if ($summaryStats['trend_direction'] === 'up') {
            $insights[] = [
                'type' => 'info',
                'title' => 'Positive Trend',
                'message' => "Kualitas meningkat {$summaryStats['trend_percentage']}% dibanding periode sebelumnya.",
                'icon' => 'fas fa-arrow-up'
            ];
        } elseif ($summaryStats['trend_direction'] === 'down') {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Declining Trend',
                'message' => "Kualitas menurun {$summaryStats['trend_percentage']}% dibanding periode sebelumnya.",
                'icon' => 'fas fa-arrow-down'
            ];
        }

        // Defect analysis
        $topDefects = $trendsData->where('failed_quantity', '>', 0)
            ->whereNotNull('defect_category')
            ->groupBy('defect_category')
            ->map->count()
            ->sortDesc()
            ->take(3);

        if ($topDefects->count() > 0) {
            $topDefect = $topDefects->keys()->first();
            $insights[] = [
                'type' => 'info',
                'title' => 'Top Defect Category',
                'message' => "Defect terbanyak: {$topDefect} ({$topDefects->first()} kasus). Focus perbaikan pada area ini.",
                'icon' => 'fas fa-chart-bar'
            ];
        }

        // Sample size insight
        if ($summaryStats['avg_sample_size'] < 10) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Low Sample Size',
                'message' => "Rata-rata sample size {$summaryStats['avg_sample_size']} terlalu kecil. Pertimbangkan untuk menaikkan sample size.",
                'icon' => 'fas fa-exclamation-circle'
            ];
        }

        return $insights;
    }

    private function calculateInspectionMetrics($qualityControl)
    {
        $totalSample = $qualityControl->passed_quantity + $qualityControl->failed_quantity;
        $productionQuantity = $qualityControl->production->good_quantity ?? 0;

        return [
            'sample_utilization' => $qualityControl->sample_size > 0 ? 
                round(($totalSample / $qualityControl->sample_size) * 100, 1) : 0,
            'defect_rate' => $totalSample > 0 ? 
                round(($qualityControl->failed_quantity / $totalSample) * 100, 1) : 0,
            'inspection_coverage' => $productionQuantity > 0 ? 
                round(($totalSample / $productionQuantity) * 100, 1) : 0
        ];
    }
}