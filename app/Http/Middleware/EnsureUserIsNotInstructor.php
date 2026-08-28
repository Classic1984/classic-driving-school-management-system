<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotInstructor
{
    /**
     * Blocks an instructor-role account out of the general staff area.
     * Applied to the same top-level "auth" route group every staff page
     * lives under, so an instructor logging in with their phone + PIN can
     * never wander into student records, payments, or anything else
     * beyond their own dedicated instructor pages - without this, the
     * only thing stopping them would be individual permission checks that
     * were never written with an instructor role in mind.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_if($request->user()?->isInstructor(), 403);

        return $next($request);
    }
}
