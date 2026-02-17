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
        // 1. Unique constraints to prevent duplicates
        if (!$this->hasIndex('attendances', 'attendances_user_id_date_unique')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->unique(['user_id', 'date']);
            });
        }

        if (!$this->hasIndex('payrolls', 'payrolls_user_id_month_year_unique')) {
            Schema::table('payrolls', function (Blueprint $table) {
                $table->unique(['user_id', 'month', 'year']);
            });
        }

        // 2. Add is_locked to leaves (already exists in attendances)
        if (!Schema::hasColumn('leaves', 'is_locked')) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->boolean('is_locked')->default(false)->after('status');
            });
        }
    }

    protected function hasIndex($table, $indexName)
    {
        $conn = Schema::getConnection();
        $dbName = $conn->getDatabaseName();
        
        $result = DB::select("
            SELECT count(*) as count
            FROM information_schema.statistics
            WHERE table_schema = ?
            AND table_name = ?
            AND index_name = ?
        ", [$dbName, $table, $indexName]);

        return $result[0]->count > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'date']);
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'month', 'year']);
        });

        Schema::table('leaves', function (Blueprint $table) {
            if (Schema::hasColumn('leaves', 'is_locked')) {
                $table->dropColumn('is_locked');
            }
        });
    }
};
