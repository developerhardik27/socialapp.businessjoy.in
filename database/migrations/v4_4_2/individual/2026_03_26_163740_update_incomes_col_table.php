<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->integer('income_id')->nullable()->comment('Reference to Invoice ID')->change();
            $table->integer('income_details_id')->nullable()->comment('Reference to Invoice Payment ID')->change();
            $table->string('voucher_no')->nullable()->change();
            $table->string('reference_no')->after('receipt_no')->nullable()->comment('Reference to Invoice Payment Receipt number');
        });
        DB::statement('ALTER TABLE incomes MODIFY COLUMN voucher_no VARCHAR(255) NULL AFTER id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->integer('income_id')->nullable(false)->change();
            $table->integer('income_details_id')->nullable(false)->change();
            $table->string('voucher_no')->nullable(false)->change();
            $table->dropColumn('reference_no');
        });
    }
};
