<?php
// File: database/migrations/2024_01_01_000006_create_machines_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel untuk menyimpan data mesin produksi
     */
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Kode mesin (MCH001, MCH002)
            $table->string('name'); // Nama mesin
            $table->foreignId('production_line_id')->constrained()->onDelete('cascade'); // FK ke production_lines
            $table->string('brand')->nullable(); // Merk mesin
            $table->string('model')->nullable(); // Model mesin
            $table->year('manufacture_year')->nullable(); // Tahun pembuatan
            $table->integer('capacity_per_hour')->default(0); // Kapasitas per jam
            $table->enum('status', ['running', 'idle', 'maintenance', 'breakdown'])->default('idle');
            $table->date('last_maintenance_date')->nullable(); // Tanggal maintenance terakhir
            $table->date('next_maintenance_date')->nullable(); // Tanggal maintenance berikutnya
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};