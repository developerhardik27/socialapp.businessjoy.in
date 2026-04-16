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
        Schema::table('expenses', function (Blueprint $table) {
            $table->integer('expense_id')->nullable()->comment('Reference to Transport Bill ID')->change();
            $table->integer('expense_details_id')->nullable()->comment('Reference to Transport Bill Payment ID')->change();
            $table->string('bill_no')->nullable()->change();
            $table->string('reference_no')->after('voucher_no')->nullable()->comment('Reference to  Transport Bill Payment Receipt number');
        });
        DB::statement('ALTER TABLE expenses MODIFY COLUMN bill_no VARCHAR(255) NULL AFTER id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->integer('expense_id')->nullable(false)->change();
            $table->integer('expense_details_id')->nullable(false)->change();
            $table->string('bill_no')->nullable(false)->change();
            $table->dropColumn('reference_no');
        });
    }
};
