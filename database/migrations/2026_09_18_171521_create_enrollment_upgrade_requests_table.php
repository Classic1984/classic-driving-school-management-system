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
        Schema::create('enrollment_upgrade_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('course_student')->cascadeOnDelete();
            $table->foreignId('from_course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('to_course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            // 'duration' (the same-track, longer-programme upgrade) or
            // 'tier' (Weekend/Executive/VIP) - see Enrollment::canUpgrade()
            // / canUpgradeTier(). Display-only; both are executed the same
            // way on approval, via EnrollmentService::upgrade().
            $table->string('upgrade_type');
            $table->decimal('previous_fee', 10, 2);
            $table->decimal('new_fee', 10, 2);
            $table->decimal('upgrade_cost', 10, 2);
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_upgrade_requests');
    }
};
