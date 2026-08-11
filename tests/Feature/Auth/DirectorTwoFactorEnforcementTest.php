<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectorTwoFactorEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_director_without_two_factor_confirmed_is_redirected_to_the_profile_page(): void
    {
        $director = User::factory()->director()->withoutTwoFactor()->create();

        $this->actingAs($director)->get('/dashboard')->assertRedirect('/profile');
        $this->actingAs($director)->get('/courses')->assertRedirect('/profile');
        $this->actingAs($director)->get('/students')->assertRedirect('/profile');
    }

    public function test_a_director_without_two_factor_confirmed_can_still_reach_the_profile_and_setup_routes(): void
    {
        $director = User::factory()->director()->withoutTwoFactor()->create();

        $this->actingAs($director)->get('/profile')->assertOk();
        $this->actingAs($director)->post('/two-factor-authentication')->assertRedirect('/profile');
    }

    public function test_a_director_with_two_factor_confirmed_has_full_access(): void
    {
        $director = User::factory()->director()->create();

        $this->actingAs($director)->get('/dashboard')->assertOk();
        $this->actingAs($director)->get('/courses')->assertOk();
    }

    public function test_secretary_and_admin_accounts_are_not_gated_by_two_factor_enforcement(): void
    {
        $secretary = User::factory()->secretary()->withoutTwoFactor()->create();
        $admin = User::factory()->admin()->withoutTwoFactor()->create();

        $this->actingAs($secretary)->get('/dashboard')->assertOk();
        $this->actingAs($admin)->get('/dashboard')->assertOk();
    }
}
