<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Data-only migration: "level" was added to courses as nullable so
     * existing rows weren't forced to pick one, but that leaves the
     * certificate's Level stat blank for any course created before the
     * field existed. The create/edit form already defaults an unset level
     * to "beginner", so backfill existing null rows the same way - staff
     * can still correct any course that's actually intermediate/advanced
     * via Courses > Edit.
     */
    public function up(): void
    {
        DB::table('courses')
            ->whereNull('level')
            ->update(['level' => 'beginner']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not reversible: there's no way to tell which rows were backfilled
        // versus genuinely set to "beginner" by staff.
    }
};
