<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('charges', function (Blueprint $table) {
            $table->foreignId('recurring_charge_id')
                ->nullable()
                ->after('id')
                ->nullOnDelete()
                ->constrained('recurring_charges');
        });
    }

    public function down(): void
    {
        Schema::table('charges', function (Blueprint $table) {
            $table->dropForeign(['recurring_charge_id']);
            $table->dropColumn('recurring_charge_id');
        });
    }
};
