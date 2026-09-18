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
        Schema::table('courses', function (Blueprint $table) {
            // Null for a standard programme. A tiered programme (weekend,
            // executive, VIP) is what a student in any standard programme
            // can upgrade into at any time - see
            // Enrollment::eligibleTierUpgrades() - independent of the
            // course_type/schedule/duration_weeks match the ordinary
            // "longer programme" upgrade requires.
            $table->string('tier')->nullable()->after('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('tier');
        });
    }
};
