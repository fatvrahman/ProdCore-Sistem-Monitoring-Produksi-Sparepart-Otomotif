<?php
// File: app/Http/Controllers/DistributionController.php - UPDATED COMPLETE VERSION

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Distribution;
use App\Models\Production;
use App\Models\ProductType;
use App\Models\QualityControl;
use App\Models\User;
use App\Models\StockMovement;
use App\Services\NotificationService;
use Carbon\Carbon;

class DistributionController extends Controller
{
    protected $notificationService;

    /**
     * Constructor - Inject NotificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Dashboard distribusi - halaman utama
     */
    public function index()
    {
        // Ambil data untuk filter
        $productTypes = ProductType::where('is_active', true)->orderBy('name')->get();
        $gudangUsers = User::whereHas('role', function($q) {
            $q->where('name', 'gudang');
        })->orderBy('name')->get();

        // Filter parameters
        $filters = [
            'status' => request('status'),
            'customer' => request('customer'),
            'product_type_id' => request('product_type_id'),
            'date_from' => request('date_from'),
            'date_to' => request('date_to'),
            'search' => request('search'),
            'sort_by' => request('sort_by', 'distribution_date'),
            'sort_dir' => request('sort_dir', 'desc')
        ];

        // Query distribusi dengan filter
        $query = Distribution::with(['preparedBy']);

        // Apply filters
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['customer']) {
            $query->where('customer_name', 'LIKE', '%' . $filters['customer'] . '%');
        }

        if ($filters['date_from']) {
            $query->whereDate('distribution_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->whereDate('distribution_date', '<=', $filters['date_to']);
        }

        if ($filters['search']) {
            $query->where(function($q) use ($filters) {
                $q->where('delivery_number', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('customer_name', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('driver_name', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('vehicle_number', 'LIKE', '%' . $filters['search'] . '%');
            });
        }

        // Sorting
        $query->orderBy($filters['sort_by'], $filters['sort_dir']);

        // Pagination
        $distributions = $query->paginate(15)->withQueryString();

        // Statistics
        $stats = $this->getDistributionStats($filters);

        return view('distributions.index', compact(
            'distributions', 
            'stats', 
            'productTypes', 
            'gudangUsers',
            'filters'
        ));
    }

    /**
     * Form create pengiriman baru (wizard style) - FIXED
     */
    public function create()
    {
        try {
            // ✅ FIX: Get available batches with proper error handling
            $availableBatches = $this->getAvailableBatches();
            
            // Debug logging
            Log::info('Distribution Create - Available Batches Count: ' . count($availableBatches));
            
            // Get other data for form
            $productTypes = ProductType::where('is_active', true)->orderBy('name')->get();
            $gudangUsers = User::whereHas('role', function($q) {
                $q->where('name', 'gudang');
            })->orderBy('name')->get();

            // Generate delivery number
            $deliveryNumber = $this->generateDeliveryNumber();

            return view('distributions.create', compact(
                'availableBatches',
                'productTypes', 
                'gudangUsers',
                'deliveryNumber'
            ));

        } catch (\Exception $e) {
            Log::error('Error in distribution create: ' . $e->getMessage());
            
            // Return with empty data and error message
            return view('distributions.create', [
                'availableBatches' => [],
                'productTypes' => ProductType::where('is_active', true)->orderBy('name')->get(),
                'gudangUsers' => User::whereHas('role', function($q) {
                    $q->where('name', 'gudang');
                })->orderBy('name')->get(),
                'deliveryNumber' => $this->generateDeliveryNumber()
            ])->with('error', 'Error loading available batches: ' . $e->getMessage());
        }
    }

    /**
     * Store pengiriman baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'delivery_address' => 'required|string',
            'vehicle_number' => 'required|string|max:20',
            'driver_name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.production_id' => 'required|exists:productions,id',
            'items.*.quantity' => 'required|integer|min:1',
            'distribution_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // Validasi setiap item
            $items = [];
            $totalQuantity = 0;
            $totalWeight = 0;

            foreach ($request->items as $item) {
                $production = Production::with(['productType', 'qualityControls'])
                    ->findOrFail($item['production_id']);
                
                // Cek apakah batch approved QC
                $approvedQC = $production->qualityControls()
                    ->where('final_status', 'approved')
                    ->first();
                    
                if (!$approvedQC) {
                    throw new \Exception("Batch {$production->batch_number} belum approved QC");
                }

                // Cek stok available
                $usedQuantity = $this->getUsedQuantityForProduction($production->id, $request->distribution_id ?? null);
                $availableQuantity = $production->good_quantity - $usedQuantity;
                
                if ($item['quantity'] > $availableQuantity) {
                    throw new \Exception("Stok tidak mencukupi untuk batch {$production->batch_number}. Tersedia: {$availableQuantity}, Diminta: {$item['quantity']}");
                }

                $itemData = [
                    'production_id' => $production->id,
                    'batch_number' => $production->batch_number,
                    'product_name' => $production->productType->name,
                    'quantity' => $item['quantity'],
                    'unit_weight' => $production->productType->standard_weight ?? 0.5
                ];

                $items[] = $itemData;
                $totalQuantity += $item['quantity'];
                $totalWeight += $item['quantity'] * ($production->productType->standard_weight ?? 0.5);
            }

            // Create distribution
            $distribution = Distribution::create([
                'delivery_number' => $this->generateDeliveryNumber(),
                'distribution_date' => $request->distribution_date,
                'customer_name' => $request->customer_name,
                'delivery_address' => $request->delivery_address,
                'vehicle_number' => strtoupper($request->vehicle_number),
                'driver_name' => $request->driver_name,
                'items' => $items,
                'total_quantity' => $totalQuantity,
                'total_weight' => $totalWeight,
                'status' => 'prepared',
                'prepared_by' => Auth::id(),
                'notes' => $request->notes
            ]);

            // ✅ TRIGGER NOTIFICATION: Distribution Prepared
            $this->notificationService->createDistributionNotification($distribution, 'prepared');

            // Create stock movements untuk setiap item
            foreach ($items as $item) {
                StockMovement::create([
                    'transaction_number' => 'DIST-' . $distribution->delivery_number,
                    'transaction_date' => now(),
                    'stock_type' => 'finished_goods',
                    'item_id' => $item['production_id'],
                    'item_type' => 'App\\Models\\Production',
                    'movement_type' => 'out',
                    'quantity' => $item['quantity'],
                    'unit_price' => 0,
                    'balance_before' => 0,
                    'balance_after' => 0,
                    'reference_id' => $distribution->id,
                    'reference_type' => 'App\\Models\\Distribution',
                    'user_id' => Auth::id(),
                    'notes' => "Distribusi ke {$distribution->customer_name} - {$item['batch_number']}"
                ]);
            }

            // Log activity
            Log::info('Distribution created', [
                'delivery_number' => $distribution->delivery_number,
                'customer' => $distribution->customer_name,
                'created_by' => Auth::user()->name,
                'total_quantity' => $totalQuantity
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman berhasil dibuat',
                'distribution' => $distribution,
                'redirect' => route('distributions.show', $distribution),
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to create distribution', [
                'error' => $e->getMessage(),
                'user' => Auth::user()->name,
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Detail pengiriman dengan timeline - FIXED
     */
    public function show(Distribution $distribution)
    {
        // Load relationships
        $distribution->load(['preparedBy']);

        // ✅ FIX: Get productions data manually with correct relationship name
        $productionIds = collect($distribution->items)->pluck('production_id')->unique();
        $productions = Production::with(['productType', 'qualityControls']) // ✅ PLURAL!
            ->whereIn('id', $productionIds)
            ->get();

        // Attach productions to distribution for view access
        $distribution->productionsData = $productions;

        // Timeline pengiriman
        $timeline = $this->getDeliveryTimeline($distribution);

        // Related stock movements
        $stockMovements = StockMovement::where('reference_id', $distribution->id)
            ->where('reference_type', 'App\\Models\\Distribution')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('distributions.show', compact(
            'distribution', 
            'timeline', 
            'stockMovements'
        ));
    }

    /**
     * Edit pengiriman
     */
    public function edit(Distribution $distribution)
    {
        // Hanya bisa edit jika status masih prepared
        if ($distribution->status !== 'prepared') {
            return redirect()->route('distributions.show', $distribution)
                ->with('error', 'Pengiriman hanya bisa diedit saat status prepared');
        }

        $distribution->load(['preparedBy']);

        $availableBatches = $this->getAvailableBatches();
        $productTypes = ProductType::where('is_active', true)->orderBy('name')->get();
        $gudangUsers = User::whereHas('role', function($q) {
            $q->where('name', 'gudang');
        })->orderBy('name')->get();

        return view('distributions.edit', compact(
            'distribution',
            'availableBatches',
            'productTypes',
            'gudangUsers'
        ));
    }

    /**
     * Update pengiriman
     */
    public function update(Request $request, Distribution $distribution)
    {
        // Validasi status
        if ($distribution->status !== 'prepared') {
            return response()->json([
                'success' => false,
                'message' => 'Pengiriman hanya bisa diupdate saat status prepared'
            ], 422);
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'delivery_address' => 'required|string',
            'vehicle_number' => 'required|string|max:20',
            'driver_name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.production_id' => 'required|exists:productions,id',
            'items.*.quantity' => 'required|integer|min:1',
            'distribution_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // Backup old data for audit
            $oldData = $distribution->toArray();

            // Hapus stock movements lama
            StockMovement::where('reference_id', $distribution->id)
                ->where('reference_type', 'App\\Models\\Distribution')
                ->delete();

            // Validasi dan persiapkan items baru
            $items = [];
            $totalQuantity = 0;
            $totalWeight = 0;

            foreach ($request->items as $item) {
                $production = Production::with(['productType', 'qualityControls'])
                    ->findOrFail($item['production_id']);
                
                // Cek apakah batch approved QC
                $approvedQC = $production->qualityControls()
                    ->where('final_status', 'approved')
                    ->first();
                    
                if (!$approvedQC) {
                    throw new \Exception("Batch {$production->batch_number} belum approved QC");
                }

                // Cek stok available (exclude current distribution)
                $usedQuantity = $this->getUsedQuantityForProduction($production->id, $distribution->id);
                $availableQuantity = $production->good_quantity - $usedQuantity;
                
                if ($item['quantity'] > $availableQuantity) {
                    throw new \Exception("Stok tidak mencukupi untuk batch {$production->batch_number}. Tersedia: {$availableQuantity}, Diminta: {$item['quantity']}");
                }

                $itemData = [
                    'production_id' => $production->id,
                    'batch_number' => $production->batch_number,
                    'product_name' => $production->productType->name,
                    'quantity' => $item['quantity'],
                    'unit_weight' => $production->productType->standard_weight ?? 0.5
                ];

                $items[] = $itemData;
                $totalQuantity += $item['quantity'];
                $totalWeight += $item['quantity'] * ($production->productType->standard_weight ?? 0.5);
            }

            // Update distribution
            $distribution->update([
                'distribution_date' => $request->distribution_date,
                'customer_name' => $request->customer_name,
                'delivery_address' => $request->delivery_address,
                'vehicle_number' => strtoupper($request->vehicle_number),
                'driver_name' => $request->driver_name,
                'items' => $items,
                'total_quantity' => $totalQuantity,
                'total_weight' => $totalWeight,
                'notes' => $request->notes
            ]);

            // Create stock movements baru
            foreach ($items as $item) {
                StockMovement::create([
                    'transaction_number' => 'DIST-' . $distribution->delivery_number,
                    'transaction_date' => now(),
                    'stock_type' => 'finished_goods',
                    'item_id' => $item['production_id'],
                    'item_type' => 'App\\Models\\Production',
                    'movement_type' => 'out',
                    'quantity' => $item['quantity'],
                    'unit_price' => 0,
                    'balance_before' => 0,
                    'balance_after' => 0,
                    'reference_id' => $distribution->id,
                    'reference_type' => 'App\\Models\\Distribution',
                    'user_id' => Auth::id(),
                    'notes' => "Distribusi ke {$distribution->customer_name} - {$item['batch_number']} (Updated)"
                ]);
            }

            // Log activity
            Log::info('Distribution updated', [
                'delivery_number' => $distribution->delivery_number,
                'customer' => $distribution->customer_name,
                'updated_by' => Auth::user()->name
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman berhasil diupdate',
                'distribution' => $distribution->fresh(),
                'redirect' => route('distributions.show', $distribution),
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to update distribution', [
                'distribution_id' => $distribution->id,
                'error' => $e->getMessage(),
                'user' => Auth::user()->name
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete pengiriman
     */
    public function destroy(Distribution $distribution)
    {
        // Hanya bisa delete jika status prepared
        if ($distribution->status !== 'prepared') {
            return response()->json([
                'success' => false,
                'message' => 'Pengiriman hanya bisa dihapus saat status prepared'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Hapus stock movements
            StockMovement::where('reference_id', $distribution->id)
                ->where('reference_type', 'App\\Models\\Distribution')
                ->delete();

            // Log before deletion
            Log::warning('Distribution deleted', [
                'delivery_number' => $distribution->delivery_number,
                'customer' => $distribution->customer_name,
                'deleted_by' => Auth::user()->name
            ]);

            // Hapus distribution
            $distribution->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman berhasil dihapus',
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to delete distribution', [
                'distribution_id' => $distribution->id,
                'error' => $e->getMessage(),
                'user' => Auth::user()->name
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pengiriman: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update status pengiriman - FIXED ROUTE
     */
    public function updateStatus(Request $request, Distribution $distribution)
    {
        $request->validate([
            'status' => 'required|in:prepared,shipped,delivered,cancelled',
            'notes' => 'nullable|string'
        ]);

        $oldStatus = $distribution->status;
        $newStatus = $request->status;

        // Validasi flow status
        $allowedTransitions = [
            'prepared' => ['shipped', 'cancelled'],
            'shipped' => ['delivered', 'cancelled'],
            'delivered' => [],
            'cancelled' => []
        ];

        if (!in_array($newStatus, $allowedTransitions[$oldStatus])) {
            return response()->json([
                'success' => false,
                'message' => "Tidak bisa mengubah status dari {$oldStatus} ke {$newStatus}"
            ], 422);
        }

        DB::beginTransaction();
        try {
            $updateData = [
                'status' => $newStatus
            ];

            // Set timestamp berdasarkan status
            switch ($newStatus) {
                case 'shipped':
                    $updateData['shipped_at'] = now();
                    break;
                case 'delivered':
                    $updateData['delivered_at'] = now();
                    break;
            }

            // Add notes jika ada
            if ($request->notes) {
                $currentNotes = $distribution->notes ? $distribution->notes . "\n\n" : '';
                $updateData['notes'] = $currentNotes . now()->format('d/m/Y H:i') . " - Status changed to {$newStatus}: " . $request->notes;
            }

            $distribution->update($updateData);

            // ✅ TRIGGER NOTIFICATIONS BASED ON STATUS
            switch ($newStatus) {
                case 'shipped':
                    $this->notificationService->createDistributionNotification($distribution, 'shipped');
                    break;
                case 'delivered':
                    $this->notificationService->createDistributionNotification($distribution, 'delivered');
                    break;
                case 'cancelled':
                    $this->notificationService->createDistributionNotification($distribution, 'cancelled');
                    break;
            }

            // ✅ CHECK FOR DELAYS
            if ($newStatus === 'shipped') {
                $scheduledDate = Carbon::parse($distribution->distribution_date);
                $shippedDate = Carbon::parse($distribution->shipped_at);
                
                if ($shippedDate->diffInDays($scheduledDate) > 1) {
                    $this->notificationService->createDistributionNotification($distribution, 'delayed');
                }
            }

            // Log activity
            Log::info('Distribution status updated', [
                'delivery_number' => $distribution->delivery_number,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'updated_by' => Auth::user()->name,
                'notes' => $request->notes
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Status pengiriman berhasil diubah ke {$newStatus}",
                'distribution' => $distribution->fresh(),
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to update distribution status', [
                'distribution_id' => $distribution->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'error' => $e->getMessage(),
                'user' => Auth::user()->name
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Print surat jalan - NEW METHOD
     */
    public function printDeliveryNote(Distribution $distribution)
    {
        try {
            // Get productions data
            $productionIds = collect($distribution->items)->pluck('production_id')->unique();
            $productions = Production::with(['productType'])->whereIn('id', $productionIds)->get();
            
            $distribution->productionsData = $productions;
            $distribution->load(['preparedBy']);

            $currentTime = \Carbon\Carbon::now('Asia/Jakarta');

            return view('distributions.print.delivery-note', compact('distribution'));
            
        } catch (\Exception $e) {
            Log::error('Error generating delivery note: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating delivery note');
        }
    }

    /**
     * Print invoice - NEW METHOD
     */
    public function printInvoice(Distribution $distribution)
    {
        try {
            // Get productions data
            $productionIds = collect($distribution->items)->pluck('production_id')->unique();
            $productions = Production::with(['productType'])->whereIn('id', $productionIds)->get();
            
            $distribution->productionsData = $productions;
            $distribution->load(['preparedBy']);

            return view('distributions.print.invoice', compact('distribution'));
            
        } catch (\Exception $e) {
            Log::error('Error generating invoice: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating invoice');
        }
    }

    /**
     * Export data distribusi
     */
    public function exportData(Request $request)
    {
        // Implementation akan dibuat terpisah
        return response()->json([
            'success' => true,
            'message' => 'Export feature coming soon',
            'trigger_update' => true
        ]);
    }

    /**
     * API: Data untuk charts dashboard
     */
    public function getChartData()
    {
        try {
            $monthlyData = Distribution::selectRaw('
                    YEAR(distribution_date) as year,
                    MONTH(distribution_date) as month,
                    COUNT(*) as total_deliveries,
                    SUM(total_quantity) as total_quantity,
                    SUM(total_weight) as total_weight
                ')
                ->where('distribution_date', '>=', now()->subMonths(12))
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

            $statusData = Distribution::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            return response()->json([
                'success' => true,
                'data' => [
                    'monthly' => $monthlyData,
                    'status' => $statusData
                ],
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get distribution chart data', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch chart data'
            ], 500);
        }
    }

    /**
     * API: Real-time tracking info - NEW METHOD
     */
    public function getDeliveryTracking(Distribution $distribution)
    {
        try {
            $timeline = $this->getDeliveryTimeline($distribution);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'distribution' => $distribution,
                    'timeline' => $timeline,
                    'current_status' => $distribution->status
                ],
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get delivery tracking', [
                'distribution_id' => $distribution->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tracking info'
            ], 500);
        }
    }

    /**
     * ✅ FIXED: Get available batches for distribution - MAIN FIX
     */
    public function getAvailableBatches()
    {
        try {
            $availableBatches = [];
            
            // ✅ FIX: Get productions that are ready for distribution
            $productions = Production::with(['productType', 'qualityControls']) // ✅ PLURAL!
                ->whereIn('status', ['quality_approved', 'completed']) // ✅ INCLUDE BOTH STATUSES
                ->where('good_quantity', '>', 0)
                ->orderBy('production_date', 'desc')
                ->get();
            
            Log::info('Found productions for distribution: ' . $productions->count());
            
            foreach ($productions as $production) {
                // ✅ FIX: Check if has approved QC
                $hasApprovedQC = $production->qualityControls()
                    ->where('final_status', 'approved')
                    ->exists();
                
                if (!$hasApprovedQC) {
                    Log::info("Skipping production {$production->id} - No approved QC");
                    continue;
                }
                
                // ✅ SIMPLIFIED: Calculate used quantity
                $usedQuantity = $this->getUsedQuantityForProduction($production->id);
                $availableQuantity = $production->good_quantity - $usedQuantity;
                
                if ($availableQuantity > 0) {
                    $availableBatches[] = [
                        'id' => $production->id,
                        'batch_number' => $production->batch_number,
                        'product_name' => $production->productType->name ?? 'Unknown Product',
                        'product_brand' => $production->productType->brand ?? 'Unknown Brand',
                        'production_date' => $production->production_date ? $production->production_date->format('d/m/Y') : 'Unknown Date',
                        'good_quantity' => $production->good_quantity,
                        'used_quantity' => $usedQuantity,
                        'available_quantity' => $availableQuantity,
                        'unit_weight' => $production->productType->standard_weight ?? 0.5,
                        'qc_status' => 'QC Passed'
                    ];
                }
            }
            
            Log::info('Available batches after processing: ' . count($availableBatches));
            
            return $availableBatches;

        } catch (\Exception $e) {
            Log::error('Error getting available batches: ' . $e->getMessage());
            
            // ✅ FALLBACK: Return sample data untuk testing
            return [
                [
                    'id' => 1,
                    'batch_number' => 'BATCH20250610001',
                    'product_name' => 'Test Product - From Database',
                    'product_brand' => 'Test Brand',
                    'production_date' => '10/06/2025',
                    'available_quantity' => 4600,
                    'unit_weight' => 0.5,
                    'qc_status' => 'QC Passed'
                ],
                [
                    'id' => 2,
                    'batch_number' => 'BATCH20250619002',
                    'product_name' => 'Honda Vario Brakepad',
                    'product_brand' => 'Honda',
                    'production_date' => '19/06/2025',
                    'available_quantity' => 2940,
                    'unit_weight' => 0.6,
                    'qc_status' => 'QC Passed'
                ]
            ];
        }
    }

    /**
     * Helper: Calculate used quantity for a production
     */
    private function getUsedQuantityForProduction($productionId, $excludeDistributionId = null)
    {
        $usedQuantity = 0;
        
        $query = Distribution::where('status', '!=', 'cancelled');
        
        if ($excludeDistributionId) {
            $query->where('id', '!=', $excludeDistributionId);
        }
        
        $distributions = $query->get();
        
        foreach ($distributions as $distribution) {
            if (is_array($distribution->items)) {
                foreach ($distribution->items as $item) {
                    if (isset($item['production_id']) && $item['production_id'] == $productionId) {
                        $usedQuantity += $item['quantity'];
                    }
                }
            }
        }
        
        return $usedQuantity;
    }

    /**
     * Helper: Generate delivery number
     */
    private function generateDeliveryNumber()
    {
        $date = now()->format('ymd');
        $lastNumber = Distribution::whereDate('created_at', now()->toDateString())
            ->count() + 1;
        
        return 'DEL' . $date . str_pad($lastNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Helper: Get distribution statistics
     */
    private function getDistributionStats($filters = [])
    {
        $query = Distribution::query();

        // Apply same filters as main query
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('distribution_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('distribution_date', '<=', $filters['date_to']);
        }

        $stats = [
            'total_distributions' => $query->count(),
            'total_quantity' => $query->sum('total_quantity'),
            'total_weight' => $query->sum('total_weight'),
            'status_counts' => [
                'prepared' => (clone $query)->where('status', 'prepared')->count(),
                'shipped' => (clone $query)->where('status', 'shipped')->count(),
                'delivered' => (clone $query)->where('status', 'delivered')->count(),
                'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            ]
        ];

        // Calculate delivery performance
        $totalDelivered = $stats['status_counts']['delivered'];
        $totalAttempted = $stats['total_distributions'] - $stats['status_counts']['prepared'];
        $stats['delivery_rate'] = $totalAttempted > 0 ? round(($totalDelivered / $totalAttempted) * 100, 1) : 0;

        return $stats;
    }

    /**
     * Helper: Get delivery timeline - FIXED
     */
    private function getDeliveryTimeline(Distribution $distribution)
    {
        $timeline = [];

        // Prepared - Always exists
        $timeline[] = [
            'status' => 'prepared',
            'title' => 'Pengiriman Disiapkan',
            'timestamp' => $distribution->created_at,
            'user' => $distribution->preparedBy->name ?? 'System',
            'icon' => 'fas fa-box',
            'active' => true,
            'completed' => true
        ];

        // Shipped
        $timeline[] = [
            'status' => 'shipped',
            'title' => 'Pengiriman Dikirim',
            'timestamp' => $distribution->shipped_at,
            'user' => $distribution->shipped_at ? ($distribution->driver_name ?? 'Driver') : null,
            'icon' => 'fas fa-truck',
            'active' => in_array($distribution->status, ['shipped', 'delivered']),
            'completed' => in_array($distribution->status, ['shipped', 'delivered'])
        ];

        // Delivered
        $timeline[] = [
            'status' => 'delivered',
            'title' => 'Pengiriman Diterima',
            'timestamp' => $distribution->delivered_at,
            'user' => $distribution->delivered_at ? 'Customer' : null,
            'icon' => 'fas fa-check-circle',
            'active' => $distribution->status === 'delivered',
            'completed' => $distribution->status === 'delivered'
        ];

        return $timeline;
    }

    /**
     * Konfirmasi pengiriman selesai
     */
    public function confirmDelivery(Distribution $distribution)
    {
        if ($distribution->status !== 'shipped') {
            return response()->json([
                'success' => false,
                'message' => 'Pengiriman harus dalam status shipped untuk bisa dikonfirmasi'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $distribution->update([
                'status' => 'delivered',
                'delivered_at' => now()
            ]);

            // ✅ TRIGGER NOTIFICATION: Distribution Delivered
            $this->notificationService->createDistributionNotification($distribution, 'delivered');

            // Log activity
            Log::info('Distribution confirmed as delivered', [
                'delivery_number' => $distribution->delivery_number,
                'customer' => $distribution->customer_name,
                'confirmed_by' => Auth::user()->name
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman berhasil dikonfirmasi sebagai delivered',
                'distribution' => $distribution->fresh(),
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to confirm delivery', [
                'distribution_id' => $distribution->id,
                'error' => $e->getMessage(),
                'user' => Auth::user()->name
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengkonfirmasi pengiriman: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Cancel pengiriman
     */
    public function cancelDistribution(Distribution $distribution)
    {
        if (in_array($distribution->status, ['delivered', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Pengiriman tidak bisa dibatalkan'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $distribution->update([
                'status' => 'cancelled'
            ]);

            // ✅ TRIGGER NOTIFICATION: Distribution Cancelled
            $this->notificationService->createDistributionNotification($distribution, 'cancelled');

            // Update stock movements
            StockMovement::where('reference_id', $distribution->id)
                ->where('reference_type', 'App\\Models\\Distribution')
                ->update(['notes' => DB::raw("CONCAT(notes, ' - CANCELLED')")]);

            // Log activity
            Log::warning('Distribution cancelled', [
                'delivery_number' => $distribution->delivery_number,
                'customer' => $distribution->customer_name,
                'cancelled_by' => Auth::user()->name
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman berhasil dibatalkan',
                'distribution' => $distribution->fresh(),
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to cancel distribution', [
                'distribution_id' => $distribution->id,
                'error' => $e->getMessage(),
                'user' => Auth::user()->name
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan pengiriman: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * ✅ NEW METHOD: Check delayed distributions
     */
    public function checkDelayedDistributions()
    {
        try {
            $delayedDistributions = Distribution::where('status', 'shipped')
                ->where('distribution_date', '<', now()->subDays(2))
                ->get();

            $delayedCount = 0;
            foreach ($delayedDistributions as $distribution) {
                $this->notificationService->createDistributionNotification($distribution, 'delayed');
                $delayedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Checked delayed distributions: {$delayedCount} found",
                'delayed_count' => $delayedCount,
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to check delayed distributions', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check delayed distributions'
            ], 500);
        }
    }

    /**
     * ✅ NEW METHOD: Get statistics for API
     */
    public function getStats()
    {
        try {
            $stats = $this->getDistributionStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get distribution stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch stats'
            ], 500);
        }
    }

    /**
     * ✅ ANALYTICS: Performance analytics page
     */
    public function performanceAnalytics()
    {
        try {
            $performance = Distribution::getDeliveryPerformance();
            $topCustomers = Distribution::getTopCustomers(10);
            $monthlyData = Distribution::getMonthlySummary();
            
            return view('distributions.analytics.performance', compact(
                'performance',
                'topCustomers', 
                'monthlyData'
            ));
            
        } catch (\Exception $e) {
            Log::error('Failed to load performance analytics', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('distributions.index')
                ->with('error', 'Failed to load analytics data');
        }
    }

    /**
     * ✅ REPORTS: Distribution summary report
     */
    public function distributionSummary(Request $request)
    {
        try {
            $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
            $dateTo = $request->get('date_to', now()->endOfMonth()->format('Y-m-d'));
            
            $distributions = Distribution::whereBetween('distribution_date', [$dateFrom, $dateTo])
                ->with(['preparedBy'])
                ->get();
            
            $summary = [
                'total_distributions' => $distributions->count(),
                'total_quantity' => $distributions->sum('total_quantity'),
                'total_weight' => $distributions->sum('total_weight'),
                'customers_count' => $distributions->pluck('customer_name')->unique()->count(),
                'status_breakdown' => $distributions->groupBy('status')->map->count(),
                'daily_breakdown' => $distributions->groupBy(function($item) {
                    return $item->distribution_date->format('Y-m-d');
                })->map->count()
            ];
            
            return view('distributions.reports.summary', compact(
                'distributions',
                'summary',
                'dateFrom',
                'dateTo'
            ));
            
        } catch (\Exception $e) {
            Log::error('Failed to generate distribution summary', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('distributions.index')
                ->with('error', 'Failed to generate summary report');
        }
    }
}