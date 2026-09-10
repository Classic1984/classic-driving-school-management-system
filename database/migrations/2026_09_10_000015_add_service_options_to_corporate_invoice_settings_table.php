<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('corporate_invoice_settings', function (Blueprint $table) {
            // One value per line, offered as pick-from-a-list-or-type-your-
            // own suggestions on a quotation/invoice's line item
            // description field (e.g. "Certificate of Completion") - same
            // convention as programme_options/duration_options.
            $table->text('service_options')->nullable()->after('driver_count_options');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corporate_invoice_settings', function (Blueprint $table) {
            $table->dropColumn('service_options');
        });
    }
};
