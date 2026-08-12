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
        Schema::table('services', function (Blueprint $table) {
            // How many days this service's real-world processing typically
            // takes once started, e.g. 30 for Driver's License Processing -
            // null for services with no meaningful turnaround to track
            // (Learner's Permit is usually issued same-day).
            $table->unsignedInteger('processing_days')->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('processing_days');
        });
    }
};
