<?php
// File: app/Models/User.php - COMPLETE VERSION

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Kolom yang bisa diisi secara mass assignment
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id', // WAJIB ADA
        'employee_id',
        'phone',
        'status',
        'last_login_at'
    ];

    /**
     * Kolom yang disembunyikan dalam serialization
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Kolom yang harus di-cast ke tipe data tertentu
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed', // Otomatis hash password
    ];

    /**
     * Relasi: User belongsTo Role
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relasi: User sebagai operator di productions
     */
    public function productions()
    {
        return $this->hasMany(Production::class, 'operator_id');
    }

    /**
     * Relasi: User sebagai QC inspector
     */
    public function qualityControls()
    {
        return $this->hasMany(QualityControl::class, 'qc_inspector_id');
    }

    /**
     * Relasi: User sebagai yang menyiapkan distribusi
     */
    public function distributions()
    {
        return $this->hasMany(Distribution::class, 'prepared_by');
    }

    /**
     * Relasi: User sebagai yang input stock movements
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Helper method: Cek apakah user memiliki role tertentu
     */
    public function hasRole($roleName)
    {
        return $this->role && $this->role->name === $roleName;
    }

    /**
     * Helper method: Cek apakah user memiliki permission
     */
    public function hasPermission($permission)
    {
        if (!$this->role) return false;
        
        $permissions = json_decode($this->role->permissions, true) ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Helper method: Get display name dengan employee ID
     */
    public function getDisplayNameAttribute()
    {
        return $this->employee_id ? "{$this->name} ({$this->employee_id})" : $this->name;
    }

    /**
     * Helper method: Get role display name
     */
    public function getRoleDisplayNameAttribute()
    {
        return $this->role ? $this->role->display_name : 'No Role';
    }

    /**
     * Scope: Users yang pernah melakukan stock movements
     */
    public function scopeHasStockMovements($query)
    {
        return $query->whereHas('stockMovements');
    }

    /**
     * Helper method: Get total stock movements oleh user ini
     */
    public function getTotalStockMovements()
    {
        return $this->stockMovements()->count();
    }

    /**
     * Helper method: Get stock movements hari ini
     */
    public function getTodayStockMovements()
    {
        return $this->stockMovements()->whereDate('transaction_date', today())->count();
    }

    /**
     * Helper method: Get status label
     */
    public function getStatusLabelAttribute()
    {
        return $this->status === 'active' ? 'Aktif' : 'Tidak Aktif';
    }

    /**
     * Helper method: Get status CSS class
     */
    public function getStatusClassAttribute()
    {
        return $this->status === 'active' ? 'success' : 'danger';
    }

    /**
     * Scope: Only active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Users by role
     */
    public function scopeByRole($query, $roleName)
    {
        return $query->whereHas('role', function($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /**
     * Helper method: Get total productions by this user
     */
    public function getTotalProductions()
    {
        return $this->productions()->count();
    }

    /**
     * Helper method: Get productions this month
     */
    public function getThisMonthProductions()
    {
        return $this->productions()
            ->whereMonth('production_date', now()->month)
            ->whereYear('production_date', now()->year)
            ->count();
    }

    /**
     * Helper method: Get total quality controls by this user
     */
    public function getTotalQualityControls()
    {
        return $this->qualityControls()->count();
    }

    /**
     * Helper method: Get QC pass rate by this user
     */
    public function getQcPassRate()
    {
        $totalInspections = $this->qualityControls()->count();
        if ($totalInspections === 0) return 0;
        
        $passedInspections = $this->qualityControls()
            ->where('final_status', 'approved')
            ->count();
        
        return round(($passedInspections / $totalInspections) * 100, 2);
    }

    /**
     * Helper method: Get total distributions by this user
     */
    public function getTotalDistributions()
    {
        return $this->distributions()->count();
    }

    /**
     * Helper method: Get distributions this month
     */
    public function getThisMonthDistributions()
    {
        return $this->distributions()
            ->whereMonth('distribution_date', now()->month)
            ->whereYear('distribution_date', now()->year)
            ->count();
    }

    /**
     * Helper method: Get user productivity score
     */
    public function getProductivityScore()
    {
        $score = 0;
        
        // Stock movements score (max 25 points)
        $stockMovements = $this->getTodayStockMovements();
        $score += min($stockMovements * 5, 25);
        
        // Productions score (max 30 points)
        $thisMonthProductions = $this->getThisMonthProductions();
        $score += min($thisMonthProductions * 2, 30);
        
        // QC score (max 25 points)
        $qcPassRate = $this->getQcPassRate();
        $score += ($qcPassRate / 100) * 25;
        
        // Distributions score (max 20 points)
        $thisMonthDistributions = $this->getThisMonthDistributions();
        $score += min($thisMonthDistributions * 4, 20);
        
        return round($score, 2);
    }

    /**
     * Helper method: Get user activity summary
     */
    public function getActivitySummary()
    {
        return [
            'total_productions' => $this->getTotalProductions(),
            'this_month_productions' => $this->getThisMonthProductions(),
            'total_quality_controls' => $this->getTotalQualityControls(),
            'qc_pass_rate' => $this->getQcPassRate(),
            'total_distributions' => $this->getTotalDistributions(),
            'this_month_distributions' => $this->getThisMonthDistributions(),
            'total_stock_movements' => $this->getTotalStockMovements(),
            'today_stock_movements' => $this->getTodayStockMovements(),
            'productivity_score' => $this->getProductivityScore()
        ];
    }

    /**
     * Helper method: Check if user is online (logged in recently)
     */
    public function isOnline()
    {
        return $this->last_login_at && $this->last_login_at->diffInMinutes(now()) <= 30;
    }

    /**
     * Helper method: Get last login status
     */
    public function getLastLoginStatus()
    {
        if (!$this->last_login_at) {
            return 'Never logged in';
        }
        
        if ($this->isOnline()) {
            return 'Online';
        }
        
        return 'Last seen ' . $this->last_login_at->diffForHumans();
    }

    /**
     * Helper method: Update last login timestamp
     */
    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Helper method: Get user avatar initials
     */
    public function getAvatarInitials()
    {
        $nameParts = explode(' ', $this->name);
        $initials = '';
        
        foreach ($nameParts as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
            if (strlen($initials) >= 2) break;
        }
        
        return $initials ?: 'U';
    }

    /**
     * Helper method: Get user role badge color
     */
    public function getRoleBadgeColor()
    {
        if (!$this->role) return 'secondary';
        
        return match($this->role->name) {
            'admin' => 'danger',
            'operator' => 'primary',
            'qc' => 'success',
            'gudang' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Mutator: Ensure employee_id is uppercase
     */
    public function setEmployeeIdAttribute($value)
    {
        $this->attributes['employee_id'] = $value ? strtoupper($value) : null;
    }

    /**
     * Mutator: Ensure name is properly formatted
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower(trim($value)));
    }

    /**
     * Mutator: Ensure phone number format
     */
    public function setPhoneAttribute($value)
    {
        // Remove all non-digit characters except +
        $phone = preg_replace('/[^\d+]/', '', $value);
        $this->attributes['phone'] = $phone;
    }
}