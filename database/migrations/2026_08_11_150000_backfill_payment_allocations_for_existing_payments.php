<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convert every existing payment into a training payment allocation
     * against its matching enrollment, so Enrollment balances - which are
     * about to start reading from allocations instead of raw payments -
     * stay correct for historical data.
     */
    public function up(): void
    {
        DB::table('payments')->orderBy('id')->chunkById(200, function ($payments) {
            foreach ($payments as $payment) {
                $enrollment = DB::table('course_student')
                    ->where('student_id', $payment->student_id)
                    ->where('course_id', $payment->course_id)
                    ->first();

                if ($enrollment === null) {
                    continue;
                }

                DB::table('payment_allocations')->insert([
                    'payment_id' => $payment->id,
                    'allocation_type' => 'training',
                    'enrollment_id' => $enrollment->id,
                    'amount' => $payment->amount,
                    'created_at' => $payment->created_at,
                    'updated_at' => $payment->updated_at,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('payment_allocations')->where('allocation_type', 'training')->delete();
    }
};
