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
        Schema::table('invoice_other_settings', function (Blueprint $table) {
            $table->integer('commission')->default(0)->comment('0 => hide button, 1=> show button');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_other_settings', function (Blueprint $table) {
            $table->dropColumn('commission');
        });
    }
};
