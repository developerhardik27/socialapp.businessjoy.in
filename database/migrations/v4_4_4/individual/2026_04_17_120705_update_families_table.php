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
        Schema::table('families', function (Blueprint $table) {
            $table->text('mainFamilyMemberFullName')->nullable()->change();
            $table->text('familyPersonIds')->nullable()->change();
            $table->integer('mainFamilyPersonId')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->text('mainFamilyMemberFullName')->nullable()->change();
            $table->text('familyPersonIds')->nullable()->change();
            $table->integer('mainFamilyPersonId')->nullable()->change();
        });
    }
};
