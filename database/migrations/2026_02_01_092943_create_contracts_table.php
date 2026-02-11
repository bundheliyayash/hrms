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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            
            $table->string('contract_number', 50)->unique();
            $table->enum('contract_type', ['permanent', 'temporary', 'one_day'])->default('permanent');
            
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('renewal_date')->nullable();
            
            $table->enum('billing_type', ['per_day', 'per_month', 'per_service'])->default('per_day');
            $table->decimal('rate_per_day', 10, 2);
            
            $table->integer('minimum_workers_required')->default(1);
            $table->integer('actual_workers_assigned')->default(0);
            
            $table->enum('payment_terms', ['weekly', 'monthly', 'on_completion'])->default('monthly');
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled', 'expired'])->default('draft');
            $table->boolean('auto_renew')->default(false);
            
            $table->text('terms_and_conditions')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('client_id', 'idx_contract_client');
            $table->index('status', 'idx_contract_status');
            $table->index(['start_date', 'end_date'], 'idx_contract_dates');
            $table->index('contract_type', 'idx_contract_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
