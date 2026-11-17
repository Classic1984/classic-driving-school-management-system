<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('requested_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending')->after('description');
            $table->foreignId('resolved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');
            $table->string('rejection_reason')->nullable()->after('resolved_at');
            $table->timestamp('paid_at')->nullable()->after('rejection_reason');
        });

        // Every expense recorded before this feature existed was entered
        // directly by a Director and was immediately effective - treat
        // all of them as already paid, so historical totals don't
        // suddenly drop to zero just because they predate the status
        // column.
        DB::table('expenses')->update(['status' => 'paid', 'paid_at' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['requested_by']);
            $table->dropForeign(['resolved_by']);
            $table->dropColumn(['requested_by', 'status', 'resolved_by', 'resolved_at', 'rejection_reason', 'paid_at']);
        });
    }
};
