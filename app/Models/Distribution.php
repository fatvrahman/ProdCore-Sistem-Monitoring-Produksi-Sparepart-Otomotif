<?php
// File: app/Models/Distribution.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Distribution extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'delivery_number',
        'distribution_date',
        'customer_name',
        'delivery_address',
        'vehicle_number',
        'driver_name',
        'items',
        'total_quantity',
        'total_weight',
        'status',
        'shipped_at',
        'delivered_at',
        'prepared_by',
        'notes'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'distribution_date' => 'date',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'items' => 'array',
        'total_quantity' => 'integer',
        'total_weight' => 'decimal:2'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * Boot method untuk set default values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($distribution) {
            if (empty($distribution->status)) {
                $distribution->status = 'prepared';
            }
        });
    }

    // ===============================
    // RELATIONSHIPS
    // ===============================

    /**
     * Relationship dengan User yang mempersiapkan distribusi
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    /**
     * Relationship dengan Production melalui items JSON
     * Virtual relationship untuk kemudahan akses
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function productions()
    {
        if (empty($this->items)) {
            return collect();
        }

        $productionIds = collect($this->items)->pluck('production_id')->unique();
        
        return Production::with(['productType', 'qualityControl'])
            ->whereIn('id', $productionIds)
            ->get();
    }

    /**
     * Relationship dengan StockMovements untuk tracking
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'reference_id')
            ->where('reference_type', 'distribution');
    }

    // ===============================
    // ACCESSORS & MUTATORS
    // ===============================

    /**
     * Get status badge untuk UI
     * 
     * @return array
     */
    public function getStatusBadgeAttribute()
    {
        $statusConfig = [
            'prepared' => [
                'class' => 'badge-warning',
                'color' => '#ffc107',
                'text' => 'Prepared',
                'icon' => 'fas fa-box'
            ],
            'shipped' => [
                'class' => 'badge-info',
                'color' => '#17a2b8',
                'text' => 'Shipped',
                'icon' => 'fas fa-truck'
            ],
            'delivered' => [
                'class' => 'badge-success',
                'color' => '#28a745',
                'text' => 'Delivered',
                'icon' => 'fas fa-check-circle'
            ],
            'cancelled' => [
                'class' => 'badge-danger',
                'color' => '#dc3545',
                'text' => 'Cancelled',
                'icon' => 'fas fa-times-circle'
            ]
        ];

        return $statusConfig[$this->status] ?? $statusConfig['prepared'];
    }

    /**
     * Get formatted total weight
     * 
     * @return string
     */
    public function getFormattedWeightAttribute()
    {
        return number_format($this->total_weight, 2) . ' kg';
    }

    /**
     * Get formatted total quantity
     * 
     * @return string
     */
    public function getFormattedQuantityAttribute()
    {
        return number_format($this->total_quantity) . ' pcs';
    }

    /**
     * Get delivery duration in days (jika sudah delivered)
     * 
     * @return int|null
     */
    public function getDeliveryDurationAttribute()
    {
        if (!$this->shipped_at || !$this->delivered_at) {
            return null;
        }

        return $this->shipped_at->diffInDays($this->delivered_at);
    }

    /**
     * Get shipping duration in days (dari created sampai shipped)
     * 
     * @return int|null
     */
    public function getShippingDurationAttribute()
    {
        if (!$this->shipped_at) {
            return null;
        }

        return $this->created_at->diffInDays($this->shipped_at);
    }

    /**
     * Check if distribution is late
     * 
     * @return bool
     */
    public function getIsLateAttribute()
    {
        if ($this->status === 'delivered' && $this->delivered_at) {
            return $this->delivered_at->gt($this->distribution_date);
        }

        if ($this->status === 'shipped') {
            return now()->gt($this->distribution_date);
        }

        return false;
    }

    /**
     * Get number of items in distribution
     * 
     * @return int
     */
    public function getItemsCountAttribute()
    {
        return is_array($this->items) ? count($this->items) : 0;
    }

    /**
     * Get unique product types in distribution
     * 
     * @return int
     */
    public function getProductTypesCountAttribute()
    {
        if (!is_array($this->items)) {
            return 0;
        }

        return collect($this->items)->pluck('product_code')->unique()->count();
    }

    // ===============================
    // SCOPES
    // ===============================

    /**
     * Scope untuk filter berdasarkan status
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk distribusi hari ini
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope untuk distribusi dalam periode tertentu
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $from
     * @param string $to
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('distribution_date', [$from, $to]);
    }

    /**
     * Scope untuk distribusi aktif (prepared atau shipped)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['prepared', 'shipped']);
    }

    /**
     * Scope untuk distribusi selesai
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope untuk distribusi terlambat
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLate($query)
    {
        return $query->where(function($q) {
            $q->where('status', 'shipped')
              ->where('distribution_date', '<', now()->toDateString())
              ->orWhere(function($q2) {
                  $q2->where('status', 'delivered')
                     ->whereColumn('delivered_at', '>', 'distribution_date');
              });
        });
    }

    /**
     * Scope untuk search berdasarkan delivery number atau customer
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('delivery_number', 'like', "%{$search}%")
              ->orWhere('customer_name', 'like', "%{$search}%")
              ->orWhere('vehicle_number', 'like', "%{$search}%")
              ->orWhere('driver_name', 'like', "%{$search}%");
        });
    }

    // ===============================
    // STATIC METHODS
    // ===============================

    /**
     * Get distribution statistics
     * 
     * @return array
     */
    public static function getStatistics()
    {
        return [
            'total' => self::count(),
            'today' => self::today()->count(),
            'prepared' => self::status('prepared')->count(),
            'shipped' => self::status('shipped')->count(),
            'delivered' => self::status('delivered')->count(),
            'cancelled' => self::status('cancelled')->count(),
            'active' => self::active()->count(),
            'late' => self::late()->count(),
            'total_quantity' => self::sum('total_quantity'),
            'total_weight' => self::sum('total_weight')
        ];
    }

    /**
     * Get monthly summary untuk dashboard
     * 
     * @param int $year
     * @param int $month
     * @return array
     */
    public static function getMonthlySummary($year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return [
            'period' => $startDate->format('F Y'),
            'total_distributions' => self::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_quantity' => self::whereBetween('created_at', [$startDate, $endDate])->sum('total_quantity'),
            'total_weight' => self::whereBetween('created_at', [$startDate, $endDate])->sum('total_weight'),
            'delivered_count' => self::whereBetween('created_at', [$startDate, $endDate])->status('delivered')->count(),
            'on_time_count' => self::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'delivered')
                ->whereColumn('delivered_at', '<=', 'distribution_date')
                ->count()
        ];
    }

    /**
     * Get top customers by distribution count
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getTopCustomers($limit = 10)
    {
        return self::selectRaw('customer_name, COUNT(*) as distribution_count, SUM(total_quantity) as total_items')
            ->groupBy('customer_name')
            ->orderByDesc('distribution_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get delivery performance metrics
     * 
     * @return array
     */
    public static function getDeliveryPerformance()
    {
        $delivered = self::status('delivered');
        $total = $delivered->count();

        if ($total === 0) {
            return [
                'total_delivered' => 0,
                'on_time_count' => 0,
                'late_count' => 0,
                'on_time_percentage' => 0,
                'average_delivery_days' => 0
            ];
        }

        $onTime = $delivered->whereColumn('delivered_at', '<=', 'distribution_date')->count();
        $late = $total - $onTime;

        $avgDeliveryTime = $delivered->get()->avg(function($dist) {
            if (!$dist->shipped_at || !$dist->delivered_at) return 0;
            return $dist->shipped_at->diffInHours($dist->delivered_at) / 24; // Convert to days
        });

        return [
            'total_delivered' => $total,
            'on_time_count' => $onTime,
            'late_count' => $late,
            'on_time_percentage' => round(($onTime / $total) * 100, 2),
            'average_delivery_days' => round($avgDeliveryTime, 2)
        ];
    }
}