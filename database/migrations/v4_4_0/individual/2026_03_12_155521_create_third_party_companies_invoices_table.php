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
        Schema::create('third_party_companies_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->notNullable();
            $table->string('email', 50)->nullable();
            $table->bigInteger('contact_no')->nullable();
            $table->bigInteger('alternative_number')->nullable();
            $table->string('house_no_building_name')->nullable();
            $table->string('road_name_area_colony')->nullable();
            $table->string('gst_no', 50)->nullable();
            $table->string('pan_number', 20)->nullable();
            $table->integer('country_id')->nullable();
            $table->integer('state_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->integer('pincode')->nullable();
            $table->text('img')->nullable();
            $table->integer('created_by');
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
        Schema::dropIfExists('third_party_companies_invoices');
    }
};
