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
        Schema::create('karobari_meetings_attendance', function (Blueprint $table) {
            $table->id();
            $table->integer('karobari_meeting_id')->nullable();
            $table->text('family_person_id')->nullable();
            $table->text('karobari_member_id')->nullable();
            $table->text('member_id')->nullable();
            $table->text('status')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('is_deleted')->default(0);
            $table->integer('is_active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karobari_meetings_attendance');
    }
};
