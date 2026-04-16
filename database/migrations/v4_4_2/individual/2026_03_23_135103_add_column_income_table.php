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
        Schema::table('incomes', function (Blueprint $table) {
            $table->text('customer_id')->nullable()->after('paid_by');
            $table->integer('category_id')->nullable()->after('customer_id');
            $table->integer('subcategory_id')->nullable()->after('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn('customer_id');
            $table->dropColumn('category_id');
            $table->dropColumn('subcategory_id');
        });
    }
};
