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
        Schema::create('monitoring_chemicals', function (Blueprint $table) {
            $table->id();
            $table->string('material_category', 20)->index();
            $table->foreignId('purchase_order_issued_id')->constrained('purchase_order_issued');
            $table->string('qc_by', 50);
            $table->string('do_number', 15)->nullable()->index();
            $table->date('received_date')->nullable()->index();
            $table->foreignId('location_id')->nullable()->constrained('location_receivings');
            $table->boolean('is_qty_tolerance')->default(false);
            $table->boolean('has_update_progress')->default(false);
            $table->text('notes')->nullable();
            $table->date('tanggal_pengajuan_simala')->nullable();
            $table->date('tanggal_pengambilan_sample')->nullable();
            $table->date('tanggal_terbit_coa')->nullable();
            $table->integer('leadtime_coa')->nullable();
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
        Schema::dropIfExists('monitoring_chemicals');
    }
};
