<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDirectorHasTwoFactorEnabled
{
    /**
     * The Director role has full access to Finance, Staff management, and
     * deleting records - the single most damaging account to lose control
     * of. This forces every Director account to finish two-factor setup
     * (via the card on their Profile page) before reaching anything else
     * in the app, rather than leaving it as an easily-skipped opt-in.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isDirector() || $user->hasEnabledTwoFactorAuthentication()) {
            return $next($request);
        }

        return redirect()->route('profile.edit')->with('status', 'two-factor-authentication-required');
    }
}
