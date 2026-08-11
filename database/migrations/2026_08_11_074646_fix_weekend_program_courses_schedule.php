<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The "Weekend Program" courses were seeded before the `schedule`
     * column existed and were never backfilled, so they silently landed
     * on the column's `weekday` default despite their name. One-time data
     * fix - not tied to the seeder, since it must also correct rows
     * already sitting in production.
     */
    public function up(): void
    {
        DB::table('courses')
            ->where('name', 'like', 'Weekend Program%')
            ->update(['schedule' => 'weekend']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('courses')
            ->where('name', 'like', 'Weekend Program%')
            ->update(['schedule' => 'weekday']);
    }
};
