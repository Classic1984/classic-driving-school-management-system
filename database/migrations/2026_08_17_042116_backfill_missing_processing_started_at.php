<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Data-only migration: a StudentService can end up with
     * processing_status = "processing" but a null processing_started_at
     * (data predating this app version always stamping both together),
     * which crashed the dashboard's Service Processing widget until it
     * was made null-safe. Backfills the missing timestamp to the row's
     * own updated_at - the best available guess for when it was actually
     * marked "Processing" - so those rows show a real date instead of
     * "—" going forward.
     */
    public function up(): void
    {
        DB::table('student_services')
            ->where('processing_status', 'processing')
            ->whereNull('processing_started_at')
            ->update(['processing_started_at' => DB::raw('updated_at')]);
    }

    /**
     * Reverse the migrations.
     *
     * Deliberately a no-op: there's no way to distinguish a
     * backfilled timestamp from a genuinely-recorded one afterward, so
     * rolling back can't safely restore the prior null state without
     * risking clobbering real data recorded after this migration ran.
     */
    public function down(): void {}
};
