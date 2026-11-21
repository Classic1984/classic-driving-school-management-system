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
        Schema::create('desk_stations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // The secret the on-site camera agent authenticates with when
            // it pings its status in - a URL param rather than a user
            // login, since nothing with a human attached is on the other
            // end of that request.
            $table->string('api_token', 64)->unique();
            $table->string('status')->default('unknown');
            $table->timestamp('status_changed_at')->nullable();
            // When the agent last pinged at all, regardless of whether the
            // status changed - used to tell "empty" apart from "offline"
            // (nothing has reported in for a while).
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desk_stations');
    }
};
