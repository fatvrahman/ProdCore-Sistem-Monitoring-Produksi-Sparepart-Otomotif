<?php
// File: database/migrations/2024_01_01_000002_create_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel untuk menyimpan data user/pengguna sistem
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama lengkap user
            $table->string('email')->unique(); // Email untuk login
            $table->string('password'); // Password (encrypted)
            $table->unsignedBigInteger('role_id'); // FK ke tabel roles - WAJIB DIISI
            $table->string('employee_id')->unique()->nullable(); // ID Karyawan
            $table->string('phone')->nullable(); // Nomor telepon
            $table->enum('status', ['active', 'inactive'])->default('active'); // Status user
            $table->timestamp('last_login_at')->nullable(); // Waktu login terakhir
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            // Index untuk optimasi query
            $table->index(['status']);
            $table->index(['role_id']);
            
            // Foreign key constraint - DIPINDAH KE AKHIR
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};