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
        // Make letters columns nullable
        Schema::table('letters', function (Blueprint $table) {
            $table->text('header_content')->nullable()->change();
            $table->text('body_content')->nullable()->change();
            $table->text('footer_content')->nullable()->change();
        });

        // Make data_formate columns nullable
        Schema::table('data_formate', function (Blueprint $table) {
            $table->text('header_content')->nullable()->change();
            $table->text('body_content')->nullable()->change();
            $table->text('footer_content')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert letters columns to NOT NULL
        Schema::table('letters', function (Blueprint $table) {
            $table->text('header_content')->nullable(false)->change();
            $table->text('body_content')->nullable(false)->change();
            $table->text('footer_content')->nullable(false)->change();
        });

        // Revert data_formate columns to NOT NULL
        Schema::table('data_formate', function (Blueprint $table) {
            $table->text('header_content')->nullable(false)->change();
            $table->text('body_content')->nullable(false)->change();
            $table->text('footer_content')->nullable(false)->change();
        });
    }
};