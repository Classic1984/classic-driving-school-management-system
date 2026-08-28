<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotStudent
{
    /**
     * Blocks a student-role account out of the general staff area, the
     * same way EnsureUserIsNotInstructor blocks an instructor-role
     * account. Applied to the same top-level "auth" route group every
     * staff page lives under, so a student logging in with their phone +
     * PIN can never wander into other students' records, payments, or
     * anything else beyond their own dedicated student pages.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_if($request->user()?->isStudent(), 403);

        return $next($request);
    }
}
