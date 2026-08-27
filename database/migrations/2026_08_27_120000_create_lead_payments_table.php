<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lead_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            // The gateway's own transaction reference - the idempotency
            // key that stops a webhook retry (Paystack retries until it
            // gets a 200) from ever being processed twice.
            $table->string('reference')->unique();
            $table->string('gateway')->default('paystack');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('NGN');
            $table->string('status');
            $table->timestamp('paid_at');
            // The full webhook payload, kept for as long as this
            // integration is new enough that a raw record to fall back on
            // is worth more than the storage it costs.
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_payments');
    }
};
