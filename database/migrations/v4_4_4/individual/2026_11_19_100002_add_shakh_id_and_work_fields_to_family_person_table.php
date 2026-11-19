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
        Schema::table('family_person', function (Blueprint $table) {
            $table->integer('shakh_id')->nullable()->after('shakh');
            $table->string('work_type')->nullable();
            $table->string('retired_title')->nullable();
            $table->text('retired_description')->nullable();
            $table->string('study_level')->nullable();
            $table->string('study_institute')->nullable();
            $table->string('study_course')->nullable();
            $table->string('study_year')->nullable();
            $table->string('education')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('family_person', function (Blueprint $table) {
            $table->dropColumn([
                'shakh_id',
                'work_type',
                'retired_title',
                'retired_description',
                'study_level',
                'study_institute',
                'study_course',
                'study_year',
                'education',
            ]);
        });
    }
};
