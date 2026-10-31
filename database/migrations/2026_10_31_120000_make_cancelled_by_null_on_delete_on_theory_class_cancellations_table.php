<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * cancelled_by was a required, RESTRICT-on-delete foreign key - unlike
     * every other "who did this" audit column in the app, which nulls out
     * instead of blocking the delete. That meant removing a staff account
     * that had ever cancelled a theory class threw an uncaught
     * QueryException instead of succeeding.
     */
    public function up(): void
    {
        Schema::table('theory_class_cancellations', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
        });

        Schema::table('theory_class_cancellations', function (Blueprint $table) {
            $table->foreignId('cancelled_by')->nullable()->change();
            $table->foreign('cancelled_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('theory_class_cancellations', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
        });

        Schema::table('theory_class_cancellations', function (Blueprint $table) {
            $table->foreignId('cancelled_by')->nullable(false)->change();
            $table->foreign('cancelled_by')->references('id')->on('users');
        });
    }
};
