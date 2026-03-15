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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('gross_salary', 12, 2)->default(0)->after('per_day_salary');
            $table->decimal('hra', 12, 2)->default(0)->after('gross_salary');
            $table->decimal('washing_allowance', 12, 2)->default(0)->after('hra');
            $table->decimal('pt_amount', 12, 2)->default(0)->after('esi_amount');
            $table->decimal('advance_amount', 12, 2)->default(0)->after('pt_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['gross_salary', 'hra', 'washing_allowance', 'pt_amount', 'advance_amount']);
        });
    }
};
