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
            $table->foreignId('site_id')->nullable()->after('user_id')->constrained('client_sites')->nullOnDelete();
            $table->decimal('latitude', 10, 8)->nullable()->after('status');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->boolean('is_verified')->default(false)->after('longitude'); // True if within radius
            $table->integer('distance_detected')->nullable()->after('is_verified'); // Meters from site center
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            //
        });
    }
};
