<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Same bug shape as the payment correction/reversal fix: these "who
     * did this" columns used cascadeOnDelete(), so removing a staff
     * account silently deleted the row along with it - for the four
     * "_requests" tables, that could delete a still-PENDING approval
     * request out from under the Director's queue with no trace, not just
     * lose an audit trail.
     */
    public function up(): void
    {
        $this->nullOnDelete('discount_audit_logs', 'applied_by');
        $this->nullOnDelete('reactivation_audit_logs', 'reactivated_by');
        $this->nullOnDelete('programme_upgrade_logs', 'upgraded_by');
        $this->nullOnDelete('discount_requests', 'requested_by');
        $this->nullOnDelete('assessment_requests', 'requested_by');
        $this->nullOnDelete('student_correction_requests', 'requested_by');
        $this->nullOnDelete('enrollment_upgrade_requests', 'requested_by');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->cascadeOnDelete('discount_audit_logs', 'applied_by');
        $this->cascadeOnDelete('reactivation_audit_logs', 'reactivated_by');
        $this->cascadeOnDelete('programme_upgrade_logs', 'upgraded_by');
        $this->cascadeOnDelete('discount_requests', 'requested_by');
        $this->cascadeOnDelete('assessment_requests', 'requested_by');
        $this->cascadeOnDelete('student_correction_requests', 'requested_by');
        $this->cascadeOnDelete('enrollment_upgrade_requests', 'requested_by');
    }

    private function nullOnDelete(string $table, string $column): void
    {
        Schema::table($table, function (Blueprint $table) use ($column) {
            $table->dropForeign([$column]);
        });

        Schema::table($table, function (Blueprint $table) use ($column) {
            $table->foreignId($column)->nullable()->change();
            $table->foreign($column)->references('id')->on('users')->nullOnDelete();
        });
    }

    private function cascadeOnDelete(string $table, string $column): void
    {
        Schema::table($table, function (Blueprint $table) use ($column) {
            $table->dropForeign([$column]);
        });

        Schema::table($table, function (Blueprint $table) use ($column) {
            $table->foreignId($column)->nullable(false)->change();
            $table->foreign($column)->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
