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
        Schema::create('worker_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_assignment_id')->constrained('daily_assignments')->onDelete('cascade');
            $table->foreignId('original_worker_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('replacement_worker_id')->constrained('users')->onDelete('restrict');
            
            $table->foreignId('site_id')->constrained('client_sites')->onDelete('restrict');
            $table->date('replacement_date');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            
            $table->enum('reason', ['absent', 'leave', 'emergency', 'client_request', 'other'])->default('absent');
            $table->text('reason_details')->nullable();
            
            $table->foreignId('requested_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('replacement_date', 'idx_replacement_date');
            $table->index(['original_worker_id', 'replacement_date'], 'idx_replacement_original');
            $table->index(['replacement_worker_id', 'replacement_date'], 'idx_replacement_substitute');
            $table->index('status', 'idx_replacement_status');
            $table->index('original_assignment_id', 'idx_replacement_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_replacements');
    }
};
