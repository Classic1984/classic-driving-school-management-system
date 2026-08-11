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
        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('online_certificate_fee', 10, 2)->nullable()->after('fee');
            $table->decimal('student_certificate_fee', 10, 2)->nullable()->after('online_certificate_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['online_certificate_fee', 'student_certificate_fee']);
        });
    }
};
