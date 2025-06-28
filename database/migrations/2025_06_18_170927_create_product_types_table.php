<?php
// File: database/migrations/2024_01_01_000003_create_product_types_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel untuk menyimpan jenis-jenis produk brakepad
     */
    public function up(): void
    {
        Schema::create('product_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Kode produk (BP001, BP002, dst)
            $table->string('name'); // Nama produk (Brakepad Honda Beat, dst)
            $table->string('brand'); // Merk motor (Honda, Yamaha, dst)
            $table->string('model'); // Model motor (Beat, Vario, dst)
            $table->text('description')->nullable(); // Deskripsi produk
            $table->decimal('standard_weight', 8, 2)->nullable(); // Berat standard (gram)
            $table->decimal('standard_thickness', 8, 2)->nullable(); // Ketebalan standard (mm)
            $table->json('specifications')->nullable(); // Spesifikasi lain dalam JSON
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_types');
    }
};