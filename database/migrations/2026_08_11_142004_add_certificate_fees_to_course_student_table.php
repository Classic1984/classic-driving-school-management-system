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
        Schema::table('course_student', function (Blueprint $table) {
            // Nullable: an enrollment only carries a certificate fee once the
            // student has actually been charged for that certificate type.
            // The price is snapshotted here from the course's outline price
            // at charge time, the same way the training `fee` column is.
            $table->decimal('online_certificate_fee', 10, 2)->nullable()->after('training_reminder_sent_at');
            $table->decimal('student_certificate_fee', 10, 2)->nullable()->after('online_certificate_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_student', function (Blueprint $table) {
            $table->dropColumn(['online_certificate_fee', 'student_certificate_fee']);
        });
    }
};
