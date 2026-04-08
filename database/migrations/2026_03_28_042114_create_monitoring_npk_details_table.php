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
        Schema::create('monitoring_npk_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_npk_id')->constrained('monitoring_npks')->onDelete('cascade');
            $table->mediumInteger('item_no');
            $table->string('material_code', 20);
            $table->text('description');
            $table->decimal('quantity', 15, 0);
            $table->string('uoi', 5);
            $table->foreignId('location_id')->nullable()->constrained('location_receivings');
            $table->boolean('is_qty_tolerance')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_npk_details');
    }
};
