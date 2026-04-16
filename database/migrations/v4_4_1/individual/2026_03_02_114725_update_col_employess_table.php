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
        Schema::table('employees', function (Blueprint $table) {
            
            $table->string('first_name')->nullable()->change();
            $table->string('surname')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('mobile', 20)->nullable()->change();
            $table->string('account_no')->nullable()->change();
            $table->string('ifsc_code')->nullable()->change();
            $table->string('bank_name')->nullable()->change();
          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
          Schema::table('employees', function (Blueprint $table) {
            $table->string('first_name')->nullable(false)->change();
            $table->string('surname')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->string('mobile')->nullable(false)->change();
            $table->string('account_no')->nullable(false)->change();
            $table->string('ifsc_code')->nullable(false)->change();
            $table->string('bank_name')->nullable(false)->change();
        });
    }
};
