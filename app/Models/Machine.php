<?php
// File: app/Models/Machine.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory;

    /**
     * Kolom yang bisa diisi secara mass assignment
     */
    protected $fillable = [
        'code',
        'name',
        'production_line_id',
        'brand',
        'model',
        'manufacture_year',
        'capacity_per_hour',
        'status',
        'last_maintenance_date',
        'next_maintenance_date',
        'notes'
    ];

    /**
     * Kolom yang harus di-cast ke tipe data tertentu
     */
    protected $casts = [
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'capacity_per_hour' => 'integer'
    ];

    /**
     * Relasi: Machine belongsTo ProductionLine
     */
    public function productionLine()
    {
        return $this->belongsTo(ProductionLine::class);
    }

    /**
     * Relasi: Machine memiliki banyak Production
     */
    public function productions()
    {
        return $this->hasMany(Production::class);
    }

    /**
     * Scope: Hanya mesin yang running
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Scope: Mesin yang perlu maintenance
     */
    public function scopeNeedMaintenance($query)
    {
        return $query->where('next_maintenance_date', '<=', now()->addDays(7));
    }

    /**
     * Helper method: Cek apakah mesin sedang running
     */
    public function isRunning()
    {
        return $this->status === 'running';
    }

    /**
     * Helper method: Cek apakah mesin perlu maintenance
     */
    public function needMaintenance()
    {
        return $this->next_maintenance_date && $this->next_maintenance_date <= now()->addDays(7);
    }

    /**
     * Helper method: Dapatkan hari sejak maintenance terakhir
     */
    public function getDaysSinceLastMaintenance()
    {
        if (!$this->last_maintenance_date) {
            return null;
        }
        
        return now()->diffInDays($this->last_maintenance_date);
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
     * Helper method: Update status mesin
     */
    public function updateStatus($status)
    {
        $this->update(['status' => $status]);
    }
}