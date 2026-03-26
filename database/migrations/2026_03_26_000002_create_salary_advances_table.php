<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('advance_date');
            $table->string('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'recovered'])->default('pending');
            $table->string('admin_remark')->nullable();
            // Deduction tracking
            $table->decimal('recovered_amount', 10, 2)->default(0);
            $table->date('recovery_month')->nullable()->comment('YYYY-MM-01 — which payroll month to deduct from');
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls')->nullOnDelete()->comment('Set when deducted in payroll');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_advances');
    }
};
