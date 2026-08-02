<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('student_id_number')->nullable()->unique()->after('id');
        });

        DB::table('students')->orderBy('id')->each(function (object $student) {
            DB::table('students')->where('id', $student->id)->update([
                'student_id_number' => 'CDS-'.str_pad((string) $student->id, 5, '0', STR_PAD_LEFT),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('student_id_number');
        });
    }
};
