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
        Schema::create('chemical_qc_tuvs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_issued_id')->constrained('purchase_order_issued');
            $table->string('tahapan_name', 100);
            $table->decimal('qty_qc_tuv', 15, 0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chemical_qc_tuvs');
    }
};
