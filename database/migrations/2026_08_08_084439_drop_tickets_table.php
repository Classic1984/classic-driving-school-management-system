<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The Daily Training Ticket feature is removed entirely, replaced by
     * Student Login Training (recorded directly on the attendances table).
     */
    public function up(): void
    {
        Schema::dropIfExists('tickets');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->string('vehicle')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('payment_status');
            $table->timestamps();
            $table->unique(['student_id', 'course_id', 'date']);
        });
    }
};
