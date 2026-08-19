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
        Schema::create('programme_upgrade_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('course_student')->cascadeOnDelete();
            $table->foreignId('from_course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('to_course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('upgraded_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('attended_days_at_upgrade');
            $table->decimal('previous_fee', 10, 2);
            $table->decimal('new_fee', 10, 2);
            $table->decimal('amount_charged', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programme_upgrade_logs');
    }
};
