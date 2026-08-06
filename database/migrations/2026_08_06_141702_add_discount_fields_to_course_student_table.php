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
        Schema::table('course_student', function (Blueprint $table) {
            $table->decimal('original_fee', 10, 2)->nullable()->after('fee');
            $table->decimal('discount_percentage', 5, 2)->nullable()->after('original_fee');
            $table->decimal('discount_amount', 10, 2)->nullable()->after('discount_percentage');
            $table->string('discount_reason')->nullable()->after('discount_amount');
            $table->string('discount_reason_note')->nullable()->after('discount_reason');
            $table->foreignId('discount_approved_by')->nullable()->after('discount_reason_note')->constrained('users')->nullOnDelete();
        });

        // Existing enrollments never had a discount, so their original fee
        // is simply their already-locked-in fee.
        DB::table('course_student')->update(['original_fee' => DB::raw('fee')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_student', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_approved_by');
            $table->dropColumn(['original_fee', 'discount_percentage', 'discount_amount', 'discount_reason', 'discount_reason_note']);
        });
    }
};
