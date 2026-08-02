<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_live_counts_and_totals(): void
    {
        $user = User::factory()->create();

        Student::factory()->count(3)->create();
        Instructor::factory()->count(2)->create();
        Certificate::factory()->count(4)->create();

        Payment::factory()->create(['amount' => 500, 'status' => 'paid']);
        Payment::factory()->create(['amount' => 300, 'status' => 'paid']);
        // Not counted towards the payments total.
        Payment::factory()->create(['amount' => 999, 'status' => 'pending']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('3');
        $response->assertSee('800.00');
        $response->assertSee('2');
        $response->assertSee('4');
    }

    public function test_dashboard_shows_zeroes_when_there_is_no_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('₦0.00', false);
    }
}
