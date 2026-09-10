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
        Schema::create('corporate_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corporate_company_id')->constrained()->cascadeOnDelete();
            $table->string('quotation_number')->nullable()->unique();
            // draft|sent|approved|rejected|expired|converted
            $table->string('status')->default('draft');
            $table->date('issue_date');
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            // converted_invoice_id is added in a later migration, once the
            // corporate_invoices table exists - the two tables reference
            // each other (a quotation optionally links to the invoice it
            // became, an invoice optionally links back to the quotation it
            // came from), so one side of that pair has to be added after
            // both tables exist.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corporate_quotations');
    }
};
