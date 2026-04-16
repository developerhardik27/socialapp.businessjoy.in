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
            $table->string('commission_party_dropdown')
                ->default(json_encode(['invoicecommissionparty']))->after('customer_dropdown');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_other_settings', function (Blueprint $table) {
             $table->dropColumn('commission_party_dropdown');
        });
    }
};
