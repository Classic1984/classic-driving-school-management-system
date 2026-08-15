<?php

namespace Tests\Feature;

use App\Console\Commands\RecordSchedulerHeartbeat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SchedulerHeartbeatTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_the_current_time_in_the_cache(): void
    {
        $this->travelTo(now()->setMicroseconds(0));

        $this->artisan('app:scheduler-heartbeat')->assertExitCode(0);

        $this->assertTrue(now()->equalTo(Cache::get(RecordSchedulerHeartbeat::CACHE_KEY)));
    }
}
