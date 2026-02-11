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
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('status');
            $table->string('flag_reason')->nullable()->after('is_locked');
        });

        // Update Status Enum - MySQL only
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'absent', 'late', 'early_out', 'half_day', 'missing') DEFAULT 'present'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['is_locked', 'flag_reason']);
        });
    }
};
