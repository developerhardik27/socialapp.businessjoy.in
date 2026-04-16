<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //  Rename column 
        Schema::table('logistic_settings', function (Blueprint $table) {
            $table->renameColumn('customer_dropdown', 'consignee_dropdown');
        });

        // Modify & add columns
        Schema::table('logistic_settings', function (Blueprint $table) {
            $table->string('consignee_dropdown')
                ->default(json_encode(['consignee']))
                ->change();

            $table->string('consignor_dropdown')
                ->default(json_encode(['consignor']));
        });
    }

    public function down(): void
    {
        // Drop newly added column
        Schema::table('logistic_settings', function (Blueprint $table) {
            $table->dropColumn('consignor_dropdown');
        });

        // Rename column back
        Schema::table('logistic_settings', function (Blueprint $table) {
            $table->renameColumn('consignee_dropdown', 'customer_dropdown');
        });
    }
};
