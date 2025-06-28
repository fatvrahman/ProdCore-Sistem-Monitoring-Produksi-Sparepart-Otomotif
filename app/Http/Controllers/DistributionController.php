<?php
// File: app/Http/Controllers/DistributionController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Distribution;
use App\Models\Production;
use App\Models\ProductType;
use App\Models\QualityControl;
use App\Models\User;
use App\Models\StockMovement;
use Carbon\Carbon;

class DistributionController extends Controller
{


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
        $query = Distribution::with([
            'items.production.productType',
            'items.production.qualityControl',
            'preparedBy'
        ]);

        // Apply filters
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['customer']) {
            $query->where('customer_name', 'LIKE', '%' . $filters['customer'] . '%');
        }

        if ($filters['product_type_id']) {
            $query->whereHas('items.production.productType', function($q) use ($filters) {
                $q->where('id', $filters['product_type_id']);
            });
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
     * Form create pengiriman baru (wizard style)
     */
    public function create()
    {
        // Ambil batch production yang ready untuk distribusi
        $availableBatches = $this->getAvailableBatches();
        
        // Ambil data untuk form
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
                $production = Production::with(['productType', 'qualityControl'])
                    ->findOrFail($item['production_id']);
                
                // Cek apakah batch approved QC
                if (!$production->qualityControls || $production->qualityControls->final_status !== 'approved') {
                    throw new \Exception("Batch {$production->batch_number} belum approved QC");
                }

                // Cek stok available
                $usedQuantity = Distribution::whereHas('items', function($q) use ($production) {
                    $q->where('production_id', $production->id);
                })->where('status', '!=', 'cancelled')->sum('total_quantity');

                $availableQuantity = $production->good_quantity - $usedQuantity;
                
                if ($item['quantity'] > $availableQuantity) {
                    throw new \Exception("Stok tidak mencukupi untuk batch {$production->batch_number}");
                }

                $itemData = [
                    'production_id' => $production->id,
                    'batch_number' => $production->batch_number,
                    'product_name' => $production->productType->name,
                    'quantity' => $item['quantity'],
                    'unit_weight' => $production->productType->standard_weight
                ];

                $items[] = $itemData;
                $totalQuantity += $item['quantity'];
                $totalWeight += $item['quantity'] * $production->productType->standard_weight;
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

            // Create stock movements untuk setiap item
            foreach ($items as $item) {
                StockMovement::create([
                    'transaction_number' => 'TXN-' . $distribution->delivery_number,
                    'transaction_date' => now(),
                    'stock_type' => 'finished_goods',
                    'item_id' => $item['production_id'],
                    'item_type' => 'production',
                    'movement_type' => 'out',
                    'quantity' => $item['quantity'],
                    'unit_price' => 0, // Finished goods tidak ada unit price
                    'balance_before' => 0, // Will be calculated
                    'balance_after' => 0,  // Will be calculated
                    'reference_id' => $distribution->id,
                    'reference_type' => 'distribution',
                    'user_id' => Auth::id(),
                    'notes' => "Distribusi ke {$distribution->customer_name}"
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman berhasil dibuat',
                'distribution' => $distribution,
                'redirect' => route('distributions.show', $distribution)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Detail pengiriman dengan timeline
     */
    public function show(Distribution $distribution)
    {
        $distribution->load([
            'items.production.productType',
            'items.production.qualityControl',
            'preparedBy'
        ]);

        // Timeline pengiriman
        $timeline = $this->getDeliveryTimeline($distribution);

        // Related stock movements
        $stockMovements = StockMovement::where('reference_id', $distribution->id)
            ->where('reference_type', 'distribution')
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

        $distribution->load([
            'items.production.productType',
            'preparedBy'
        ]);

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
            // Hapus stock movements lama
            StockMovement::where('reference_id', $distribution->id)
                ->where('reference_type', 'distribution')
                ->delete();

            // Validasi dan persiapkan items baru
            $items = [];
            $totalQuantity = 0;
            $totalWeight = 0;

            foreach ($request->items as $item) {
                $production = Production::with(['productType', 'qualityControl'])
                    ->findOrFail($item['production_id']);
                
                // Cek apakah batch approved QC
                if (!$production->qualityControls || $production->qualityControls->final_status !== 'approved') {
                    throw new \Exception("Batch {$production->batch_number} belum approved QC");
                }

                // Cek stok available (exclude current distribution)
                $usedQuantity = Distribution::whereHas('items', function($q) use ($production) {
                    $q->where('production_id', $production->id);
                })->where('status', '!=', 'cancelled')
                  ->where('id', '!=', $distribution->id)
                  ->sum('total_quantity');

                $availableQuantity = $production->good_quantity - $usedQuantity;
                
                if ($item['quantity'] > $availableQuantity) {
                    throw new \Exception("Stok tidak mencukupi untuk batch {$production->batch_number}");
                }

                $itemData = [
                    'production_id' => $production->id,
                    'batch_number' => $production->batch_number,
                    'product_name' => $production->productType->name,
                    'quantity' => $item['quantity'],
                    'unit_weight' => $production->productType->standard_weight
                ];

                $items[] = $itemData;
                $totalQuantity += $item['quantity'];
                $totalWeight += $item['quantity'] * $production->productType->standard_weight;
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
                    'transaction_number' => 'TXN-' . $distribution->delivery_number,
                    'transaction_date' => now(),
                    'stock_type' => 'finished_goods',
                    'item_id' => $item['production_id'],
                    'item_type' => 'production',
                    'movement_type' => 'out',
                    'quantity' => $item['quantity'],
                    'unit_price' => 0,
                    'balance_before' => 0,
                    'balance_after' => 0,
                    'reference_id' => $distribution->id,
                    'reference_type' => 'distribution',
                    'user_id' => Auth::id(),
                    'notes' => "Distribusi ke {$distribution->customer_name} (Updated)"
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman berhasil diupdate',
                'distribution' => $distribution->fresh(),
                'redirect' => route('distributions.show', $distribution)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
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
                ->where('reference_type', 'distribution')
                ->delete();

            // Hapus distribution
            $distribution->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pengiriman: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update status pengiriman
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
            'delivered' => [], // Final status
            'cancelled' => []  // Final status
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

            // Create stock movement log untuk status change
            StockMovement::create([
                'transaction_number' => 'STS-' . $distribution->delivery_number,
                'transaction_date' => now(),
                'stock_type' => 'finished_goods',
                'item_id' => $distribution->id,
                'item_type' => 'distribution_status',
                'movement_type' => 'status_change',
                'quantity' => 0,
                'unit_price' => 0,
                'balance_before' => 0,
                'balance_after' => 0,
                'reference_id' => $distribution->id,
                'reference_type' => 'distribution',
                'user_id' => Auth::id(),
                'notes' => "Status changed from {$oldStatus} to {$newStatus}" . ($request->notes ? ": " . $request->notes : '')
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Status pengiriman berhasil diubah ke {$newStatus}",
                'distribution' => $distribution->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage()
            ], 422);
        }
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

        $distribution->update([
            'status' => 'delivered',
            'delivered_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengiriman berhasil dikonfirmasi sebagai delivered',
            'distribution' => $distribution->fresh()
        ]);
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

            // Update stock movements
            StockMovement::where('reference_id', $distribution->id)
                ->where('reference_type', 'distribution')
            ->update(['notes' => DB::raw("CONCAT(notes, ' - CANCELLED')")]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman berhasil dibatalkan',
                'distribution' => $distribution->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan pengiriman: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Print surat jalan
     */
    public function printDeliveryNote(Distribution $distribution)
    {
        $distribution->load([
            'items.production.productType',
            'preparedBy'
        ]);

        return view('distributions.print.delivery-note', compact('distribution'));
    }

    /**
     * Print invoice
     */
    public function printInvoice(Distribution $distribution)
    {
        $distribution->load([
            'items.production.productType',
            'preparedBy'
        ]);

        return view('distributions.print.invoice', compact('distribution'));
    }

    /**
     * Export data distribusi
     */
    public function exportData(Request $request)
    {
        // Implementation akan dibuat terpisah
        // Return temporary response
        return response()->json([
            'success' => true,
            'message' => 'Export feature coming soon'
        ]);
    }

    /**
     * API: Data untuk charts dashboard
     */
    public function getChartData()
    {
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
            'monthly' => $monthlyData,
            'status' => $statusData
        ]);
    }

    /**
     * API: Batch production yang ready untuk distribusi
     */
    public function getAvailableBatches()
    {
        return Production::with(['productType', 'qualityControl'])
            ->whereHas('qualityControls', function($q) {
                $q->where('final_status', 'approved');
            })
            ->where('status', 'completed')
            ->where('good_quantity', '>', 0)
            ->orderBy('production_date', 'desc')
            ->get()
            ->map(function($production) {
                // Hitung sisa stok yang bisa didistribusi
                $usedQuantity = Distribution::whereJsonContains('items', [['production_id' => $production->id]])
                    ->where('status', '!=', 'cancelled')
                    ->get()
                    ->sum(function($dist) use ($production) {
                        foreach($dist->items as $item) {
                            if ($item['production_id'] == $production->id) {
                                return $item['quantity'];
                            }
                        }
                        return 0;
                    });
                
                $availableQuantity = $production->good_quantity - $usedQuantity;
                
                return [
                    'id' => $production->id,
                    'batch_number' => $production->batch_number,
                    'product_name' => $production->productType->name,
                    'product_brand' => $production->productType->brand,
                    'production_date' => $production->production_date->format('d/m/Y'),
                    'good_quantity' => $production->good_quantity,
                    'used_quantity' => $usedQuantity,
                    'available_quantity' => $availableQuantity,
                    'unit_weight' => $production->productType->standard_weight,
                    'qc_status' => $production->qualityControls->final_status ?? 'pending'
                ];
            })
            ->filter(function($batch) {
                return $batch['available_quantity'] > 0;
            })
            ->values();
    }

    /**
     * API: Real-time tracking info
     */
    public function getDeliveryTracking(Distribution $distribution)
    {
        $timeline = $this->getDeliveryTimeline($distribution);
        
        return response()->json([
            'distribution' => $distribution,
            'timeline' => $timeline,
            'current_status' => $distribution->status
        ]);
    }

    /**
     * Helper: Generate delivery number
     */
    private function generateDeliveryNumber()
    {
        $date = now()->format('Ymd');
        $lastNumber = Distribution::whereDate('created_at', now()->toDateString())
            ->count() + 1;
        
        return 'DEL-' . $date . '-' . str_pad($lastNumber, 4, '0', STR_PAD_LEFT);
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
     * Helper: Get delivery timeline
     */
    private function getDeliveryTimeline(Distribution $distribution)
    {
        $timeline = [];

        // Prepared
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
            'user' => $distribution->shipped_at ? 'Driver' : null,
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
}