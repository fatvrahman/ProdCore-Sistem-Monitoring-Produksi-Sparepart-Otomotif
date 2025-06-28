<?php
// File: app/Models/Role.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /**
     * Kolom yang bisa diisi secara mass assignment
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_active'
    ];

    /**
     * Kolom yang harus di-cast ke tipe data tertentu
     */
    protected $casts = [
        'permissions' => 'array', // JSON akan di-cast ke array
        'is_active' => 'boolean'
    ];

    /**
     * Relasi: Role memiliki banyak User
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope: Hanya role yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Helper method: Cek apakah role memiliki permission tertentu
     */
    public function hasPermission($permission)
    {
        if (!$this->permissions) {
            return false;
        }
        
        return in_array($permission, $this->permissions);
    }

    /**
     * Helper method: Dapatkan semua permissions sebagai string
     */
    public function getPermissionsString()
    {
        if (!$this->permissions) {
            return 'Tidak ada permission';
        }
        
        return implode(', ', $this->permissions);
    }
}