<?php
// File: app/Models/User.php

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
     * Scope: Hanya user yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Filter berdasarkan role
     */
    public function scopeByRole($query, $roleName)
    {
        return $query->whereHas('role', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /**
     * Helper method: Cek apakah user adalah admin
     */
    public function isAdmin()
    {
        return $this->role && $this->role->name === 'admin';
    }

    /**
     * Helper method: Cek apakah user adalah operator
     */
    public function isOperator()
    {
        return $this->role && $this->role->name === 'operator';
    }

    /**
     * Helper method: Cek apakah user adalah QC
     */
    public function isQC()
    {
        return $this->role && $this->role->name === 'qc';
    }

    /**
     * Helper method: Cek apakah user adalah gudang
     */
    public function isGudang()
    {
        return $this->role && $this->role->name === 'gudang';
    }

    /**
     * Helper method: Dapatkan nama role
     */
    public function getRoleName()
    {
        return $this->role ? $this->role->display_name : 'Tidak ada role';
    }

    /**
     * Helper method: Update last login
     */
    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }
}