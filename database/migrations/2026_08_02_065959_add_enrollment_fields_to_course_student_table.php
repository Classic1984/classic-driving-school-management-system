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
            $table->date('enrolled_at')->nullable()->after('student_id');
            $table->date('due_date')->nullable()->after('enrolled_at');
            $table->string('status')->default('active')->after('due_date');
            $table->string('locked_reason')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_student', function (Blueprint $table) {
            $table->dropColumn(['enrolled_at', 'due_date', 'status', 'locked_reason']);
        });
    }
};
