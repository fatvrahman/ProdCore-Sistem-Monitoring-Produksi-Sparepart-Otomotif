<?php
// File: app/Models/ProductType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    use HasFactory;

    /**
     * Kolom yang bisa diisi secara mass assignment
     */
    protected $fillable = [
        'code',
        'name',
        'brand',
        'model',
        'description',
        'standard_weight',
        'standard_thickness',
        'specifications',
        'is_active'
    ];

    /**
     * Kolom yang harus di-cast ke tipe data tertentu
     */
    protected $casts = [
        'specifications' => 'array', // JSON akan di-cast ke array
        'is_active' => 'boolean',
        'standard_weight' => 'decimal:2',
        'standard_thickness' => 'decimal:2'
    ];

    /**
     * Relasi: ProductType memiliki banyak Production
     */
    public function productions()
    {
        return $this->hasMany(Production::class);
    }

    /**
     * Scope: Hanya produk yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter berdasarkan brand
     */
    public function scopeByBrand($query, $brand)
    {
        return $query->where('brand', $brand);
    }

    /**
     * Helper method: Dapatkan nama lengkap produk
     */
    public function getFullName()
    {
        return "{$this->name} - {$this->brand} {$this->model}";
    }

    /**
     * Helper method: Dapatkan total produksi bulan ini
     */
    public function getMonthlyProduction()
    {
        return $this->productions()
            ->whereMonth('production_date', now()->month)
            ->whereYear('production_date', now()->year)
            ->where('status', 'completed')
            ->sum('actual_quantity');
    }

    /**
     * Helper method: Dapatkan total produksi hari ini
     */
    public function getTodayProduction()
    {
        return $this->productions()
            ->whereDate('production_date', now()->toDateString())
            ->where('status', 'completed')
            ->sum('actual_quantity');
    }
}