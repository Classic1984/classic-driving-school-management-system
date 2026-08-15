<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Data-only migration: the "Driver's License Processing" service was
     * never seeded with a processing_days value, so it silently never met
     * the dashboard Service Processing widget's whereNotNull('processing_days')
     * condition - staff could charge a student and mark it "Processing"
     * and it would still never appear. Backfills the real-world 30-day
     * turnaround, but only where it hasn't already been set some other way
     * (e.g. a Director editing it manually via Admin > Services).
     */
    public function up(): void
    {
        DB::table('services')
            ->where('name', "Driver's License Processing")
            ->whereNull('processing_days')
            ->update(['processing_days' => 30]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('services')
            ->where('name', "Driver's License Processing")
            ->where('processing_days', 30)
            ->update(['processing_days' => null]);
    }
};
