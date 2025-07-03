<?php
// File: app/Models/StockMovement.php - COMPLETE VERSION

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    /**
     * Kolom yang bisa diisi secara mass assignment
     */
    protected $fillable = [
        'transaction_number',
        'transaction_date',
        'stock_type',
        'item_id',
        'item_type',
        'movement_type',
        'quantity',
        'unit_price',
        'balance_before',
        'balance_after',
        'reference_id',
        'reference_type',
        'user_id',
        'notes'
    ];

    /**
     * Kolom yang harus di-cast ke tipe data tertentu
     */
    protected $casts = [
        'transaction_date' => 'datetime',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2'
    ];

    /**
     * Relasi: StockMovement belongsTo User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi Polymorphic: item bisa RawMaterial atau ProductType
     */
    public function item()
    {
        return $this->morphTo();
    }

    /**
     * Relasi Polymorphic: reference bisa Production, Distribution, dll
     */
    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Scope: Filter berdasarkan tipe stok
     */
    public function scopeByStockType($query, $type)
    {
        return $query->where('stock_type', $type);
    }

    /**
     * Scope: Filter berdasarkan tipe movement
     */
    public function scopeByMovementType($query, $type)
    {
        return $query->where('movement_type', $type);
    }

    /**
     * Scope: Filter berdasarkan tanggal
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('transaction_date', $date);
    }

    /**
     * Helper method: Dapatkan nama tipe movement (Indonesian)
     */
    public function getMovementTypeName()
    {
        $types = [
            'in' => 'Masuk',
            'out' => 'Keluar',
            'adjustment' => 'Penyesuaian'
        ];
        
        return $types[$this->movement_type] ?? 'Unknown';
    }

    /**
     * Helper method: Dapatkan nama tipe stok (Indonesian)
     */
    public function getStockTypeName()
    {
        $types = [
            'raw_material' => 'Bahan Baku',
            'finished_product' => 'Barang Jadi',
            'finished_goods' => 'Barang Jadi'
        ];
        
        return $types[$this->stock_type] ?? 'Unknown';
    }

    /**
     * Helper method: Hitung total nilai transaksi
     */
    public function getTotalValue()
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Helper method: Generate transaction number otomatis
     */
    public static function generateTransactionNumber()
    {
        $date = now()->format('Ymd');
        $lastTransaction = self::where('transaction_number', 'like', "TRX-{$date}-%")
            ->orderBy('transaction_number', 'desc')
            ->first();
        
        if ($lastTransaction) {
            $lastNumber = intval(substr($lastTransaction->transaction_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return "TRX-{$date}-{$newNumber}";
    }

    /**
     * Helper method: Get item name safely (untuk kompatibilitas dengan StockController)
     */
    public function getItemNameAttribute()
    {
        try {
            $item = $this->item;
            return $item ? $item->name : 'Unknown Item';
        } catch (\Exception $e) {
            // Fallback: try to get item manually
            if ($this->item_type === 'App\\Models\\RawMaterial') {
                $item = \App\Models\RawMaterial::find($this->item_id);
                return $item ? $item->name : 'Raw Material Not Found';
            } elseif ($this->item_type === 'App\\Models\\ProductType') {
                $item = \App\Models\ProductType::find($this->item_id);
                return $item ? $item->name : 'Product Not Found';
            }
            return 'Unknown Item';
        }
    }

    /**
     * Helper method: Get item code safely
     */
    public function getItemCodeAttribute()
    {
        try {
            $item = $this->item;
            return $item ? $item->code : 'N/A';
        } catch (\Exception $e) {
            // Fallback: try to get item manually
            if ($this->item_type === 'App\\Models\\RawMaterial') {
                $item = \App\Models\RawMaterial::find($this->item_id);
                return $item ? $item->code : 'N/A';
            } elseif ($this->item_type === 'App\\Models\\ProductType') {
                $item = \App\Models\ProductType::find($this->item_id);
                return $item ? $item->code : 'N/A';
            }
            return 'N/A';
        }
    }

    /**
     * Helper method: Get item unit safely
     */
    public function getItemUnitAttribute()
    {
        try {
            $item = $this->item;
            return $item ? $item->unit : 'unit';
        } catch (\Exception $e) {
            // Fallback: try to get item manually
            if ($this->item_type === 'App\\Models\\RawMaterial') {
                $item = \App\Models\RawMaterial::find($this->item_id);
                return $item ? $item->unit : 'unit';
            }
            return 'unit';
        }
    }

    /**
     * Helper method: Get movement type label (English)
     */
    public function getMovementTypeLabel()
    {
        return match($this->movement_type) {
            'in' => 'Stock In',
            'out' => 'Stock Out',
            'adjustment' => 'Stock Adjustment',
            default => 'Unknown'
        };
    }

    /**
     * Helper method: Get stock type label (English)
     */
    public function getStockTypeLabel()
    {
        return match($this->stock_type) {
            'raw_material' => 'Raw Material',
            'finished_goods' => 'Finished Goods',
            'finished_product' => 'Finished Product',
            default => 'Unknown'
        };
    }

    /**
     * Helper method: Get formatted total value
     */
    public function getFormattedTotalValue()
    {
        return 'Rp ' . number_format($this->getTotalValue(), 0, ',', '.');
    }

    /**
     * Helper method: Get formatted quantity
     */
    public function getFormattedQuantity()
    {
        return number_format($this->quantity, 2) . ' ' . $this->item_unit;
    }

    /**
     * Helper method: Get formatted unit price
     */
    public function getFormattedUnitPrice()
    {
        return 'Rp ' . number_format($this->unit_price, 0, ',', '.');
    }

    /**
     * Helper method: Check if this is a stock increase
     */
    public function isStockIncrease()
    {
        return $this->movement_type === 'in' || 
               ($this->movement_type === 'adjustment' && $this->balance_after > $this->balance_before);
    }

    /**
     * Helper method: Check if this is a stock decrease
     */
    public function isStockDecrease()
    {
        return $this->movement_type === 'out' || 
               ($this->movement_type === 'adjustment' && $this->balance_after < $this->balance_before);
    }

    /**
     * Helper method: Get balance change
     */
    public function getBalanceChange()
    {
        return $this->balance_after - $this->balance_before;
    }

    /**
     * Helper method: Get formatted balance change
     */
    public function getFormattedBalanceChange()
    {
        $change = $this->getBalanceChange();
        $prefix = $change > 0 ? '+' : '';
        return $prefix . number_format($change, 2);
    }

    /**
     * Helper method: Get CSS class untuk movement type
     */
    public function getMovementTypeClass()
    {
        if ($this->isStockIncrease()) {
            return 'success';
        } elseif ($this->isStockDecrease()) {
            return 'danger';
        } else {
            return 'warning';
        }
    }

    /**
     * Helper method: Get icon untuk movement type
     */
    public function getMovementTypeIcon()
    {
        return match($this->movement_type) {
            'in' => 'fas fa-arrow-up',
            'out' => 'fas fa-arrow-down',
            'adjustment' => 'fas fa-edit',
            default => 'fas fa-question'
        };
    }

    /**
     * Scope: Movement berdasarkan tipe
     */
    public function scopeByType($query, $type)
    {
        return $query->where('movement_type', $type);
    }

    /**
     * Scope: Movement untuk raw materials saja
     */
    public function scopeRawMaterials($query)
    {
        return $query->where('item_type', 'App\\Models\\RawMaterial');
    }

    /**
     * Scope: Movement untuk finished goods saja
     */
    public function scopeFinishedGoods($query)
    {
        return $query->whereIn('item_type', ['App\\Models\\ProductType', 'finished_goods']);
    }

    /**
     * Scope: Movement hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', today());
    }

    /**
     * Scope: Movement minggu ini
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('transaction_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope: Movement bulan ini
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year);
    }

    /**
     * Scope: Movement dalam periode tertentu
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Movement dengan nilai transaksi diatas threshold
     */
    public function scopeHighValue($query, $threshold = 100000)
    {
        return $query->whereRaw('quantity * unit_price >= ?', [$threshold]);
    }

    /**
     * Scope: Movement dengan reference tertentu
     */
    public function scopeByReference($query, $referenceType, $referenceId = null)
    {
        $q = $query->where('reference_type', $referenceType);
        
        if ($referenceId) {
            $q->where('reference_id', $referenceId);
        }
        
        return $q;
    }

    /**
     * Helper method: Get reference display name
     */
    public function getReferenceDisplayName()
    {
        if (!$this->reference_type || !$this->reference_id) {
            return 'Manual Entry';
        }
        
        try {
            $reference = $this->reference;
            if ($reference) {
                return match($this->reference_type) {
                    'App\\Models\\Production' => "Production: {$reference->batch_number}",
                    'App\\Models\\Distribution' => "Distribution: {$reference->delivery_number}",
                    'App\\Models\\QualityControl' => "QC: {$reference->inspection_number}",
                    default => "Ref: {$this->reference_id}"
                };
            }
        } catch (\Exception $e) {
            // Ignore and return fallback
        }
        
        return "Reference: {$this->reference_id}";
    }

    /**
     * Helper method: Check if movement has reference
     */
    public function hasReference()
    {
        return !empty($this->reference_type) && !empty($this->reference_id);
    }

    /**
     * Helper method: Get transaction age in days
     */
    public function getTransactionAge()
    {
        return $this->transaction_date->diffInDays(now());
    }

    /**
     * Helper method: Check if transaction is recent (within 24 hours)
     */
    public function isRecent()
    {
        return $this->getTransactionAge() <= 1;
    }

    /**
     * Static method: Get movement summary for period
     */
    public static function getSummaryForPeriod($startDate, $endDate, $itemType = null)
    {
        $query = self::betweenDates($startDate, $endDate);
        
        if ($itemType) {
            $query->where('item_type', $itemType);
        }
        
        return [
            'total_movements' => $query->count(),
            'total_in' => (clone $query)->where('movement_type', 'in')->sum('quantity'),
            'total_out' => (clone $query)->where('movement_type', 'out')->sum('quantity'),
            'total_adjustments' => (clone $query)->where('movement_type', 'adjustment')->count(),
            'total_value' => $query->sum(\DB::raw('quantity * unit_price')),
            'avg_value_per_transaction' => $query->avg(\DB::raw('quantity * unit_price')) ?? 0
        ];
    }

    /**
     * Static method: Get top materials by movement volume
     */
    public static function getTopMaterialsByVolume($period = 30, $limit = 10)
    {
        return self::where('item_type', 'App\\Models\\RawMaterial')
            ->where('transaction_date', '>=', now()->subDays($period))
            ->selectRaw('item_id, SUM(quantity) as total_quantity, COUNT(*) as movement_count')
            ->groupBy('item_id')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->with('item')
            ->get();
    }

    /**
     * Mutator: Ensure transaction_number is uppercase
     */
    public function setTransactionNumberAttribute($value)
    {
        $this->attributes['transaction_number'] = strtoupper($value);
    }

    /**
     * Mutator: Ensure quantity is positive
     */
    public function setQuantityAttribute($value)
    {
        $this->attributes['quantity'] = abs($value);
    }

    /**
     * Mutator: Ensure unit_price is positive
     */
    public function setUnitPriceAttribute($value)
    {
        $this->attributes['unit_price'] = abs($value);
    }

    /**
     * Accessor: Get transaction date formatted
     */
    public function getFormattedTransactionDateAttribute()
    {
        return $this->transaction_date->format('d M Y H:i');
    }

    /**
     * Accessor: Get short transaction date
     */
    public function getShortTransactionDateAttribute()
    {
        return $this->transaction_date->format('d/m/Y');
    }
}