<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use App\Models\StockMovement;
use App\Models\Production;
use App\Models\User;
use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class StockController extends Controller
{
    public function __construct()
    {
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
            $lowStockItems = RawMaterial::lowStock()
                ->orderBy(DB::raw('(current_stock / minimum_stock)'), 'asc')
                ->limit(5)
                ->get();
            
            // Recent stock movements (latest 10)
            $recentMovements = StockMovement::with(['item', 'user'])
                ->latest('transaction_date')
                ->limit(10)
                ->get();
            
            return view('stocks.index', compact(
                'summaryStats',
                'chartData', 
                'stockValues',
                'lowStockItems',
                'recentMovements'
            ));
            
        } catch (\Exception $e) {
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
            $query = StockMovement::with(['item', 'user']);
            
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
            return redirect()->back()
                ->with('error', 'Gagal memuat data movements: ' . $e->getMessage());
        }
    }

    /**
     * Halaman alerts & warnings
     * Menampilkan notifikasi stock rendah dan rekomendasi
     */
    public function alerts()
    {
        // Check role access
        $this->checkStockAccess();
        
        try {
            // Ambil items yang out of stock
            $outOfStockItems = RawMaterial::where('current_stock', '<=', 0)
                ->where('is_active', true)
                ->orderBy('updated_at', 'desc')
                ->get();
            
            // Ambil items dengan stock rendah (tidak termasuk yang out of stock)
            $lowStockItems = RawMaterial::whereRaw('current_stock <= minimum_stock')
                ->where('current_stock', '>', 0)
                ->where('is_active', true)
                ->orderBy(DB::raw('(current_stock / minimum_stock)'), 'asc')
                ->get();
            
            // Alert statistics
            $alertStats = [
                'out_of_stock_count' => $outOfStockItems->count(),
                'low_stock_count' => $lowStockItems->count(),
                'expired_count' => 0, // TODO: implement expiry tracking
                'total_alerts' => $outOfStockItems->count() + $lowStockItems->count()
            ];
            
            // Generate reorder recommendations
            $reorderRecommendations = $this->generateReorderRecommendations();
            
            return view('stocks.alerts', compact(
                'outOfStockItems',
                'lowStockItems',
                'alertStats',
                'reorderRecommendations'
            ));
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memuat alerts: ' . $e->getMessage());
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
                    ]
                ]);
            }
            
            return redirect()->back()
                ->with('success', 'Stock movement berhasil dicatat');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
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

            // Clear cache
            Cache::forget('stock_summary_stats');

            return redirect()->route('stocks.materials')
                ->with('success', 'Material berhasil ditambahkan!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan material: ' . $e->getMessage())
                ->withInput();
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
                'data' => $materials
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show individual material details
     */
    public function showMaterial($id)
    {
        try {
            $material = RawMaterial::with(['stockMovements' => function($query) {
                $query->latest('transaction_date')->limit(20);
            }])->findOrFail($id);
            
            // Recent movements untuk material ini
            $recentMovements = $material->stockMovements()
                ->with('user')
                ->latest('transaction_date')
                ->paginate(15);
            
            // Stock statistics
            $stats = [
                'total_movements' => $material->stockMovements()->count(),
                'total_in' => $material->stockMovements()->where('movement_type', 'in')->sum('quantity'),
                'total_out' => $material->stockMovements()->where('movement_type', 'out')->sum('quantity'),
                'last_movement' => $material->stockMovements()->latest('transaction_date')->first()
            ];
            
            return view('stocks.materials.show', compact('material', 'recentMovements', 'stats'));
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Material tidak ditemukan: ' . $e->getMessage());
        }
    }

    /**
     * Export material data
     */
    public function exportMaterial($id)
    {
        try {
            $material = RawMaterial::with('stockMovements')->findOrFail($id);
            
            // Generate CSV export
            $filename = "material-{$material->code}-" . now()->format('Y-m-d') . ".csv";
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\""
            ];
            
            $callback = function() use ($material) {
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
                foreach ($material->stockMovements as $movement) {
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
            $movement = StockMovement::with(['item', 'user'])->findOrFail($id);
            
            return view('stocks.movements.print', compact('movement'));
            
        } catch (\Exception $e) {
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
            return redirect()->back()
                ->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }

    // =================== PRIVATE HELPER METHODS ===================

    /**
     * Get summary statistics untuk dashboard
     */
    private function getSummaryStatistics()
    {
        return Cache::remember('stock_summary_stats', 300, function() {
            $totalMaterials = RawMaterial::where('is_active', true)->count();
            $lowStockItems = RawMaterial::lowStock()->count();
            $outOfStockItems = RawMaterial::where('current_stock', '<=', 0)->count();
            
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
        });
    }

    /**
     * Get chart data untuk dashboard - renamed untuk avoid conflict
     */
    private function getChartDataForDashboard()
    {
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
    }

    /**
     * Get stock values by supplier untuk pie chart
     */
    private function getStockValuesBySupplier()
    {
        return RawMaterial::where('is_active', true)
            ->whereNotNull('supplier')
            ->select('supplier', DB::raw('SUM(current_stock * unit_price) as total_value'))
            ->groupBy('supplier')
            ->orderBy('total_value', 'desc')
            ->limit(8)
            ->get();
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
     * Generate reorder recommendations dengan business logic
     */
    private function generateReorderRecommendations()
    {
        $lowStockItems = RawMaterial::whereRaw('current_stock <= minimum_stock')
            ->where('is_active', true)
            ->orderBy(DB::raw('(current_stock / minimum_stock)'), 'asc')
            ->limit(10)
            ->get();
        
        return $lowStockItems->map(function($material) {
            // Hitung recommended quantity berdasarkan max stock
            $recommendedQuantity = max(0, $material->maximum_stock - $material->current_stock);
            
            // Tentukan urgency level berdasarkan stock ratio
            $stockRatio = $material->minimum_stock > 0 ? 
                $material->current_stock / $material->minimum_stock : 0;
            
            $urgency = $stockRatio <= 0.2 ? 'critical' : 
                      ($stockRatio <= 0.5 ? 'high' : 'medium');
            
            // Hitung estimated cost
            $estimatedCost = $recommendedQuantity * $material->unit_price;
            
            return [
                'material' => $material,
                'recommended_quantity' => $recommendedQuantity,
                'estimated_cost' => $estimatedCost,
                'urgency' => $urgency,
                'stock_ratio' => round($stockRatio, 2),
                'days_supply' => $this->calculateDaysSupply($material)
            ];
        });
    }

    /**
     * Calculate days supply berdasarkan consumption history
     */
    private function calculateDaysSupply($material)
    {
        // Hitung average daily consumption dalam 30 hari terakhir
        $avgDailyConsumption = StockMovement::where('item_id', $material->id)
            ->where('item_type', get_class($material))
            ->where('movement_type', 'out')
            ->whereDate('transaction_date', '>=', now()->subDays(30))
            ->avg('quantity') ?? 0;
        
        if ($avgDailyConsumption > 0) {
            return round($material->current_stock / $avgDailyConsumption, 1);
        }
        
        return 999; // Tidak ada konsumsi, supply sangat lama
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
            ]
        ]);
    }

    /**
     * Export materials sebagai CSV
     */
    private function exportMaterials(Request $request, $format)
    {
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
            $filename = "raw-materials-" . now()->format('Y-m-d') . ".csv";
            
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
    }

    /**
     * Export movements sebagai CSV
     */
    private function exportMovements(Request $request, $format)
    {
        // Get filtered movements berdasarkan request parameters
        $query = StockMovement::with(['item', 'user']);
        
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
                    
                    fputcsv($file, [
                        $movement->transaction_date->format('Y-m-d H:i:s'),
                        $movement->transaction_number,
                        ucfirst(str_replace('_', ' ', $movement->stock_type)),
                        $movement->item->name ?? 'Unknown Item',
                        $movement->item->code ?? 'N/A',
                        strtoupper($movement->movement_type),
                        $movement->quantity,
                        $movement->item->unit ?? 'unit',
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
    }

    /**
     * Export alerts sebagai CSV
     */
    private function exportAlerts(Request $request, $format)
    {
        $outOfStockItems = RawMaterial::where('current_stock', '<=', 0)
            ->where('is_active', true)
            ->orderBy('updated_at', 'desc')
            ->get();
        
        $lowStockItems = RawMaterial::whereRaw('current_stock <= minimum_stock')
            ->where('current_stock', '>', 0)
            ->where('is_active', true)
            ->orderBy(DB::raw('(current_stock / minimum_stock)'), 'asc')
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
            // Fallback ke CSV jika PDF gagal
            return $this->exportFinishedGoodsAsCSV($finishedGoods);
        }
    }
}