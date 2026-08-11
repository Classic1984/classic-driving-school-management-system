<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorChallengeTest extends TestCase
{
    use RefreshDatabase;

    protected function twoFactorUser(): User
    {
        return User::factory()->create([
            'two_factor_secret' => (new Google2FA)->generateSecretKey(),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function test_logging_in_with_a_two_factor_enabled_account_redirects_to_the_challenge_instead_of_the_dashboard(): void
    {
        $user = $this->twoFactorUser();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
    }

    public function test_the_challenge_page_redirects_to_login_without_a_pending_login(): void
    {
        $this->get('/two-factor-challenge')->assertRedirect('/login');
    }

    public function test_a_valid_authenticator_code_completes_the_login(): void
    {
        $user = $this->twoFactorUser();
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $code = (new Google2FA)->getCurrentOtp($user->two_factor_secret);
        $response = $this->post('/two-factor-challenge', ['code' => $code]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_an_invalid_code_fails_the_challenge(): void
    {
        $user = $this->twoFactorUser();
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $response = $this->post('/two-factor-challenge', ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_a_valid_recovery_code_completes_the_login_and_is_consumed(): void
    {
        $user = $this->twoFactorUser();
        $user->forceFill([
            'two_factor_recovery_codes' => json_encode(['recovery-code-one', 'recovery-code-two']),
        ])->save();
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $response = $this->post('/two-factor-challenge', ['code' => 'recovery-code-one']);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertSame(['recovery-code-two'], json_decode($user->fresh()->two_factor_recovery_codes, true));
    }

    public function test_a_used_recovery_code_cannot_be_reused(): void
    {
        $user = $this->twoFactorUser();
        $user->forceFill(['two_factor_recovery_codes' => json_encode(['recovery-code-one'])])->save();
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post('/two-factor-challenge', ['code' => 'recovery-code-one']);
        $this->post('/logout');

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $response = $this->post('/two-factor-challenge', ['code' => 'recovery-code-one']);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_repeated_invalid_codes_are_rate_limited(): void
    {
        $user = $this->twoFactorUser();
        RateLimiter::clear('two-factor-challenge:'.$user->id);
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/two-factor-challenge', ['code' => '000000']);
        }

        $response = $this->post('/two-factor-challenge', ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $this->assertStringContainsString('seconds', $response->getSession()->get('errors')->first('code'));
    }
}
