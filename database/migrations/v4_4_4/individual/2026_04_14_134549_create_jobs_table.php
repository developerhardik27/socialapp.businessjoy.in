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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->integer('familyPersonId')->nullable();
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->text('image')->nullable();
            $table->text('type')->nullable();
            $table->text('company_name')->nullable();
            $table->text('company_house_no_building_name')->nullable();
            $table->text('company_landmark')->nullable();
            $table->text('company_area')->nullable();
            $table->integer('company_country_id')->nullable();
            $table->integer('company_state_id')->nullable();
            $table->integer('company_city_id')->nullable();
            $table->bigInteger('company_pincode')->nullable();
            $table->unsignedBigInteger('salary_from')->nullable();
            $table->unsignedBigInteger('salary_to')->nullable();
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
        Schema::dropIfExists('jobs');
    }
};
