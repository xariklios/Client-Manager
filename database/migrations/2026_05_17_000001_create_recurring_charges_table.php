<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->nullOnDelete()->constrained();
            $table->string('title');
            $table->decimal('amount', 10, 2);
            $table->unsignedTinyInteger('due_day');
            $table->unsignedTinyInteger('due_month');
            $table->boolean('notify_client')->default(true);
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_charges');
    }
};
