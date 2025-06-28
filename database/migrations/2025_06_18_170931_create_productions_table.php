<?php
// File: database/migrations/2024_01_01_000007_create_productions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel untuk menyimpan data produksi harian
     */
    public function up(): void
    {
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique(); // Nomor batch produksi
            $table->date('production_date'); // Tanggal produksi
            $table->enum('shift', ['1', '2', '3']); // Shift kerja (1=pagi, 2=siang, 3=malam)
            $table->foreignId('product_type_id')->constrained()->onDelete('cascade'); // FK ke product_types
            $table->foreignId('production_line_id')->constrained()->onDelete('cascade'); // FK ke production_lines
            $table->foreignId('machine_id')->constrained()->onDelete('cascade'); // FK ke machines
            $table->foreignId('operator_id')->constrained('users')->onDelete('cascade'); // FK ke users (operator)
            
            // Data produksi
            $table->integer('target_quantity'); // Target produksi
            $table->integer('actual_quantity')->default(0); // Jumlah aktual yang diproduksi
            $table->integer('good_quantity')->default(0); // Jumlah barang bagus
            $table->integer('defect_quantity')->default(0); // Jumlah barang cacat
            
            // Waktu produksi
            $table->time('start_time'); // Waktu mulai
            $table->time('end_time')->nullable(); // Waktu selesai
            $table->integer('downtime_minutes')->default(0); // Waktu downtime (menit)
            
            // Bahan baku yang digunakan (JSON format)
            $table->json('raw_materials_used'); // {"material_id": quantity_used}
            
            // Catatan dan status
            $table->text('notes')->nullable(); // Catatan produksi
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            
            $table->timestamps();
            
            // Index untuk optimasi query
            $table->index(['production_date', 'shift']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};