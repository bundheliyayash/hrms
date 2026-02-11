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
        Schema::table('contracts', function (Blueprint $table) {
            $table->boolean('ot_enabled')->default(false)->after('rate_per_day');
            $table->decimal('ot_multiplier', 5, 2)->default(1.50)->after('ot_enabled');
            $table->integer('calculate_ot_after_minutes')->default(0)->after('ot_multiplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['ot_enabled', 'ot_multiplier', 'calculate_ot_after_minutes']);
        });
    }
};
