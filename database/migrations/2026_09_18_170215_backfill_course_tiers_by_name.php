<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Data-only migration: "tier" was added to courses as nullable, but
     * the school's existing Weekend/Executive/VIP programmes were already
     * seeded by name before this column existed - see
     * CoursePriceListSeeder. Tag those known rows by name so the new
     * "upgrade to a tiered programme" feature works immediately, without
     * requiring the seeder to be re-run in production.
     */
    public function up(): void
    {
        DB::table('courses')
            ->where('name', 'like', 'Weekend Program%')
            ->whereNull('tier')
            ->update(['tier' => 'weekend']);

        DB::table('courses')
            ->where(fn ($query) => $query->where('name', 'like', 'Executive Program%')->orWhere('name', 'like', 'Executive Training%'))
            ->whereNull('tier')
            ->update(['tier' => 'executive']);

        DB::table('courses')
            ->where('name', 'like', 'VIP Program%')
            ->whereNull('tier')
            ->update(['tier' => 'vip']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('courses')
            ->whereIn('tier', ['weekend', 'executive', 'vip'])
            ->update(['tier' => null]);
    }
};
