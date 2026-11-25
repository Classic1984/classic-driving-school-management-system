<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('training_scan_token', 64)->nullable()->unique()->after('student_id_number');
        });

        DB::table('students')->whereNull('training_scan_token')->orderBy('id')->each(function (object $student) {
            DB::table('students')->where('id', $student->id)->update([
                'training_scan_token' => Str::random(40),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('training_scan_token');
        });
    }
};
