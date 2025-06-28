<?php
// File: app/Models/QualityControl.php - FIXED VERSION

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class QualityControl extends Model
{
    use HasFactory;

    /**
     * Kolom yang bisa diisi secara mass assignment
     */
    protected $fillable = [
        'inspection_number',
        'production_id',
        'qc_inspector_id',
        'inspection_date',
        'sample_size',
        'passed_quantity',
        'failed_quantity',
        'inspection_criteria',
        'test_results',
        'defect_category',
        'defect_description',
        'corrective_action',
        'final_status',
        'notes'
    ];

    /**
     * Kolom yang harus di-cast ke tipe data tertentu
     */
    protected $casts = [
        'inspection_date' => 'datetime',
        'inspection_criteria' => 'array', // JSON akan di-cast ke array
        'test_results' => 'array', // JSON akan di-cast ke array
        'sample_size' => 'integer',
        'passed_quantity' => 'integer',
        'failed_quantity' => 'integer'
    ];

    /**
     * Relasi: QualityControl belongsTo Production
     */
    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    /**
     * Relasi: QualityControl belongsTo User (QC Inspector)
     * FIXED: Method name sesuai dengan usage di controller
     */
    public function qcInspector()
    {
        return $this->belongsTo(User::class, 'qc_inspector_id');
    }

    /**
     * Alias untuk backward compatibility
     */
    public function inspector()
    {
        return $this->qcInspector();
    }

    /**
     * Scope: Filter berdasarkan tanggal
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('inspection_date', $date);
    }

    /**
     * Scope: Filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('final_status', $status);
    }

    /**
     * Scope: Filter berdasarkan periode (days)
     */
    public function scopePeriod($query, $days)
    {
        return $query->where('inspection_date', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Scope: Filter berdasarkan inspector
     */
    public function scopeByInspector($query, $inspectorId)
    {
        return $query->where('qc_inspector_id', $inspectorId);
    }

    /**
     * Scope: Filter berdasarkan product type melalui production
     */
    public function scopeByProductType($query, $productTypeId)
    {
        return $query->whereHas('production', function ($q) use ($productTypeId) {
            $q->where('product_type_id', $productTypeId);
        });
    }

    /**
     * Scope: Filter passed inspections
     */
    public function scopePassed($query)
    {
        return $query->where('final_status', 'approved');
    }

    /**
     * Scope: Filter failed inspections
     */
    public function scopeFailed($query)
    {
        return $query->where('final_status', 'rejected');
    }

    /**
     * Helper method: Hitung pass rate
     */
    public function getPassRate()
    {
        $total = $this->passed_quantity + $this->failed_quantity;
        if ($total <= 0) {
            return 0;
        }
        
        return round(($this->passed_quantity / $total) * 100, 2);
    }

    /**
     * Helper method: Hitung fail rate
     */
    public function getFailRate()
    {
        $total = $this->passed_quantity + $this->failed_quantity;
        if ($total <= 0) {
            return 0;
        }
        
        return round(($this->failed_quantity / $total) * 100, 2);
    }

    /**
     * Helper method: Get total inspected quantity
     */
    public function getTotalQuantity()
    {
        return $this->passed_quantity + $this->failed_quantity;
    }

    /**
     * Helper method: Get sample utilization percentage
     */
    public function getSampleUtilization()
    {
        if ($this->sample_size <= 0) {
            return 0;
        }
        
        $total = $this->getTotalQuantity();
        return round(($total / $this->sample_size) * 100, 1);
    }

    /**
     * Helper method: Cek apakah inspeksi lolos
     */
    public function isPassed()
    {
        return $this->final_status === 'approved';
    }

    /**
     * Helper method: Cek apakah inspeksi gagal
     */
    public function isFailed()
    {
        return $this->final_status === 'rejected';
    }

    /**
     * Helper method: Cek apakah perlu rework
     */
    public function needsRework()
    {
        return $this->final_status === 'rework';
    }

    /**
     * Helper method: Dapatkan nama kategori defect
     */
    public function getDefectCategoryName()
    {
        $categories = [
            'dimensional' => 'Dimensional Defects',
            'surface' => 'Surface Defects',
            'material' => 'Material Defects',
            'assembly' => 'Assembly Defects',
            'packaging' => 'Packaging Defects',
            'contamination' => 'Contamination',
            'other' => 'Other Defects'
        ];
        
        return $categories[$this->defect_category] ?? 'No Defects';
    }

    /**
     * Helper method: Dapatkan nama status
     */
    public function getStatusName()
    {
        $statuses = [
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'rework' => 'Needs Rework',
            'pending' => 'Pending'
        ];
        
        return $statuses[$this->final_status] ?? 'Unknown';
    }

    /**
     * Helper method: Get status badge class
     */
    public function getStatusBadgeClass()
    {
        $classes = [
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'rework' => 'bg-warning',
            'pending' => 'bg-secondary'
        ];
        
        return $classes[$this->final_status] ?? 'bg-secondary';
    }

    /**
     * Helper method: Check if inspection has defects
     */
    public function hasDefects()
    {
        return !empty($this->defect_category) || $this->failed_quantity > 0;
    }

    /**
     * Helper method: Get inspection criteria as array
     */
    public function getInspectionCriteriaArray()
    {
        if (is_string($this->inspection_criteria)) {
            return json_decode($this->inspection_criteria, true) ?: [];
        }
        
        return $this->inspection_criteria ?: [];
    }

    /**
     * Helper method: Get test results as array
     */
    public function getTestResultsArray()
    {
        if (is_string($this->test_results)) {
            return json_decode($this->test_results, true) ?: [];
        }
        
        return $this->test_results ?: [];
    }

    /**
     * Helper method: Check if has critical test failures
     */
    public function hasCriticalFailures()
    {
        $testResults = $this->getTestResultsArray();
        
        foreach ($testResults as $result) {
            if (isset($result['is_critical']) && $result['is_critical'] === 'true') {
                if (isset($result['result']) && $result['result'] === 'fail') {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Helper method: Generate inspection number otomatis
     */
    public static function generateInspectionNumber()
    {
        $today = Carbon::now();
        $prefix = 'QC' . $today->format('Ymd');
        
        $lastInspection = self::where('inspection_number', 'like', $prefix . '%')
            ->orderBy('inspection_number', 'desc')
            ->first();
        
        if ($lastInspection) {
            $lastNumber = intval(substr($lastInspection->inspection_number, -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        return $prefix . $newNumber;
    }

    /**
     * Static method: Get quality statistics for period
     */
    public static function getQualityStats($days = 30, $inspectorId = null, $productTypeId = null)
    {
        $query = self::period($days);
        
        if ($inspectorId) {
            $query->byInspector($inspectorId);
        }
        
        if ($productTypeId) {
            $query->byProductType($productTypeId);
        }
        
        $totalInspections = $query->count();
        $passedInspections = $query->clone()->passed()->count();
        $failedInspections = $query->clone()->failed()->count();
        
        $totalQuantity = $query->sum(\DB::raw('passed_quantity + failed_quantity'));
        $passedQuantity = $query->sum('passed_quantity');
        $failedQuantity = $query->sum('failed_quantity');
        
        $passRate = $totalInspections > 0 ? round(($passedInspections / $totalInspections) * 100, 1) : 0;
        $quantityPassRate = $totalQuantity > 0 ? round(($passedQuantity / $totalQuantity) * 100, 1) : 0;
        
        return [
            'total_inspections' => $totalInspections,
            'passed_inspections' => $passedInspections,
            'failed_inspections' => $failedInspections,
            'pass_rate' => $passRate,
            'total_quantity' => $totalQuantity,
            'passed_quantity' => $passedQuantity,
            'failed_quantity' => $failedQuantity,
            'quantity_pass_rate' => $quantityPassRate
        ];
    }

    /**
     * Accessor untuk formatted inspection date
     */
    public function getFormattedInspectionDateAttribute()
    {
        return $this->inspection_date ? $this->inspection_date->format('d/m/Y') : '-';
    }

    /**
     * Accessor untuk formatted inspection time
     */
    public function getFormattedInspectionTimeAttribute()
    {
        return $this->inspection_date ? $this->inspection_date->format('H:i') : '-';
    } 
}