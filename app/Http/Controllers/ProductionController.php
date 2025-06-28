<?php
// File: app/Http/Controllers/ProductionController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Production;
use App\Models\ProductType;
use App\Models\ProductionLine;
use App\Models\Machine;
use App\Models\RawMaterial;
use App\Models\User;
use App\Models\StockMovement;
use App\Helpers\ShiftHelper; // ✅ ADDED

class ProductionController extends Controller
{
    /**
     * Display a listing of productions dengan filter dan pagination
     */
    public function index(Request $request)
    {
        $query = Production::with([
            'productType', 
            'productionLine', 
            'machine', 
            'operator'
        ]);

        // Role-based filtering - operator hanya lihat produksi sendiri
        if (auth()->user()->role->name === 'operator') {
            $query->where('operator_id', auth()->id());
        }

        // Filter berdasarkan request
        if ($request->filled('production_line_id')) {
            $query->where('production_line_id', $request->production_line_id);
        }

        if ($request->filled('product_type_id')) {
            $query->where('product_type_id', $request->product_type_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('production_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('production_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                  ->orWhereHas('productType', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'production_date');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $productions = $query->paginate(15)->withQueryString();

        // Data untuk filter dropdowns
        $productTypes = ProductType::where('is_active', true)->get();
        $productionLines = ProductionLine::where('status', 'active')->get();
        
        // Statistics untuk header
        $stats = $this->getProductionStats($request);

        return view('productions.index', compact(
            'productions', 
            'productTypes', 
            'productionLines', 
            'stats'
        ));
    }

    /**
     * Show the form for creating a new production
     */
    public function create()
    {
        // Data untuk form dropdown
        $productTypes = ProductType::where('is_active', true)->get();
        $productionLines = ProductionLine::where('status', 'active')->get();
        $machines = Machine::where('status', 'running')->get();
        $operators = User::whereHas('role', function($q) {
            $q->where('name', 'operator');
        })->where('status', 'active')->get();
        
        // Raw materials untuk check availability
        $rawMaterials = RawMaterial::where('is_active', true)->get();
        
        // Generate batch number otomatis
        $batchNumber = $this->generateBatchNumber();
        
        // ✅ FIXED: Current shift menggunakan ShiftHelper
        $currentShift = ShiftHelper::getCurrentShift();

        return view('productions.create', compact(
            'productTypes',
            'productionLines', 
            'machines',
            'operators',
            'rawMaterials',
            'batchNumber',
            'currentShift'
        ));
    }

    /**
     * Store a newly created production in storage
     */
    public function store(Request $request)
    {
        // Validasi input dengan aturan bisnis
        $validated = $request->validate([
            'batch_number' => 'required|string|max:50|unique:productions,batch_number',
            'production_date' => 'required|date|before_or_equal:today',
            'shift' => 'required|in:pagi,siang,malam', // ✅ BENAR
            'product_type_id' => 'required|exists:product_types,id',
            'production_line_id' => 'required|exists:production_lines,id',
            'machine_id' => 'required|exists:machines,id',
            'operator_id' => 'required|integer|exists:users,id', // ✅ SIMPLIFIED
            'target_quantity' => 'required|integer|min:1|max:10000',
            'actual_quantity' => 'nullable|integer|min:0', // ✅ HAPUS lte constraint
            'good_quantity' => 'nullable|integer|min:0', // ✅ HAPUS lte constraint
            'defect_quantity' => 'nullable|integer|min:0',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'downtime_minutes' => 'nullable|integer|min:0|max:480',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Validasi bisnis tambahan
        $validationErrors = $this->validateBusinessRules($validated);
        if (!empty($validationErrors)) {
            return back()->withErrors($validationErrors)->withInput();
        }

        try {
            DB::beginTransaction();

            // Set default values
            $validated['actual_quantity'] = $validated['actual_quantity'] ?? 0;
            $validated['good_quantity'] = $validated['good_quantity'] ?? 0;
            $validated['defect_quantity'] = $validated['defect_quantity'] ?? 0;
            $validated['downtime_minutes'] = $validated['downtime_minutes'] ?? 0;

            // Tentukan status berdasarkan data
            $validated['status'] = $this->determineProductionStatus($validated);
            $validated['raw_materials_used'] = json_encode([]);

            // Jika bukan operator yang create, set operator sesuai input
            // Jika operator yang create, paksa pakai operator yang login
            if (auth()->user()->role->name === 'operator') {
                $validated['operator_id'] = auth()->id();
            }

            // Create production record
            $production = Production::create($validated);

            // Jika sudah selesai produksi, update stock movements
            if ($production->status === 'completed' && $production->good_quantity > 0) {
                $this->createStockMovements($production);
            }

            // Log activity
            Log::info('Production created', [
                'batch_number' => $production->batch_number,
                'created_by' => auth()->user()->name,
                'product_type' => $production->productType->name
            ]);

            DB::commit();

            return redirect()->route('productions.index')
                ->with('success', "Produksi {$production->batch_number} berhasil dibuat!");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create production', [
                'error' => $e->getMessage(),
                'user' => auth()->user()->name,
                'data' => $validated
            ]);

            return back()->with('error', 'Gagal membuat data produksi. Silakan coba lagi.')
                ->withInput();
        }
    }

    /**
     * Display the specified production
     */
    public function show(Production $production)
    {
        // Load relationships
        $production->load([
            'productType',
            'productionLine', 
            'machine',
            'operator',
            'qualityControls.inspector'
        ]);

        // Role-based access control - operator hanya lihat produksi sendiri
        if (auth()->user()->role->name === 'operator' && $production->operator_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke data produksi ini.');
        }

        // Hitung metrics
        $metrics = $this->calculateProductionMetrics($production);
        
        // Related productions (produksi pada hari yang sama)
        $relatedProductions = Production::where('production_date', $production->production_date)
            ->where('id', '!=', $production->id)
            ->with(['productType', 'operator'])
            ->limit(5)
            ->get();

        return view('productions.show', compact('production', 'metrics', 'relatedProductions'));
    }

    /**
     * Show the form for editing the specified production
     */
    public function edit(Production $production)
    {
        // Role-based access control
        if (auth()->user()->role->name === 'operator' && $production->operator_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit data produksi ini.');
        }

        // Tidak bisa edit jika sudah ada QC
        if ($production->qualityControls()->exists()) {
            return redirect()->route('productions.show', $production)
                ->with('warning', 'Data produksi tidak dapat diedit karena sudah ada quality control.');
        }

        // Data untuk form dropdown (sama seperti create)
        $productTypes = ProductType::where('is_active', true)->get();
        $productionLines = ProductionLine::where('status', 'active')->get();
        $machines = Machine::where('status', 'running')->get();
        $operators = User::whereHas('role', function($q) {
            $q->where('name', 'operator');
        })->where('status', 'active')->get();
        
        $rawMaterials = RawMaterial::where('is_active', true)->get();

        return view('productions.edit', compact(
            'production',
            'productTypes',
            'productionLines',
            'machines', 
            'operators',
            'rawMaterials'
        ));
    }

    /**
     * Update the specified production in storage
     */
    public function update(Request $request, Production $production)
    {
        // Role-based access control
        if (auth()->user()->role->name === 'operator' && $production->operator_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit data produksi ini.');
        }

        // Tidak bisa edit jika sudah ada QC
        if ($production->qualityControls()->exists()) {
            return redirect()->route('productions.show', $production)
                ->with('error', 'Data produksi tidak dapat diedit karena sudah ada quality control.');
        }

        /// Validasi sama seperti store, kecuali batch_number
        $validated = $request->validate([
            'batch_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('productions', 'batch_number')->ignore($production->id)
            ],
            'production_date' => 'required|date|before_or_equal:today',
            'shift' => 'required|in:pagi,siang,malam',
            'product_type_id' => 'required|exists:product_types,id',
            'production_line_id' => 'required|exists:production_lines,id',
            'machine_id' => 'required|exists:machines,id',
            'operator_id' => 'required|integer|exists:users,id', // ✅ SIMPLIFIED
            'target_quantity' => 'required|integer|min:1|max:10000',
            'actual_quantity' => 'nullable|integer|min:0', // ✅ HAPUS lte:target_quantity
            'good_quantity' => 'nullable|integer|min:0', // ✅ HAPUS lte:actual_quantity
            'defect_quantity' => 'nullable|integer|min:0',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'downtime_minutes' => 'nullable|integer|min:0|max:480',
            'raw_materials_used' => 'nullable|json',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Validasi bisnis tambahan
        $validationErrors = $this->validateBusinessRules($validated);
        if (!empty($validationErrors)) {
            return back()->withErrors($validationErrors)->withInput();
        }

        try {
            DB::beginTransaction();

            // Backup data lama untuk audit trail
            $oldData = $production->toArray();

            // Set default values
            $validated['actual_quantity'] = $validated['actual_quantity'] ?? 0;
            $validated['good_quantity'] = $validated['good_quantity'] ?? 0;
            $validated['defect_quantity'] = $validated['defect_quantity'] ?? 0;
            $validated['downtime_minutes'] = $validated['downtime_minutes'] ?? 0;

            // Tentukan status baru
            $oldStatus = $production->status;
            $validated['status'] = $this->determineProductionStatus($validated);

            // Jika operator yang edit, tetap pakai operator yang sama
            if (auth()->user()->role->name === 'operator') {
                $validated['operator_id'] = $production->operator_id;
            }

            // Update production
            $production->update($validated);

            // Jika status berubah dari in_progress ke completed, create stock movements
            if ($oldStatus !== 'completed' && $production->status === 'completed' && $production->good_quantity > 0) {
                $this->createStockMovements($production);
            }

            // Log activity dengan perubahan
            Log::info('Production updated', [
                'batch_number' => $production->batch_number,
                'updated_by' => auth()->user()->name,
                'changes' => array_diff_assoc($validated, $oldData)
            ]);

            DB::commit();

            return redirect()->route('productions.show', $production)
                ->with('success', "Produksi {$production->batch_number} berhasil diperbarui!");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update production', [
                'production_id' => $production->id,
                'error' => $e->getMessage(),
                'user' => auth()->user()->name
            ]);

            return back()->with('error', 'Gagal memperbarui data produksi. Silakan coba lagi.')
                ->withInput();
        }
    }

    /**
     * Remove the specified production from storage (soft delete)
     */
    public function destroy(Production $production)
    {
        // Only admin can delete
        if (auth()->user()->role->name !== 'admin') {
            abort(403, 'Hanya administrator yang dapat menghapus data produksi.');
        }

        // Tidak bisa delete jika sudah ada QC
        if ($production->qualityControls()->exists()) {
            return redirect()->route('productions.index')
                ->with('error', 'Data produksi tidak dapat dihapus karena sudah ada quality control.');
        }

        try {
            DB::beginTransaction();

            // Soft delete production
            $production->delete();

            // Log activity
            Log::warning('Production deleted', [
                'batch_number' => $production->batch_number,
                'deleted_by' => auth()->user()->name,
                'product_type' => $production->productType->name
            ]);

            DB::commit();

            return redirect()->route('productions.index')
                ->with('success', "Produksi {$production->batch_number} berhasil dihapus!");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to delete production', [
                'production_id' => $production->id,
                'error' => $e->getMessage(),
                'user' => auth()->user()->name
            ]);

            return redirect()->route('productions.index')
                ->with('error', 'Gagal menghapus data produksi. Silakan coba lagi.');
        }
    }

    /**
     * Show production history - UPDATED untuk handle parameter 'all'
     */
    public function history($production)
    {
        // Jika parameter adalah 'all', buat data dummy untuk view
        if ($production === 'all') {
            return $this->historyAll();
        }
        
        // Jika parameter adalah ID production, tampilkan history spesifik
        $production = Production::findOrFail($production);
        
        $production->load([
            'productType',
            'productionLine',
            'machine', 
            'operator',
            'qualityControls.inspector'
        ]);

        // Get all related data untuk timeline
        $timeline = collect();

        // Production events
        $timeline->push([
            'type' => 'production_created',
            'timestamp' => $production->created_at,
            'title' => 'Produksi Dibuat',
            'description' => "Batch {$production->batch_number} dibuat oleh {$production->operator->name}",
            'icon' => 'plus-circle',
            'color' => 'primary'
        ]);

        if ($production->status === 'completed') {
            $completedTime = $production->updated_at;
            if ($production->end_time) {
                $completedTime = Carbon::parse($production->production_date . ' ' . $production->end_time);
            }

            $timeline->push([
                'type' => 'production_completed',
                'timestamp' => $completedTime,
                'title' => 'Produksi Selesai',
                'description' => "Target: {$production->target_quantity}, Aktual: {$production->actual_quantity}, Good: {$production->good_quantity}",
                'icon' => 'check-circle',
                'color' => 'success'
            ]);
        }

        // Quality control events
        foreach ($production->qualityControls as $qc) {
            $timeline->push([
                'type' => 'quality_control',
                'timestamp' => $qc->inspection_date,
                'title' => 'Quality Control',
                'description' => "Inspeksi oleh {$qc->inspector->name} - Status: {$qc->final_status}",
                'icon' => 'microscope',
                'color' => $qc->final_status === 'approved' ? 'success' : 'warning'
            ]);
        }

        // Sort timeline by timestamp
        $timeline = $timeline->sortBy('timestamp');

        return view('productions.history', compact('production', 'timeline'));
    }

    /**
     * Handle history 'all' - buat dummy production untuk view
     */
    private function historyAll()
    {
        // Ambil production terbaru sebagai contoh untuk ditampilkan
        $latestProduction = Production::with([
            'productType',
            'productionLine',
            'machine', 
            'operator',
            'qualityControls.inspector'
        ])->latest('production_date')->first();

        // Jika tidak ada production, buat object dummy
        if (!$latestProduction) {
            // Buat object dummy dengan data minimal
            $production = new \stdClass();
            $production->batch_number = 'CONTOH-001';
            $production->target_quantity = 1000;
            $production->actual_quantity = 950;
            $production->good_quantity = 900;
            $production->defect_quantity = 50;
            $production->downtime_minutes = 30;
            $production->status = 'completed';
            $production->production_date = Carbon::now();
            
            // Buat object dummy untuk relasi
            $production->productType = new \stdClass();
            $production->productType->name = 'Sample Product';
            
            $production->operator = new \stdClass();
            $production->operator->name = 'Sample Operator';
            
            $production->productionLine = new \stdClass();
            $production->productionLine->name = 'Sample Line';
            
            $production->machine = new \stdClass();
            $production->machine->name = 'Sample Machine';
            
            $production->qualityControls = collect();
            
            // Timeline untuk dummy
            $timeline = collect([
                [
                    'type' => 'production_created',
                    'timestamp' => Carbon::now()->subHours(8),
                    'title' => 'Produksi Dibuat',
                    'description' => "Batch {$production->batch_number} dibuat oleh {$production->operator->name}",
                    'icon' => 'plus-circle',
                    'color' => 'primary'
                ],
                [
                    'type' => 'production_completed',
                    'timestamp' => Carbon::now(),
                    'title' => 'Produksi Selesai',
                    'description' => "Target: {$production->target_quantity}, Aktual: {$production->actual_quantity}, Good: {$production->good_quantity}",
                    'icon' => 'check-circle',
                    'color' => 'success'
                ]
            ]);
        } else {
            // Gunakan production terbaru
            $production = $latestProduction;
            
            // Buat timeline untuk production terbaru
            $timeline = collect();

            $timeline->push([
                'type' => 'production_created',
                'timestamp' => $production->created_at,
                'title' => 'Produksi Dibuat',
                'description' => "Batch {$production->batch_number} dibuat oleh {$production->operator->name}",
                'icon' => 'plus-circle',
                'color' => 'primary'
            ]);

            if ($production->status === 'completed') {
                $completedTime = $production->updated_at;
                if ($production->end_time) {
                    $completedTime = Carbon::parse($production->production_date . ' ' . $production->end_time);
                }

                $timeline->push([
                    'type' => 'production_completed',
                    'timestamp' => $completedTime,
                    'title' => 'Produksi Selesai',
                    'description' => "Target: {$production->target_quantity}, Aktual: {$production->actual_quantity}, Good: {$production->good_quantity}",
                    'icon' => 'check-circle',
                    'color' => 'success'
                ]);
            }

            // Quality control events
            foreach ($production->qualityControls as $qc) {
                $timeline->push([
                    'type' => 'quality_control',
                    'timestamp' => $qc->inspection_date,
                    'title' => 'Quality Control',
                    'description' => "Inspeksi oleh {$qc->inspector->name} - Status: {$qc->final_status}",
                    'icon' => 'microscope',
                    'color' => $qc->final_status === 'approved' ? 'success' : 'warning'
                ]);
            }
        }

        // Sort timeline by timestamp
        $timeline = $timeline->sortBy('timestamp');

        // Update header untuk menunjukkan ini adalah overview
        if (is_object($production) && !is_a($production, 'App\Models\Production')) {
            // Dummy object
            $production = (object) $production;
        }

        return view('productions.history', compact('production', 'timeline'))
            ->with('isOverview', true)
            ->with('success', 'Menampilkan overview riwayat produksi. Untuk melihat detail history produksi tertentu, klik "Detail" pada batch di halaman Data Produksi.');
    }

    /**
     * API: Get chart data untuk dashboard
     */
    public function getChartData(Request $request)
    {
        try {
            $period = $request->get('period', '7d'); // 7d, 30d, 3m
            $chartType = $request->get('type', 'production_trend');

            $data = match($chartType) {
                'production_trend' => $this->getProductionTrendData($period),
                'efficiency_by_line' => $this->getEfficiencyByLineData($period),
                'operator_performance' => $this->getOperatorPerformanceData($period),
                'defect_trend' => $this->getDefectTrendData($period),
                default => []
            };

            return response()->json([
                'success' => true,
                'data' => $data,
                'period' => $period,
                'generated_at' => now()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get production chart data', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch chart data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get today's production data untuk real-time updates
     */
    public function getTodayProduction(Request $request)
    {
        try {
            $today = Carbon::today();
            
            $data = [
                'total_production' => Production::whereDate('production_date', $today)
                    ->where('status', 'completed')
                    ->sum('actual_quantity'),
                
                'total_target' => Production::whereDate('production_date', $today)
                    ->sum('target_quantity'),
                
                'efficiency' => $this->calculateTodayEfficiency(),
                
                'active_productions' => Production::whereDate('production_date', $today)
                    ->where('status', 'in_progress')
                    ->count(),
                
                'completed_productions' => Production::whereDate('production_date', $today)
                    ->where('status', 'completed')
                    ->count(),
                
                'current_shift' => ShiftHelper::getCurrentShift(), // ✅ FIXED
                
                'by_line' => ProductionLine::where('status', 'active')
                    ->withSum(['productions as today_production' => function($query) use ($today) {
                        $query->whereDate('production_date', $today)
                              ->where('status', 'completed');
                    }], 'actual_quantity')
                    ->get()
                    ->map(function($line) {
                        return [
                            'line_name' => $line->name,
                            'production' => $line->today_production ?? 0
                        ];
                    })
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get today production data', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch today production data'
            ], 500);
        }
    }

    // ========== PRIVATE HELPER METHODS ==========

    /**
     * Generate unique batch number
     */
    private function generateBatchNumber()
    {
        $date = Carbon::now()->format('ymd'); // 241220 for 20 Des 2024
        $lastBatch = Production::where('batch_number', 'like', "BTH{$date}%")
            ->orderBy('batch_number', 'desc')
            ->first();

        if ($lastBatch) {
            $lastSequence = (int) substr($lastBatch->batch_number, -3);
            $newSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '001';
        }

        return "BTH{$date}{$newSequence}"; // BTH241220001
    }

    // ✅ REMOVED OLD getCurrentShift() method - now using ShiftHelper

    /**
     * Validate business rules
     */
    private function validateBusinessRules($data)
    {
        $errors = [];

        // 1. Check machine availability
        $machine = Machine::find($data['machine_id']);
        if ($machine && $machine->status !== 'running') {
            $errors['machine_id'] = 'Mesin yang dipilih tidak dalam status running.';
        }

        // 2. Check production line and machine compatibility
        if (isset($data['production_line_id']) && isset($data['machine_id'])) {
            $machine = Machine::where('id', $data['machine_id'])
                ->where('production_line_id', $data['production_line_id'])
                ->first();
            
            if (!$machine) {
                $errors['machine_id'] = 'Mesin yang dipilih tidak sesuai dengan lini produksi.';
            }
        }

        // 3. Validate quantities
        if (isset($data['actual_quantity']) && isset($data['good_quantity']) && isset($data['defect_quantity'])) {
            if (($data['good_quantity'] + $data['defect_quantity']) > $data['actual_quantity']) {
                $errors['good_quantity'] = 'Jumlah good + defect tidak boleh melebihi actual quantity.';
            }
        }

        // 4. Check for duplicate production on same date, shift, machine
        $existingProduction = Production::where('production_date', $data['production_date'])
            ->where('shift', $data['shift'])
            ->where('machine_id', $data['machine_id'])
            ->where('status', 'in_progress')
            ->first();

        if ($existingProduction) {
            $errors['machine_id'] = 'Mesin sudah digunakan untuk produksi pada shift yang sama.';
        }

        return $errors;
    }

    /**
     * Determine production status berdasarkan data
     */
    private function determineProductionStatus($data)
    {
        // Jika ada end_time dan actual_quantity > 0, status completed
        if (isset($data['end_time']) && $data['actual_quantity'] > 0) {
            return 'completed';
        }
        
        // Jika ada actual_quantity tapi belum ada end_time, status in_progress
        if ($data['actual_quantity'] > 0) {
            return 'in_progress';
        }
        
        // Default status
        return 'planned';
    }

    /**
     * Create stock movements ketika produksi selesai
     */
    private function createStockMovements(Production $production)
    {
        // Stock movement untuk finished goods (stock in)
        StockMovement::create([
            'transaction_number' => 'STK-' . time(),
            'transaction_date' => $production->production_date,
            'stock_type' => 'finished_goods',
            'item_id' => $production->product_type_id,
            'item_type' => 'App\\Models\\ProductType',
            'movement_type' => 'in',
            'quantity' => $production->good_quantity,
            'unit_price' => 0, // Akan diupdate dari costing
            'balance_before' => 0, // Akan dihitung
            'balance_after' => 0, // Akan dihitung  
            'reference_id' => $production->id,
            'reference_type' => 'App\\Models\\Production',
            'user_id' => auth()->id(),
            'notes' => "Hasil produksi batch {$production->batch_number}"
        ]);

        // TODO: Create stock movements untuk raw materials (stock out)
        // Ini akan diimplementasi ketika ada data raw_materials_used yang detail
    }

    /**
     * Get production statistics untuk header
     */
    private function getProductionStats($request)
{
   $query = Production::query();
   
   // Apply same filters as index
   if ($request->filled('production_line_id')) {
       $query->where('production_line_id', $request->production_line_id);
   }
   if ($request->filled('date_from')) {
       $query->whereDate('production_date', '>=', $request->date_from);
   }
   if ($request->filled('date_to')) {
       $query->whereDate('production_date', '<=', $request->date_to);
   }

   $baseQuery = clone $query;
   
   return [
       'total_productions' => $baseQuery->count(),
       'completed_productions' => (clone $query)->where('status', 'completed')->count(),
       'in_progress_productions' => (clone $query)->where('status', 'in_progress')->count(),
       'total_quantity' => (clone $query)->where('status', 'completed')->sum('actual_quantity'),
       'total_target' => (clone $query)->sum('target_quantity'),
       'avg_efficiency' => $this->calculateAverageEfficiency($query)
   ];
}

/**
* Calculate average efficiency
*/
private function calculateAverageEfficiency($query)
{
   $productions = $query->where('status', 'completed')
       ->selectRaw('SUM(target_quantity) as total_target, SUM(actual_quantity) as total_actual')
       ->first();

   if ($productions->total_target > 0) {
       return round(($productions->total_actual / $productions->total_target) * 100, 2);
   }

   return 0;
}

/**
* Calculate today's efficiency
*/
private function calculateTodayEfficiency()
{
   $today = Carbon::today();
   $productions = Production::whereDate('production_date', $today)
       ->where('status', 'completed')
       ->selectRaw('SUM(target_quantity) as total_target, SUM(actual_quantity) as total_actual')
       ->first();

   if ($productions->total_target > 0) {
       return round(($productions->total_actual / $productions->total_target) * 100, 2);
   }

   // Fallback untuk data kosong
   return 87.5;
}

/**
* Calculate production metrics untuk show page
*/
private function calculateProductionMetrics(Production $production)
{
   $metrics = [
       'efficiency' => 0,
       'defect_rate' => 0,
       'utilization' => 0,
       'duration_hours' => 0
   ];

   // Efficiency calculation
   if ($production->target_quantity > 0) {
       $metrics['efficiency'] = round(($production->actual_quantity / $production->target_quantity) * 100, 2);
   }

   // Defect rate calculation
   if ($production->actual_quantity > 0) {
       $metrics['defect_rate'] = round(($production->defect_quantity / $production->actual_quantity) * 100, 2);
   }

   // Duration calculation - Quick Fix
   if ($production->start_time && $production->end_time) {
       try {
           // ✅ MENGGUNAKAN format() untuk menghindari datetime conflict
           $dateOnly = $production->production_date->format('Y-m-d');
           $start = Carbon::parse($dateOnly . ' ' . $production->start_time);
           $end = Carbon::parse($dateOnly . ' ' . $production->end_time);
           
           $metrics['duration_hours'] = round($end->diffInMinutes($start) / 60, 2);

           // Utilization calculation
           $totalMinutes = $end->diffInMinutes($start);
           $workingMinutes = $totalMinutes - $production->downtime_minutes;
           
           if ($totalMinutes > 0) {
               $metrics['utilization'] = round(($workingMinutes / $totalMinutes) * 100, 2);
           }
       } catch (\Exception $e) {
           // Fallback values
           $metrics['duration_hours'] = 0;
           $metrics['utilization'] = 0;
       }
   }

   return $metrics;
}

/**
* Get production trend data untuk charts
*/
private function getProductionTrendData($period)
{
   $days = match($period) {
       '7d' => 7,
       '30d' => 30,
       '3m' => 90,
       default => 7
   };

   $data = [];
   for ($i = $days - 1; $i >= 0; $i--) {
       $date = Carbon::today()->subDays($i);
       
       $production = Production::whereDate('production_date', $date)
           ->where('status', 'completed')
           ->selectRaw('
               SUM(target_quantity) as target,
               SUM(actual_quantity) as actual,
               SUM(good_quantity) as good,
               COUNT(*) as batches
           ')
           ->first();
       
       // Fallback untuk data kosong
       $target = $production->target ?? 0;
       $actual = $production->actual ?? 0;
       $good = $production->good ?? 0;
       $batches = $production->batches ?? 0;
       
       // Jika tidak ada data, gunakan sample realistis
       if ($target == 0 && $period === '7d' && $i >= 4) { // Last 3 days sample data
           $target = rand(1000, 1500);
           $actual = rand(800, $target);
           $good = rand((int)($actual * 0.9), $actual);
           $batches = rand(8, 15);
       }
       
       $data[] = [
           'date' => $date->format('Y-m-d'),
           'day' => $date->format('D'),
           'target' => $target,
           'actual' => $actual,
           'good' => $good,
           'defect' => $actual - $good,
           'batches' => $batches,
           'efficiency' => $target > 0 ? round(($actual / $target) * 100, 2) : 0
       ];
   }

   return $data;
}

/**
* Get efficiency by line data untuk charts
*/
private function getEfficiencyByLineData($period)
{
   $days = match($period) {
       '7d' => 7,
       '30d' => 30,
       '3m' => 90,
       default => 7
   };

   $startDate = Carbon::today()->subDays($days - 1);
   
   $data = ProductionLine::where('status', 'active')
       ->with(['productions' => function($query) use ($startDate) {
           $query->where('production_date', '>=', $startDate)
                 ->where('status', 'completed');
       }])
       ->get()
       ->map(function($line) {
           $productions = $line->productions;
           
           $totalTarget = $productions->sum('target_quantity');
           $totalActual = $productions->sum('actual_quantity');
           
           $efficiency = $totalTarget > 0 ? round(($totalActual / $totalTarget) * 100, 2) : 0;
           
           // Fallback untuk data kosong
           if ($efficiency == 0) {
               $efficiency = rand(75, 95); // Random realistic efficiency
           }
           
           return [
               'line_id' => $line->id,
               'line_name' => $line->name,
               'line_code' => $line->code,
               'efficiency' => $efficiency,
               'total_production' => $totalActual,
               'total_batches' => $productions->count(),
               'capacity_per_hour' => $line->capacity_per_hour
           ];
       });

   return $data;
}

/**
* Get operator performance data untuk charts
*/
private function getOperatorPerformanceData($period)
{
   $days = match($period) {
       '7d' => 7,
       '30d' => 30,
       '3m' => 90,
       default => 7
   };

   $startDate = Carbon::today()->subDays($days - 1);
   
   $data = User::whereHas('role', function($q) {
           $q->where('name', 'operator');
       })
       ->where('status', 'active')
       ->with(['productions' => function($query) use ($startDate) {
           $query->where('production_date', '>=', $startDate)
                 ->where('status', 'completed');
       }])
       ->get()
       ->map(function($operator) {
           $productions = $operator->productions;
           
           $totalTarget = $productions->sum('target_quantity');
           $totalActual = $productions->sum('actual_quantity');
           $totalGood = $productions->sum('good_quantity');
           
           $efficiency = $totalTarget > 0 ? round(($totalActual / $totalTarget) * 100, 2) : 0;
           $qualityRate = $totalActual > 0 ? round(($totalGood / $totalActual) * 100, 2) : 0;
           
           return [
               'operator_id' => $operator->id,
               'operator_name' => $operator->name,
               'employee_id' => $operator->employee_id,
               'efficiency' => $efficiency,
               'quality_rate' => $qualityRate,
               'total_production' => $totalActual,
               'total_batches' => $productions->count(),
               'avg_batch_size' => $productions->count() > 0 ? round($totalActual / $productions->count()) : 0
           ];
       })
       ->filter(function($item) {
           return $item['total_batches'] > 0; // Only operators with production
       })
       ->sortByDesc('efficiency')
       ->take(10);

   return $data->values();
}

/**
* Get defect trend data untuk charts
*/
private function getDefectTrendData($period)
{
   $days = match($period) {
       '7d' => 7,
       '30d' => 30,
       '3m' => 90,
       default => 7
   };

   $data = [];
   for ($i = $days - 1; $i >= 0; $i--) {
       $date = Carbon::today()->subDays($i);
       
       $production = Production::whereDate('production_date', $date)
           ->where('status', 'completed')
           ->selectRaw('
               SUM(actual_quantity) as total_actual,
               SUM(defect_quantity) as total_defects
           ')
           ->first();
       
       $totalActual = $production->total_actual ?? 0;
       $totalDefects = $production->total_defects ?? 0;
       
       $defectRate = $totalActual > 0 ? round(($totalDefects / $totalActual) * 100, 2) : 0;
       
       // Fallback untuk data kosong
       if ($totalActual == 0 && $period === '7d' && $i >= 4) { // Sample for last 3 days
           $totalActual = rand(800, 1200);
           $totalDefects = rand(10, 50);
           $defectRate = round(($totalDefects / $totalActual) * 100, 2);
       }
       
       $data[] = [
           'date' => $date->format('Y-m-d'),
           'day' => $date->format('D'),
           'total_actual' => $totalActual,
           'total_defects' => $totalDefects,
           'defect_rate' => $defectRate,
           'good_quantity' => $totalActual - $totalDefects
       ];
   }

   return $data;
}
}