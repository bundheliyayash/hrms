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
        // 1. Restrict Roles to Admin, Manager, Employee
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'employee') NOT NULL DEFAULT 'employee'");
        }

        // 2. Create Menus Table (Admin Controlled Sidebar)
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('route_name')->nullable();
            $table->string('icon')->default('bi-circle');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Create Menu Permissions Table
        Schema::create('menu_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->enum('role', ['admin', 'manager', 'employee']);
            $table->timestamps();

            $table->unique(['menu_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_permissions');
        Schema::dropIfExists('menus');

        // Revert roles (adding super_admin/client back just in case)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'manager', 'employee', 'client') NOT NULL DEFAULT 'employee'");
        }
    }
};
