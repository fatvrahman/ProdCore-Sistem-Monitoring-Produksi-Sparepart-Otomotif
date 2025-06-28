<?php
// File: app/Models/ProductionLine.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionLine extends Model
{
    use HasFactory;

    /**
     * Kolom yang bisa diisi secara mass assignment
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'capacity_per_hour',
        'status',
        'shift_schedule'
    ];

    /**
     * Kolom yang harus di-cast ke tipe data tertentu
     */
    protected $casts = [
        'shift_schedule' => 'array', // JSON akan di-cast ke array
        'capacity_per_hour' => 'integer'
    ];

    /**
     * Relasi: ProductionLine memiliki banyak Machine
     */
    public function machines()
    {
        return $this->hasMany(Machine::class);
    }

    /**
     * Relasi: ProductionLine memiliki banyak Production
     */
    public function productions()
    {
        return $this->hasMany(Production::class);
    }

    /**
     * Scope: Hanya line yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Helper method: Cek apakah line sedang maintenance
     */
    public function isMaintenance()
    {
        return $this->status === 'maintenance';
    }

    /**
     * Helper method: Dapatkan efficiency hari ini
     */
    public function getTodayEfficiency()
    {
        $todayProductions = $this->productions()
            ->whereDate('production_date', now()->toDateString())
            ->where('status', 'completed')
            ->get();

        if ($todayProductions->isEmpty()) {
            return 0;
        }

        $totalTarget = $todayProductions->sum('target_quantity');
        $totalActual = $todayProductions->sum('actual_quantity');

        return $totalTarget > 0 ? round(($totalActual / $totalTarget) * 100, 2) : 0;
    }

    /**
     * Helper method: Dapatkan produksi hari ini
     */
    public function getTodayProduction()
    {
        return $this->productions()
            ->whereDate('production_date', now()->toDateString())
            ->where('status', 'completed')
            ->sum('actual_quantity');
    }
}