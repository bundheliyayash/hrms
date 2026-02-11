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
        Schema::create('invoice_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('client_invoices')->onDelete('cascade');
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->onDelete('set null');
            $table->foreignId('daily_assignment_id')->nullable()->constrained('daily_assignments')->onDelete('set null');
            
            $table->foreignId('site_id')->constrained('client_sites')->onDelete('restrict');
            $table->foreignId('worker_id')->constrained('users')->onDelete('restrict');
            $table->string('worker_name', 255);
            
            $table->date('service_date');
            $table->decimal('hours_worked', 5, 2)->default(0);
            $table->decimal('rate', 10, 2);
            $table->decimal('amount', 10, 2);
            
            $table->text('description')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('invoice_id', 'idx_line_item_invoice');
            $table->index('service_date', 'idx_line_item_date');
            $table->index('site_id', 'idx_line_item_site');
            $table->index('worker_id', 'idx_line_item_worker');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_line_items');
    }
};
