<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->decimal('original_price', 10, 2)->nullable()->after('total');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->date('domain_expiry_date')->nullable()->after('renewal_date');
        });
    }

    public function down(): void
    {
        Schema::table('offers',   fn($t) => $t->dropColumn('original_price'));
        Schema::table('projects', fn($t) => $t->dropColumn('domain_expiry_date'));
    }
};
