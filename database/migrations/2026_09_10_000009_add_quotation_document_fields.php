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
        Schema::table('corporate_quotations', function (Blueprint $table) {
            // Mirrors the same descriptive fields on corporate_invoices, so
            // "Convert to Invoice" can carry the training details straight
            // across without the Director having to retype them.
            $table->string('programme_name')->nullable()->after('corporate_company_id');
            $table->string('duration_label')->nullable()->after('programme_name');
            $table->unsignedInteger('participant_count')->nullable()->after('duration_label');
            $table->text('course_coverage')->nullable()->after('participant_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corporate_quotations', function (Blueprint $table) {
            $table->dropColumn(['programme_name', 'duration_label', 'participant_count', 'course_coverage']);
        });
    }
};
