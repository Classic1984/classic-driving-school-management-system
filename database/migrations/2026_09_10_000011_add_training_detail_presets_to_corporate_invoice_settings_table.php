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
            // own suggestions (via <datalist>) on the Programme, Duration,
            // and Number of Drivers fields when creating a quotation or
            // invoice - a plain preset list set once here, not a separate
            // managed table, same convention as payment_terms.
            $table->text('programme_options')->nullable()->after('payment_terms');
            $table->text('duration_options')->nullable()->after('programme_options');
            $table->text('driver_count_options')->nullable()->after('duration_options');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corporate_invoice_settings', function (Blueprint $table) {
            $table->dropColumn(['programme_options', 'duration_options', 'driver_count_options']);
        });
    }
};
