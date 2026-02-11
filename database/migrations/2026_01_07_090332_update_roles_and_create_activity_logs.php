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
        // 1. Update Users Table Role Enum
        // Using raw statement to modify enum column safely
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'manager', 'employee', 'client') NOT NULL DEFAULT 'employee'");
        }

        // 2. Create Activity Logs Table
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('causer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description');
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            
            $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        
        // Revert role enum - WARNING: This might fail if data exists with new roles
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'hr', 'employee') NOT NULL DEFAULT 'employee'");
        }
    }
};
