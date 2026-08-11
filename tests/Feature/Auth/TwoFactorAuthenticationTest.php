<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function validCodeFor(User $user): string
    {
        return (new Google2FA)->getCurrentOtp($user->fresh()->two_factor_secret);
    }

    public function test_guests_are_redirected_to_login_from_two_factor_management_routes(): void
    {
        $this->post('/two-factor-authentication')->assertRedirect('/login');
        $this->post('/two-factor-authentication/confirm')->assertRedirect('/login');
        $this->post('/two-factor-authentication/recovery-codes')->assertRedirect('/login');
        $this->delete('/two-factor-authentication')->assertRedirect('/login');
    }

    public function test_enabling_two_factor_authentication_generates_a_pending_secret(): void
    {
        $user = User::factory()->withoutTwoFactor()->create();

        $this->actingAs($user)->post('/two-factor-authentication')->assertRedirect('/profile');

        $this->assertNotNull($user->fresh()->two_factor_secret);
        $this->assertFalse($user->fresh()->hasEnabledTwoFactorAuthentication());
    }

    public function test_the_profile_page_shows_a_qr_code_while_setup_is_pending(): void
    {
        $user = User::factory()->withoutTwoFactor()->create();
        $this->actingAs($user)->post('/two-factor-authentication');

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $response->assertSee('<svg', false);
    }

    public function test_confirming_with_a_valid_code_enables_two_factor_authentication_and_issues_recovery_codes(): void
    {
        $user = User::factory()->withoutTwoFactor()->create();
        $this->actingAs($user)->post('/two-factor-authentication');

        $response = $this->actingAs($user)->post('/two-factor-authentication/confirm', [
            'code' => $this->validCodeFor($user),
        ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHasNoErrors();
        $this->assertTrue($user->fresh()->hasEnabledTwoFactorAuthentication());
        $this->assertCount(8, json_decode($user->fresh()->two_factor_recovery_codes, true));
    }

    public function test_confirming_with_an_invalid_code_fails(): void
    {
        $user = User::factory()->withoutTwoFactor()->create();
        $this->actingAs($user)->post('/two-factor-authentication');

        $response = $this->actingAs($user)->post('/two-factor-authentication/confirm', [
            'code' => '000000',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertFalse($user->fresh()->hasEnabledTwoFactorAuthentication());
    }

    public function test_regenerating_recovery_codes_replaces_the_old_batch(): void
    {
        $user = User::factory()->withoutTwoFactor()->create();
        $this->actingAs($user)->post('/two-factor-authentication');
        $this->actingAs($user)->post('/two-factor-authentication/confirm', ['code' => $this->validCodeFor($user)]);
        $originalCodes = json_decode($user->fresh()->two_factor_recovery_codes, true);

        $response = $this->actingAs($user)->post('/two-factor-authentication/recovery-codes');

        $response->assertRedirect('/profile');
        $newCodes = json_decode($user->fresh()->two_factor_recovery_codes, true);
        $this->assertCount(8, $newCodes);
        $this->assertNotSame($originalCodes, $newCodes);
    }

    public function test_regenerating_recovery_codes_requires_two_factor_to_already_be_enabled(): void
    {
        $user = User::factory()->withoutTwoFactor()->create();

        $this->actingAs($user)->post('/two-factor-authentication/recovery-codes')->assertStatus(400);
    }

    public function test_disabling_two_factor_authentication_clears_it_completely(): void
    {
        $user = User::factory()->withoutTwoFactor()->create();
        $this->actingAs($user)->post('/two-factor-authentication');
        $this->actingAs($user)->post('/two-factor-authentication/confirm', ['code' => $this->validCodeFor($user)]);

        $response = $this->actingAs($user)->delete('/two-factor-authentication');

        $response->assertRedirect('/profile');
        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
    }
}
