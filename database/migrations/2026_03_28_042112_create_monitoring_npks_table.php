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
        Schema::create('monitoring_npks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_terbit_id')->index()->constrained('purchase_order_issued');
            $table->string('delivery_oder_number', 15)->nullable()->index();
            $table->foreignId('location_id')->constrained('location_receivings');
            $table->date('sample_receivied_date')->nullable();
            $table->string('stage', 50)->nullable();
            $table->date('delivery_oder_delivery_date')->nullable();
            $table->date('purchase_order_103_date')->nullable();
            $table->date('received_date')->nullable()->index();
            $table->string('purchase_order_status', 1)->nullable();
            $table->date('purchase_order_status_a_date')->nullable();
            $table->date('purchase_order_status_b_date')->nullable();
            $table->json('purchase_order_status_a_files')->nullable();
            $table->date('laprima_date')->nullable();
            $table->date('coa_date')->nullable();
            $table->json('coa_files')->nullable();
            $table->string('doc_status', 20)->default('Outstanding')->index();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_npks');
    }
};
