<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->decimal('hours', 6, 2);
            $table->boolean('is_billable')->default(true);
            $table->decimal('hourly_rate', 8, 2)->default(0);
            $table->date('date');
            $table->timestamps();

            // Strategic indexes for reporting queries
            $table->index('user_id');
            $table->index('task_id');
            $table->index('date');
            $table->index(['user_id', 'date']);
            $table->index('is_billable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
