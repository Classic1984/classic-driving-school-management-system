<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fills in the school's website so it prints on every quotation,
     * invoice, and receipt without the Director having to type it in
     * first. Only touches the row if the column is still blank, so it can
     * never overwrite a value a director has already typed and saved -
     * same convention as the training detail presets seed.
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
                $query->whereNull('website')->orWhere('website', '');
            })
            ->update(['website' => 'classicdriving.com.ng']);
    }

    /**
     * Reverse the migrations.
     *
     * Deliberately a no-op: this is just a starter value a director may
     * since have edited, so there's nothing safe to "undo" back to.
     */
    public function down(): void {}
};
