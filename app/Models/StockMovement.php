<?php
// File: app/Models/StockMovement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    /**
     * Kolom yang bisa diisi secara mass assignment
     */
    protected $fillable = [
        'transaction_number',
        'transaction_date',
        'stock_type',
        'item_id',
        'item_type',
        'movement_type',
        'quantity',
        'unit_price',
        'balance_before',
        'balance_after',
        'reference_id',
        'reference_type',
        'user_id',
        'notes'
    ];

    /**
     * Kolom yang harus di-cast ke tipe data tertentu
     */
    protected $casts = [
        'transaction_date' => 'datetime',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2'
    ];

    /**
     * Relasi: StockMovement belongsTo User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi Polymorphic: item bisa RawMaterial atau ProductType
     */
    public function item()
    {
        return $this->morphTo();
    }

    /**
     * Relasi Polymorphic: reference bisa Production, Distribution, dll
     */
    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Scope: Filter berdasarkan tipe stok
     */
    public function scopeByStockType($query, $type)
    {
        return $query->where('stock_type', $type);
    }

    /**
     * Scope: Filter berdasarkan tipe movement
     */
    public function scopeByMovementType($query, $type)
    {
        return $query->where('movement_type', $type);
    }

    /**
     * Scope: Filter berdasarkan tanggal
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('transaction_date', $date);
    }

    /**
     * Helper method: Dapatkan nama tipe movement
     */
    public function getMovementTypeName()
    {
        $types = [
            'in' => 'Masuk',
            'out' => 'Keluar',
            'adjustment' => 'Penyesuaian'
        ];
        
        return $types[$this->movement_type] ?? 'Unknown';
    }

    /**
     * Helper method: Dapatkan nama tipe stok
     */
    public function getStockTypeName()
    {
        $types = [
            'raw_material' => 'Bahan Baku',
            'finished_product' => 'Barang Jadi'
        ];
        
        return $types[$this->stock_type] ?? 'Unknown';
    }

    /**
     * Helper method: Hitung total nilai transaksi
     */
    public function getTotalValue()
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Helper method: Generate transaction number otomatis
     */
    public static function generateTransactionNumber()
    {
        $date = now()->format('Ymd');
        $lastTransaction = self::where('transaction_number', 'like', "TRX-{$date}-%")
            ->orderBy('transaction_number', 'desc')
            ->first();
        
        if ($lastTransaction) {
            $lastNumber = intval(substr($lastTransaction->transaction_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return "TRX-{$date}-{$newNumber}";
    }
}