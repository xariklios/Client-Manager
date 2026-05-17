<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recurring_charges', function (Blueprint $table) {
            $table->tinyInteger('interval_years')->default(1)->after('due_month');
            $table->smallInteger('start_year')->after('interval_years');
        });

        // Set start_year = current year for all existing records
        DB::table('recurring_charges')->update(['start_year' => now()->year]);
    }

    public function down(): void
    {
        Schema::table('recurring_charges', function (Blueprint $table) {
            $table->dropColumn(['interval_years', 'start_year']);
        });
    }
};
