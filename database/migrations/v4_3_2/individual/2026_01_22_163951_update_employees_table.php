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
            $table->renameColumn('address', 'house_no_building_name');
            $table->renameColumn('bank_details', 'holder_name');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('house_no_building_name')->nullable()->change();

            $table->string('road_name_area_colony')->nullable()->after('house_no_building_name');
            $table->unsignedBigInteger('country_id')->nullable()->after('road_name_area_colony');
            $table->unsignedBigInteger('state_id')->nullable()->after('country_id');
            $table->unsignedBigInteger('city_id')->nullable()->after('state_id');
            $table->string('pincode', 10)->nullable()->after('city_id');

            $table->string('holder_name')->nullable()->change();
            $table->string('account_no', 50)->after('holder_name');
            $table->string('swift_code', 20)->nullable()->after('account_no');
            $table->string('ifsc_code', 20)->after('swift_code');
            $table->string('branch_name', 50)->nullable()->after('ifsc_code');
            $table->string('bank_name', 50)->after('branch_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('house_no_building_name', 'address');
            $table->renameColumn('holder_name', 'bank_details');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'road_name_area_colony',
                'country_id',
                'state_id',
                'city_id',
                'pincode',
                'account_no',
                'swift_code',
                'ifsc_code',
                'branch_name',
                'bank_name',
            ]);
        });
    }
};
