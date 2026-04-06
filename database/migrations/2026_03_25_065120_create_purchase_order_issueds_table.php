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
        Schema::create('purchase_order_issued', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_order_and_item', 20)->nullable()->index();
            $table->string('material_type', 5)->default('ZSP');
            $table->string('mrp_type', 10);
            $table->string('purchase_order_no', 12)->index(); // Index untuk pencarian nomor PO
            $table->mediumInteger('item_no');
            $table->string('material_code', 20)->nullable()->index(); // Index untuk filter material
            $table->string('aac', 1)->nullable();
            $table->string('abc_indicator', 1)->nullable();
            $table->text('description');
            $table->decimal('qty_po', 12, 0)->default(0);
            $table->string('uoi', 5);
            $table->string('vendor_id', 20)->nullable()->index(); // Index untuk filter per vendor
            $table->string('vendor_name', 100);
            $table->date('date_create')->index(); // Index untuk laporan bulanan/tahunan
            $table->date('delivery_date_po')->nullable();
            $table->string('po_status', 2)->nullable()->index(); // Index untuk filter status (Open/Close/Draft)
            $table->string('incoterm', 100)->nullable();
            $table->decimal('total_amount_in_lc', 20, 0)->default(0); // Tambahkan precision agar akurat
            $table->string('requisitioner', 100);
            $table->timestamps();

            $table->unique(['purchase_order_no', 'item_no'], 'po_item_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_issueds');
    }
};
