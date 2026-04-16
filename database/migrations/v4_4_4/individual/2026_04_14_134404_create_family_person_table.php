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
        Schema::create('family_person', function (Blueprint $table) {
            $table->id();
            $table->integer('family_id')->nullable();
            $table->text('first_name')->nullable();
            $table->text('last_name')->nullable();
            $table->text('surname')->nullable();
            $table->text('full_name')->nullable();
            $table->date('dob')->nullable();
            $table->integer('age')->nullable();
            $table->text('email')->nullable();
            $table->text('mobile')->nullable();
            $table->text('address_house_no_building_name')->nullable();
            $table->text('address_landmark')->nullable();
            $table->text('address_area')->nullable();
            $table->integer('address_country_id')->nullable();
            $table->integer('address_state_id')->nullable();
            $table->integer('address_city_id')->nullable();
            $table->integer('address_pincode')->nullable();
            $table->text('marital_status')->nullable();
            $table->text('gender')->nullable();
            $table->text('job_role')->nullable();
            $table->text('company_name')->nullable();
            $table->text('company_house_no_building_name')->nullable();
            $table->text('company_landmark')->nullable();
            $table->text('company_area')->nullable();
            $table->integer('company_country_id')->nullable();
            $table->integer('company_state_id')->nullable();
            $table->integer('company_city_id')->nullable();
            $table->integer('company_pincode')->nullable();
            $table->text('business_intro')->nullable();
            $table->text('services')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->integer('business_category')->nullable();
            $table->integer('business_subcategory')->nullable();
            $table->integer('main_family_member')->nullable();
            $table->integer('relationship_id')->default(0);
            $table->text('shakh')->nullable();
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
        Schema::dropIfExists('family_person');
    }
};
