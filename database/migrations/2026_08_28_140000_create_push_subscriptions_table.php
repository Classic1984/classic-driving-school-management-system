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
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            // Any logged-in user - staff, instructor, or student - can
            // subscribe from their own dashboard, and the same account can
            // hold more than one subscription (a phone and a desktop
            // browser, say), so this is a plain FK, not unique.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // The browser's own subscription URL - globally unique by
            // construction (one endpoint is one specific browser
            // installation), which is what lets a re-subscribe from the
            // same device update its row instead of duplicating it.
            $table->string('endpoint', 500)->unique();
            $table->string('public_key')->nullable();
            $table->string('auth_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
