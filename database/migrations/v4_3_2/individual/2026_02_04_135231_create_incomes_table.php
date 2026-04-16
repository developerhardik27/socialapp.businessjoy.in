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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->integer('income_details_id')->nullable();
            $table->string('receipt_no')->nullable();
            $table->text('description')->nullable();
            $table->double('amount')->default(0); 
            $table->string('payment_type')->nullable()->comment('cash or online');
            $table->text('paid_by')->nullable();
            $table->date('date')->nullable();
            $table->string('entry_type')->default('a')->comment('a => auto, m => manual');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->integer('is_active')->default(1);
            $table->integer('is_deleted')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
