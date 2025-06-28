<?php
// File: database/migrations/2024_01_01_000004_create_production_lines_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel untuk menyimpan data lini produksi
     */
    public function up(): void
    {
        Schema::create('production_lines', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Kode lini (LINE-A, LINE-B, dst)
            $table->string('name'); // Nama lini produksi
            $table->text('description')->nullable(); // Deskripsi lini
            $table->integer('capacity_per_hour')->default(0); // Kapasitas per jam
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
            $table->json('shift_schedule')->nullable(); // Jadwal shift dalam JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_lines');
    }
};