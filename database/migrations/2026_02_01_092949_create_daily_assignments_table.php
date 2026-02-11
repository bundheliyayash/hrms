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
        Schema::create('daily_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('site_id')->constrained('client_sites')->onDelete('cascade');
            $table->foreignId('contract_id')->nullable()->constrained('contracts')->onDelete('set null');
            
            $table->date('assigned_date');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            
            $table->enum('assignment_type', ['permanent', 'temporary', 'one_day', 'replacement'])->default('one_day');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('restrict');
            
            $table->enum('status', ['assigned', 'confirmed', 'completed', 'cancelled', 'no_show'])->default('assigned');
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Ensure one employee can only be assigned to one site per day
            $table->unique(['user_id', 'assigned_date'], 'unique_user_date');
            
            // Indexes for performance
            $table->index('assigned_date', 'idx_assignment_date');
            $table->index(['site_id', 'assigned_date'], 'idx_assignment_site_date');
            $table->index(['user_id', 'assigned_date'], 'idx_assignment_user_date');
            $table->index('status', 'idx_assignment_status');
            $table->index('contract_id', 'idx_assignment_contract');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_assignments');
    }
};
