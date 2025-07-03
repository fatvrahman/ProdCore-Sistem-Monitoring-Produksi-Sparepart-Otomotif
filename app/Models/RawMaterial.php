<?php
// File: app/Models/RawMaterial.php - UPDATED with additional helper methods

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    use HasFactory;

    /**
     * Kolom yang bisa diisi secara mass assignment
     */
    protected $fillable = [
        'code',
        'name',
        'unit',
        'current_stock',
        'minimum_stock',
        'maximum_stock',
        'unit_price',
        'supplier',
        'description',
        'is_active'
    ];

    /**
     * Kolom yang harus di-cast ke tipe data tertentu
     */
    protected $casts = [
        'current_stock' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'maximum_stock' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relasi: RawMaterial memiliki banyak StockMovement
     */
    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'item', 'item_type', 'item_id');
    }

    /**
     * Relasi: StockMovement untuk material ini (alternative approach)
     */
    public function movements()
    {
        return $this->hasMany(StockMovement::class, 'item_id')
                    ->where('item_type', 'App\\Models\\RawMaterial');
    }

    /**
     * Scope: Hanya material yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Material dengan stok rendah
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('current_stock <= minimum_stock')
                     ->where('current_stock', '>', 0);
    }

    /**
     * Scope: Material yang out of stock
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('current_stock', '<=', 0);
    }

    /**
     * Scope: Material yang perlu reorder
     */
    public function scopeNeedsReorder($query)
    {
        return $query->whereRaw('current_stock <= minimum_stock');
    }

    /**
     * Helper method: Cek apakah stok rendah
     */
    public function isLowStock()
    {
        return $this->current_stock <= $this->minimum_stock && $this->current_stock > 0;
    }

    /**
     * Helper method: Cek apakah stok habis
     */
    public function isOutOfStock()
    {
        return $this->current_stock <= 0;
    }

    /**
     * Helper method: Cek apakah perlu reorder
     */
    public function needsReorder()
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    /**
     * Helper method: Hitung persentase stok berdasarkan maximum stock
     */
    public function getStockPercentage()
    {
        if ($this->maximum_stock <= 0) {
            return 0;
        }
        
        return round(($this->current_stock / $this->maximum_stock) * 100, 2);
    }

    /**
     * Helper method: Hitung persentase stok berdasarkan minimum stock
     */
    public function getMinimumStockPercentage()
    {
        if ($this->minimum_stock <= 0) {
            return 0;
        }
        
        return round(($this->current_stock / $this->minimum_stock) * 100, 2);
    }

    /**
     * Helper method: Get status stok (string)
     */
    public function getStockStatus()
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        } elseif ($this->isLowStock()) {
            return 'low_stock';
        } elseif ($this->current_stock >= $this->maximum_stock) {
            return 'overstock';
        } else {
            return 'normal';
        }
    }

    /**
     * Helper method: Get status label untuk display
     */
    public function getStockStatusLabel()
    {
        return match($this->getStockStatus()) {
            'out_of_stock' => 'Out of Stock',
            'low_stock' => 'Low Stock',
            'overstock' => 'Overstock',
            'normal' => 'Normal',
            default => 'Unknown'
        };
    }

    /**
     * Helper method: Get CSS class untuk status
     */
    public function getStockStatusClass()
    {
        return match($this->getStockStatus()) {
            'out_of_stock' => 'danger',
            'low_stock' => 'warning',
            'overstock' => 'info',
            'normal' => 'success',
            default => 'secondary'
        };
    }

    /**
     * Helper method: Update stok (dengan validasi)
     */
    public function updateStock($quantity, $type = 'in')
    {
        $oldStock = $this->current_stock;
        
        if ($type === 'in') {
            $this->current_stock += $quantity;
        } elseif ($type === 'out') {
            $this->current_stock -= $quantity;
        } elseif ($type === 'adjustment') {
            $this->current_stock = $quantity;
        }
        
        // Pastikan stok tidak negatif
        if ($this->current_stock < 0) {
            $this->current_stock = 0;
        }
        
        $this->save();
        
        return [
            'old_stock' => $oldStock,
            'new_stock' => $this->current_stock,
            'difference' => $this->current_stock - $oldStock
        ];
    } 

    /**
     * Helper method: Dapatkan nilai stok saat ini (quantity × unit price)
     */
    public function getStockValue()
    {
        return $this->current_stock * $this->unit_price;
    }

    /**
     * Helper method: Get formatted stock value
     */
    public function getFormattedStockValue()
    {
        return 'Rp ' . number_format($this->getStockValue(), 0, ',', '.');
    }

    /**
     * Helper method: Get days supply berdasarkan usage
     */
    public function getDaysSupply()
    {
        try {
            // Hitung average daily consumption dalam 30 hari terakhir
            $avgDailyConsumption = $this->movements()
                ->where('movement_type', 'out')
                ->where('transaction_date', '>=', now()->subDays(30))
                ->avg('quantity') ?? 0;
            
            if ($avgDailyConsumption > 0) {
                return round($this->current_stock / $avgDailyConsumption, 1);
            }
            
            return 999; // Infinite supply (no consumption)
            
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Helper method: Get last movement date
     */
    public function getLastMovementDate()
    {
        try {
            $lastMovement = $this->movements()
                ->orderBy('transaction_date', 'desc')
                ->first();
            
            return $lastMovement ? $lastMovement->transaction_date : null;
            
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Helper method: Get last stock in date
     */
    public function getLastStockInDate()
    {
        try {
            $lastInMovement = $this->movements()
                ->where('movement_type', 'in')
                ->orderBy('transaction_date', 'desc')
                ->first();
            
            return $lastInMovement ? $lastInMovement->transaction_date : null;
            
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Helper method: Calculate turnover rate
     */
    public function getTurnoverRate($days = 30)
    {
        try {
            $totalOut = $this->movements()
                ->where('movement_type', 'out')
                ->where('transaction_date', '>=', now()->subDays($days))
                ->sum('quantity');
            
            $avgStock = $this->current_stock; // Simplified, bisa diperbaiki dengan historical avg
            
            if ($avgStock > 0) {
                return round($totalOut / $avgStock, 2);
            }
            
            return 0;
            
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Accessor: Get supplier display name
     */
    public function getSupplierDisplayAttribute()
    {
        return $this->supplier ?: 'Not specified';
    }

    /**
     * Accessor: Get full name dengan code
     */
    public function getFullNameAttribute()
    {
        return $this->code . ' - ' . $this->name;
    }

    /**
     * Mutator: Format code to uppercase
     */
    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper($value);
    }

    /**
     * Mutator: Ensure stock values are not negative
     */
    public function setCurrentStockAttribute($value)
    {
        $this->attributes['current_stock'] = max(0, $value);
    }

    public function setMinimumStockAttribute($value)
    {
        $this->attributes['minimum_stock'] = max(0, $value);
    }

    public function setMaximumStockAttribute($value)
    {
        $this->attributes['maximum_stock'] = max(0, $value);
    }

    public function setUnitPriceAttribute($value)
    {
        $this->attributes['unit_price'] = max(0, $value);
    }
}