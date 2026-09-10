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
        Schema::create('corporate_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corporate_company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('corporate_quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number')->nullable()->unique();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('programme_name')->nullable();
            $table->string('duration_label')->nullable();
            $table->unsignedInteger('participant_count')->nullable();
            // pending|sent|paid|cancelled - "overdue" is a computed display
            // state (due_date passed, not paid/cancelled), not stored here,
            // so it never needs a scheduled job to stay accurate.
            $table->string('status')->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corporate_invoices');
    }
};
