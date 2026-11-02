<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * corrected_by and reversed_by used cascadeOnDelete(), unlike
     * payments.recorded_by which correctly nulls out - so deleting a staff
     * account that had ever corrected or reversed a payment silently
     * deleted that permanent audit row along with it, erasing the very
     * trail these tables exist to keep.
     */
    public function up(): void
    {
        Schema::table('payment_correction_logs', function (Blueprint $table) {
            $table->dropForeign(['corrected_by']);
        });

        Schema::table('payment_correction_logs', function (Blueprint $table) {
            $table->foreignId('corrected_by')->nullable()->change();
            $table->foreign('corrected_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('payment_reversals', function (Blueprint $table) {
            $table->dropForeign(['reversed_by']);
        });

        Schema::table('payment_reversals', function (Blueprint $table) {
            $table->foreignId('reversed_by')->nullable()->change();
            $table->foreign('reversed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_correction_logs', function (Blueprint $table) {
            $table->dropForeign(['corrected_by']);
        });

        Schema::table('payment_correction_logs', function (Blueprint $table) {
            $table->foreignId('corrected_by')->nullable(false)->change();
            $table->foreign('corrected_by')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('payment_reversals', function (Blueprint $table) {
            $table->dropForeign(['reversed_by']);
        });

        Schema::table('payment_reversals', function (Blueprint $table) {
            $table->foreignId('reversed_by')->nullable(false)->change();
            $table->foreign('reversed_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
