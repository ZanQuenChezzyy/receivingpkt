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
        Schema::create('monitoring_chemical_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_chemical_id')->constrained('monitoring_chemicals')->onDelete('cascade');
            $table->foreignId('chemical_qc_tuv_id')->nullable()->constrained('chemical_qc_tuvs');
            $table->decimal('quantity_received', 15, 0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_chemical_details');
    }
};
