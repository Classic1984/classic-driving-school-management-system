<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('receipt_number')->nullable()->unique()->after('reference_number');
            $table->foreignId('recorded_by')->nullable()->after('receipt_number')->constrained('users')->nullOnDelete();
        });

        // Existing payments never had a receipt number - backfill one in
        // the same CDS-RC-{payment year}-{00001} form new payments get,
        // derived from each row's own id like Certificate::certificate_number
        // already is.
        DB::table('payments')->orderBy('id')->chunkById(200, function ($payments) {
            foreach ($payments as $payment) {
                DB::table('payments')->where('id', $payment->id)->update([
                    'receipt_number' => sprintf(
                        'CDS-RC-%s-%s',
                        Carbon::parse($payment->payment_date)->format('Y'),
                        str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT)
                    ),
                ]);
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('receipt_number')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recorded_by');
            $table->dropColumn('receipt_number');
        });
    }
};
