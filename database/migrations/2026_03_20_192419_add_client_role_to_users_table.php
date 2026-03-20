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
        // Adding 'client' to the role enum
        \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'employee', 'client') NOT NULL DEFAULT 'employee'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting back to original enum values
        \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'employee') NOT NULL DEFAULT 'employee'");
    }
};
