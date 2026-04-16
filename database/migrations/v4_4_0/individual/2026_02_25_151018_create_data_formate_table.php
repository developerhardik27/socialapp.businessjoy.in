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
        Schema::create('data_formate', function (Blueprint $table) {
            $table->id();
            $table->string('letter_name');
            $table->string('header_image')->nullable();
            $table->string('header_align');
            $table->integer('header_width');
            $table->text('header_content');

            $table->text('body_content');

            $table->string('footer_image')->nullable();
            $table->string('footer_align');
            $table->integer('footer_width');
            $table->text('footer_content');

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
        Schema::dropIfExists('data_formate');
    }
};
