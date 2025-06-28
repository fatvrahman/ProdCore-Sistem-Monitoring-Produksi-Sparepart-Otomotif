<?php
// File: database/migrations/2024_01_01_000010_create_distributions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel untuk menyimpan data distribusi barang jadi
     */
    public function up(): void
    {
        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_number')->unique(); // Nomor surat jalan
            $table->date('distribution_date'); // Tanggal distribusi
            
            // Detail distribusi
            $table->string('customer_name'); // Nama customer/tujuan
            $table->text('delivery_address'); // Alamat pengiriman
            $table->string('vehicle_number')->nullable(); // Nomor kendaraan
            $table->string('driver_name')->nullable(); // Nama driver
            
            // Items yang didistribusikan (JSON format)
            $table->json('items'); // [{"product_type_id": 1, "quantity": 100, "batch_numbers": ["BATCH001"]}]
            
            $table->integer('total_quantity'); // Total quantity semua item
            $table->decimal('total_weight', 10, 2)->nullable(); // Total berat (kg)
            
            // Status distribusi
            $table->enum('status', [
                'prepared', // Disiapkan
                'loading', // Sedang dimuat
                'shipped', // Dikirim
                'delivered', // Terkirim
                'returned' // Dikembalikan
            ])->default('prepared');
            
            // Waktu
            $table->datetime('shipped_at')->nullable(); // Waktu dikirim
            $table->datetime('delivered_at')->nullable(); // Waktu sampai
            
            $table->foreignId('prepared_by')->constrained('users')->onDelete('cascade'); // User yang menyiapkan
            $table->text('notes')->nullable(); // Catatan distribusi
            
            $table->timestamps();
            
            // Index untuk optimasi query
            $table->index(['distribution_date']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributions');
    }
};