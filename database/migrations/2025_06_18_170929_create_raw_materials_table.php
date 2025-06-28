<?php
// File: database/migrations/2024_01_01_000005_create_raw_materials_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel untuk menyimpan data bahan baku
     */
    public function up(): void
    {
        Schema::create('raw_materials', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Kode bahan (MAT001, MAT002)
            $table->string('name'); // Nama bahan (Serbuk Logam, Resin, dst)
            $table->string('unit'); // Satuan (kg, liter, pcs)
            $table->decimal('current_stock', 12, 2)->default(0); // Stok saat ini
            $table->decimal('minimum_stock', 12, 2)->default(0); // Stok minimum
            $table->decimal('maximum_stock', 12, 2)->default(0); // Stok maksimum
            $table->decimal('unit_price', 12, 2)->default(0); // Harga per unit
            $table->string('supplier')->nullable(); // Nama supplier
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_materials');
    }
};