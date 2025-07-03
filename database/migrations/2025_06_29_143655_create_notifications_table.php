<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50)->index(); // 'production', 'qc', 'stock', 'distribution', 'system'
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Data tambahan untuk notification
            $table->string('action_url')->nullable(); // URL untuk redirect
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Indexes untuk performance
            $table->index(['user_id', 'read_at']);
            $table->index(['type', 'created_at']);
            $table->index(['priority', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};