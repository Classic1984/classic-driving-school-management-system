<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One payroll run per calendar month. A run starts as a 'draft' - a
     * snapshot of who's on the active salary roster and what they're
     * owed - that the Director reviews before processing it; processing
     * is what actually creates the Expense rows that move money.
     */
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->date('month');
            $table->string('status')->default('draft');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique('month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_runs');
    }
};
