<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('effective_date');
            $table->string('change_reason')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_histories');
    }
};
