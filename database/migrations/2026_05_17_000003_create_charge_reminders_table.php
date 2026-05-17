<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charge_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('charge_id')->constrained()->cascadeOnDelete();
            $table->integer('days_offset'); // negative = days before due, positive = days after
            $table->timestamp('sent_at');

            $table->unique(['charge_id', 'days_offset']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charge_reminders');
    }
};
