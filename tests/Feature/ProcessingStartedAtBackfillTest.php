<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Covers the data-only migration that backfills processing_started_at for
 * any student_services row stuck with processing_status = "processing"
 * but no start date recorded - the gap that crashed the dashboard's
 * Service Processing widget until it was made null-safe.
 */
class ProcessingStartedAtBackfillTest extends TestCase
{
    use RefreshDatabase;

    protected function migration(): object
    {
        return require database_path('migrations/2026_08_17_042116_backfill_missing_processing_started_at.php');
    }

    public function test_it_backfills_a_missing_started_at_to_the_rows_updated_at(): void
    {
        $service = Service::factory()->create(['processing_days' => 30]);
        $student = Student::factory()->create();
        $studentServiceId = DB::table('student_services')->insertGetId([
            'student_id' => $student->id,
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'processing',
            'processing_started_at' => null,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(2),
        ]);

        $this->migration()->up();

        $row = DB::table('student_services')->find($studentServiceId);
        $this->assertNotNull($row->processing_started_at);
        $this->assertSame(
            now()->subDays(2)->toDateString(),
            Carbon::parse($row->processing_started_at)->toDateString()
        );
    }

    public function test_it_does_not_touch_a_row_that_already_has_a_started_at(): void
    {
        $service = Service::factory()->create(['processing_days' => 30]);
        $student = Student::factory()->create();
        $existingStart = now()->subDays(10);
        $studentServiceId = DB::table('student_services')->insertGetId([
            'student_id' => $student->id,
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'processing',
            'processing_started_at' => $existingStart,
            'created_at' => now()->subDays(20),
            'updated_at' => now()->subDay(),
        ]);

        $this->migration()->up();

        $row = DB::table('student_services')->find($studentServiceId);
        $this->assertSame(
            $existingStart->toDateString(),
            Carbon::parse($row->processing_started_at)->toDateString()
        );
    }

    public function test_it_does_not_touch_a_not_started_row(): void
    {
        $service = Service::factory()->create(['processing_days' => 30]);
        $student = Student::factory()->create();
        $studentServiceId = DB::table('student_services')->insertGetId([
            'student_id' => $student->id,
            'service_id' => $service->id,
            'price' => $service->price,
            'processing_status' => 'not_started',
            'processing_started_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->migration()->up();

        $row = DB::table('student_services')->find($studentServiceId);
        $this->assertNull($row->processing_started_at);
    }
}
