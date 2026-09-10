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
        Schema::table('corporate_quotations', function (Blueprint $table) {
            $table->foreignId('converted_invoice_id')->nullable()->after('sent_at')->constrained('corporate_invoices')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corporate_quotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('converted_invoice_id');
        });
    }
};
