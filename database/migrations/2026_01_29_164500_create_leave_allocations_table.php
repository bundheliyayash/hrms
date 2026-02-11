<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('leave_type'); // e.g., 'Annual', 'Sick', 'Casual', 'Unpaid'
            $table->decimal('total_days', 5, 1)->default(0);
            $table->decimal('used_days', 5, 1)->default(0);
            $table->integer('year')->default(date('Y'));
            $table->timestamps();

            $table->unique(['user_id', 'leave_type', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_allocations');
    }
};
