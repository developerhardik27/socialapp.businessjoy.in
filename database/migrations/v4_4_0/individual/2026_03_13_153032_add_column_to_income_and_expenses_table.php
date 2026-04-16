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
        Schema::table('expenses', function (Blueprint $table) {
             $table->string('bill_no')->nullable();
        });
        Schema::table('incomes', function (Blueprint $table) {
             $table->string('voucher_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
             $table->dropColumn('bill_no');
        });
        Schema::table('incomes', function (Blueprint $table) {
         $table->dropColumn('voucher_no');
        });
    }
};
