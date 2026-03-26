<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->boolean('is_pf_applicable')->default(true)->after('basic_salary');
            $table->boolean('is_esi_applicable')->default(true)->after('is_pf_applicable');
        });
    }

    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropColumn(['is_pf_applicable', 'is_esi_applicable']);
        });
    }
};
