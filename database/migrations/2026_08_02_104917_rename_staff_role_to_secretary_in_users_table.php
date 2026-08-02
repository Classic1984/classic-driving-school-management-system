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
        DB::table('users')->where('role', 'staff')->update(['role' => 'secretary']);

        // New self-registrations default to the restricted "admin" role
        // (view everything, create/edit students/attendance/payments/tickets,
        // no deletion rights, no course/instructor management, no Finance)
        // rather than the elevated "secretary" role.
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('staff')->change();
        });

        DB::table('users')->where('role', 'secretary')->update(['role' => 'staff']);
    }
};
