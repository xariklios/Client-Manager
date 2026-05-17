<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->unsignedTinyInteger('vat_rate')->default(24)->after('total');
        });

        Schema::table('charges', function (Blueprint $table) {
            $table->unsignedTinyInteger('vat_rate')->default(24)->after('amount');
        });

        Schema::table('recurring_charges', function (Blueprint $table) {
            $table->unsignedTinyInteger('vat_rate')->default(24)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('offers',            fn($t) => $t->dropColumn('vat_rate'));
        Schema::table('charges',           fn($t) => $t->dropColumn('vat_rate'));
        Schema::table('recurring_charges', fn($t) => $t->dropColumn('vat_rate'));
    }
};
