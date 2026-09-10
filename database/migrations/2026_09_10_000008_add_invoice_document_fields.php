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
        Schema::table('corporate_invoices', function (Blueprint $table) {
            // Free-text, one topic per line - typed per invoice rather than
            // a fixed list, since the topics covered vary by programme.
            // Hidden on the printed document when left blank.
            $table->text('course_coverage')->nullable()->after('participant_count');
        });

        Schema::table('corporate_invoice_settings', function (Blueprint $table) {
            $table->string('tagline')->nullable()->after('company_name');
            $table->string('slogan')->nullable()->after('tagline');
            // Boilerplate policy bullets (one per line) shown on every
            // invoice's Payment Terms box - set once here rather than
            // retyped per invoice.
            $table->text('payment_terms')->nullable()->after('receipt_prefix');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corporate_invoices', function (Blueprint $table) {
            $table->dropColumn('course_coverage');
        });

        Schema::table('corporate_invoice_settings', function (Blueprint $table) {
            $table->dropColumn(['tagline', 'slogan', 'payment_terms']);
        });
    }
};
