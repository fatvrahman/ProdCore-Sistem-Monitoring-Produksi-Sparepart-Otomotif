<?php
// File: app/Models/RawMaterial.php

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
        'is_active' => 'boolean'
    ];

    /**
     * Relasi: RawMaterial memiliki banyak StockMovement
     */
    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'item');
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
        return $query->whereRaw('current_stock <= minimum_stock');
    }

    /**
     * Helper method: Cek apakah stok rendah
     */
    public function isLowStock()
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    /**
     * Helper method: Cek apakah stok habis
     */
    public function isOutOfStock()
    {
        return $this->current_stock <= 0;
    }

    /**
     * Helper method: Hitung persentase stok
     */
    public function getStockPercentage()
    {
        if ($this->maximum_stock <= 0) {
            return 0;
        }
        
        return round(($this->current_stock / $this->maximum_stock) * 100, 2);
    }

    /**
     * Helper method: Update stok
     */
    public function updateStock($quantity, $type = 'in')
    {
        if ($type === 'in') {
            $this->current_stock += $quantity;
        } else {
            $this->current_stock -= $quantity;
        }
        
        // Pastikan stok tidak negatif
        if ($this->current_stock < 0) {
            $this->current_stock = 0;
        }
        
        $this->save();
    } 

    /**
     * Helper method: Dapatkan nilai stok saat ini
     */
    public function getStockValue()
    {
        return $this->current_stock * $this->unit_price;
    }
} 