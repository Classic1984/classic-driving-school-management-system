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
            $table->date('reactivated_at')->nullable()->after('locked_reason');
            $table->decimal('reactivation_fee', 10, 2)->nullable()->after('reactivated_at');
            $table->foreignId('reactivated_by')->nullable()->after('reactivation_fee')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_student', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reactivated_by');
            $table->dropColumn(['reactivated_at', 'reactivation_fee']);
        });
    }
};
