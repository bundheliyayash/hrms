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
        Schema::create('client_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
            
            $table->string('invoice_number', 50)->unique();
            $table->date('invoice_date');
            $table->date('due_date');
            
            $table->date('billing_period_start');
            $table->date('billing_period_end');
            
            $table->decimal('total_worker_days', 10, 2)->default(0);
            $table->decimal('rate_per_day', 10, 2);
            
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(18);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            
            $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('client_id', 'idx_invoice_client');
            $table->index('contract_id', 'idx_invoice_contract');
            $table->index('status', 'idx_invoice_status');
            $table->index('invoice_date', 'idx_invoice_date');
            $table->index(['billing_period_start', 'billing_period_end'], 'idx_invoice_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_invoices');
    }
};
