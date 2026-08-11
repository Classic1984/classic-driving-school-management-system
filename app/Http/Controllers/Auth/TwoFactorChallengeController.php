<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function __construct(protected TwoFactorAuthenticationService $twoFactor) {}

    /**
     * Show the two-factor challenge form. Only reachable mid-login, after
     * AuthenticatedSessionController::store() has verified the password
     * and parked the pending user id in the session.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verify the submitted authenticator code (or a one-time recovery
     * code) and complete the login that was started in
     * AuthenticatedSessionController::store().
     */
    public function store(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('login.id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        $this->ensureIsNotRateLimited($user);

        $request->validate(['code' => ['required', 'string']]);

        $code = $request->string('code')->trim()->replace(' ', '')->toString();

        if ($this->twoFactor->verify($user->two_factor_secret, $code) || $this->recoveryCodeMatches($user, $code)) {
            RateLimiter::clear($this->throttleKey($user));

            return $this->login($request, $user);
        }

        RateLimiter::hit($this->throttleKey($user));

        throw ValidationException::withMessages([
            'code' => __('The provided two factor authentication code was invalid.'),
        ]);
    }

    /**
     * Check the code against the user's unused recovery codes, consuming
     * it (one-time use) if it matches.
     */
    protected function recoveryCodeMatches(User $user, string $code): bool
    {
        $codes = json_decode($user->two_factor_recovery_codes ?? '[]', true) ?: [];

        if (! in_array($code, $codes, true)) {
            return false;
        }

        $user->forceFill([
            'two_factor_recovery_codes' => json_encode(array_values(array_diff($codes, [$code]))),
        ])->save();

        return true;
    }

    protected function login(Request $request, User $user): RedirectResponse
    {
        Auth::login($user, (bool) $request->session()->pull('login.remember', false));

        $request->session()->forget('login.id');
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    protected function ensureIsNotRateLimited(User $user): void
    {
        $key = $this->throttleKey($user);

        if (! RateLimiter::tooManyAttempts($key, 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'code' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(User $user): string
    {
        return 'two-factor-challenge:'.$user->id;
    }
}
