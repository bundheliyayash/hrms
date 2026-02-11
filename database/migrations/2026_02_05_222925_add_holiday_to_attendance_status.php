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
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'absent', 'late', 'half_day', 'holiday') DEFAULT 'absent'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'absent', 'late', 'half_day') DEFAULT 'absent'");
    }
};
