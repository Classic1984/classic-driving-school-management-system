<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The current monthly salary for each paid person - a staff User
     * (director/secretary/accountant/admin) or an Instructor. One row per
     * person; Director edits it in place rather than versioning it, since
     * every PayrollRunItem already keeps its own amount snapshot at the
     * time it was paid, which is the real historical record.
     */
    public function up(): void
    {
        Schema::create('payroll_salaries', function (Blueprint $table) {
            $table->id();
            $table->string('payee_type');
            $table->unsignedBigInteger('payee_id');
            $table->decimal('monthly_amount', 10, 2);
            $table->boolean('active')->default(true);
            $table->foreignId('set_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['payee_type', 'payee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_salaries');
    }
};
