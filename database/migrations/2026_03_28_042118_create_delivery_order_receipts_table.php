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
        Schema::create('delivery_order_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_npk_id')->nullable()->unique()->constrained('monitoring_npks');
            $table->foreignId('monitoring_chemical_id')->nullable()->unique()->constrained('monitoring_chemicals');
            $table->string('delivery_oder_no', 25)->index();
            $table->date('received_date')->index();
            $table->foreignId('received_by')->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->string('source_type', 20)->index();
            $table->string('stage', 100)->nullable();
            $table->string('document_code', 100)->unique()->index();
            $table->string('status', 20)->default('Active')->index();
            $table->date('post_103')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_order_receipts');
    }
};
