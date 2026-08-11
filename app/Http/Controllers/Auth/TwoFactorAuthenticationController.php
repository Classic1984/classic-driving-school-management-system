<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class TwoFactorAuthenticationController extends Controller
{
    public function __construct(protected TwoFactorAuthenticationService $twoFactor) {}

    /**
     * Generate a new, unconfirmed secret and start the setup flow. Any
     * previously confirmed setup is cleared - the user isn't "enabled"
     * again until they confirm a code against this new secret.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->user()->forceFill([
            'two_factor_secret' => $this->twoFactor->generateSecretKey(),
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return Redirect::route('profile.edit')->with('status', 'two-factor-authentication-setup');
    }

    /**
     * Confirm the pending secret with a code from the authenticator app,
     * finishing setup and issuing one-time recovery codes.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $user = $request->user();

        if (! $user->two_factor_secret || ! $this->twoFactor->verify($user->two_factor_secret, $request->string('code')->trim()->toString())) {
            throw ValidationException::withMessages([
                'code' => __('The provided two factor authentication code was invalid.'),
            ]);
        }

        $recoveryCodes = $this->twoFactor->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return Redirect::route('profile.edit')
            ->with('status', 'two-factor-authentication-confirmed')
            ->with('recoveryCodes', $recoveryCodes);
    }

    /**
     * Generate a fresh batch of recovery codes, invalidating the old ones -
     * for when a code has been used up or the old batch may be compromised.
     */
    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->hasEnabledTwoFactorAuthentication(), 400);

        $recoveryCodes = $this->twoFactor->generateRecoveryCodes();

        $user->forceFill(['two_factor_recovery_codes' => json_encode($recoveryCodes)])->save();

        return Redirect::route('profile.edit')
            ->with('status', 'two-factor-recovery-codes-regenerated')
            ->with('recoveryCodes', $recoveryCodes);
    }

    /**
     * Disable two-factor authentication entirely.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return Redirect::route('profile.edit')->with('status', 'two-factor-authentication-disabled');
    }
}
