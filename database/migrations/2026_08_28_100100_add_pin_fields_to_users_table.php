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
        Schema::table('users', function (Blueprint $table) {
            // Instructor accounts log in with a phone number + PIN instead
            // of email + password (their `password` column is set to an
            // unusable random value so the normal staff login form can
            // never authenticate them). Null pin_set_at means the account
            // was granted but the instructor hasn't completed their
            // first-login OTP verification yet.
            $table->string('pin')->nullable();
            $table->timestamp('pin_set_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pin', 'pin_set_at']);
        });
    }
};
