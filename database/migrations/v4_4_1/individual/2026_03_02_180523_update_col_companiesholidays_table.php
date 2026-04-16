<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companiesholidays', function (Blueprint $table) {
            
            // Drop old columns
            if (Schema::hasColumn('companiesholidays', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('companiesholidays', 'date')) {
                $table->dropColumn('date');
            }

            // Add new columns (all nullable)
            if (!Schema::hasColumn('companiesholidays', 'event_type')) {
                $table->text('event_type')->nullable()->after('id');
            }
            if (!Schema::hasColumn('companiesholidays', 'event_title')) {
                $table->text('event_title')->nullable()->after('event_type');
            }
            if (!Schema::hasColumn('companiesholidays', 'event_date')) {
                $table->dateTime('event_date')->nullable()->after('event_title');
            }
            if (!Schema::hasColumn('companiesholidays', 'description')) {
                $table->text('description')->nullable()->after('event_date');
            }
            if (!Schema::hasColumn('companiesholidays', 'employee_name')) {
                $table->text('employee_name')->nullable()->after('description');
            }
            if (!Schema::hasColumn('companiesholidays', 'candidate_name')) {
                $table->text('candidate_name')->nullable()->after('employee_name');
            }
            if (!Schema::hasColumn('companiesholidays', 'place_name')) {
                $table->text('place_name')->nullable()->after('candidate_name');
            }
            if (!Schema::hasColumn('companiesholidays', 'employee_id')) {
                $table->text('employee_id')->nullable()->after('place_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companiesholidays', function (Blueprint $table) {
            // Add back old columns
            if (!Schema::hasColumn('companiesholidays', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::hasColumn('companiesholidays', 'date')) {
                $table->date('date')->after('name');
            }

            // Drop new columns
            if (Schema::hasColumn('companiesholidays', 'event_type')) {
                $table->dropColumn('event_type');
            }
            if (Schema::hasColumn('companiesholidays', 'event_title')) {
                $table->dropColumn('event_title');
            }
            if (Schema::hasColumn('companiesholidays', 'event_date')) {
                $table->dropColumn('event_date');
            }
            if (Schema::hasColumn('companiesholidays', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('companiesholidays', 'employee_name')) {
                $table->dropColumn('employee_name');
            }
            if (Schema::hasColumn('companiesholidays', 'candidate_name')) {
                $table->dropColumn('candidate_name');
            }
            if (Schema::hasColumn('companiesholidays', 'place_name')) {
                $table->dropColumn('place_name');
            }
            if (Schema::hasColumn('companiesholidays', 'employee_id')) {
                $table->dropColumn('employee_id');
            }
        });
    }
};