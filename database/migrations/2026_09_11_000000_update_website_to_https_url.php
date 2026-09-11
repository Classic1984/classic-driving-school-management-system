<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Director-requested change: the website printed on every quotation,
     * invoice, and receipt becomes the full https:// URL instead of the
     * bare domain. Unlike the earlier "seed a starter value" migrations in
     * this file group, this deliberately overwrites whatever is currently
     * saved - it's a direct instruction to change the value, not a
     * blank-only default.
     */
    public function up(): void
    {
        $settingsId = DB::table('corporate_invoice_settings')->value('id');

        if ($settingsId === null) {
            return;
        }

        DB::table('corporate_invoice_settings')
            ->where('id', $settingsId)
            ->update(['website' => 'https://classicdriving.com.ng']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $settingsId = DB::table('corporate_invoice_settings')->value('id');

        if ($settingsId === null) {
            return;
        }

        DB::table('corporate_invoice_settings')
            ->where('id', $settingsId)
            ->where('website', 'https://classicdriving.com.ng')
            ->update(['website' => 'classicdriving.com.ng']);
    }
};
