<?php
// File: database/migrations/2024_01_01_000001_create_roles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel untuk menyimpan role/peran user (Admin, Operator, QC, Gudang)
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // admin, operator, qc, gudang
            $table->string('display_name'); // Admin, Operator Produksi, Quality Control, Tim Gudang
            $table->text('description')->nullable();
            $table->json('permissions')->nullable(); // permissions dalam format JSON
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};