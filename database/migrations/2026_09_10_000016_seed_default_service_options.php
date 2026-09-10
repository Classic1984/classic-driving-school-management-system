<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fills in a starter set of line-item description suggestions (e.g.
     * "Certificate of Completion") so the quotation/invoice charges table
     * has something useful to pick from the first time a director opens
     * it, instead of an empty "no suggestions yet" state. Only touches the
     * row if the column is still blank, so it can never overwrite a value
     * a director has already typed and saved - same convention as the
     * training detail presets seed.
     */
    public function up(): void
    {
        $settingsId = DB::table('corporate_invoice_settings')->value('id');

        if ($settingsId === null) {
            return;
        }

        DB::table('corporate_invoice_settings')
            ->where('id', $settingsId)
            ->where(function ($query) {
                $query->whereNull('service_options')->orWhere('service_options', '');
            })
            ->update([
                'service_options' => implode("\n", [
                    'Certificate of Completion',
                    'Registration Fee',
                    'Training Materials',
                    'Assessment Fee',
                ]),
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * Deliberately a no-op: these are just starter suggestions a director
     * may since have edited, so there's nothing safe to "undo" back to.
     */
    public function down(): void {}
};
