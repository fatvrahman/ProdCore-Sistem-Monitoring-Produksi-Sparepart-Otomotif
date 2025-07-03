<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use App\Models\StockMovement;
use App\Models\Production;
use App\Models\User;
use App\Models\ProductType;
use App\Services\NotificationService; // ✅ ADDED
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class StockController extends Controller
{
    protected $notificationService; // ✅ ADDED

    /**
     * Constructor - Inject NotificationService
     */
    public function __construct(NotificationService $notificationService) // ✅ ADDED
    {
        $this->notificationService = $notificationService;
        // Manual role check akan dilakukan di setiap method
        // Alternatif untuk middleware yang mungkin belum tersedia
    }

    /**
     * Check role access untuk stock management
     */
    private function checkStockAccess()
    {
        // Sementara disable check untuk testing
        // Uncomment jika role system sudah ready
        /*
        if (!Auth::check()) {
            abort(403, 'Access denied - Please login first');
        }
        
        $userRole = Auth::user()->role->name ?? null;
        $allowedRoles = ['admin', 'gudang'];
        
        if (!in_array($userRole, $allowedRoles)) {
            abort(403, 'Access denied - Insufficient permissions for stock management');
        }
        */
        
        return true; // Allow access untuk testing
    }

    /**
     * Dashboard utama stock management
     * Menampilkan overview semua data stock
     */
    public function index()
    {
        // Check role access
        $this->checkStockAccess();
        
        try {
            // Ambil summary statistics untuk dashboard
            $summaryStats = $this->getSummaryStatistics();
            
            // Data untuk charts
            $chartData = $this->getChartDataForDashboard();
            
            // Stock values by supplier untuk pie chart
            $stockValues = $this->getStockValuesBySupplier();
            
            // Items dengan stock rendah (top 5)
            $lowStockItems = RawMaterial::where('is_active', true)
                ->whereRaw('current_stock <= minimum_stock')
                ->orderBy(DB::raw('CASE WHEN minimum_stock > 0 THEN (current_stock / minimum_stock) ELSE 0 END'), 'asc')
                ->limit(5)
                ->get();
            
            // Recent stock movements (latest 10)
            $recentMovements = StockMovement::with(['user'])
                ->latest('transaction_date')
                ->limit(10)
                ->get();

            // ✅ TRIGGER AUTOMATIC STOCK CHECK ON DASHBOARD LOAD
            $this->performAutomaticStockCheck();
            
            return view('stocks.index', compact(
                'summaryStats',
                'chartData', 
                'stockValues',
                'lowStockItems',
                'recentMovements'
            ));
            
        } catch (\Exception $e) {
            Log::error('Stock dashboard error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal memuat dashboard stock: ' . $e->getMessage());
        }
    }

    /**
     * Halaman management raw materials
     * Dengan filtering, searching, dan pagination
     */
    public function materials(Request $request)
    {
        // Check role access
        $this->checkStockAccess();
        
        try {
            // Validasi input filters
            $request->validate([
                'search' => 'nullable|string|max:100',
                'supplier' => 'nullable|string|max:100',
                'stock_status' => 'nullable|in:high,low,out',
                'sort' => 'nullable|in:name,current_stock,unit_price,supplier',
                'direction' => 'nullable|in:asc,desc'
            ]);

            // Build query dengan filters
            $query = RawMaterial::query()->where('is_active', true);
            
            // Filter berdasarkan pencarian
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            // Filter berdasarkan supplier
            if ($request->filled('supplier')) {
                $query->where('supplier', $request->supplier);
            }
            
            // Filter berdasarkan status stock
            if ($request->filled('stock_status')) {
                switch ($request->stock_status) {
                    case 'low':
                        $query->whereRaw('current_stock <= minimum_stock');
                        break;
                    case 'out':
                        $query->where('current_stock', '<=', 0);
                        break;
                    case 'high':
                        $query->whereRaw('current_stock > (minimum_stock * 1.5)');
                        break;
                }
            }
            
            // Sorting
            $sortField = $request->sort ?? 'name';
            $sortDirection = $request->direction ?? 'asc';
            
            if ($sortField === 'current_stock') {
                $query->orderBy('current_stock', $sortDirection);
            } else {
                $query->orderBy($sortField, $sortDirection);
            }
            
            // Pagination
            $materials = $query->paginate(15)->withQueryString();
            
            // Summary untuk filtered results
            $filterSummary = [
                'total_items' => $query->count(),
                'total_value' => $query->sum(DB::raw('current_stock * unit_price')),
                'avg_stock' => $query->avg('current_stock') ?? 0
            ];
            
            // Daftar suppliers untuk filter dropdown
            $suppliers = RawMaterial::whereNotNull('supplier')
                ->where('supplier', '!=', '')
                ->distinct()
                ->pluck('supplier');
            
            // Store filters untuk view - FIXED: pastikan semua key ada
            $filters = [
                'search' => $request->get('search', ''),
                'supplier' => $request->get('supplier', ''),
                'stock_status' => $request->get('stock_status', ''),
                'sort' => $request->get('sort', 'name'),
                'direction' => $request->get('direction', 'asc')
            ];
            
            return view('stocks.materials', compact(
                'materials',
                'filterSummary',
                'suppliers',
                'filters'
            ));
            
        } catch (\Exception $e) {
            Log::error('Stock materials error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal memuat data materials: ' . $e->getMessage());
        }
    }

    /**
     * Halaman finished goods management
     * Menampilkan produk yang sudah selesai produksi
     */
    public function finishedGoods(Request $request)
    {
        // Check role access
        $this->checkStockAccess();
        
        try {
            // Validasi input filters
            $request->validate([
                'product_type' => 'nullable|exists:product_types,id',
                'status' => 'nullable|in:completed,distributed',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from'
            ]);

            // Query productions yang sudah selesai (completed/distributed)
            $query = Production::with(['productType', 'machine', 'operator', 'qualityControls'])
                ->whereIn('status', ['completed', 'distributed'])
                ->where('good_quantity', '>', 0); // Hanya yang ada produk bagus
            
            // Filter berdasarkan product type
            if ($request->filled('product_type')) {
                $query->where('product_type_id', $request->product_type);
            }
            
            // Filter berdasarkan status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            // Filter berdasarkan tanggal
            if ($request->filled('date_from')) {
                $query->whereDate('production_date', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('production_date', '<=', $request->date_to);
            }
            
            // Urutkan berdasarkan tanggal terbaru
            $query->orderBy('production_date', 'desc');
            
            // Pagination
            $finishedGoods = $query->paginate(12)->withQueryString();
            
            // Summary statistics
            $summary = [
                'total_batches' => $query->count(),
                'total_quantity' => $query->sum('good_quantity'),
                'avg_quality' => $this->calculateAverageQualityRate($query->get())
            ];
            
            // Product types untuk filter
            $productTypes = ProductType::where('is_active', true)
                ->orderBy('name')
                ->get();
            
            // Store filters - FIXED: pastikan semua key ada
            $filters = [
                'product_type' => $request->get('product_type', ''),
                'status' => $request->get('status', ''),
                'date_from' => $request->get('date_from', ''),
                'date_to' => $request->get('date_to', '')
            ];
            
            return view('stocks.finished-goods', compact(
                'finishedGoods',
                'summary',
                'productTypes',
                'filters'
            ));
            
        } catch (\Exception $e) {
            Log::error('Finished goods error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal memuat data finished goods: ' . $e->getMessage());
        }
    }

    /**
     * Halaman stock movements history
     * Menampilkan semua transaksi stock
     */
    public function movements(Request $request)
    {
        // Check role access
        $this->checkStockAccess();
        
        try {
            // Validasi input filters
            $request->validate([
                'search' => 'nullable|string|max:100',
                'movement_type' => 'nullable|in:in,out,adjustment',
                'stock_type' => 'nullable|in:raw_material,finished_product',
                'user_id' => 'nullable|exists:users,id',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from'
            ]);

            // Build query movements
            $query = StockMovement::with(['user']);
            
            // Filter berdasarkan pencarian
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('transaction_number', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%");
                });
            }
            
            // Filter berdasarkan movement type
            if ($request->filled('movement_type')) {
                $query->where('movement_type', $request->movement_type);
            }
            
            // Filter berdasarkan stock type
            if ($request->filled('stock_type')) {
                $query->where('stock_type', $request->stock_type);
            }
            
            // Filter berdasarkan user
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            
            // Filter berdasarkan tanggal
            if ($request->filled('date_from')) {
                $query->whereDate('transaction_date', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('transaction_date', '<=', $request->date_to);
            }
            
            // Urutkan berdasarkan tanggal terbaru
            $query->orderBy('transaction_date', 'desc');
            
            // Pagination
            $movements = $query->paginate(20)->withQueryString();
            
            // Summary untuk movements
            $summary = [
                'total_movements' => $query->count(),
                'in_movements' => (clone $query)->where('movement_type', 'in')->count(),
                'out_movements' => (clone $query)->where('movement_type', 'out')->count(),
                'total_value' => $query->sum(DB::raw('quantity * unit_price'))
            ];
            
            // Users untuk filter dropdown
            $users = User::whereHas('stockMovements')
                ->orderBy('name')
                ->get(['id', 'name', 'employee_id']);
            
            // Store filters - FIXED: pastikan semua key ada
            $filters = [
                'search' => $request->get('search', ''),
                'movement_type' => $request->get('movement_type', ''),
                'stock_type' => $request->get('stock_type', ''),
                'user_id' => $request->get('user_id', ''),
                'date_from' => $request->get('date_from', ''),
                'date_to' => $request->get('date_to', '')
            ];
            
            return view('stocks.movements', compact(
                'movements',
                'summary',
                'users',
                'filters'
            ));
            
        } catch (\Exception $e) {
            Log::error('Stock movements error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal memuat data movements: ' . $e->getMessage());
        }
    }

    /**
     * Halaman alerts & warnings - FIXED untuk data real dari database
     * Menampilkan notifikasi stock rendah dan rekomendasi
     */
    public function alerts(Request $request)
    {
        // Check role access
        $this->checkStockAccess();
        
        try {
            // Handle AJAX request untuk auto-refresh
            if ($request->ajax()) {
                $currentAlertCount = RawMaterial::where('is_active', true)
                    ->where(function($query) {
                        $query->where('current_stock', '<=', 0)
                              ->orWhereRaw('current_stock <= minimum_stock');
                    })
                    ->count();
                
                return response()->json([
                    'success' => true,
                    'hasNewAlerts' => $currentAlertCount > 0,
                    'alertCount' => $currentAlertCount,
                    'trigger_update' => true // ✅ SIGNAL FOR FRONTEND UPDATE
                ]);
            }

            // Ambil items yang out of stock (stok = 0 atau negatif)
            $outOfStockItems = RawMaterial::where('is_active', true)
                ->where('current_stock', '<=', 0)
                ->orderBy('updated_at', 'desc')
                ->get();
            
            // Ambil items dengan stock rendah (current_stock <= minimum_stock tapi > 0)
            $lowStockItems = RawMaterial::where('is_active', true)
                ->whereRaw('current_stock <= minimum_stock')
                ->where('current_stock', '>', 0)
                ->orderBy(DB::raw('CASE WHEN minimum_stock > 0 THEN (current_stock / minimum_stock) ELSE 0 END'), 'asc')
                ->get();
            
            // Items mendekati expired (untuk future implementation)
            $expiredItems = collect(); // Placeholder untuk sekarang
            
            // Alert statistics
            $alertStats = [
                'out_of_stock_count' => $outOfStockItems->count(),
                'low_stock_count' => $lowStockItems->count(),
                'expired_count' => $expiredItems->count(),
                'total_alerts' => $outOfStockItems->count() + $lowStockItems->count() + $expiredItems->count()
            ];
            
            // Generate reorder recommendations berdasarkan data real
            $reorderRecommendations = $this->generateSmartReorderRecommendations($outOfStockItems, $lowStockItems);
            
            return view('stocks.alerts', compact(
                'outOfStockItems',
                'lowStockItems',
                'alertStats',
                'reorderRecommendations'
            ));
            
        } catch (\Exception $e) {
            Log::error('Stock alerts error: ' . $e->getMessage());
            
            // Return dengan fallback data jika ada error
            return view('stocks.alerts', [
                'outOfStockItems' => collect(),
                'lowStockItems' => collect(),
                'alertStats' => [
                    'out_of_stock_count' => 0,
                    'low_stock_count' => 0,
                    'expired_count' => 0,
                    'total_alerts' => 0
                ],
                'reorderRecommendations' => collect()
            ])->with('error', 'Gagal memuat alerts: ' . $e->getMessage());
        }
    }

    /**
     * Store new stock movement
     * Untuk menambah/mengurangi/adjust stock
     */
    public function storeMovement(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'stock_type' => 'required|in:raw_material,finished_product',
                'item_id' => 'required|integer',
                'movement_type' => 'required|in:in,out,adjustment',
                'quantity' => 'required|numeric|min:0.01',
                'unit_price' => 'required|numeric|min:0',
                'notes' => 'nullable|string|max:500',
                'reference_type' => 'nullable|string|max:50',
                'reference_number' => 'nullable|string|max:100'
            ]);

            // Validasi item berdasarkan stock type
            if ($request->stock_type === 'raw_material') {
                $item = RawMaterial::findOrFail($request->item_id);
            } else {
                // TODO: implement finished product handling
                return response()->json([
                    'success' => false,
                    'message' => 'Finished product stock management belum tersedia'
                ]);
            }
            
            // Mulai database transaction
            DB::beginTransaction();
            
            // Cek stock availability untuk movement out
            if ($request->movement_type === 'out') {
                if ($item->current_stock < $request->quantity) {
                    throw new \Exception("Stock tidak mencukupi. Stock tersedia: {$item->current_stock} {$item->unit}");
                }
            }
            
            // Generate transaction number
            $transactionNumber = $this->generateTransactionNumber($request->movement_type);
            
            // Hitung balance before & after
            $balanceBefore = $item->current_stock;
            
            switch ($request->movement_type) {
                case 'in':
                    $balanceAfter = $balanceBefore + $request->quantity;
                    break;
                case 'out':
                    $balanceAfter = $balanceBefore - $request->quantity;
                    break;
                case 'adjustment':
                    $balanceAfter = $request->quantity; // Adjustment set ke nilai absolut
                    break;
            }
            
            // Create stock movement record
            $movement = StockMovement::create([
                'transaction_number' => $transactionNumber,
                'transaction_date' => now(),
                'stock_type' => $request->stock_type,
                'item_id' => $request->item_id,
                'item_type' => get_class($item),
                'movement_type' => $request->movement_type,
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_id' => $request->reference_id ?? null,
                'reference_type' => $request->reference_type ?? null,
                'user_id' => Auth::id(),
                'notes' => $request->notes
            ]);
            
            // Update item stock
            $item->update([
                'current_stock' => $balanceAfter,
                'updated_at' => now()
            ]);
            
            // Update unit price jika berbeda (untuk stock in)
            if ($request->movement_type === 'in' && $request->unit_price != $item->unit_price) {
                // Hitung weighted average price
                $totalValue = ($balanceBefore * $item->unit_price) + ($request->quantity * $request->unit_price);
                $totalQuantity = $balanceAfter;
                $newUnitPrice = $totalQuantity > 0 ? $totalValue / $totalQuantity : $request->unit_price;
                
                $item->update(['unit_price' => $newUnitPrice]);
            }

            // ✅ TRIGGER STOCK NOTIFICATIONS BASED ON NEW BALANCE
            $this->checkAndTriggerStockNotifications($item, $request->movement_type);
            
            DB::commit();
            
            // Clear cache untuk dashboard
            Cache::forget('stock_summary_stats');
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stock movement berhasil dicatat',
                    'data' => [
                        'movement' => $movement,
                        'new_balance' => $balanceAfter
                    ],
                    'trigger_update' => true // ✅ SIGNAL FOR FRONTEND UPDATE
                ]);
            }
            
            return redirect()->back()
                ->with('success', 'Stock movement berhasil dicatat')
                ->with('trigger_update', true);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store movement error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal mencatat stock movement: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Store new raw material
     */
    public function storeMaterial(Request $request)
    {
        // Check role access
        $this->checkStockAccess();
        
        try {
            // Validasi input
            $request->validate([
                'code' => 'required|string|max:50|unique:raw_materials,code',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'unit' => 'required|string|max:20',
                'unit_price' => 'required|numeric|min:0',
                'current_stock' => 'nullable|numeric|min:0',
                'minimum_stock' => 'required|numeric|min:0',
                'maximum_stock' => 'required|numeric|min:0',
                'supplier' => 'nullable|string|max:255'
            ]);

            // Create new raw material
            $material = RawMaterial::create([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
                'unit' => $request->unit,
                'unit_price' => $request->unit_price,
                'current_stock' => $request->current_stock ?? 0,
                'minimum_stock' => $request->minimum_stock,
                'maximum_stock' => $request->maximum_stock,
                'supplier' => $request->supplier,
                'is_active' => true
            ]);

            // ✅ CHECK IF NEW MATERIAL NEEDS IMMEDIATE NOTIFICATION
            if ($material->current_stock <= $material->minimum_stock) {
                if ($material->current_stock <= 0) {
                    $this->notificationService->createStockNotification($material, 'out_of_stock');
                } else {
                    $this->notificationService->createStockNotification($material, 'low_stock');
                }
            }

            // Clear cache
            Cache::forget('stock_summary_stats');

            return redirect()->route('stocks.materials')
                ->with('success', 'Material berhasil ditambahkan!')
                ->with('trigger_update', true); // ✅ SIGNAL FOR FRONTEND UPDATE

        } catch (\Exception $e) {
            Log::error('Store material error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menambahkan material: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ✅ NEW METHOD: Update stock levels (bulk update)
     */
    public function updateStockLevels(Request $request)
    {
        try {
            $request->validate([
                'updates' => 'required|array',
                'updates.*.material_id' => 'required|exists:raw_materials,id',
                'updates.*.new_stock' => 'required|numeric|min:0',
                'notes' => 'nullable|string|max:500'
            ]);

            $updatedCount = 0;
            $notificationsTriggered = 0;

            DB::beginTransaction();

            foreach ($request->updates as $update) {
                $material = RawMaterial::findOrFail($update['material_id']);
                $oldStock = $material->current_stock;
                $newStock = $update['new_stock'];

                if ($oldStock != $newStock) {
                    // Create stock movement for the adjustment
                    $transactionNumber = $this->generateTransactionNumber('adjustment');
                    
                    StockMovement::create([
                        'transaction_number' => $transactionNumber,
                        'transaction_date' => now(),
                        'stock_type' => 'raw_material',
                        'item_id' => $material->id,
                        'item_type' => 'App\\Models\\RawMaterial',
                        'movement_type' => 'adjustment',
                        'quantity' => $newStock,
                        'unit_price' => $material->unit_price,
                        'balance_before' => $oldStock,
                        'balance_after' => $newStock,
                        'user_id' => Auth::id(),
                        'notes' => $request->notes ?: "Bulk stock adjustment"
                    ]);

                    // Update material stock
                    $material->update(['current_stock' => $newStock]);

                    // ✅ CHECK AND TRIGGER NOTIFICATIONS
                    if ($this->checkAndTriggerStockNotifications($material, 'adjustment')) {
                        $notificationsTriggered++;
                    }

                    $updatedCount++;
                }
            }

            DB::commit();

            // Clear cache
            Cache::forget('stock_summary_stats');

            return response()->json([
                'success' => true,
                'message' => "Berhasil memperbarui {$updatedCount} item stock",
                'updated_count' => $updatedCount,
                'notifications_triggered' => $notificationsTriggered,
                'trigger_update' => true
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk stock update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show individual material details
     */
    public function showMaterial($id)
    {
        try {
            $material = RawMaterial::findOrFail($id);
            
            // Recent movements untuk material ini
            $recentMovements = StockMovement::where('item_id', $id)
                ->where('item_type', 'App\\Models\\RawMaterial')
                ->with('user')
                ->orderBy('transaction_date', 'desc')
                ->paginate(15);
            
            // Stock statistics
            $stats = [
                'total_movements' => StockMovement::where('item_id', $id)
                    ->where('item_type', 'App\\Models\\RawMaterial')
                    ->count(),
                'total_in' => StockMovement::where('item_id', $id)
                    ->where('item_type', 'App\\Models\\RawMaterial')
                    ->where('movement_type', 'in')
                    ->sum('quantity'),
                'total_out' => StockMovement::where('item_id', $id)
                    ->where('item_type', 'App\\Models\\RawMaterial')
                    ->where('movement_type', 'out')
                    ->sum('quantity'),
                'last_movement' => StockMovement::where('item_id', $id)
                    ->where('item_type', 'App\\Models\\RawMaterial')
                    ->orderBy('transaction_date', 'desc')
                    ->first()
            ];
            
            return view('stocks.materials.show', compact('material', 'recentMovements', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Show material error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Material tidak ditemukan: ' . $e->getMessage());
        }
    }

    /**
     * Show individual movement details
     */
    public function showMovement($id)
    {
        try {
            $movement = StockMovement::with(['user'])->findOrFail($id);
            
            // Get item details
            if ($movement->item_type === 'App\\Models\\RawMaterial') {
                $item = RawMaterial::find($movement->item_id);
            } else {
                $item = null; // Handle other item types
            }
            
            return view('stocks.movements.show', compact('movement', 'item'));
            
        } catch (\Exception $e) {
            Log::error('Show movement error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Movement tidak ditemukan: ' . $e->getMessage());
        }
    }

    /**
     * Create movement form
     */
    public function createMovement()
    {
        try {
            $materials = RawMaterial::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'unit', 'current_stock']);
                
            $users = User::where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'employee_id']);
            
            return view('stocks.movements.create', compact('materials', 'users'));
            
        } catch (\Exception $e) {
            Log::error('Create movement form error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal memuat form: ' . $e->getMessage());
        }
    }

    /**
     * API: Get chart data untuk dashboard
     */
    public function getChartData(Request $request)
    {
        try {
            $type = $request->get('type', 'movements');
            $period = $request->get('period', '7'); // days
            
            if ($type === 'movements') {
                return $this->getMovementTrendsData($period);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Chart type tidak dikenal'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Chart data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * API: Get current stock levels
     */
    public function getStockLevels()
    {
        try {
            $materials = RawMaterial::where('is_active', true)
                ->select('id', 'name', 'code', 'current_stock', 'minimum_stock', 'maximum_stock', 'unit')
                ->get()
                ->map(function($material) {
                    return [
                        'id' => $material->id,
                        'name' => $material->name,
                        'code' => $material->code,
                        'current_stock' => $material->current_stock,
                        'minimum_stock' => $material->minimum_stock,
                        'maximum_stock' => $material->maximum_stock,
                        'unit' => $material->unit,
                        'percentage' => $material->getStockPercentage(),
                        'is_low_stock' => $material->isLowStock(),
                        'status' => $material->current_stock <= 0 ? 'out' : 
                                   ($material->isLowStock() ? 'low' : 'normal')
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $materials,
                'trigger_update' => true // ✅ SIGNAL FOR FRONTEND UPDATE
            ]);
            
        } catch (\Exception $e) {
            Log::error('Stock levels error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Export material data
     */
    public function exportMaterial($id)
    {
        try {
            $material = RawMaterial::findOrFail($id);
            $movements = StockMovement::where('item_id', $id)
                ->where('item_type', 'App\\Models\\RawMaterial')
                ->with('user')
                ->orderBy('transaction_date', 'desc')
                ->get();
            
            // Generate CSV export
            $filename = "material-{$material->code}-" . now()->format('Y-m-d') . ".csv";
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\""
            ];
            
            $callback = function() use ($material, $movements) {
                $file = fopen('php://output', 'w');
                
                // CSV Headers
                fputcsv($file, [
                    'Material Code', 'Material Name', 'Current Stock', 'Unit',
                    'Minimum Stock', 'Maximum Stock', 'Unit Price', 'Supplier'
                ]);
                
                // Material data
                fputcsv($file, [
                    $material->code,
                    $material->name,
                    $material->current_stock,
                    $material->unit,
                    $material->minimum_stock,
                    $material->maximum_stock,
                    $material->unit_price,
                    $material->supplier
                ]);
                
                // Movement history headers
                fputcsv($file, []); // Empty row
                fputcsv($file, ['Movement History']);
                fputcsv($file, [
                    'Date', 'Transaction Number', 'Type', 'Quantity', 'Unit Price', 
                    'Balance Before', 'Balance After', 'User', 'Notes'
                ]);
                
                // Movement data
                foreach ($movements as $movement) {
                    fputcsv($file, [
                        $movement->transaction_date->format('Y-m-d H:i:s'),
                        $movement->transaction_number,
                        strtoupper($movement->movement_type),
                        $movement->quantity,
                        $movement->unit_price,
                        $movement->balance_before,
                        $movement->balance_after,
                        $movement->user->name ?? 'System',
                        $movement->notes
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error('Export material error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal export material: ' . $e->getMessage());
        }
    }

    /**
     * Export finished goods data
     */
    public function exportFinishedGoods(Request $request, $format)
    {
        try {
            // Get filtered data berdasarkan request parameters
            $query = Production::with(['productType', 'machine', 'operator', 'qualityControls'])
                ->whereIn('status', ['completed', 'distributed'])
                ->where('good_quantity', '>', 0);
            
            // Apply filters dari request
            if ($request->filled('product_type')) {
                $query->where('product_type_id', $request->product_type);
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
            
            $finishedGoods = $query->orderBy('production_date', 'desc')->get();
            
            if ($format === 'csv') {
                return $this->exportFinishedGoodsAsCSV($finishedGoods);
            } elseif ($format === 'excel') {
                return $this->exportFinishedGoodsAsExcel($finishedGoods);
            } elseif ($format === 'pdf') {
                return $this->exportFinishedGoodsAsPDF($finishedGoods);
            }
            
            return redirect()->back()->with('error', 'Format export tidak didukung');
            
        } catch (\Exception $e) {
            Log::error('Export finished goods error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal export finished goods: ' . $e->getMessage());
        }
    }

    /**
     * Print movement receipt
     */
    public function printMovement($id)
    {
        try {
            $movement = StockMovement::with(['user'])->findOrFail($id);
            
            // Get item details
            if ($movement->item_type === 'App\\Models\\RawMaterial') {
                $item = RawMaterial::find($movement->item_id);
            } else {
                $item = null;
            }
            
            return view('stocks.movements.print', compact('movement', 'item'));
            
        } catch (\Exception $e) {
            Log::error('Print movement error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Movement tidak ditemukan: ' . $e->getMessage());
        }
    }

    /**
     * General export function untuk stocks
     */
    public function export(Request $request, $type, $format)
    {
        try {
            switch ($type) {
                case 'materials':
                    return $this->exportMaterials($request, $format);
                case 'movements':
                    return $this->exportMovements($request, $format);
                case 'alerts':
                    return $this->exportAlerts($request, $format);
                default:
                    return redirect()->back()->with('error', 'Export type tidak dikenal');
            }
        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }

    // =================== NOTIFICATION INTEGRATION METHODS =================== ✅ ADDED

    /**
     * ✅ NEW METHOD: Perform automatic stock check for all materials
     */
    private function performAutomaticStockCheck()
    {
        try {
            // Check semua materials untuk stock level issues
            $materialsToCheck = RawMaterial::where('is_active', true)->get();
            
            foreach ($materialsToCheck as $material) {
                $this->checkAndTriggerStockNotifications($material, 'check');
            }
            
            Log::info('Automatic stock check completed', [
                'materials_checked' => $materialsToCheck->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Automatic stock check error: ' . $e->getMessage());
        }
    }

    /**
     * ✅ NEW METHOD: Check stock levels and trigger appropriate notifications
     */
    private function checkAndTriggerStockNotifications($material, $movementType)
    {
        try {
            $notificationsTriggered = false;
            
            // Out of stock notification
            if ($material->current_stock <= 0) {
                $this->notificationService->createStockNotification($material, 'out_of_stock');
                $notificationsTriggered = true;
                
                Log::info('Out of stock notification triggered', [
                    'material_id' => $material->id,
                    'material_name' => $material->name,
                    'current_stock' => $material->current_stock
                ]);
            }
            // Low stock notification (stock <= minimum but > 0)
            elseif ($material->current_stock <= $material->minimum_stock) {
                $this->notificationService->createStockNotification($material, 'low_stock');
                $notificationsTriggered = true;
                
                Log::info('Low stock notification triggered', [
                    'material_id' => $material->id,
                    'material_name' => $material->name,
                    'current_stock' => $material->current_stock,
                    'minimum_stock' => $material->minimum_stock
                ]);
            }
            // Stock replenished notification (only for 'in' movements)
            elseif ($movementType === 'in' && $material->current_stock > $material->minimum_stock) {
                // Check if it was previously low/out of stock dalam 24 jam terakhir
                $previousLowStock = StockMovement::where('item_id', $material->id)
                    ->where('item_type', 'App\\Models\\RawMaterial')
                    ->where('transaction_date', '>=', now()->subDay())
                    ->where('balance_before', '<=', $material->minimum_stock)
                    ->exists();
                
                if ($previousLowStock) {
                    $this->notificationService->createStockNotification($material, 'stock_replenished');
                    $notificationsTriggered = true;
                    
                    Log::info('Stock replenished notification triggered', [
                        'material_id' => $material->id,
                        'material_name' => $material->name,
                        'current_stock' => $material->current_stock,
                        'minimum_stock' => $material->minimum_stock
                    ]);
                }
            }
            
            // Check for expiry warning if expiry date exists (future implementation)
            // TODO: Add expiry date field to raw_materials table
            // if ($material->expiry_date && $material->expiry_date <= now()->addDays(7)) {
            //     $this->notificationService->createStockNotification($material, 'expiry_warning');
            //     $notificationsTriggered = true;
            // }
            
            return $notificationsTriggered;
            
        } catch (\Exception $e) {
            Log::error('Stock notification check error', [
                'material_id' => $material->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * ✅ NEW METHOD: Manual trigger for stock level check (API endpoint)
     */
    public function triggerStockCheck(Request $request)
    {
        try {
            $materialId = $request->get('material_id');
            
            if ($materialId) {
                // Check specific material
                $material = RawMaterial::findOrFail($materialId);
                $triggered = $this->checkAndTriggerStockNotifications($material, 'manual');
                
                return response()->json([
                    'success' => true,
                    'message' => $triggered ? 'Notifikasi stock berhasil dikirim' : 'Tidak ada notifikasi yang perlu dikirim',
                    'notifications_triggered' => $triggered ? 1 : 0,
                    'trigger_update' => true
                ]);
            } else {
                // Check all materials
                $this->performAutomaticStockCheck();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Stock check untuk semua material berhasil dilakukan',
                    'trigger_update' => true
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Manual stock check error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan stock check: ' . $e->getMessage()
            ], 500);
        }
    }

    // =================== PRIVATE HELPER METHODS ===================

    /**
     * Get summary statistics untuk dashboard
     */
    private function getSummaryStatistics()
    {
        return Cache::remember('stock_summary_stats', 300, function() {
            try {
                $totalMaterials = RawMaterial::where('is_active', true)->count();
                $lowStockItems = RawMaterial::where('is_active', true)
                    ->whereRaw('current_stock <= minimum_stock')
                    ->count();
                $outOfStockItems = RawMaterial::where('is_active', true)
                    ->where('current_stock', '<=', 0)
                    ->count();
                
                // Hitung stock health percentage
                $healthyItems = $totalMaterials - $lowStockItems - $outOfStockItems;
                $stockHealth = $totalMaterials > 0 ? round(($healthyItems / $totalMaterials) * 100) : 0;
                
                // Today's movements
                $todayMovements = StockMovement::whereDate('transaction_date', today())->count();
                
                // Total stock value
                $totalStockValue = RawMaterial::where('is_active', true)
                    ->sum(DB::raw('current_stock * unit_price'));
                
                return [
                    'total_materials' => $totalMaterials,
                    'stock_health' => $stockHealth,
                    'low_stock_items' => $lowStockItems,
                    'out_of_stock_items' => $outOfStockItems,
                    'today_movements' => $todayMovements,
                    'total_stock_value' => $totalStockValue
                ];
                
            } catch (\Exception $e) {
                Log::error('Summary stats error: ' . $e->getMessage());
                return [
                    'total_materials' => 0,
                    'stock_health' => 0,
                    'low_stock_items' => 0,
                    'out_of_stock_items' => 0,
                    'today_movements' => 0,
                    'total_stock_value' => 0
                ];
            }
        });
    }

    /**
     * Get chart data untuk dashboard - renamed untuk avoid conflict
     */
    private function getChartDataForDashboard()
    {
        try {
            $days = 7;
            $dates = collect();
            
            for ($i = $days - 1; $i >= 0; $i--) {
                $dates->push(Carbon::today()->subDays($i));
            }
            
            $movementTrends = $dates->map(function($date) {
                $inMovements = StockMovement::whereDate('transaction_date', $date)
                    ->where('movement_type', 'in')
                    ->sum('quantity');
                    
                $outMovements = StockMovement::whereDate('transaction_date', $date)
                    ->where('movement_type', 'out')
                    ->sum('quantity');
                
                return [
                    'date' => $date->format('Y-m-d'),
                    'in' => $inMovements,
                    'out' => $outMovements
                ];
            });
            
            return [
                'movement_trends' => $movementTrends
            ];
            
        } catch (\Exception $e) {
            Log::error('Chart data dashboard error: ' . $e->getMessage());
            return [
                'movement_trends' => collect()
            ];
        }
    }

    /**
     * Get stock values by supplier untuk pie chart
     */
    private function getStockValuesBySupplier()
    {
        try {
            return RawMaterial::where('is_active', true)
                ->whereNotNull('supplier')
                ->select('supplier', DB::raw('SUM(current_stock * unit_price) as total_value'))
                ->groupBy('supplier')
                ->orderBy('total_value', 'desc')
                ->limit(8)
                ->get();
                
        } catch (\Exception $e) {
            Log::error('Stock values by supplier error: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Calculate average quality rate untuk finished goods
     */
    private function calculateAverageQualityRate($productions)
    {
        if ($productions->isEmpty()) {
            return 0;
        }
        
        $totalRate = 0;
        $count = 0;
        
        foreach ($productions as $production) {
            if ($production->actual_quantity > 0) {
                $rate = ($production->good_quantity / $production->actual_quantity) * 100;
                $totalRate += $rate;
                $count++;
            }
        }
        
        return $count > 0 ? round($totalRate / $count, 1) : 0;
    }

    /**
     * Generate smart reorder recommendations berdasarkan data real - UPDATED
     */
    private function generateSmartReorderRecommendations($outOfStockItems, $lowStockItems)
    {
        $recommendations = collect();
        
        // Gabungkan out of stock dan low stock items
        $allAlertItems = $outOfStockItems->merge($lowStockItems);
        
        foreach ($allAlertItems as $material) {
            try {
                // Hitung recommended quantity
                $recommendedQuantity = $this->calculateRecommendedQuantity($material);
                
                // Tentukan urgency level
                $urgency = $this->determineUrgencyLevel($material);
                
                // Hitung estimated cost
                $estimatedCost = $recommendedQuantity * $material->unit_price;
                
                // Calculate average daily usage dari 30 hari terakhir
                $avgDailyUsage = $this->calculateAverageDailyUsage($material);
                
                // Calculate days supply yang tersisa
                $daysSupply = $avgDailyUsage > 0 ? 
                    round($material->current_stock / $avgDailyUsage, 1) : 999;
                
                // Stock ratio untuk prioritization
                $stockRatio = $material->minimum_stock > 0 ? 
                    round($material->current_stock / $material->minimum_stock, 2) : 0;
                
                $recommendations->push([
                    'material' => $material,
                    'recommended_quantity' => $recommendedQuantity,
                    'estimated_cost' => $estimatedCost,
                    'urgency' => $urgency,
                    'stock_ratio' => $stockRatio,
                    'days_supply' => $daysSupply,
                    'avg_daily_usage' => $avgDailyUsage,
                    'supplier' => $material->supplier ?: 'Not specified',
                    'last_order_date' => $this->getLastOrderDate($material),
                    'reorder_point_reached' => $material->current_stock <= $material->minimum_stock
                ]);
                
            } catch (\Exception $e) {
                Log::warning("Error generating recommendation for material {$material->id}: " . $e->getMessage());
                continue;
            }
        }
        
        // Sort by urgency dan stock ratio
        return $recommendations->sortBy(function($item) {
            $urgencyScore = match($item['urgency']) {
                'critical' => 1,
                'high' => 2,
                'medium' => 3,
                default => 4
            };
            
            return [$urgencyScore, $item['stock_ratio']];
        })->take(10); // Limit ke 10 recommendations teratas
    }

    /**
     * Calculate recommended quantity untuk reorder
     */
    private function calculateRecommendedQuantity($material)
    {
        // Strategi: target ke maximum stock
        $targetStock = $material->maximum_stock;
        $currentStock = $material->current_stock;
        
        // Base recommendation: isi sampai max stock
        $baseRecommendation = max(0, $targetStock - $currentStock);
        
        // Jika max stock tidak reasonable, gunakan minimum stock + buffer
        if ($material->maximum_stock <= $material->minimum_stock) {
            $buffer = $material->minimum_stock * 0.5; // 50% buffer
            $baseRecommendation = max(0, ($material->minimum_stock + $buffer) - $currentStock);
        }
        
        // Calculate berdasarkan usage pattern jika ada data
        $avgMonthlyUsage = $this->calculateAverageMonthlyUsage($material);
        if ($avgMonthlyUsage > 0) {
            // Recomend untuk 2 bulan supply
            $usageBasedRecommendation = max(0, ($avgMonthlyUsage * 2) - $currentStock);
            
            // Gunakan yang lebih besar antara base dan usage-based
            $baseRecommendation = max($baseRecommendation, $usageBasedRecommendation);
        }
        
        // Round ke nilai yang reasonable
        return round($baseRecommendation, 2);
    }

    /**
     * Determine urgency level berdasarkan kondisi stock
     */
    private function determineUrgencyLevel($material)
    {
        // Critical: Out of stock atau kurang dari 10% minimum
        if ($material->current_stock <= 0 || 
            ($material->minimum_stock > 0 && $material->current_stock < ($material->minimum_stock * 0.1))) {
            return 'critical';
        }
        
        // High: Kurang dari 50% minimum stock
        if ($material->minimum_stock > 0 && $material->current_stock < ($material->minimum_stock * 0.5)) {
            return 'high';
        }
        
        // Medium: Mencapai minimum stock
        if ($material->current_stock <= $material->minimum_stock) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Calculate average daily usage dari historical data
     */
    private function calculateAverageDailyUsage($material)
    {
        try {
            $totalOutMovements = StockMovement::where('item_id', $material->id)
                ->where('item_type', 'App\\Models\\RawMaterial')
                ->where('movement_type', 'out')
                ->where('transaction_date', '>=', now()->subDays(30))
                ->sum('quantity');
            
            return $totalOutMovements > 0 ? round($totalOutMovements / 30, 2) : 0;
            
        } catch (\Exception $e) {
            Log::warning("Error calculating daily usage for material {$material->id}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate average monthly usage
     */
    private function calculateAverageMonthlyUsage($material)
    {
        try {
            $totalOutMovements = StockMovement::where('item_id', $material->id)
                ->where('item_type', 'App\\Models\\RawMaterial')
                ->where('movement_type', 'out')
                ->where('transaction_date', '>=', now()->subDays(90)) // 3 bulan
                ->sum('quantity');
            
            return $totalOutMovements > 0 ? round($totalOutMovements / 3, 2) : 0;
            
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get last order date untuk material
     */
    private function getLastOrderDate($material)
    {
        try {
            $lastInMovement = StockMovement::where('item_id', $material->id)
                ->where('item_type', 'App\\Models\\RawMaterial')
                ->where('movement_type', 'in')
                ->orderBy('transaction_date', 'desc')
                ->first();
            
            return $lastInMovement ? $lastInMovement->transaction_date->format('Y-m-d') : 'Never';
            
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Generate transaction number untuk movements
     */
    private function generateTransactionNumber($movementType)
    {
        $prefix = match($movementType) {
            'in' => 'STK-IN',
            'out' => 'STK-OUT',
            'adjustment' => 'STK-ADJ',
            default => 'STK'
        };
        
        $date = now()->format('Ymd');
        
        // Ambil nomor urut terakhir untuk hari ini
        $lastMovement = StockMovement::where('transaction_number', 'like', "{$prefix}-{$date}-%")
            ->orderBy('transaction_number', 'desc')
            ->first();
        
        if ($lastMovement) {
            $lastNumber = (int) substr($lastMovement->transaction_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return sprintf('%s-%s-%04d', $prefix, $date, $nextNumber);
    }

    /**
     * Get movement trends data untuk chart API
     */
    private function getMovementTrendsData($period)
    {
        try {
            $days = (int) $period;
            $dates = collect();
            
            for ($i = $days - 1; $i >= 0; $i--) {
                $dates->push(Carbon::today()->subDays($i));
            }
            
            $labels = $dates->map(function($date) {
                return $date->format('M d');
            })->toArray();
            
            $inData = $dates->map(function($date) {
                return StockMovement::whereDate('transaction_date', $date)
                    ->where('movement_type', 'in')
                    ->sum('quantity');
            })->toArray();
            
            $outData = $dates->map(function($date) {
                return StockMovement::whereDate('transaction_date', $date)
                    ->where('movement_type', 'out')
                    ->sum('quantity');
            })->toArray();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Stock In',
                            'data' => $inData,
                            'borderColor' => '#28a745',
                            'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
                            'fill' => true
                        ],
                        [
                            'label' => 'Stock Out',
                            'data' => $outData,
                            'borderColor' => '#dc3545',
                            'backgroundColor' => 'rgba(220, 53, 69, 0.1)',
                            'fill' => true
                        ]
                    ]
                ],
                'trigger_update' => true // ✅ SIGNAL FOR FRONTEND UPDATE
            ]);
            
        } catch (\Exception $e) {
            Log::error('Movement trends data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Export materials sebagai CSV
     */
    private function exportMaterials(Request $request, $format)
    {
        try {
            // Get filtered materials berdasarkan request parameters
            $query = RawMaterial::where('is_active', true);
            
            // Apply filters sama seperti di method materials()
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('supplier')) {
                $query->where('supplier', $request->supplier);
            }
            
            if ($request->filled('stock_status')) {
                switch ($request->stock_status) {
                    case 'low':
                        $query->whereRaw('current_stock <= minimum_stock');
                        break;
                    case 'out':
                        $query->where('current_stock', '<=', 0);
                        break;
                    case 'high':
                        $query->whereRaw('current_stock > (minimum_stock * 1.5)');
                        break;
                }
            }
            
            $materials = $query->orderBy('name')->get();
            
            if ($format === 'csv') {
                
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename=\"{$filename}\""
                ];
                
                $callback = function() use ($materials) {
                    $file = fopen('php://output', 'w');
                    
                    // CSV Headers
                    fputcsv($file, [
                        'Code', 'Name', 'Description', 'Current Stock', 'Unit',
                        'Minimum Stock', 'Maximum Stock', 'Unit Price', 'Supplier',
                        'Stock Status', 'Stock Percentage', 'Total Value'
                    ]);
                    
                    // Data rows
                    foreach ($materials as $material) {
                        $stockPercentage = $material->getStockPercentage();
                        $stockStatus = $material->current_stock <= 0 ? 'OUT OF STOCK' : 
                                      ($material->isLowStock() ? 'LOW STOCK' : 'NORMAL');
                        $totalValue = $material->current_stock * $material->unit_price;
                        
                        fputcsv($file, [
                            $material->code,
                            $material->name,
                            $material->description,
                            $material->current_stock,
                            $material->unit,
                            $material->minimum_stock,
                            $material->maximum_stock,
                            $material->unit_price,
                            $material->supplier,
                            $stockStatus,
                            $stockPercentage . '%',
                            $totalValue
                        ]);
                    }
                    
                    fclose($file);
                };
                
                return response()->stream($callback, 200, $headers);
            }
            
            return redirect()->back()->with('error', 'Format export belum didukung');
            
        } catch (\Exception $e) {
            Log::error('Export materials error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export materials: ' . $e->getMessage());
        }
    }

    /**
     * Export movements sebagai CSV
     */
    private function exportMovements(Request $request, $format)
    {
        try {
            // Get filtered movements berdasarkan request parameters
            $query = StockMovement::with(['user']);
            
            // Apply filters sama seperti di method movements()
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('transaction_number', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('movement_type')) {
                $query->where('movement_type', $request->movement_type);
            }
            
            if ($request->filled('stock_type')) {
                $query->where('stock_type', $request->stock_type);
            }
            
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            
            if ($request->filled('date_from')) {
                $query->whereDate('transaction_date', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('transaction_date', '<=', $request->date_to);
            }
            
            $movements = $query->orderBy('transaction_date', 'desc')->get();
            
            if ($format === 'csv') {
                $filename = "stock-movements-" . now()->format('Y-m-d') . ".csv";
                
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename=\"{$filename}\""
                ];
                
                $callback = function() use ($movements) {
                    $file = fopen('php://output', 'w');
                    
                    // CSV Headers
                    fputcsv($file, [
                        'Date', 'Transaction Number', 'Stock Type', 'Item Name', 'Item Code',
                        'Movement Type', 'Quantity', 'Unit', 'Unit Price', 'Total Value',
                        'Balance Before', 'Balance After', 'User', 'Notes'
                    ]);
                    
                    // Data rows
                    foreach ($movements as $movement) {
                        $totalValue = $movement->quantity * $movement->unit_price;
                        
                        // Get item name safely
                        $itemName = 'Unknown Item';
                        $itemCode = 'N/A';
                        $itemUnit = 'unit';
                        
                        try {
                            if ($movement->item_type === 'App\\Models\\RawMaterial') {
                                $item = RawMaterial::find($movement->item_id);
                                if ($item) {
                                    $itemName = $item->name;
                                    $itemCode = $item->code;
                                    $itemUnit = $item->unit;
                                }
                            }
                        } catch (\Exception $e) {
                            // Keep default values
                        }
                        
                        fputcsv($file, [
                            $movement->transaction_date->format('Y-m-d H:i:s'),
                            $movement->transaction_number,
                            ucfirst(str_replace('_', ' ', $movement->stock_type)),
                            $itemName,
                            $itemCode,
                            strtoupper($movement->movement_type),
                            $movement->quantity,
                            $itemUnit,
                            $movement->unit_price,
                            $totalValue,
                            $movement->balance_before,
                            $movement->balance_after,
                            $movement->user->name ?? 'System',
                            $movement->notes
                        ]);
                    }
                    
                    fclose($file);
                };
                
                return response()->stream($callback, 200, $headers);
            }
            
            return redirect()->back()->with('error', 'Format export belum didukung');
            
        } catch (\Exception $e) {
            Log::error('Export movements error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export movements: ' . $e->getMessage());
        }
    }

    /**
     * Export alerts sebagai CSV
     */
    private function exportAlerts(Request $request, $format)
    {
        try {
            $outOfStockItems = RawMaterial::where('current_stock', '<=', 0)
                ->where('is_active', true)
                ->orderBy('updated_at', 'desc')
                ->get();
            
            $lowStockItems = RawMaterial::whereRaw('current_stock <= minimum_stock')
                ->where('current_stock', '>', 0)
                ->where('is_active', true)
                ->orderBy(DB::raw('CASE WHEN minimum_stock > 0 THEN (current_stock / minimum_stock) ELSE 0 END'), 'asc')
                ->get();
            
            if ($format === 'csv') {
                $filename = "stock-alerts-" . now()->format('Y-m-d') . ".csv";
                
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename=\"{$filename}\""
                ];
                
                $callback = function() use ($outOfStockItems, $lowStockItems) {
                    $file = fopen('php://output', 'w');
                    
                    // Out of Stock Section
                    fputcsv($file, ['OUT OF STOCK ITEMS']);
                    fputcsv($file, [
                        'Code', 'Name', 'Current Stock', 'Minimum Stock', 'Unit',
                        'Supplier', 'Last Updated', 'Urgency'
                    ]);
                    
                    foreach ($outOfStockItems as $item) {
                        fputcsv($file, [
                            $item->code,
                            $item->name,
                            $item->current_stock,
                            $item->minimum_stock,
                            $item->unit,
                            $item->supplier ?? 'N/A',
                            $item->updated_at->format('Y-m-d H:i:s'),
                            'CRITICAL'
                        ]);
                    }
                    
                    // Empty row
                    fputcsv($file, []);
                    
                    // Low Stock Section
                    fputcsv($file, ['LOW STOCK ITEMS']);
                    fputcsv($file, [
                        'Code', 'Name', 'Current Stock', 'Minimum Stock', 'Maximum Stock',
                        'Unit', 'Stock Percentage', 'Supplier', 'Urgency'
                    ]);
                    
                    foreach ($lowStockItems as $item) {
                        $stockPercentage = $item->getStockPercentage();
                        $urgency = $stockPercentage < 10 ? 'CRITICAL' : 
                                  ($stockPercentage < 25 ? 'HIGH' : 'MEDIUM');
                        
                        fputcsv($file, [
                            $item->code,
                            $item->name,
                            $item->current_stock,
                            $item->minimum_stock,
                            $item->maximum_stock,
                            $item->unit,
                            $stockPercentage . '%',
                            $item->supplier ?? 'N/A',
                            $urgency
                        ]);
                    }
                    
                    fclose($file);
                };
                
                return response()->stream($callback, 200, $headers);
            }
            
            return redirect()->back()->with('error', 'Format export belum didukung');
            
        } catch (\Exception $e) {
            Log::error('Export alerts error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export alerts: ' . $e->getMessage());
        }
    }

    /**
     * Export finished goods sebagai CSV
     */
    private function exportFinishedGoodsAsCSV($finishedGoods)
    {
        $filename = "finished-goods-" . now()->format('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\""
        ];
        
        $callback = function() use ($finishedGoods) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Batch Number', 'Production Date', 'Shift', 'Product Name', 'Brand', 'Model',
                'Machine', 'Operator', 'Target Quantity', 'Actual Quantity', 'Good Quantity',
                'Defect Quantity', 'Quality Rate (%)', 'Status', 'QC Status'
            ]);
            
            // Data rows
            foreach ($finishedGoods as $production) {
                $qualityRate = $production->actual_quantity > 0 ? 
                    round(($production->good_quantity / $production->actual_quantity) * 100, 2) : 0;
                
                $qcStatus = $production->qualityControls->first() ? 
                    $production->qualityControls->first()->final_status : 'N/A';
                
                fputcsv($file, [
                    $production->batch_number,
                    $production->production_date->format('Y-m-d'),
                    ucfirst($production->shift),
                    $production->productType->name ?? 'N/A',
                    $production->productType->brand ?? 'N/A',
                    $production->productType->model ?? 'N/A',
                    $production->machine->name ?? 'N/A',
                    $production->operator->name ?? 'N/A',
                    $production->target_quantity,
                    $production->actual_quantity,
                    $production->good_quantity,
                    $production->defect_quantity,
                    $qualityRate,
                    ucfirst($production->status),
                    ucfirst($qcStatus)
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export finished goods sebagai Excel (placeholder)
     */
    private function exportFinishedGoodsAsExcel($finishedGoods)
    {
        // TODO: Implement Excel export menggunakan PhpSpreadsheet
        // Untuk sekarang, return CSV sebagai fallback
        return $this->exportFinishedGoodsAsCSV($finishedGoods);
    }

    /**
     * Export finished goods sebagai PDF (placeholder)
     */
    private function exportFinishedGoodsAsPDF($finishedGoods)
    {
        // TODO: Implement PDF export menggunakan DomPDF
        try {
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('stocks.finished-goods.pdf', compact('finishedGoods'));
            
            $filename = "finished-goods-" . now()->format('Y-m-d') . ".pdf";
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            Log::error('PDF export error: ' . $e->getMessage());
            // Fallback ke CSV jika PDF gagal
            return $this->exportFinishedGoodsAsCSV($finishedGoods);
        }
    }
}