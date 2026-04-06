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
        Schema::create('delivery_order_receipt_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_receipt_id')->constrained('delivery_order_receipts')->onDelete('cascade');
            $table->foreignId('purchase_order_issued_id')->constrained('purchase_order_issued');
            $table->mediumInteger('item_no');
            $table->decimal('quantity', 15, 0);
            $table->string('material_code', 20);
            $table->text('description')->nullable();
            $table->string('uoi', 5)->nullable();
            $table->string('mrp_type', 10)->nullable();
            $table->string('material_type', 5)->nullable();
            $table->string('aac', 1)->nullable();
            $table->string('abc_indicator', 1)->nullable();
            $table->string('requisitioner', 100)->nullable();
            $table->decimal('total_amount_snapshot', 20, 0);
            $table->foreignId('location_id')->constrained('location_receivings');
            $table->boolean('is_different_location')->default(false);
            $table->boolean('is_qty_tolerance')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_order_receipt_details');
    }
};
