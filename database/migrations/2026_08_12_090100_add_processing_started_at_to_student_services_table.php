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
        Schema::table('student_services', function (Blueprint $table) {
            // Stamped the moment processing_status first moves to
            // "processing", so progress toward the service's
            // processing_days turnaround can be tracked and shown on the
            // dashboard.
            $table->timestamp('processing_started_at')->nullable()->after('processing_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_services', function (Blueprint $table) {
            $table->dropColumn('processing_started_at');
        });
    }
};
