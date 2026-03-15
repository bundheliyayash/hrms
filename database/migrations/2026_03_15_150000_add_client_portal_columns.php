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
        // Link clients to a user account for portal login
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        // Track who submitted the attendance record
        Schema::table('attendances', function (Blueprint $table) {
            $table->enum('source', ['employee', 'admin', 'client_portal'])->default('employee')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
