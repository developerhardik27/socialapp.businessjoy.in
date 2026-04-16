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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->integer('familyPersonId')->nullable();
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->date('event_date_from')->nullable();
            $table->date('event_date_to')->nullable();
            $table->time('event_time_from')->nullable();
            $table->time('event_time_to')->nullable();
            $table->text('image')->nullable();    
            $table->text('video')->nullable();  
            $table->text('event_status')->nullable();
            $table->text('event_type')->nullable();
            $table->text('building_name')->nullable();
            $table->text('landmark')->nullable();
            $table->text('area')->nullable();
            $table->integer('country_id')->nullable();
            $table->integer('state_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->bigInteger('pincode')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by');
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
        Schema::dropIfExists('events');
    }
};
