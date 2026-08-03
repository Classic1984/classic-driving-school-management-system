<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('course_student', function (Blueprint $table) {
            $table->decimal('fee', 10, 2)->nullable()->after('course_id');
        });

        // Lock in the course's current fee for every existing enrollment, so
        // a future price change on the course only affects new enrollments.
        // Done per-course (rather than a single join-update) to stay
        // portable across the database engines this app runs on.
        DB::table('courses')->select('id', 'fee')->orderBy('id')->each(function ($course) {
            DB::table('course_student')->where('course_id', $course->id)->update(['fee' => $course->fee]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_student', function (Blueprint $table) {
            $table->dropColumn('fee');
        });
    }
};
