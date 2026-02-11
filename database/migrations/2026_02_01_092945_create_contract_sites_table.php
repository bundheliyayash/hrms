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
        Schema::create('contract_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->foreignId('site_id')->constrained('client_sites')->onDelete('cascade');
            
            $table->integer('workers_required')->default(1);
            
            $table->timestamps();
            
            // Ensure unique combination of contract and site
            $table->unique(['contract_id', 'site_id'], 'unique_contract_site');
            
            // Indexes
            $table->index('contract_id', 'idx_contract_site_contract');
            $table->index('site_id', 'idx_contract_site_site');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_sites');
    }
};
