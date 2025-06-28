<?php
// File: database/migrations/2024_01_01_000009_create_stock_movements_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel untuk menyimpan pergerakan stok (bahan baku dan barang jadi)
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique(); // Nomor transaksi
            $table->datetime('transaction_date'); // Tanggal transaksi
            
            // Tipe stok: raw_material atau finished_product
            $table->enum('stock_type', ['raw_material', 'finished_product']);
            
            // Reference ke tabel terkait (bisa raw_materials atau product_types)
            $table->unsignedBigInteger('item_id'); // ID item (material atau produk)
            $table->string('item_type'); // Tipe item (raw_material atau product_type)
            
            // Tipe transaksi
            $table->enum('movement_type', [
                'in', // Masuk (pembelian, produksi selesai)
                'out', // Keluar (penggunaan produksi, distribusi)
                'adjustment' // Penyesuaian stok
            ]);
            
            $table->decimal('quantity', 12, 2); // Jumlah yang bergerak
            $table->decimal('unit_price', 12, 2)->default(0); // Harga per unit
            
            // Saldo stok
            $table->decimal('balance_before', 12, 2); // Saldo sebelum transaksi
            $table->decimal('balance_after', 12, 2); // Saldo setelah transaksi
            
            // Reference ke transaksi terkait
            $table->unsignedBigInteger('reference_id')->nullable(); // ID reference (production, distribution, dll)
            $table->string('reference_type')->nullable(); // Tipe reference
            
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User yang input
            $table->text('notes')->nullable(); // Catatan transaksi
            
            $table->timestamps();
            
            // Index untuk optimasi query
            $table->index(['transaction_date']);
            $table->index(['stock_type', 'item_id']);
            $table->index(['movement_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};