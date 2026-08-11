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
        Schema::table('student_services', function (Blueprint $table) {
            // Deliberately independent of payment status - a service like
            // Driver's License Processing can be fully paid for while its
            // actual processing hasn't started, or vice versa.
            $table->string('processing_status')->default('not_started')->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_services', function (Blueprint $table) {
            $table->dropColumn('processing_status');
        });
    }
};
