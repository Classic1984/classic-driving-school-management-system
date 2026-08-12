<?php

namespace Tests\Feature;

use App\Models\TheoryClassCancellation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TheoryClassCancellationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/theory-class-cancellations')->assertRedirect('/login');
    }

    public function test_a_secretary_cannot_manage_cancellations(): void
    {
        $secretary = User::factory()->secretary()->create();

        $this->actingAs($secretary)->get('/theory-class-cancellations')->assertForbidden();
        $this->actingAs($secretary)->post('/theory-class-cancellations', ['class_date' => now()->addDay()->toDateString()])->assertForbidden();
    }

    public function test_an_admin_cannot_manage_cancellations(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/theory-class-cancellations')->assertForbidden();
    }

    public function test_a_director_can_view_the_cancellation_list(): void
    {
        $director = User::factory()->director()->create();
        TheoryClassCancellation::factory()->create(['class_date' => now()->addDay(), 'reason' => 'Public holiday']);

        $this->actingAs($director)
            ->get('/theory-class-cancellations')
            ->assertOk()
            ->assertSee('Public holiday');
    }

    public function test_a_director_can_cancel_a_theory_class(): void
    {
        $director = User::factory()->director()->create();
        $date = now()->addDay()->toDateString();

        $this->actingAs($director)
            ->post('/theory-class-cancellations', ['class_date' => $date, 'reason' => 'Instructor unavailable'])
            ->assertRedirect(route('theory-class-cancellations.index'));

        $this->assertDatabaseHas('theory_class_cancellations', [
            'class_date' => $date,
            'reason' => 'Instructor unavailable',
            'cancelled_by' => $director->id,
        ]);
    }

    public function test_a_reason_is_optional(): void
    {
        $director = User::factory()->director()->create();

        $this->actingAs($director)
            ->post('/theory-class-cancellations', ['class_date' => now()->addDay()->toDateString()])
            ->assertSessionHasNoErrors();
    }

    public function test_a_past_date_cannot_be_cancelled(): void
    {
        $director = User::factory()->director()->create();

        $this->actingAs($director)
            ->post('/theory-class-cancellations', ['class_date' => now()->subDay()->toDateString()])
            ->assertSessionHasErrors('class_date');
    }

    public function test_the_same_date_cannot_be_cancelled_twice(): void
    {
        $director = User::factory()->director()->create();
        $date = now()->addDay();
        TheoryClassCancellation::factory()->create(['class_date' => $date]);

        $this->actingAs($director)
            ->post('/theory-class-cancellations', ['class_date' => $date->toDateString()])
            ->assertSessionHasErrors('class_date');
    }

    public function test_a_director_can_undo_a_cancellation(): void
    {
        $director = User::factory()->director()->create();
        $cancellation = TheoryClassCancellation::factory()->create(['class_date' => now()->addDay()]);

        $this->actingAs($director)
            ->delete("/theory-class-cancellations/{$cancellation->id}")
            ->assertRedirect(route('theory-class-cancellations.index'));

        $this->assertDatabaseMissing('theory_class_cancellations', ['id' => $cancellation->id]);
    }
}
