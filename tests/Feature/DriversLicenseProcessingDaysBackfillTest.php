<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Covers the data-only migration that backfills processing_days for the
 * "Driver's License Processing" service: without it, that service never
 * met the dashboard Service Processing widget's whereNotNull condition,
 * so a charge marked "Processing" would silently never show up there.
 */
class DriversLicenseProcessingDaysBackfillTest extends TestCase
{
    use RefreshDatabase;

    protected function migration(): object
    {
        return require database_path('migrations/2026_08_15_100000_set_processing_days_for_drivers_license_service.php');
    }

    public function test_it_backfills_processing_days_for_a_drivers_license_service_missing_it(): void
    {
        DB::table('services')->insert([
            'name' => "Driver's License Processing",
            'price' => 50000,
            'is_active' => 1,
            'processing_days' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->migration()->up();

        $this->assertDatabaseHas('services', [
            'name' => "Driver's License Processing",
            'processing_days' => 30,
        ]);
    }

    public function test_it_does_not_override_an_already_set_processing_days_value(): void
    {
        DB::table('services')->insert([
            'name' => "Driver's License Processing",
            'price' => 50000,
            'is_active' => 1,
            'processing_days' => 45,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->migration()->up();

        $this->assertDatabaseHas('services', [
            'name' => "Driver's License Processing",
            'processing_days' => 45,
        ]);
    }

    public function test_it_does_not_affect_other_services(): void
    {
        DB::table('services')->insert([
            'name' => "Learner's Permit",
            'price' => 6000,
            'is_active' => 1,
            'processing_days' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->migration()->up();

        $this->assertDatabaseHas('services', [
            'name' => "Learner's Permit",
            'processing_days' => null,
        ]);
    }
}
