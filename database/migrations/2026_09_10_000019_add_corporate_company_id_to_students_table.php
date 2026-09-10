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
        Schema::table('students', function (Blueprint $table) {
            // Set when this student is a corporate client's driver, quick-
            // enrolled straight from their company's driver roster - the
            // company sponsors (has already paid for) their training, so
            // this is what Enrollment::balance() checks to waive their
            // individual balance rather than tracking a second, redundant
            // payment for money the company already paid via its invoice.
            $table->foreignId('corporate_company_id')->nullable()->after('license_number')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('corporate_company_id');
        });
    }
};
