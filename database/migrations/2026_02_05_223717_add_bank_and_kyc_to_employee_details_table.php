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
        Schema::table('employee_details', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('employment_type');
            $table->string('account_number')->nullable()->after('bank_name');
            $table->string('ifsc_code')->nullable()->after('account_number');
            $table->string('aadhar_number')->nullable()->after('ifsc_code');
            $table->string('pan_number')->nullable()->after('aadhar_number');
            $table->string('emergency_contact_name')->nullable()->after('pan_number');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'account_number',
                'ifsc_code',
                'aadhar_number',
                'pan_number',
                'emergency_contact_name',
                'emergency_contact_phone'
            ]);
        });
    }
};
