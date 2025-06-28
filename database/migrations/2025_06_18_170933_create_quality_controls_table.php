<?php
// File: database/migrations/2024_01_01_000008_create_quality_controls_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel untuk menyimpan data inspeksi quality control
     */
    public function up(): void
    {
        Schema::create('quality_controls', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_number')->unique(); // Nomor inspeksi
            $table->foreignId('production_id')->constrained()->onDelete('cascade'); // FK ke productions
            $table->foreignId('qc_inspector_id')->constrained('users')->onDelete('cascade'); // FK ke users (QC)
            
            $table->datetime('inspection_date'); // Tanggal dan waktu inspeksi
            $table->integer('sample_size'); // Jumlah sampel yang diinspeksi
            $table->integer('passed_quantity'); // Jumlah yang lolos inspeksi
            $table->integer('failed_quantity'); // Jumlah yang gagal inspeksi
            
            // Detail inspeksi (JSON format)
            $table->json('inspection_criteria'); // Kriteria yang diinspeksi
            $table->json('test_results'); // Hasil test detail
            
            // Kategori kegagalan
            $table->enum('defect_category', [
                'dimensional', // Cacat dimensi
                'surface', // Cacat permukaan  
                'material', // Cacat material
                'assembly', // Cacat perakitan
                'other' // Lainnya
            ])->nullable();
            
            $table->text('defect_description')->nullable(); // Deskripsi cacat
            $table->text('corrective_action')->nullable(); // Tindakan korektif
            
            $table->enum('final_status', ['approved', 'rejected', 'rework']); // Status akhir
            $table->text('notes')->nullable(); // Catatan QC
            
            $table->timestamps();
            
            // Index untuk optimasi query
            $table->index(['inspection_date']);
            $table->index(['final_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_controls');
    }
};