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
        Schema::table('corporate_company_drivers', function (Blueprint $table) {
            // Set once this driver has been quick-enrolled into a real
            // Student record, so the company page can show "View Student"
            // instead of offering to enroll them again.
            $table->foreignId('student_id')->nullable()->after('license_number')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corporate_company_drivers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('student_id');
        });
    }
};
