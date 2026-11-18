<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One line per transaction on the uploaded/entered bank statement -
     * signed amount (positive = money in, negative = money out), and
     * where it currently stands against the school's own paid Payments
     * and paid Expenses for the period.
     */
    public function up(): void
    {
        Schema::create('bank_statement_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_reconciliation_id')->constrained()->cascadeOnDelete();
            $table->date('line_date');
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('unmatched');
            $table->string('matched_type')->nullable();
            $table->unsignedBigInteger('matched_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_statement_lines');
    }
};
