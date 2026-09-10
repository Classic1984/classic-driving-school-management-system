<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Appends "Feeding Allowance" to the existing service_options
     * suggestions (e.g. "Certificate of Completion") for a company that
     * needs the school to feed drivers during training. Appends rather
     * than overwrites, and skips rows that already have it, so a
     * director's own customizations to this list are never touched -
     * same convention as the earlier default-service-options seed, just
     * additive since that seed already ran for every existing row.
     */
    public function up(): void
    {
        $settings = DB::table('corporate_invoice_settings')->get(['id', 'service_options']);

        foreach ($settings as $row) {
            $lines = collect(preg_split('/\r\n|\r|\n/', (string) $row->service_options))
                ->map(fn ($line) => trim($line))
                ->filter()
                ->values();

            if ($lines->contains('Feeding Allowance')) {
                continue;
            }

            DB::table('corporate_invoice_settings')
                ->where('id', $row->id)
                ->update(['service_options' => $lines->push('Feeding Allowance')->implode("\n")]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * Deliberately a no-op: this is just a starter suggestion a director
     * may since have edited, so there's nothing safe to "undo" back to.
     */
    public function down(): void {}
};
