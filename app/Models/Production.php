<?php
// File: app/Models/Production.php - COMPLETE VERSION

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Production extends Model
{
    use HasFactory;

    /**
     * Kolom yang bisa diisi secara mass assignment
     */
    protected $fillable = [
        'batch_number',
        'production_date',
        'shift',
        'product_type_id',
        'production_line_id',
        'machine_id',
        'operator_id',
        'target_quantity',
        'actual_quantity',
        'good_quantity',
        'defect_quantity',
        'start_time',
        'end_time',
        'downtime_minutes',
        'raw_materials_used',
        'notes',
        'status'
    ];

    /**
     * Kolom yang harus di-cast ke tipe data tertentu
     */
    protected $casts = [
        'production_date' => 'date',
        'raw_materials_used' => 'array', // JSON akan di-cast ke array
        'target_quantity' => 'integer',
        'actual_quantity' => 'integer',
        'good_quantity' => 'integer',
        'defect_quantity' => 'integer',
        'downtime_minutes' => 'integer'
    ];

    /**
     * Relasi: Production belongsTo ProductType
     */
    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    /**
     * Relasi: Production belongsTo ProductionLine
     */
    public function productionLine()
    {
        return $this->belongsTo(ProductionLine::class);
    }

    /**
     * Relasi: Production belongsTo Machine
     */
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Relasi: Production belongsTo User (operator)
     */
    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * Relasi: Production memiliki banyak QualityControl
     */
    public function qualityControls()
    {
        return $this->hasMany(QualityControl::class);
    }

    /**
     * Scope: Filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter berdasarkan tanggal
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('production_date', $date);
    }

    /**
     * Scope: Filter berdasarkan shift
     */
    public function scopeByShift($query, $shift)
    {
        return $query->where('shift', $shift);
    }

    /**
     * Scope: Production yang completed
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Helper method: Hitung efficiency
     */
    public function getEfficiency()
    {
        if ($this->target_quantity <= 0) {
            return 0;
        }
        
        return round(($this->actual_quantity / $this->target_quantity) * 100, 2);
    }

    /**
     * Helper method: Hitung quality rate
     */
    public function getQualityRate()
    {
        if ($this->actual_quantity <= 0) {
            return 0;
        }
        
        return round(($this->good_quantity / $this->actual_quantity) * 100, 2);
    }

    /**
     * Helper method: Hitung defect rate
     */
    public function getDefectRate()
    {
        if ($this->actual_quantity <= 0) {
            return 0;
        }
        
        return round(($this->defect_quantity / $this->actual_quantity) * 100, 2);
    }

    /**
     * Helper method: Hitung durasi produksi (jam)
     */
    public function getProductionDuration()
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }
        
        $totalMinutes = $this->start_time->diffInMinutes($this->end_time);
        $actualMinutes = $totalMinutes - $this->downtime_minutes;
        
        return round($actualMinutes / 60, 2);
    }

    /**
     * Helper method: Cek apakah sudah ada QC
     */
    public function hasQualityControl()
    {
        return $this->qualityControls()->exists();
    }

    /**
     * Helper method: Dapatkan status QC
     */
    public function getQCStatus()
    {
        $qc = $this->qualityControls()->latest()->first();
        return $qc ? $qc->final_status : null;
    }

    /**
     * Helper method: Generate batch number otomatis
     */
    public static function generateBatchNumber()
    {
        $date = Carbon::now()->format('Ymd');
        $lastProduction = self::where('batch_number', 'like', "BATCH{$date}%")
            ->orderBy('batch_number', 'desc')
            ->first();
        
        if ($lastProduction) {
            $lastNumber = intval(substr($lastProduction->batch_number, -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        return "BATCH{$date}{$newNumber}";
    }

    /**
     * Helper method: Cek apakah produksi bisa diinspeksi QC
     */
    public function canBeInspected()
    {
        return $this->status === 'completed' && !$this->hasQualityControl();
    }

    /**
     * Accessor: Get formatted production date
     */
    public function getFormattedProductionDateAttribute()
    {
        return $this->production_date ? $this->production_date->format('d/m/Y') : '-';
    }

    /**
     * Accessor: Get shift name
     */
    public function getShiftNameAttribute()
    {
        $shifts = [
            'pagi' => 'Pagi (06:00-14:00)',
            'siang' => 'Siang (14:00-22:00)',
            'malam' => 'Malam (22:00-06:00)'
        ];
        
        return $shifts[$this->shift] ?? ucfirst($this->shift);
    }
} 