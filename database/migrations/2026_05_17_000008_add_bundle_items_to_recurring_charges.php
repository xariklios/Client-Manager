<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recurring_charges', function (Blueprint $table) {
            $table->json('bundle_items')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('recurring_charges', function (Blueprint $table) {
            $table->dropColumn('bundle_items');
        });
    }
};
