<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One line per paid person on a given payroll run, with the payee's
     * name and amount snapshotted at the time the run was generated -
     * so a later salary change, or the person leaving, never rewrites
     * what an old payslip says they were paid. expense_id is only set
     * once the run is processed and the line becomes a real Expense.
     */
    public function up(): void
    {
        Schema::create('payroll_run_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->string('payee_type');
            $table->unsignedBigInteger('payee_id');
            $table->string('payee_name');
            $table->decimal('amount', 10, 2);
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_run_items');
    }
};
