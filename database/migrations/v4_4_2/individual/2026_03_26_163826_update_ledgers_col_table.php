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
        Schema::table('ledgers', function (Blueprint $table) {
            $table->integer('payment_id')->nullable()->comment('Reference to Income or Expense ID')->change();
            $table->string('type')->nullable()->comment('Type of transaction: Income or Expense')->change();
            $table->string('reference_no')->after('type')->nullable()->comment('Reference to Invoice and Transport Bill Payment Receipt number');
        });
        DB::statement('ALTER TABLE ledgers MODIFY COLUMN type VARCHAR(255) NULL AFTER payment_id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ledgers', function (Blueprint $table) {
            $table->integer('payment_id')->nullable(false)->change();
            $table->string('type')->nullable(false)->change();
            $table->dropColumn('reference_no');
        });
    }
};
