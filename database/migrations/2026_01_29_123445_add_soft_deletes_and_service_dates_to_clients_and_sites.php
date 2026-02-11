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
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
            if (!Schema::hasColumn('clients', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('address');
            }
            if (!Schema::hasColumn('clients', 'service_start_date')) {
                $table->date('service_start_date')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('clients', 'service_end_date')) {
                $table->date('service_end_date')->nullable()->after('service_start_date');
            }
        });

        Schema::table('client_sites', function (Blueprint $table) {
            if (!Schema::hasColumn('client_sites', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
            if (!Schema::hasColumn('client_sites', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('radius_meters');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['is_active', 'service_start_date', 'service_end_date']);
        });

        Schema::table('client_sites', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('is_active');
        });
    }
};
