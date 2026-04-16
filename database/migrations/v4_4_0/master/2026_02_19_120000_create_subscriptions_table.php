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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id'); // master company id
            $table->unsignedBigInteger('package_id')->nullable();
            $table->date('trial_start_date')->nullable();
            $table->date('trial_end_date')->nullable();
            $table->integer('trial_days')->nullable();
            $table->date('subscription_start_date')->nullable();
            $table->date('subscription_end_date')->nullable();
            $table->string('billing_cycle')->nullable(); // monthly, quarterly, yearly
            $table->string('cycle_duration')->nullable(); // 90 days
            $table->date('payment_cycle_start_date')->nullable(); 
            $table->date('payment_cycle_end_date')->nullable(); 
            $table->date('next_billing_date')->nullable();
            $table->string('package_type')->nullable();
            $table->double('package_price')->default(0);
            $table->double('emi_cost')->default(0);
            $table->string('emi_calculation')->nullable();
            $table->integer('auto_generate_invoice')->default(0);
            $table->string('status')->default('trial'); // active, trial, expired, suspended
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
        Schema::dropIfExists('subscriptions');
    }
};
