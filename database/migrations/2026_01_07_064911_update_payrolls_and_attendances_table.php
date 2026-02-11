<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->integer('total_days')->default(30)->after('user_id');
            $table->decimal('working_days', 4, 1)->default(0)->after('total_days');
            $table->decimal('present_days', 4, 1)->default(0)->after('working_days');
            $table->decimal('payable_days', 4, 1)->default(0)->after('present_days');
            $table->decimal('per_day_salary', 10, 2)->default(0)->after('payable_days');
        });

        // Modifying ENUM is tricky in migrations, running raw statement to add 'half_day'
        // Using ALTER TABLE for MySQL
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'absent', 'late', 'on_leave', 'half_day') NOT NULL DEFAULT 'absent'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['total_days', 'working_days', 'present_days', 'payable_days', 'per_day_salary']);
        });
        
         // DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'absent', 'late', 'on_leave') NOT NULL DEFAULT 'absent'");
    }
};
