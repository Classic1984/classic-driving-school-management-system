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
        Schema::table('corporate_invoice_settings', function (Blueprint $table) {
            // Document numbers (INV-2026-00001, QUO-2026-00001, REC-
            // 2026-00001) used to be derived straight from the row's own
            // auto-increment id, which never resets - by year 2027 the
            // first invoice would carry on from wherever 2026 left off
            // instead of starting over at 00001. These track a running
            // count per document type, reset whenever the year changes
            // (see CorporateInvoiceSetting::nextSequence()).
            $table->unsignedSmallInteger('quotation_numbering_year')->nullable()->after('quotation_prefix');
            $table->unsignedInteger('quotation_numbering_sequence')->default(0)->after('quotation_numbering_year');
            $table->unsignedSmallInteger('invoice_numbering_year')->nullable()->after('invoice_prefix');
            $table->unsignedInteger('invoice_numbering_sequence')->default(0)->after('invoice_numbering_year');
            $table->unsignedSmallInteger('receipt_numbering_year')->nullable()->after('receipt_prefix');
            $table->unsignedInteger('receipt_numbering_sequence')->default(0)->after('receipt_numbering_year');
        });

        // Seed each counter from whatever's already been issued this year,
        // so the next number picks up after the highest one already in
        // use instead of colliding with it - existing invoice/quotation/
        // receipt numbers themselves are never touched or renumbered.
        $settingsId = DB::table('corporate_invoice_settings')->value('id');

        if ($settingsId === null) {
            return;
        }

        $currentYear = (int) now()->format('Y');

        DB::table('corporate_invoice_settings')->where('id', $settingsId)->update([
            'quotation_numbering_year' => $currentYear,
            'quotation_numbering_sequence' => DB::table('corporate_quotations')->whereYear('issue_date', $currentYear)->count(),
            'invoice_numbering_year' => $currentYear,
            'invoice_numbering_sequence' => DB::table('corporate_invoices')->whereYear('invoice_date', $currentYear)->count(),
            'receipt_numbering_year' => $currentYear,
            'receipt_numbering_sequence' => DB::table('corporate_payments')->whereYear('payment_date', $currentYear)->count(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corporate_invoice_settings', function (Blueprint $table) {
            $table->dropColumn([
                'quotation_numbering_year',
                'quotation_numbering_sequence',
                'invoice_numbering_year',
                'invoice_numbering_sequence',
                'receipt_numbering_year',
                'receipt_numbering_sequence',
            ]);
        });
    }
};
