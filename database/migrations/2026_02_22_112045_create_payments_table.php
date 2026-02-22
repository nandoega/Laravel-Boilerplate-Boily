<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('method')->nullable();      // cash, bank_transfer, card, etc.
            $table->string('reference')->nullable();   // transaction ref
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->boolean('is_refunded')->default(false);
            $table->timestamp('refunded_at')->nullable();
            $table->text('refund_reason')->nullable();
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('is_refunded');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
